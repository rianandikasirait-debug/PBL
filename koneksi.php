<?php
// Set timezone ke WIB (Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi koneksi database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "notulen";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set MySQL timezone ke Asia/Jakarta (WIB, UTC+7)
mysqli_query($conn, "SET time_zone = '+07:00'");
?>