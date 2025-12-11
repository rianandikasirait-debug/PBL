<?php
session_start();
// Mulai session untuk menyimpan error/login state dan data user jika berhasil login

// Sesuaikan path koneksi
require_once __DIR__ . '/../koneksi.php';
// Sertakan file koneksi database (variabel $conn diasumsikan tersedia)

// Pastikan request menggunakan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Jika bukan POST, simpan pesan error dan redirect ke halaman login
    $_SESSION['login_error'] = 'Metode tidak diperbolehkan.';
    header('Location: ../login.php');
    exit;
}

// Ambil input dari form login (trim untuk email/nik)
$emailOrNik = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi sederhana: email/nik dan password harus diisi
if ($emailOrNik === '' || $password === '') {
    $_SESSION['login_error'] = 'Email/NIK dan password harus diisi.';
    header('Location: ../login.php');
    exit;
}

// =====================================================
// CEK LOGIN DENGAN EMAIL ATAU NIK
// =====================================================
// Coba cari user berdasarkan EMAIL terlebih dahulu
$sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    // Jika prepare gagal, catat ke log server dan beri pesan generik ke user
    error_log('Prepare failed: ' . $conn->error);
    $_SESSION['login_error'] = 'Kesalahan server.';
    header('Location: ../login.php');
    exit;
}

// Bind parameter dan eksekusi
$stmt->bind_param("s", $emailOrNik);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Jika tidak ketemu email, coba cari berdasarkan NIK
if (!$user) {
    $sql = "SELECT * FROM users WHERE nik = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log('Prepare failed: ' . $conn->error);
        $_SESSION['login_error'] = 'Kesalahan server.';
        header('Location: ../login.php');
        exit;
    }
    
    // Bind NIK (convert ke int karena field nik adalah int)
    $nikInt = intval($emailOrNik);
    $stmt->bind_param("i", $nikInt);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// Jika user tetap tidak ditemukan baik dari email maupun nik
if (!$user) {
    $_SESSION['login_error'] = 'Email/NIK tidak ditemukan.';
    header('Location: ../login.php');
    exit;
}

// =====================================================
// VALIDASI PASSWORD
// =====================================================
// Flag autentikasi awal false sampai terverifikasi
$authenticated = false;

// Kasus 1: password SUDAH disimpan dalam bentuk hash
// Deteksi kasar: hash biasanya lebih panjang (>20 karakter)
if (!empty($user['password']) && strlen($user['password']) > 20) {

    // Verifikasi password menggunakan password_verify (hash-aware)
    if (password_verify($password, $user['password'])) {
        $authenticated = true;

        // Jika hash menggunakan algoritma lama, rehash agar selalu pakai algoritma terbaru
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $u_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($u_stmt) {
                // Simpan hash baru ke database untuk peningkatan keamanan
                $u_stmt->bind_param('si', $newHash, $user['id']);
                $u_stmt->execute();
                $u_stmt->close();
            }
        }
    }
}
// Kasus 2: password lama masih plain text → migrasi otomatis
elseif ($password === $user['password']) {

    // Jika password sama dengan yang tersimpan (plain text), anggap terautentikasi
    $authenticated = true;

    // Migrasi segera: hash password plain text dan simpan ke DB
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $u_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    if ($u_stmt) {
        $u_stmt->bind_param('si', $newHash, $user['id']);
        $u_stmt->execute();
        $u_stmt->close();
    }
}

// Jika autentikasi gagal, set error dan redirect kembali ke login
if (!$authenticated) {
    $_SESSION['login_error'] = 'Password salah.';
    header('Location: ../login.php');
    exit;
}

// =====================================================
// CHECK PASSWORD PERTAMA KALI LOGIN - DISABLED
// =====================================================
// FITUR INI DINONAKTIFKAN - Peserta langsung ke dashboard
// Jika ingin mengaktifkan kembali, uncomment kode di bawah ini

/*
// Jika password masih default (=== NIK) dan belum pernah diubah
$isFirstLogin = (isset($user['is_first_login']) && $user['is_first_login'] == 1) || 
                (isset($user['password_updated']) && $user['password_updated'] == 0);

// Password default = NIK user
$passwordDefault = (string)$user['nik'];

// Cek apakah password yang digunakan adalah password default
$isUsingDefaultPassword = ($password === $passwordDefault);

// Jika peserta, cek apakah ini login pertama atau menggunakan password default
if (($user['role'] === 'peserta' || $user['role'] === NULL) && $isFirstLogin && $isUsingDefaultPassword) {
    // Regenerate session id terlebih dahulu
    session_regenerate_id(true);
    
    // Simpan data user sementara untuk redirect ke ubah password
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['nama'] ?? $user['nik'] ?? '';
    $_SESSION['user_role'] = $user['role'] ?? 'peserta';
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_foto']  = $user['foto'] ?? '';
    $_SESSION['force_password_change'] = true; // Flag untuk force change password
    
    // Redirect ke halaman ubah password
    header('Location: ../peserta/ubah_password.php');
    exit;
}
*/

// =====================================================
// LOGIN BERHASIL - BISA LANGSUNG KE DASHBOARD
// =====================================================
// Regenerate session id untuk mencegah session fixation
session_regenerate_id(true);

// Simpan data user ke session untuk dipakai sepanjang sesi
$_SESSION['user_id']   = $user['id'];
$_SESSION['user_name'] = $user['nama'] ?? $user['nik'] ?? '';
$_SESSION['user_role'] = $user['role'] ?? 'peserta';
$_SESSION['user_email'] = $user['email'];              // ➜ TAMBAHAN WAJIB
$_SESSION['user_foto']  = $user['foto'] ?? '';

// Redirect berdasarkan role user
if (trim(strtolower($_SESSION['user_role'])) === 'admin') {
    header('Location: ../admin/dashboard_admin.php');
    exit;
} else {
    header('Location: ../peserta/dashboard_peserta.php');
    exit;
}
?>
