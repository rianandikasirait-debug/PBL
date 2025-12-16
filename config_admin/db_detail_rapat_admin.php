<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../koneksi.php';

// Cek Login & Peran
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
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

$id_notulen = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_notulen <= 0) {
    echo "<script>showToast('ID Notulen tidak valid!', 'error'); setTimeout(() => window.location.href='dashboard_admin.php', 2000);</script>";
    exit;
}

// Tandai sebagai dilihat dalam sesi
if (!isset($_SESSION['viewed_notulen'])) {
    $_SESSION['viewed_notulen'] = [];
}
if (!in_array($id_notulen, $_SESSION['viewed_notulen'])) {
    $_SESSION['viewed_notulen'][] = $id_notulen;
}

// Ambil data notulen
$sql = "SELECT * FROM tambah_notulen WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_notulen);
$stmt->execute();
$result = $stmt->get_result();
$notulen = $result->fetch_assoc();

// Fetch Lampiran (tb_lampiran)
$stmtLampiran = $conn->prepare("SELECT * FROM tb_lampiran WHERE id_notulen = ?");
$stmtLampiran->bind_param("i", $id_notulen);
$stmtLampiran->execute();
$resLampiran = $stmtLampiran->get_result();
$lampiranList = [];
while ($row = $resLampiran->fetch_assoc()) {
    $lampiranList[] = $row;
}

if (!$notulen) {
    echo "<script>showToast('Data notulen tidak ditemukan!', 'error'); setTimeout(() => window.location.href='dashboard_admin.php', 2000);</script>";
    exit;
}

// Format tanggal
$tanggal = date('d/m/Y', strtotime($notulen['tanggal']));

// Uraikan peserta (string dipisahkan koma)
// Asumsi: $notulen['peserta'] berisi daftar ID pengguna seperti "15,17,18".
// Jika sistem menyimpan nama sebagai peserta, kode ini tetap menampilkan nama yang ada di DB (fallback ke string asli jika tidak ditemukan).
$peserta_raw = $notulen['peserta'] ?? '';
$peserta_ids = [];
$peserta_names = [];

if (trim($peserta_raw) !== '') {
    // pisahkan & sanitasi ke integer
    $parts = array_filter(array_map('trim', explode(',', $peserta_raw)), function($v){ return $v !== ''; });
    foreach ($parts as $p) {
        // jika numerik, masukkan sebagai int
        if (is_numeric($p)) {
            $peserta_ids[] = (int)$p;
        }
    }

    if (!empty($peserta_ids)) {
        // Bangun daftar ID yang aman (integer)
        $unique_ids = array_values(array_unique($peserta_ids));
        $ids_list = implode(',', $unique_ids); // aman karena sudah di-cast ke int

        // Ambil nama dari tabel pengguna
        $sql_users = "SELECT id, nama, email, nik, foto FROM users WHERE id IN ($ids_list)";
        $res_users = $conn->query($sql_users);
        $map = [];
        while ($r = $res_users->fetch_assoc()) {
            $map[(int)$r['id']] = $r;
        }

        // isi nama_peserta dari urutan asli (jika ID ditemukan gunakan nama, jika tidak gunakan ID asli)
        foreach ($parts as $orig) {
            if (is_numeric($orig)) {
                $idint = (int)$orig;
                if (isset($map[$idint])) {
                    $peserta_details[] = $map[$idint];
                } else {
                    // fallback: tampilkan ID jika nama tidak ditemukan
                     $peserta_details[] = ['nama' => (string)$idint, 'email' => '', 'nik' => ''];
                }
            } else {
                // jika bukan numerik (mungkin nama lama), tampilkan apa adanya
                $peserta_details[] = ['nama' => $orig, 'email' => '', 'nik' => ''];
            }
        }
    } else {
        // Jika tidak ada ID valid tetapi string ada (mungkin nama manual), tampilkan sebagai nama
        foreach ($parts as $p) {
            $peserta_details[] = ['nama' => $p, 'email' => '', 'nik' => ''];
        }
    }
}
?>
