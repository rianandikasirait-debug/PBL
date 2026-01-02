<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../koneksi.php';

// Pastikan pengguna sudah login dan role admin/notulis
$allowedRoles = ['admin', 'notulis'];
if (!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['user_role'] ?? ''), $allowedRoles)) {
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

// Ambil daftar peserta (Admin + Peserta)
$users = [];
$q = $conn->prepare("SELECT id, nama, email, role FROM users WHERE role IN ('admin', 'peserta', 'notulis') ORDER BY nama ASC");
if ($q) {
    $q->execute();
    $res = $q->get_result();
    while ($r = $res->fetch_assoc()) {
        $users[] = $r;
    }
    $q->close();
}
?>
