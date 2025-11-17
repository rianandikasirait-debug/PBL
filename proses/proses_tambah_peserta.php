<?php
session_start();

// 1. MEMANGGIL KONEKSI DATABASE
// Path ini SUDAH BENAR jika file ini ada di web/proses/
require_once __DIR__ . '/../koneksi.php';

// 2. KEAMANAN: Pastikan ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Metode tidak diizinkan.';
    // Path ini SUDAH BENAR
    header('Location: ../admin/tambah_peserta_admin.php');
    exit;
}

$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// --- PERBAIKAN 1 & 2 DI SINI ---
if (empty($nama) || empty($email) || empty($username) || empty($password)) {
    // PERBAIKAN 1: 'error message' diubah jadi 'error_message' (pakai underscore)
    $_SESSION['error_message'] = 'Semua field harus diisi.';
    
    // PERBAIKAN 2: Tambahkan header redirect yang hilang di sini
    header('Location: ../admin/tambah_peserta_admin.php');
    exit;
}
// ------------------------------------

if (strlen($password) < 8) {
    $_SESSION['error_message'] = 'Password harus minimal 8 karakter.';
    // Path ini SUDAH BENAR
    header('Location: ../admin/tambah_peserta_admin.php');
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $sql_check = "SELECT id FROM users WHERE email = ? OR username = ?";
    $stmt_check = $conn->prepare($sql_check);
    
    if (!$stmt_check) {
        throw new Exception("Kesalahan server (prepare check): " . $conn->error);
    }
    
    $stmt_check->bind_param("ss", $email, $username);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Ada duplikat
        $_SESSION['error_message'] = 'Email atau Username sudah terdaftar. Silakan gunakan yang lain.';
        $stmt_check->close();
        // Path ini SUDAH BENAR
        header('Location: ../admin/tambah_peserta_admin.php');
        exit;
    }
    $stmt_check->close();

    // 7. INSERT DATA KE DATABASE
    $sql_insert = "INSERT INTO users (nama, email, username, password, role) VALUES (?, ?, ?, ?, 'peserta')";
    $stmt_insert = $conn->prepare($sql_insert);

    if (!$stmt_insert) {
        throw new Exception("Kesalahan server (prepare insert): " . $conn->error);
    }
                                    
    // "ssss" berarti 4 variabel string
    $stmt_insert->bind_param("ssss", $nama, $email, $username, $hashed_password);

    // 8. EKSEKUSI DAN BERI FEEDBACK
    if ($stmt_insert->execute()) {
        // Berhasil!
        $_SESSION['success_message'] = "Berhasil menambahkan peserta baru: $nama ($username)";
    } else {
        // Gagal
        throw new Exception("Gagal mengeksekusi query: " . $stmt_insert->error);
    }

    $stmt_insert->close();

} catch (Exception $e) {
    // Tangani error
    error_log($e->getMessage()); // Catat error untuk debugging
    $_SESSION['error_message'] = 'Terjadi kesalahan pada server. Coba lagi nanti.';
}

// 9. TUTUP KONEKSI DAN REDIRECT
$conn->close();
// Path ini SUDAH BENAR
header('Location: ../admin/tambah_peserta_admin.php');
exit;
?>