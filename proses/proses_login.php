<?php
session_start();

// Sesuaikan path koneksi
require_once __DIR__ . '/../koneksi.php';

// Pastikan lewat POST
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
    $_SESSION['login_error'] = 'Email dan password harus diisi.';
    header('Location: ../login.php');
    exit;
}

// Ambil user berdasarkan email
$sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log('Prepare failed: ' . $conn->error);
    $_SESSION['login_error'] = 'Kesalahan server.';
    header('Location: ../login.php');
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['login_error'] = 'Email tidak ditemukan.';
    header('Location: ../login.php');
    exit;
}

// --------------------------------------
//          LOGIN VALIDATION FIXED
// --------------------------------------
$authenticated = false;

// Kasus 1: password SUDAH disimpan dalam bentuk hash
// (hash panjang, biasanya > 20 karakter)
if (!empty($user['password']) && strlen($user['password']) > 20) {

    if (password_verify($password, $user['password'])) {
        $authenticated = true;

        // Rehash jika perlu
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $u_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($u_stmt) {
                $u_stmt->bind_param('si', $newHash, $user['id']);
                $u_stmt->execute();
                $u_stmt->close();
            }
        }
    }
}
// Kasus 2: password lama masih plain text → migrasi otomatis
elseif ($password === $user['password']) {

    $authenticated = true;

    // Migrasi password ke hash agar lebih aman
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $u_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    if ($u_stmt) {
        $u_stmt->bind_param('si', $newHash, $user['id']);
        $u_stmt->execute();
        $u_stmt->close();
    }
}

// Jika gagal login
if (!$authenticated) {
    $_SESSION['login_error'] = 'Password salah.';
    header('Location: ../login.php');
    exit;
}

// --------------------------------------
//        LOGIN BERHASIL
// --------------------------------------
session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['nama'] ?? $user['username'] ?? '';
$_SESSION['user_role'] = $user['role'] ?? 'peserta';

// Redirect berdasarkan role
if (trim(strtolower($_SESSION['user_role'])) === 'admin') {
    header('Location: ../admin/dashboard_admin.php');
    exit;
} else {
    header('Location: ../peserta/dashboard_peserta.php');
    exit;
}
?>
<?php
session_start();

// Sesuaikan path koneksi
require_once __DIR__ . '/../koneksi.php';

// Pastikan lewat POST
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
    $_SESSION['login_error'] = 'Email dan password harus diisi.';
    header('Location: ../login.php');
    exit;
}

// Ambil user berdasarkan email
$sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log('Prepare failed: ' . $conn->error);
    $_SESSION['login_error'] = 'Kesalahan server.';
    header('Location: ../login.php');
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['login_error'] = 'Email tidak ditemukan.';
    header('Location: ../login.php');
    exit;
}

// --------------------------------------
//          LOGIN VALIDATION FIXED
// --------------------------------------
$authenticated = false;

// Kasus 1: password SUDAH disimpan dalam bentuk hash
// (hash panjang, biasanya > 20 karakter)
if (!empty($user['password']) && strlen($user['password']) > 20) {

    if (password_verify($password, $user['password'])) {
        $authenticated = true;

        // Rehash jika perlu
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $u_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($u_stmt) {
                $u_stmt->bind_param('si', $newHash, $user['id']);
                $u_stmt->execute();
                $u_stmt->close();
            }
        }
    }
}
// Kasus 2: password lama masih plain text → migrasi otomatis
elseif ($password === $user['password']) {

    $authenticated = true;

    // Migrasi password ke hash agar lebih aman
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $u_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    if ($u_stmt) {
        $u_stmt->bind_param('si', $newHash, $user['id']);
        $u_stmt->execute();
        $u_stmt->close();
    }
}

// Jika gagal login
if (!$authenticated) {
    $_SESSION['login_error'] = 'Password salah.';
    header('Location: ../login.php');
    exit;
}

// --------------------------------------
//        LOGIN BERHASIL
// --------------------------------------
session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['nama'] ?? $user['username'] ?? '';
$_SESSION['user_role'] = $user['role'] ?? 'peserta';

// Redirect berdasarkan role
if (trim(strtolower($_SESSION['user_role'])) === 'admin') {
    header('Location: ../admin/dashboard_admin.php');
    exit;
} else {
    header('Location: ../peserta/dashboard_peserta.php');
    exit;
}
?>
