<?php
session_start(); // Mulai session — memastikan variabel session tersedia
require_once __DIR__ . '/../koneksi.php'; // Sertakan koneksi database (variabel $conn diasumsikan ada)

// Cek Login & Role
$allowedRoles = ['admin', 'notulis'];
if (!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['user_role'] ?? ''), $allowedRoles)) {
    // Jika tidak login atau bukan admin/notulis -> arahkan ke halaman login
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
$jam_mulai = $_POST['jam_mulai'] ?? null;
$jam_selesai = $_POST['jam_selesai'] ?? null;
$isi = $_POST['isi'] ?? '';
$status = $_POST['status'] ?? 'draft'; // Ambil status
// Sanitasi peserta
$peserta_arr = isset($_POST['peserta']) ? $_POST['peserta'] : [];



$clean_peserta = [];
if (is_array($peserta_arr)) {
    foreach ($peserta_arr as $p) {
        $val = (int)$p;
        if ($val > 0) $clean_peserta[] = $val;
    }
}

$peserta_str = implode(',', array_unique($clean_peserta));

// Penanggung Jawab (ID user yang bertanggung jawab) - Validasi awal
$penanggung_jawab = isset($_POST['penanggung_jawab']) && $_POST['penanggung_jawab'] !== '' ? (int)$_POST['penanggung_jawab'] : null;

// Validasi sederhana: cek field wajib (judul, tanggal, jam, penanggung jawab, dan isi)
if ($id <= 0 || empty($judul) || empty($tanggal) || empty($jam_mulai) || empty($jam_selesai) || $penanggung_jawab === null || $penanggung_jawab <= 0 || empty($isi)) {
    echo json_encode(['success' => false, 'message' => 'Judul, tanggal, jam, penanggung jawab, dan isi wajib diisi']);
    exit;
}

// Ensure data limits (similar to add notulen)
if (strlen($judul) > 50) {
    $judul = substr($judul, 0, 50);
}
// Limit validation for title
if (strlen($judul) > 255) {
    $judul = substr($judul, 0, 255);
}
// Peserta limit removed (LONGTEXT supported)

// --- 1. Proses Upload File Baru (Multiple ke tb_lampiran) ---
// Note: File lama sudah ditangani oleh proses_hapus_lampiran.php secara terpisah via AJAX.
// Di sini kita hanya menangani penambahan file baru.

$uploadErrors = [];

if (isset($_FILES['file_lampiran']) && isset($_POST['judul_lampiran'])) {
    $files = $_FILES['file_lampiran'];
    $titles = $_POST['judul_lampiran'];
    $count = count($files['name']);
    
    // Prepare insert statement for lampiran
    $stmtLampiran = $conn->prepare("INSERT INTO tb_lampiran (id_notulen, judul_lampiran, file_lampiran) VALUES (?, ?, ?)");
    
    for ($i = 0; $i < $count; $i++) {
        // Validation: Skip input if no file selected (empty name)
        if (empty($files['name'][$i])) {
            continue;
        }

        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $tmp = $files['tmp_name'][$i];
            $originalName = basename($files['name'][$i]);
            $title = trim($titles[$i]);
            if (empty($title)) $title = $originalName; // Fallback title
            
            $safeName = time() . '_' . $i . '_' . preg_replace('/[^a-z0-9\-_.]/i', '_', $originalName);
            $dest = __DIR__ . '/../uploads/' . $safeName;
            
            if (move_uploaded_file($tmp, $dest)) {
                $stmtLampiran->bind_param('iss', $id, $title, $safeName);
                $stmtLampiran->execute();
            } else {
                $uploadErrors[] = "Gagal upload: $originalName";
            }
        }
    }
}

// --- 2. Update Database Notulen (Data Utama) ---
// Penanggung Jawab sudah divalidasi di awal file

$sql = "UPDATE tambah_notulen SET judul=?, tanggal=?, jam_mulai=?, jam_selesai=?, hasil=?, peserta=?, status=?, penanggung_jawab=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssii", $judul, $tanggal, $jam_mulai, $jam_selesai, $isi, $peserta_str, $status, $penanggung_jawab, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Notulen berhasil diperbarui!']);
} else {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
