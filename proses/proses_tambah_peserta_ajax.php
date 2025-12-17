<?php
/**
 * AJAX Handler untuk Tambah Peserta dari Modal
 * 
 * Endpoint ini digunakan untuk menambah peserta baru via AJAX
 * dari halaman Tambah Notulen dan Edit Notulen
 */
session_start();

require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/whatsapp.php';

// Set response header JSON
header('Content-Type: application/json');

// Pastikan request menggunakan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']);
    exit;
}

// Ambil dan bersihkan input dari form
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$nik = trim($_POST['nik'] ?? '');
$nomor_whatsapp = trim($_POST['nomor_whatsapp'] ?? '');

// Validasi wajib: nama, email, nik harus terisi
if (empty($nama) || empty($email) || empty($nik)) {
    echo json_encode(['success' => false, 'message' => 'Nama, Email, dan NIK wajib diisi']);
    exit;
}

// Validasi format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
    exit;
}

// Password default = NIK (akan di-hash)
$password = $nik;

// Validasi nomor WhatsApp jika diisi
if (!empty($nomor_whatsapp)) {
    $waManager = new WhatsAppManager($conn);
    if (!$waManager->isValidPhoneNumber($waManager->normalizePhoneNumber($nomor_whatsapp))) {
        echo json_encode(['success' => false, 'message' => 'Format nomor WhatsApp tidak valid. Gunakan format: 62xxxxxxxxxx atau 0xxxxxxxxxx']);
        exit;
    }
}

// Enkripsi password default (=NIK) dengan hash yang aman
$hashed = password_hash($password, PASSWORD_DEFAULT);

try {
    // Cek apakah email atau NIK sudah terdaftar
    $sql_check = "SELECT id FROM users WHERE email = ? OR nik = ?";
    $stmt_check = $conn->prepare($sql_check);
    if (!$stmt_check) throw new Exception($conn->error);

    $stmt_check->bind_param("ss", $email, $nik);
    $stmt_check->execute();
    $res = $stmt_check->get_result();

    if ($res->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email atau NIK sudah terdaftar']);
        $stmt_check->close();
        exit;
    }
    $stmt_check->close();

    // Insert peserta baru ke table users
    $sql_insert = "INSERT INTO users (nama, email, nik, password, nomor_whatsapp, role, password_updated, is_first_login) 
                   VALUES (?, ?, ?, ?, ?, 'peserta', 0, 1)";
    $stmt = $conn->prepare($sql_insert);
    if (!$stmt) throw new Exception($conn->error);

    $stmt->bind_param("sssss", $nama, $email, $nik, $hashed, $nomor_whatsapp);

    if ($stmt->execute()) {
        $userId = $conn->insert_id;
        
        // Kirim pesan WhatsApp jika nomor diisi
        $waResult = ['success' => false];
        if (!empty($nomor_whatsapp) && defined('SEND_WA_ON_PARTICIPANT_ADD') && SEND_WA_ON_PARTICIPANT_ADD === true) {
            $waManager = new WhatsAppManager($conn);
            
            // Format pesan dengan informasi akun default
            $pesan = "\xE2\x9C\xA8 *Halo, Akun SmartNote Siap!* \xE2\x9C\xA8\n\n";
            $pesan .= "Berikut akses masuk Anda:\n";
            $pesan .= "\xF0\x9F\x93\xA7 Email: {$email}\n";
            $pesan .= "\xF0\x9F\x94\x91 NIK: {$nik}\n\n";
            $pesan .= "\xF0\x9F\x94\x92 *Password default Anda adalah NIK: {$nik}. Mohon segera ganti password setelah login ya!*\n\n";
            $pesan .= "_Admin SmartNote_ \xF0\x9F\x93\x9D";
            
            $waResult = $waManager->sendMessage($userId, $nomor_whatsapp, $pesan);
        }
        
        // Return success dengan data peserta baru
        echo json_encode([
            'success' => true,
            'message' => 'Peserta berhasil ditambahkan',
            'data' => [
                'id' => $userId,
                'nama' => $nama,
                'email' => $email
            ],
            'wa_sent' => $waResult['success'] ?? false
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data']);
    }
    
    $stmt->close();

} catch (Exception $e) {
    error_log('Error tambah peserta AJAX: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server: ' . $e->getMessage()]);
}

$conn->close();
?>
