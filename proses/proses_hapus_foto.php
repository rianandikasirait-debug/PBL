<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

if (!isset($_POST['id_user'])) {
    header("Location: ../admin/profile.php");
    exit;
}

$id_user = (int) $_POST['id_user'];

// Ambil foto lama
$sql = "SELECT foto FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && !empty($user['foto'])) {
    $filePath = "../uploads/" . $user['foto'];

    // Hapus file jika ada
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Set foto jadi NULL
    $update = $conn->prepare("UPDATE users SET foto = NULL WHERE id = ?");
    $update->bind_param("i", $id_user);
    $update->execute();
}

$_SESSION['success_message'] = "Foto profil berhasil dihapus.";
header("Location: ../admin/edit_profile_admin.php");
exit;
?>
