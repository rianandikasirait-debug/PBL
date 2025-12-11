<?php
session_start(); // Mulai session — memastikan variabel session tersedia
require_once __DIR__ . '/../koneksi.php'; // Sertakan koneksi database (variabel $conn diasumsikan ada)

// Cek Login & Role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Jika tidak login atau bukan admin -> arahkan ke halaman login
    header("Location: ../login.php");
    exit;
}

// Pastikan request method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

// Ambil dan sanitasi input dasar
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$judul = trim($_POST['judul'] ?? '');
$tanggal = $_POST['tanggal'] ?? '';
$isi = $_POST['isi'] ?? '';
$status = $_POST['status'] ?? 'draft'; // Ambil status
$peserta_arr = isset($_POST['peserta']) ? $_POST['peserta'] : [];
// Sanitasi peserta (ensure int)
$clean_peserta = [];
if (is_array($peserta_arr)) {
    foreach ($peserta_arr as $p) {
        $val = (int)$p;
        if ($val > 0) $clean_peserta[] = $val;
    }
}
$peserta_str = implode(',', $clean_peserta);

// Validasi sederhana: cek field wajib
if ($id <= 0 || empty($judul) || empty($tanggal) || empty($isi)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap!']);
    exit;
}

// Ensure data limits (similar to add notulen)
if (strlen($judul) > 50) {
    $judul = substr($judul, 0, 50);
}
if (strlen($peserta_str) > 255) {
    echo json_encode(['success' => false, 'message' => 'Terlalu banyak peserta (max 255 chars).']);
    exit;
}

// Cek apakah ada upload lampiran baru
$lampiran_baru = null;
if (!empty($_FILES['lampiran']['name'])) {
    $file = $_FILES['lampiran'];
    $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];

    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Format file tidak didukung!']);
        exit;
    }

    if ($file['size'] > 5 * 1024 * 1024) { // batas 5MB
        echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar (Maks 5MB)!']);
        exit;
    }

    $namaFile = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "", basename($file['name']));
    $target_dir = __DIR__ . '/../file/';

    if (move_uploaded_file($file['tmp_name'], $target_dir . $namaFile)) {
        $lampiran_baru = $namaFile;
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal upload file!']);
        exit;
    }
}

// Update Database
// Update Database
if ($lampiran_baru) {
    // Gunakan kolom yang benar: judul, tanggal, hasil, peserta, tindak_lanjut (file), status
    $sql = "UPDATE tambah_notulen SET judul=?, tanggal=?, hasil=?, peserta=?, tindak_lanjut=CONCAT(IFNULL(tindak_lanjut, ''), IF(tindak_lanjut IS NOT NULL AND tindak_lanjut != '', '|', ''), ?), status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $judul, $tanggal, $isi, $peserta_str, $lampiran_baru, $status, $id);
} else {
    $sql = "UPDATE tambah_notulen SET judul=?, tanggal=?, hasil=?, peserta=?, status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $judul, $tanggal, $isi, $peserta_str, $status, $id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Notulen berhasil diperbarui!']);
} else {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
