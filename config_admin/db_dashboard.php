<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../koneksi.php';

// Pastikan login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil data user login
$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nama, foto FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userRes = $stmt->get_result();
$userData = $userRes->fetch_assoc();
$stmt->close();
$userName = $userData['nama'] ?? 'Admin';
$userPhoto = $userData['foto'] ?? null;

// Initialize viewed notulen session if not exists
if (!isset($_SESSION['viewed_notulen'])) {
    $_SESSION['viewed_notulen'] = [];
}

// Ambil data untuk highlight cards
// 1. Total Peserta
$sqlPeserta = "SELECT COUNT(*) as total FROM users WHERE role = 'peserta'";
$resPeserta = $conn->query($sqlPeserta);
$totalPeserta = $resPeserta ? $resPeserta->fetch_assoc()['total'] : 0;

// 2. Total Notulen
$sqlNotulen = "SELECT COUNT(*) as total FROM tambah_notulen";
$resNotulen = $conn->query($sqlNotulen);
$totalNotulen = $resNotulen ? $resNotulen->fetch_assoc()['total'] : 0;

// 3. Total Notulen berdasarkan Status (Draft dan Final)
$sqlDraft = "SELECT COUNT(*) as total FROM tambah_notulen WHERE status = 'draft' OR status IS NULL";
$resDraft = $conn->query($sqlDraft);
$totalDraft = $resDraft ? $resDraft->fetch_assoc()['total'] : 0;

$sqlFinal = "SELECT COUNT(*) as total FROM tambah_notulen WHERE status = 'final'";
$resFinal = $conn->query($sqlFinal);
$totalFinal = $resFinal ? $resFinal->fetch_assoc()['total'] : 0;

// Ambil 10 notulen terbaru untuk tabel (dengan status)
$sql = "SELECT id, judul, tanggal, tempat, peserta, status, created_at
         FROM tambah_notulen 
         ORDER BY created_at DESC LIMIT 10";
$result = $conn->query($sql);
// Konversi ke format array dan tambahkan status is_viewed
$dataNotulen = [];
$viewedIdsSession = $_SESSION['viewed_notulen'] ?? [];
while ($row = $result->fetch_assoc()) {
    $row['is_viewed'] = in_array((int)$row['id'], $viewedIdsSession);
    $dataNotulen[] = $row;
}
?>
