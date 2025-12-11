<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Pastikan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Metode tidak diizinkan.';
    header("Location: ../peserta/ubah_password.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];
$oldPassword = $_POST['old_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validasi input
if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
    $_SESSION['error_message'] = 'Semua field harus diisi.';
    header("Location: ../peserta/ubah_password.php");
    exit;
}

// Validasi panjang password
if (strlen($newPassword) < 8) {
    $_SESSION['error_message'] = 'Password baru harus minimal 8 karakter.';
    header("Location: ../peserta/ubah_password.php");
    exit;
}

// Validasi konfirmasi password
if ($newPassword !== $confirmPassword) {
    $_SESSION['error_message'] = 'Password baru dan konfirmasi tidak cocok.';
    header("Location: ../peserta/ubah_password.php");
    exit;
}

// Validasi password baru berbeda dengan lama
if ($oldPassword === $newPassword) {
    $_SESSION['error_message'] = 'Password baru harus berbeda dengan password lama.';
    header("Location: ../peserta/ubah_password.php");
    exit;
}

try {
    // Ambil user saat ini
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        $_SESSION['error_message'] = 'User tidak ditemukan.';
        header("Location: ../peserta/ubah_password.php");
        exit;
    }
    
    // Verifikasi password lama
    $oldPasswordValid = false;
    
    // Cek dengan password_verify (untuk hash)
    if (strlen($user['password']) > 20 && password_verify($oldPassword, $user['password'])) {
        $oldPasswordValid = true;
    } 
    // Cek plain text (legacy)
    elseif ($oldPassword === $user['password']) {
        $oldPasswordValid = true;
    }
    
    if (!$oldPasswordValid) {
        $_SESSION['error_message'] = 'Password lama tidak sesuai.';
        header("Location: ../peserta/ubah_password.php");
        exit;
    }
    
    // Hash password baru
    $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    $sqlUpdate = "UPDATE users SET password = ?, password_updated = 1, is_first_login = 0 WHERE id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    
    if (!$stmtUpdate) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $stmtUpdate->bind_param("si", $hashedNewPassword, $userId);
    
    if (!$stmtUpdate->execute()) {
        throw new Exception('Gagal mengupdate password: ' . $stmtUpdate->error);
    }
    
    $stmtUpdate->close();
    
    // Hapus flag force password change dari session
    unset($_SESSION['force_password_change']);
    
    $_SESSION['success_message'] = 'Password berhasil diubah. Silakan login kembali.';
    
    // Logout dan redirect ke login
    session_destroy();
    header("Location: ../login.php");
    exit;
    
} catch (Exception $e) {
    error_log('Password change error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Terjadi kesalahan server: ' . $e->getMessage();
    header("Location: ../peserta/ubah_password.php");
    exit;
}

$conn->close();
?>
