<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek Login & Peran
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'peserta') {
    header("Location: ../login.php");
    exit;
}

$id_notulen = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id_notulen <= 0) {
    echo "<script>alert('ID Notulen tidak valid!'); window.location.href='dashboard_peserta.php';</script>";
    exit;
}

// Tandai sebagai dilihat dalam sesi
if (!isset($_SESSION['viewed_notulen'])) {
    $_SESSION['viewed_notulen'] = [];
}

if (!in_array($id_notulen, $_SESSION['viewed_notulen'])) {
    $_SESSION['viewed_notulen'][] = $id_notulen;
}

// Ambil data pengguna yang sedang login (nama + foto)
$userId = (int) ($_SESSION['user_id'] ?? 0);
if ($userId > 0) {
    $s = $conn->prepare("SELECT nama, foto FROM users WHERE id = ?");
    $s->bind_param("i", $userId);
    $s->execute();
    $r = $s->get_result();
    $u = $r->fetch_assoc();
    $s->close();
    $sessionUserName = $u['nama'] ?? null;
    $userPhoto = $u['foto'] ?? null;
    $userEmail = $u['email'] ?? null; // Ambil email pengguna

} else {
    $sessionUserName = null;
    $userPhoto = null;
    $userEmail = null;
}


// Ambil data notulen dengan nama notulis yang membuat dan penanggung jawab
$sql = "SELECT n.*, u.nama AS created_by, pj.nama AS penanggung_jawab_nama
        FROM tambah_notulen n 
        LEFT JOIN users u ON n.id_user = u.id 
        LEFT JOIN users pj ON n.penanggung_jawab = pj.id
        WHERE n.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_notulen);
$stmt->execute();
$result = $stmt->get_result();
$notulen = $result->fetch_assoc();

if (!$notulen) {
    // Jika tidak ketemu berdasarkan ID, atau jika ada filter tambahan yang tidak terpenuhi
    echo "<script>showToast('Data tidak ditemukan atau Anda tidak memiliki akses!', 'error'); setTimeout(() => window.location.href='dashboard_peserta.php', 2000);</script>";
    exit;
}

// Fetch Lampiran (tb_lampiran)
$stmtLampiran = $conn->prepare("SELECT * FROM tb_lampiran WHERE id_notulen = ?");
$stmtLampiran->bind_param("i", $id_notulen);
$stmtLampiran->execute();
$resLampiran = $stmtLampiran->get_result();
$lampiranList = [];

while ($row = $resLampiran->fetch_assoc()) {
    $lampiranList[] = $row;
}

// Siapkan variabel yang dipakai di HTML
$tanggal = !empty($notulen['tanggal']) ? date('d/m/Y', strtotime($notulen['tanggal'])) : '-';
$lampiran = $notulen['tindak_lanjut'] ?? '';
$created_by = $notulen['created_by'] ?? 'Admin';

// Uraikan peserta SEBELUM menutup koneksi
$peserta_raw = $notulen['peserta'] ?? '';
$peserta_names = [];
$peserta_details = []; // Initialize the array
if (trim($peserta_raw) !== '') {
    $parts = array_filter(array_map('trim', explode(',', $peserta_raw)), function($v){ return $v !== ''; });
    $peserta_ids = array_filter(array_map('intval', $parts), function($v){ return $v > 0; });
    if (!empty($peserta_ids)) {
        $ids_list = implode(',', $peserta_ids);
        $sql_users = "SELECT id, nama, email, nik, foto FROM users WHERE id IN ($ids_list)";
        $res_users = $conn->query($sql_users);
        $map = [];

        while ($r = $res_users->fetch_assoc()) {
            $map[(int)$r['id']] = $r;
        }

        foreach ($parts as $orig) {
            if (is_numeric($orig)) {
                $idint = (int)$orig;
                if (isset($map[$idint])) {
                    $peserta_details[] = $map[$idint];
                } else {
                     $peserta_details[] = ['nama' => (string)$idint, 'email' => '', 'nik' => ''];
                }

            } else {
                $peserta_details[] = ['nama' => $orig, 'email' => '', 'nik' => ''];
            }
            }
    }
}

?>
