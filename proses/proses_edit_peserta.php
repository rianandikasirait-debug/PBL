<?php
// proses_edit_peserta.php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/whatsapp.php';

header('Content-Type: application/json');

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak valid']);
    exit;
}

// Cek sesi login admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Ambil input
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$nik = trim($_POST['nik'] ?? '');
$whatsapp = trim($_POST['nomor_whatsapp'] ?? '');
$reset_password = isset($_POST['reset_password']) && $_POST['reset_password'] == '1';

if ($id <= 0 || empty($nama) || empty($email) || empty($nik)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

// Ambil data lama untuk perbandingan
$sqlOld = "SELECT nama, email, nik, nomor_whatsapp FROM users WHERE id = ?";
$stmtOld = $conn->prepare($sqlOld);
$stmtOld->bind_param("i", $id);
$stmtOld->execute();
$resOld = $stmtOld->get_result();
$oldData = $resOld->fetch_assoc();
$stmtOld->close();

if (!$oldData) {
    echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
    exit;
}

// Validasi Unik (Email/NIK tidak boleh sama dengan user LAIN)
$sqlCheck = "SELECT id FROM users WHERE (email = ? OR nik = ?) AND id != ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("ssi", $email, $nik, $id);
$stmtCheck->execute();
$resCheck = $stmtCheck->get_result();

if ($resCheck->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email atau NIK sudah digunakan pengguna lain.']);
    exit;
}
$stmtCheck->close();

try {
    // Siapkan query update dasar
    $query = "UPDATE users SET nama = ?, email = ?, nik = ?, nomor_whatsapp = ? WHERE id = ?";
    
    // Jika reset password dicentang, update juga passwordnya
    if ($reset_password) {
        $newPassword = password_hash($nik, PASSWORD_DEFAULT); // Reset ke NIK
        $query = "UPDATE users SET nama = ?, email = ?, nik = ?, nomor_whatsapp = ?, password = ? WHERE id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $nama, $email, $nik, $whatsapp, $newPassword, $id);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $nama, $email, $nik, $whatsapp, $id);
    }

    if ($stmt->execute()) {
        $waErrors = [];
        $waSent = [];
        
        // Tentukan nomor WA tujuan (prioritaskan nomor baru jika ada)
        $targetWa = !empty($whatsapp) ? $whatsapp : $oldData['nomor_whatsapp'];
        
        // Cek apakah ada perubahan NIK atau Email
        $nikChanged = ($oldData['nik'] !== $nik);
        $emailChanged = ($oldData['email'] !== $email);
        $waChanged = ($oldData['nomor_whatsapp'] !== $whatsapp && !empty($whatsapp));
        
        // Kirim notifikasi WA jika ada nomor dan ada perubahan
        if (!empty($targetWa)) {
            $waManager = new WhatsAppManager($conn);
            
            // 1. Jika ada perubahan NIK atau Email
            if ($nikChanged || $emailChanged) {
                $pesan = "\xE2\x9C\x8F\xEF\xB8\x8F *Akun SmartNote Diperbarui* \xE2\x9C\x8F\xEF\xB8\x8F\n\n";
                $pesan .= "Halo {$nama}, akun Anda telah diperbarui oleh Notulis.\n\n";
                $pesan .= "Data akun terbaru:\n";
                $pesan .= "\xF0\x9F\x93\xA7 Email: {$email}\n";
                $pesan .= "\xF0\x9F\x94\x91 NIK: {$nik}\n\n";
                $pesan .= "_Notulis SmartNote_ \xF0\x9F\x93\x9D";
                
                $result = $waManager->sendMessage($id, $targetWa, $pesan);
                if ($result['success']) {
                    $waSent[] = 'Data akun diperbarui';
                } else {
                    $waErrors[] = 'Gagal kirim notifikasi perubahan data';
                }
            }
            
            // 2. Jika reset password
            if ($reset_password) {
                $pesan = "\xF0\x9F\x94\x92 *Password Direset* \xF0\x9F\x94\x92\n\n";
                $pesan .= "Halo {$nama}, password akun SmartNote Anda telah direset oleh Notulis.\n\n";
                $pesan .= "Password baru Anda adalah NIK:\n";
                $pesan .= "\xF0\x9F\x94\x91 Password: {$nik}\n\n";
                $pesan .= "\xE2\x9A\xA0\xEF\xB8\x8F *Segera ganti password setelah login!*\n\n";
                $pesan .= "_Notulis SmartNote_ \xF0\x9F\x93\x9D";
                
                $result = $waManager->sendMessage($id, $targetWa, $pesan);
                if ($result['success']) {
                    $waSent[] = 'Password reset';
                } else {
                    $waErrors[] = 'Gagal kirim notifikasi reset password';
                }
            }
            
            // 3. Jika nomor WA berubah, kirim ke nomor baru
            if ($waChanged) {
                $pesan = "\xE2\x9C\x85 *Nomor WhatsApp Terdaftar* \xE2\x9C\x85\n\n";
                $pesan .= "Halo {$nama}, nomor WhatsApp Anda berhasil diperbarui di SmartNote.\n\n";
                $pesan .= "Data akun Anda:\n";
                $pesan .= "\xF0\x9F\x93\xA7 Email: {$email}\n";
                $pesan .= "\xF0\x9F\x94\x91 NIK: {$nik}\n\n";
                $pesan .= "Anda akan menerima notifikasi di nomor ini.\n\n";
                $pesan .= "_Notulis SmartNote_ \xF0\x9F\x93\x9D";
                
                $result = $waManager->sendMessage($id, $whatsapp, $pesan);
                if ($result['success']) {
                    $waSent[] = 'Nomor WA baru terdaftar';
                } else {
                    $waErrors[] = 'Gagal kirim ke nomor WA baru';
                }
            }
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Data pengguna berhasil diperbarui.',
            'wa_sent' => $waSent,
            'wa_errors' => $waErrors
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui database.']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server.']);
}

$conn->close();

