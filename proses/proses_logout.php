<?php
session_start(); 
// Mulai sesi agar bisa menghapusnya

// Hapus semua data sesi
$_SESSION = [];

// Hapus cookie sesi jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000, 
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Hancurkan sesi di server
session_destroy();

// (Opsional) Kirim pesan agar login.php bisa menampilkan notifikasi
session_start();
$_SESSION['success_message'] = 'Anda berhasil logout.';

// Redirect ke halaman login
header("Location: ../login.php");
exit;
