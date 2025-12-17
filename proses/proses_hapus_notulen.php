<?php
session_start(); // Mulai session — pastikan session tersedia
require_once __DIR__ . '/../koneksi.php'; // Sertakan file koneksi (variabel $conn diasumsikan ada)

// Set header response JSON — cocok untuk API
header('Content-Type: application/json');

// 1. CEK LOGIN & ROLE
$role = isset($_SESSION['user_role']) ? trim(strtolower($_SESSION['user_role'])) : '';

if (!isset($_SESSION['user_id']) || $role !== 'admin') {
    // Jika tidak login atau bukan admin -> 403 Forbidden
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Role: ' . $role]);
    exit;
}

// 2. AMBIL DATA JSON
$input = json_decode(file_get_contents('php://input'), true); // decode body JSON
$id = isset($input['id']) ? (int) $input['id'] : 0;

if ($id <= 0) {
    // Validasi ID sederhana
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

try {
    // 3. HAPUS FILE LAMPIRAN DULU
    $stmt = $conn->prepare("SELECT tindak_lanjut FROM tambah_notulen WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result(); // memerlukan mysqlnd
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row && !empty($row['tindak_lanjut'])) {
        $file = __DIR__ . '/../uploads/' . $row['tindak_lanjut'];
        if (file_exists($file)) {
            unlink($file); // hapus file lampiran dari filesystem
        }
    }

    // 4. HAPUS DATA DARI DATABASE
    $stmt = $conn->prepare("DELETE FROM tambah_notulen WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Jika berhasil -> kirim respons sukses
        echo json_encode(['success' => true, 'message' => 'Notulen berhasil dihapus.']);
    } else {
        // Jika gagal -> lempar exception agar masuk ke catch
        throw new Exception("Gagal hapus DB: " . $stmt->error);
    }
    $stmt->close();

} catch (Exception $e) {
    // Log error server-side; kembalikan respons generik ke client
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server.']);
}

$conn->close();
?>
