<?php
session_start(); 
// Memulai session untuk mengakses data login user

require_once __DIR__ . '/../koneksi.php';
// Menghubungkan file koneksi database

header('Content-Type: application/json');
// Mengatur response agar selalu dikirim dalam format JSON

// =============================
// 1. VALIDASI LOGIN ADMIN
// =============================
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Jika user belum login atau bukan admin → tolak akses
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Akses ditolak. Anda harus login sebagai admin.'
    ]);
    exit;
}

// =============================
// 2. VALIDASI METODE REQUEST
// =============================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Endpoint ini hanya boleh diakses dengan metode POST
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Metode tidak diizinkan.'
    ]);
    exit;
}

// =============================
// 3. MEMBACA DAN VALIDASI JSON INPUT
// =============================
$input = file_get_contents('php://input'); 
// Mengambil raw JSON body dari fetch/AJAX

$data = json_decode($input, true); 
// Mengubah JSON menjadi array PHP

// Validasi apakah JSON valid dan ID tersedia
if (json_last_error() !== JSON_ERROR_NONE || empty($data['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID pengguna tidak valid.'
    ]);
    exit;
}

$id_to_delete = $data['id']; 
// ID pengguna yang ingin dihapus

// =============================
// 4. CEGAH ADMIN MENGHAPUS DIRI SENDIRI
// =============================
$current_admin_id = $_SESSION['user_id'];

if ($id_to_delete == $current_admin_id) {
    // Mencegah admin menghapus akunnya sendiri
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Anda tidak dapat menghapus akun Anda sendiri.'
    ]);
    exit;
}

// =============================
// 5. PROSES PENGHAPUSAN USER
// =============================
try {
    // -----------------------------------------
    // A. AMBIL DATA FOTO USER SEBELUM MENGHAPUS
    // -----------------------------------------
    $sql_info = "SELECT foto FROM users WHERE id = ?";
    $stmt_info = $conn->prepare($sql_info);
    $stmt_info->bind_param("i", $id_to_delete);
    $stmt_info->execute();
    $result_info = $stmt_info->get_result();

    if ($result_info->num_rows === 0) {
        // Jika user tidak ditemukan
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Pengguna tidak ditemukan.'
        ]);
        exit;
    }

    $user_data = $result_info->fetch_assoc();
    $foto_file = $user_data['foto']; 
    // Nama file foto yang akan dihapus setelah DB delete

    $stmt_info->close();

    // -----------------------------------------
    // B. HAPUS DATA USER DARI DATABASE
    // -----------------------------------------
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        // Jika prepare gagal, lempar error
        throw new Exception("Gagal mempersiapkan statement: " . $conn->error);
    }

    $stmt->bind_param("i", $id_to_delete);

    if ($stmt->execute()) {
        // -----------------------------------------
        // C. HAPUS FILE FOTO USER (JIKA ADA)
        // -----------------------------------------
        $path_foto = __DIR__ . '/../uploads/' . $foto_file;

        // Hanya hapus jika:
        // - File ada
        // - Bukan foto default template
        if ($foto_file && $foto_file !== 'user.jpg' && file_exists($path_foto)) {
            unlink($path_foto); 
            // Menghapus file foto dari folder server
        }

        // Kirim response sukses
        echo json_encode([
            'success' => true,
            'message' => 'Pengguna berhasil dihapus.'
        ]);

    } else {
        // Jika eksekusi gagal → lempar error
        throw new Exception("Gagal mengeksekusi penghapusan: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Menangani error tak terduga
    error_log($e->getMessage()); 
    // Catat ke log server

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server. Gagal menghapus pengguna.'
    ]);
}
?>
