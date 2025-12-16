<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../koneksi.php';

// Cek Login & Role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

// Ambil data user login (nama + foto)
$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nama, foto FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userRes = $stmt->get_result();
$userData = $userRes->fetch_assoc();
$stmt->close();
$userName = $userData['nama'] ?? 'Admin';
$userPhoto = $userData['foto'] ?? null;

$id_notulen = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_notulen <= 0) {
  echo "<script>showToast('ID Notulen tidak valid!', 'error'); setTimeout(() => window.location.href='dashboard_admin.php', 2000);</script>";
  exit;
}

// Ambil data notulen
$sql = "SELECT * FROM tambah_notulen WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_notulen);
$stmt->execute();
$result = $stmt->get_result();
$notulen = $result->fetch_assoc();


if (!$notulen) {
  echo "<script>showToast('Data notulen tidak ditemukan!', 'error'); setTimeout(() => window.location.href='dashboard_admin.php', 2000);</script>";
  exit;
}

// Fetch Attachments (tb_lampiran)
$stmtLampiran = $conn->prepare("SELECT * FROM tb_lampiran WHERE id_notulen = ?");
$stmtLampiran->bind_param("i", $id_notulen);
$stmtLampiran->execute();
$resultLampiran = $stmtLampiran->get_result();
$lampiranList = [];
while ($row = $resultLampiran->fetch_assoc()) {
    $lampiranList[] = $row;
}
$hasLampiran = count($lampiranList) > 0;

// Ambil daftar semua user untuk modal peserta (Admin + Peserta)
$sql_users = "SELECT id, nama, email FROM users WHERE role IN ('admin', 'peserta') ORDER BY nama ASC";
$stmt_users = $conn->prepare($sql_users);
$stmt_users->execute();
$res_users = $stmt_users->get_result();
$all_users = [];
while ($row = $res_users->fetch_assoc()) {
  $all_users[] = $row;
}

// Parse peserta yang sudah ada di notulen
$current_participants = array_filter(array_map('trim', explode(',', $notulen['peserta'])), function ($v) {
  return $v !== '';
});
// Jika peserta disimpan sebagai ID, ambil nama-nama peserta dari DB
$participants_map = []; // id => nama
if (!empty($current_participants)) {
  // sanitize ke int
  $ids = array_map('intval', $current_participants);
  $ids_list = implode(',', array_unique($ids));
  if ($ids_list !== '') {
    $sql_part = "SELECT id, nama FROM users WHERE id IN ($ids_list)";
    $res_part = $conn->query($sql_part);
    while ($r = $res_part->fetch_assoc()) {
      $participants_map[(int)$r['id']] = $r['nama'];
    }
  }
}
// for display, build array of ['id'=>..,'nama'=>..]
$current_participant_items = [];
foreach ($current_participants as $pid) {
  $pid_int = (int)$pid;
  if ($pid_int > 0 && isset($participants_map[$pid_int])) {
    $current_participant_items[] = ['id' => $pid_int, 'nama' => $participants_map[$pid_int]];
  } elseif ($pid !== '') {
    // fallback: jika DB tidak punya, tampilkan apa yang ada (biasanya not expected)
    $current_participant_items[] = ['id' => $pid, 'nama' => $pid];
  }
}
?>
