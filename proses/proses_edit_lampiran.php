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
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$judul = isset($_POST['judul_lampiran']) ? trim($_POST['judul_lampiran']) : '';

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Lampiran tidak valid']);
    exit;
}

// Check if lampiran exists
$stmt = $conn->prepare("SELECT * FROM tb_lampiran WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$existing = $result->fetch_assoc();
$stmt->close();

if (!$existing) {
    echo json_encode(['success' => false, 'message' => 'Lampiran tidak ditemukan']);
    exit;
}

$updateQuery = "UPDATE tb_lampiran SET judul_lampiran = ?";
$params = [$judul];
$types = "s";

// 3. File Processing (Optional)
if (isset($_FILES['file_lampiran']) && $_FILES['file_lampiran']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['file_lampiran'];
    $originalName = basename($file['name']);
    $tmpName = $file['tmp_name'];

    // Generate Safe Filename
    $safeName = time() . '_' . preg_replace('/[^a-z0-9\-_.]/i', '_', $originalName);
    $destination = __DIR__ . '/../file/' . $safeName;

    if (move_uploaded_file($tmpName, $destination)) {
        // Delete old file if exists
        $oldFile = __DIR__ . '/../file/' . $existing['file_lampiran'];
        if (file_exists($oldFile)) {
            @unlink($oldFile);
        }

        $updateQuery .= ", file_lampiran = ?";
        $params[] = $safeName;
        $types .= "s";
        
        // Update updated data for response
        $existing['file_lampiran'] = $safeName;
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupload file baru']);
        exit;
    }
}

// 4. Update Database
$updateQuery .= " WHERE id = ?";
$params[] = $id;
$types .= "i";

$stmt = $conn->prepare($updateQuery);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Lampiran berhasil diperbarui',
        'data' => [
            'id' => $id,
            'judul_lampiran' => $judul,
            'file_lampiran' => $existing['file_lampiran']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui database: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
