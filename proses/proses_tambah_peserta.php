<?php
// proses_tambah_peserta.php
session_start(); 
// Memulai session untuk menyimpan pesan sukses/error

require_once __DIR__ . '/../koneksi.php'; 
require_once __DIR__ . '/../includes/whatsapp.php';
// Menghubungkan ke database dan WhatsApp manager

// Pastikan request berasal dari POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Jika bukan POST, tolak permintaan dan kembali ke form
    $_SESSION['error_message'] = 'Metode tidak diizinkan.';
    header('Location: ../admin/tambah_peserta_admin.php');
    exit;
}

// Ambil dan bersihkan input dari form
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$nik = trim($_POST['nik'] ?? '');
$nomor_whatsapp = trim($_POST['nomor_whatsapp'] ?? '');

// Validasi wajib: nama, email, nik harus terisi
// PASSWORD OTOMATIS = NIK (tidak perlu input manual)
if (empty($nama) || empty($email) || empty($nik)) {
    $_SESSION['error_message'] = 'Semua field harus diisi (nama, email, nik).';
    header('Location: ../admin/tambah_peserta_admin.php');
    exit;
}

// Password default = NIK (akan di-hash nanti)
$password = $nik;

// Validasi nomor WhatsApp jika diisi
if (!empty($nomor_whatsapp)) {
    $waManager = new WhatsAppManager($conn);
    if (!$waManager->isValidPhoneNumber($waManager->normalizePhoneNumber($nomor_whatsapp))) {
        $_SESSION['error_message'] = 'Format nomor WhatsApp tidak valid. Gunakan format: 62xxxxxxxxxx atau 0xxxxxxxxxx';
        header('Location: ../admin/tambah_peserta_admin.php');
        exit;
    }
}

// Enkripsi password default (=NIK) dengan hash yang aman
$hashed = password_hash($password, PASSWORD_DEFAULT);

try {

    // ---------------------------------------------------
    // CEK APAKAH EMAIL ATAU NIK SUDAH TERDAFTAR SEBELUMNYA
    // ---------------------------------------------------
    $sql_check = "SELECT id FROM users WHERE email = ? OR nik = ?";
    $stmt_check = $conn->prepare($sql_check);
    if (!$stmt_check) throw new Exception($conn->error);

    // Bind parameter email dan nik
    $stmt_check->bind_param("ss", $email, $nik);
    $stmt_check->execute();
    $res = $stmt_check->get_result();

    // Jika sudah ada user dengan email/nik tersebut → tolak
    if ($res->num_rows > 0) {
        $_SESSION['error_message'] = 'Email atau nik sudah terdaftar.';
        $stmt_check->close();
        header('Location: ../admin/tambah_peserta_admin.php');
        exit;
    }
    $stmt_check->close();

    // ---------------------------------------------------
    // INSERT PESERTA BARU KE TABLE USERS
    // ---------------------------------------------------
    // password_updated = 0 karena ini password pertama (default)
    // is_first_login = 1 untuk force peserta ubah password saat login
    $sql_insert = "INSERT INTO users (nama, email, nik, password, nomor_whatsapp, role, password_updated, is_first_login) 
                   VALUES (?, ?, ?, ?, ?, 'peserta', 0, 1)";
    $stmt = $conn->prepare($sql_insert);
    if (!$stmt) throw new Exception($conn->error);

    // Bind input user + password hash
    $stmt->bind_param("sssss", $nama, $email, $nik, $hashed, $nomor_whatsapp);

    // Eksekusi insert
    if ($stmt->execute()) {
        $userId = $conn->insert_id;
        
        // ---------------------------------------------------
        // KIRIM PESAN WHATSAPP JIKA NOMOR DIISI
        // ---------------------------------------------------
        if (!empty($nomor_whatsapp)) {
            $waManager = new WhatsAppManager($conn);
            
            // Format pesan dengan informasi akun default - friendly & professional
            $pesan = "🎉 *Selamat Datang di SmartNote!*\n\n";
            $pesan .= "Halo! Akun Anda telah berhasil dibuat. Berikut adalah informasi login Anda:\n\n";
            $pesan .= "━━━━━━━━━━━━━━━━━━━\n";
            $pesan .= "👤 *Informasi Akun*\n";
            $pesan .= "📧 Email: {$email}\n";
            $pesan .= "🔑 Password: {$nik}\n";
            $pesan .= "━━━━━━━━━━━━━━━━━━━\n\n";
            $pesan .= "🔒 *Langkah Keamanan Penting:*\n";
            $pesan .= "✅ Login ke sistem menggunakan kredensial di atas\n";
            $pesan .= "✅ Segera ganti password dengan yang lebih aman\n";
            $pesan .= "✅ Jangan bagikan informasi ini kepada siapapun\n\n";
            $pesan .= "💡 *Tips:* Gunakan kombinasi huruf besar, kecil, angka, dan simbol untuk password yang kuat.\n\n";
            $pesan .= "Jika ada pertanyaan, jangan ragu untuk menghubungi admin.\n\n";
            $pesan .= "Salam hangat,\n";
            $pesan .= "Tim SmartNote Notulen 📝";
            
            
            $waResult = $waManager->sendMessage($userId, $nomor_whatsapp, $pesan);
            
            // Generate WhatsApp link
            $waLink = $waManager->generateWhatsAppLink($nomor_whatsapp, $pesan);
            
            // Simpan link WA ke session untuk dibuka di halaman berikutnya
            if ($waLink) {
                $_SESSION['wa_link'] = $waLink;
                $_SESSION['wa_nomor'] = $nomor_whatsapp;
            }
            
            // Catat hasil pengiriman WA ke session (opsional)
            if ($waResult['success']) {
                $_SESSION['wa_message'] = "✅ Pesan WhatsApp berhasil disiapkan untuk {$nomor_whatsapp}";
            } else {
                $_SESSION['wa_warning'] = "⚠️ Peserta berhasil ditambahkan, tapi pengiriman WhatsApp gagal: " . $waResult['message'];
            }
        }
        
        // Simpan pesan sukses ke session
        $_SESSION['success_message'] = "Berhasil menambahkan peserta baru: {$nama} ({$nik})";
        
    } else {
        // Jika gagal eksekusi SQL insert
        $_SESSION['error_message'] = 'Gagal menyimpan data.';
        $stmt->close();
        header('Location: ../admin/tambah_peserta_admin.php');
        exit;
    }

    $stmt->close();

} catch (Exception $e) {
    // Jika error tak terduga terjadi (prepare atau eksekusi gagal)
    error_log($e->getMessage()); 
    // Mencatat error ke server log

    $_SESSION['error_message'] = 'Terjadi kesalahan server: ' . $e->getMessage();
    header('Location: ../admin/tambah_peserta_admin.php');
    exit;
}

// Tutup koneksi DB
$conn->close();

// Setelah selesai, redirect ke halaman kelola rapat
header('Location: ../admin/kelola_rapat_admin.php');
exit;

