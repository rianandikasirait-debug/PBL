<?php
session_start();
require_once __DIR__ . '/koneksi.php';

// Pastikan method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['login_error'] = 'Metode tidak diperbolehkan.';
    header('Location: ../login.php');
    exit;
}

// Ambil input
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi sederhana
if ($email === '' || $password === '') {
    $_SESSION['login_error'] = 'Email dan password wajib diisi.';
    header('Location: ../login.php');
    exit;
}

// Ambil user berdasar email
$sql = "SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    $_SESSION['login_error'] = 'Kesalahan server (prepare).';
    header('Location: ../login.php');
    exit;
}
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['login_error'] = 'Email atau password salah.';
    header('Location: ../login.php');
    exit;
}

// Gunakan password_verify (mengasumsikan password di DB adalah hasil password_hash)
if (!password_verify($password, $user['password'])) {
    $_SESSION['login_error'] = 'Email atau password salah.';
    header('Location: ../login.php');
    exit;
}

// Login sukses
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];

// Redirect berdasarkan role (sesuaikan path dashboardmu)
if ($user['role'] === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit;
} else {
    header('Location: ../peserta/dashboard.php');
    exit;
}
