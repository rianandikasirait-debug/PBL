<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Cek Login
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Sesi habis, silakan login kembali.';
    echo json_encode($response);
    exit;
}

$id = $_SESSION['user_id'];
$nama = trim($_POST['nama'] ?? '');
$nomor_whatsapp = trim($_POST['nomor_whatsapp'] ?? '');
$password_baru = $_POST['password_baru'] ?? '';
$konfirmasi = $_POST['password_konfirmasi'] ?? '';

// Validasi Nama
if (empty($nama)) {
    $response['message'] = 'Nama tidak boleh kosong.';
    echo json_encode($response);
    exit;
}

// Validasi Password
if (!empty($password_baru)) {
    if ($password_baru !== $konfirmasi) {
        $response['message'] = 'Konfirmasi password tidak cocok.';
        echo json_encode($response);
        exit;
    }
    // Update Password
    $hash = password_hash($password_baru, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $hash, $id);
    $stmt->execute();
}

// Update Nama dan Nomor HP
$stmt = $conn->prepare("UPDATE users SET nama=?, nomor_whatsapp=? WHERE id=?");
$stmt->bind_param("ssi", $nama, $nomor_whatsapp, $id);
if ($stmt->execute()) {
    $_SESSION['user_name'] = $nama;
} else {
    $response['message'] = 'Gagal memperbarui profil.';
    echo json_encode($response);
    exit;
}

// Handle Delete Photo
if (isset($_POST['delete_photo']) && $_POST['delete_photo'] == '1') {
    $stmt_old = $conn->prepare("SELECT foto FROM users WHERE id=?");
    $stmt_old->bind_param("i", $id);
    $stmt_old->execute();
    $res_old = $stmt_old->get_result();
    if ($row_old = $res_old->fetch_assoc()) {
        $old_photo = $row_old['foto'];
        if ($old_photo && $old_photo !== 'user.jpg' && file_exists(__DIR__ . '/../file/' . $old_photo)) {
            unlink(__DIR__ . '/../file/' . $old_photo);
        }
    }
    
    $stmt = $conn->prepare("UPDATE users SET foto=NULL WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['user_foto'] = null;
}

// Handle Upload Photo
if (isset($_FILES['foto']) && $_FILES['foto']['error'] != UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['foto'];
    
    // Validasi Error Upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = "Error upload: " . $file['error'];
        echo json_encode($response);
        exit;
    }

    // Validasi Tipe
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        $response['message'] = "Format file tidak didukung (hanya JPG, PNG, GIF, WEBP).";
        echo json_encode($response);
        exit;
    }

    // Validasi Ukuran (2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        $response['message'] = "Ukuran file terlalu besar (maks 2MB).";
        echo json_encode($response);
        exit;
    }

    // Proses Upload
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $namaFile = time() . "_" . uniqid() . "." . $ext;
    $target_dir = __DIR__ . '/../file/';

    if (move_uploaded_file($file['tmp_name'], $target_dir . $namaFile)) {
        // Hapus foto lama
        $stmt_old = $conn->prepare("SELECT foto FROM users WHERE id=?");
        $stmt_old->bind_param("i", $id);
        $stmt_old->execute();
        $res_old = $stmt_old->get_result();
        if ($row_old = $res_old->fetch_assoc()) {
            $old_photo = $row_old['foto'];
            if ($old_photo && $old_photo !== 'user.jpg' && file_exists($target_dir . $old_photo)) {
                unlink($target_dir . $old_photo);
            }
        }

        // Update DB
        $stmt = $conn->prepare("UPDATE users SET foto=? WHERE id=?");
        $stmt->bind_param("si", $namaFile, $id);
        $stmt->execute();
        
        $_SESSION['user_foto'] = $namaFile;
    } else {
        $response['message'] = "Gagal memindahkan file upload.";
        echo json_encode($response);
        exit;
    }
}

$response['success'] = true;
$response['message'] = 'Profil berhasil diperbarui!';
echo json_encode($response);
