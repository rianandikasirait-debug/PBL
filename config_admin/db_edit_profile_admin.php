<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../koneksi.php';

// Cek Login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['user_id'];

// Ambil data terbaru dari database
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

//update edit profile
if (isset($updateBerhasil) && $updateBerhasil) {
    $_SESSION['success_message'] = 'Profil berhasil diperbarui';
    header('Location: ../admin/profile.php');
    exit;
}


$foto_path = !empty($user['foto']) ? '../file/' . $user['foto'] : '';
$foto_profile = (!empty($foto_path) && file_exists($foto_path)) ? $foto_path : '';
?>
