<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Atur header sebagai JSON
header('Content-Type: application/json');

// 1. PERIKSA APAKAH ADMIN SUDAH LOGIN (PENTING!)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Akses ditolak. Anda harus login sebagai admin.'
    ]);
    exit;
}

// 2. PERIKSA METODE REQUEST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Metode tidak diizinkan.'
    ]);
    exit;
}

// 3. AMBIL DATA JSON DARI BODY REQUEST
$data = json_decode(file_get_contents('php://input'), true);
$id_to_delete = $data['id'] ?? 0;

if (empty($id_to_delete)) {
    echo json_encode([
        'success' => false,
        'message' => 'ID pengguna tidak valid.'
    ]);
    exit;
}

// 4. PENCEGAHAN KRUSIAL: JANGAN BIARKAN ADMIN MENGHAPUS DIRINYA SENDIRI
$current_admin_id = $_SESSION['user_id'];
if ($id_to_delete == $current_admin_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Anda tidak dapat menghapus akun Anda sendiri.'
    ]);
    exit;
}

// 5. EKSEKUSI PENGHAPUSAN
try {
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Gagal mempersiapkan statement: " . $conn->error);
    }

    $stmt->bind_param("i", $id_to_delete);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Berhasil
            echo json_encode([
                'success' => true,
                'message' => 'Pengguna berhasil dihapus.'
            ]);
        } else {
            // Berhasil tapi tidak ada yang dihapus (misal ID tidak ditemukan)
            echo json_encode([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.'
            ]);
        }
    } else {
        throw new Exception("Gagal mengeksekusi penghapusan: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log($e->getMessage()); // Catat error
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server. Gagal menghapus pengguna.'
    ]);
}
?>