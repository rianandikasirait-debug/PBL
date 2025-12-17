<?php
session_start(); // Mulai session — memastikan variabel session tersedia
require_once __DIR__ . '/../koneksi.php'; // Sertakan koneksi database ($conn diasumsikan tersedia)

// Pastikan user sudah login; jika belum arahkan ke login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_SESSION['user_id']; // Ambil ID user dari session (digunakan untuk update)
$role = $_SESSION['user_role'] ?? 'peserta'; // Ambil role; default 'peserta' jika tidak ada

// Tentukan halaman redirect sesuai role (profil & edit)
$redirect_profile = ($role === 'admin') ? '../admin/profile.php' : '../peserta/profile_peserta.php';
$redirect_edit = ($role === 'admin') ? '../admin/edit_profile_admin.php' : '../peserta/edit_profile_peserta.php';

$nama = trim($_POST['nama']); // Ambil nama baru (trim spasi)
$password_baru = $_POST['password_baru'] ?? ''; // Ambil password baru jika ada
$konfirmasi = $_POST['password_konfirmasi'] ?? ''; // Ambil konfirmasi password
$foto_baru = null; // Placeholder untuk nama file foto baru

// Validasi password baru: jika diisi, harus cocok dengan konfirmasi
// Validasi password baru: jika diisi, harus cocok dengan konfirmasi
if (!empty($password_baru)) {
    if ($password_baru !== $konfirmasi) {
        $_SESSION['error_message'] = "Konfirmasi password tidak cocok.";
        header("Location: $redirect_edit");
        exit;
    }
}

// Hapus Foto (Jika diminta)
if (isset($_POST['delete_photo']) && $_POST['delete_photo'] == '1') {
    // Ambil foto lama
    $stmt_old = $conn->prepare("SELECT foto FROM users WHERE id=?");
    $stmt_old->bind_param("i", $id);
    $stmt_old->execute();
    $res_old = $stmt_old->get_result();
    if ($row_old = $res_old->fetch_assoc()) {
        $old_photo = $row_old['foto'];
        if ($old_photo && file_exists(__DIR__ . '/../uploads/' . $old_photo)) {
            unlink(__DIR__ . '/../uploads/' . $old_photo);
        }
    }
    $stmt_old->close();

    // Update DB
    $stmt = $conn->prepare("UPDATE users SET foto=NULL WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $_SESSION['user_foto'] = null; // Update session
    
    // Redirect langsung biar refresh
    $_SESSION['success_message'] = "Foto profil berhasil dihapus.";
    header("Location: $redirect_edit");
    exit;
}

// Upload Foto (jika ada file di input 'foto')
// Upload Foto (jika ada file di input 'foto')
if (isset($_FILES['foto']) && $_FILES['foto']['error'] != UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['foto'];

    // Cek error bawaan PHP (misal melebihi upload_max_filesize)
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $msg = "Ukuran file terlalu besar (melebihi batas server).";
                break;
            case UPLOAD_ERR_PARTIAL:
                $msg = "File hanya terupload sebagian.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $msg = "Folder temporary hilang.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $msg = "Gagal menulis file ke disk.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $msg = "Upload dihentikan oleh ekstensi PHP.";
                break;
            default:
                $msg = "Terjadi kesalahan upload (Kode: " . $file['error'] . ").";
                break;
        }
        $_SESSION['error_message'] = $msg;
        header("Location: $redirect_edit");
        exit;
    }

    // Validasi tipe file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error_message'] = "Format file tidak didukung (hanya JPG, PNG, GIF, WEBP).";
        header("Location: $redirect_edit");
        exit;
    }

    // Validasi ukuran (maks 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        $_SESSION['error_message'] = "Ukuran file terlalu besar (maks 2MB).";
        header("Location: $redirect_edit");
        exit;
    }

    // Buat nama file aman
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $namaFile = time() . "_" . uniqid() . "." . $ext; // Lebih unik
    $target_dir = __DIR__ . '/../uploads/';

    // Pindahkan file
    if (move_uploaded_file($file['tmp_name'], $target_dir . $namaFile)) {
        $foto_baru = $namaFile;

        // Ambil foto lama
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
        $stmt_old->close();

        // Update database
        $stmt = $conn->prepare("UPDATE users SET foto=? WHERE id=?");
        $stmt->bind_param("si", $foto_baru, $id);
        $stmt->execute();

        $_SESSION['user_foto'] = $foto_baru;
    } else {
        $_SESSION['error_message'] = "Gagal memindahkan file upload.";
        header("Location: $redirect_edit");
        exit;
    }
} elseif (empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post') { 
    // Tangkap kasus post_max_size exceeded
    $_SESSION['error_message'] = "File upload terlalu besar (Melebihi POST Max Size server).";
    header("Location: $redirect_edit");
    exit;
}

// Update nama (jika ada)
if (!empty($nama)) {
    $stmt = $conn->prepare("UPDATE users SET nama=? WHERE id=?");
    $stmt->bind_param("si", $nama, $id);
    $stmt->execute();
    $_SESSION['user_name'] = $nama; // sinkron session
}

// Update password baru (jika ada)
if (!empty($password_baru)) {
    $hash = password_hash($password_baru, PASSWORD_DEFAULT); // hash password aman
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $hash, $id);
    $stmt->execute();
}

// Sukses — set pesan sukses dan redirect ke profil
$_SESSION['success_message'] = "Profil berhasil diperbarui!";
header("Location: $redirect_profile");
exit;
?>
