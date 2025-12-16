<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../koneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil data pengguna yang sedang login
$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nama, foto FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userRes = $stmt->get_result();
$userData = $userRes->fetch_assoc();
$stmt->close();
$userName = $userData['nama'] ?? 'Admin';
$userPhoto = $userData['foto'] ?? null;

// 1. AMBIL DATA PENGGUNA (HANYA PESERTA)
$all_users = [];
$sql = "SELECT id, foto, nama, nik, email, role FROM users WHERE LOWER(role) = 'peserta' ORDER BY nama ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    // Ambil semua data sekaligus
    $all_users = $result->fetch_all(MYSQLI_ASSOC);
}
$conn->close();

// Kita juga perlu tahu siapa ID admin yang sedang login
// (Meskipun sekarang tidak terlalu relevan karena kita hanya menampilkan peserta,
// ini tetap praktik yang baik untuk dijaga)
$current_admin_id = $_SESSION['user_id'] ?? 0;

// Periksa tautan WhatsApp di sesi
$wa_link = $_SESSION['wa_link'] ?? null;
$wa_nomor = $_SESSION['wa_nomor'] ?? null;
$wa_message = $_SESSION['wa_message'] ?? null;

// Hapus variabel sesi setelah diambil
if ($wa_link) {
    unset($_SESSION['wa_link']);
    unset($_SESSION['wa_nomor']);
}
if ($wa_message) {
    unset($_SESSION['wa_message']);
}
?>
