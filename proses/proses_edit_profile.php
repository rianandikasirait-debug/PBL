<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_SESSION['user_id'];
$nama = trim($_POST['nama']);
$password_baru = $_POST['password_baru'] ?? '';
$konfirmasi = $_POST['password_konfirmasi'] ?? '';
$foto_baru = null;

// Validasi password baru
if (!empty($password_baru)) {
    if ($password_baru !== $konfirmasi) {
        $_SESSION['error_message'] = "Konfirmasi password tidak cocok.";
        header("Location: ../admin/edit_profile_admin.php");
        exit;
    }
}

// Upload Foto
if (!empty($_FILES['foto']['name'])) {
    $file = $_FILES['foto'];
    $namaFile = time() . "_" . $file['name'];
    move_uploaded_file($file['tmp_name'], "../file/$namaFile");
    $foto_baru = $namaFile;

    // update foto di database
    $stmt = $conn->prepare("UPDATE users SET foto=? WHERE id=?");
    $stmt->bind_param("si", $foto_baru, $id);
    $stmt->execute();

    $_SESSION['user_foto'] = $foto_baru; // sinkron session
}

// Update nama
$stmt = $conn->prepare("UPDATE users SET nama=? WHERE id=?");
$stmt->bind_param("si", $nama, $id);
$stmt->execute();

$_SESSION['user_name'] = $nama;

// Update password baru
if (!empty($password_baru)) {
    $hash = password_hash($password_baru, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $hash, $id);
    $stmt->execute();
}

$_SESSION['success_message'] = "Profil berhasil diperbarui!";
header("Location: ../admin/profile.php");
exit;
