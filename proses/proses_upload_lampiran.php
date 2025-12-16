<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

header('Content-Type: application/json');

// 1. Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// 2. Input Validation
$id_notulen = isset($_POST['id_notulen']) ? (int)$_POST['id_notulen'] : 0;
$judul = isset($_POST['judul_lampiran']) ? trim($_POST['judul_lampiran']) : '';

if ($id_notulen <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Notulen tidak valid']);
    exit;
}

if (!isset($_FILES['file_lampiran']) || $_FILES['file_lampiran']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File tidak ditemukan atau terjadi error saat upload']);
    exit;
}

// 3. File Processing
$file = $_FILES['file_lampiran'];
$originalName = basename($file['name']);
$tmpName = $file['tmp_name'];

// Fallback title if empty
if (empty($judul)) {
    $judul = $originalName;
}

// Generate Safe Filename
$safeName = time() . '_' . preg_replace('/[^a-z0-9\-_.]/i', '_', $originalName);
$destination = __DIR__ . '/../file/' . $safeName;

// 4. Upload & Insert
if (move_uploaded_file($tmpName, $destination)) {
    $stmt = $conn->prepare("INSERT INTO tb_lampiran (id_notulen, judul_lampiran, file_lampiran) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $id_notulen, $judul, $safeName);
    
    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        echo json_encode([
            'success' => true, 
            'message' => 'Lampiran berhasil diupload',
            'data' => [
                'id' => $newId,
                'judul_lampiran' => $judul,
                'file_lampiran' => $safeName
            ]
        ]);
    } else {
        // If DB insert fails, try to delete the uploaded file to keep clean
        @unlink($destination);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal memindahkan file upload']);
}

$conn->close();
?>
