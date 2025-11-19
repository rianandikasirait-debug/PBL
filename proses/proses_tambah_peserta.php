<?php
// proses_tambah_peserta.php
session_start();
require_once __DIR__ . '/../koneksi.php'; // sesuaikan path

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Metode tidak diizinkan.';
    header('Location: ../admin/tambah_peserta_admin.php');
    exit;
}

$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$nik = trim($_POST['nik'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($nama) || empty($email) || empty($nik) || empty($password)) {
    $_SESSION['error_message'] = 'Semua field harus diisi.';
    header('Location: ../admin/tambah_peserta_admin.php');
    exit;
}

if (strlen($password) < 8) {
    $_SESSION['error_message'] = 'Password harus minimal 8 karakter.';
    header('Location: ../admin/tambah_peserta_admin.php');
    exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

try {
    $sql_check = "SELECT id FROM users WHERE email = ? OR nik = ?";
    $stmt_check = $conn->prepare($sql_check);
    if (!$stmt_check) throw new Exception($conn->error);
    $stmt_check->bind_param("ss", $email, $nik);
    $stmt_check->execute();
    $res = $stmt_check->get_result();
    if ($res->num_rows > 0) {
        $_SESSION['error_message'] = 'Email atau nik sudah terdaftar.';
        $stmt_check->close();
        header('Location: ../admin/tambah_peserta_admin.php');
        exit;
    }
    $stmt_check->close();

    $sql_insert = "INSERT INTO users (nama, email, nik, password, role) VALUES (?, ?, ?, ?, 'peserta')";
    $stmt = $conn->prepare($sql_insert);
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("ssss", $nama, $email, $nik, $hashed);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Berhasil menambahkan peserta baru: $nama ($nik)";
    } else {
        $_SESSION['error_message'] = 'Gagal menyimpan data.';
    }
    $stmt->close();

} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error_message'] = 'Terjadi kesalahan server. Coba lagi nanti.';
}

$conn->close();
header('Location: ../admin/kelola_rapat_admin.php');
exit;
