<?php
session_start();
require_once '../koneksi.php';

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? intval($input['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

// Check admin session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get file info first to delete physical file
$stmt = $conn->prepare("SELECT file_lampiran FROM tb_lampiran WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$lampiran = $result->fetch_assoc();

if ($lampiran) {
    // Delete record from DB
    $delStmt = $conn->prepare("DELETE FROM tb_lampiran WHERE id = ?");
    $delStmt->bind_param("i", $id);
    
    if ($delStmt->execute()) {
        // Delete physical file
        $filePath = __DIR__ . '/../uploads/' . $lampiran['file_lampiran'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Lampiran tidak ditemukan']);
}
?>
