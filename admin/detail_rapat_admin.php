<?php
session_start();
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
        $sql_users = "SELECT id, nama FROM users WHERE id IN ($ids_list)";
        $res_users = $conn->query($sql_users);
        $map = [];
        while ($r = $res_users->fetch_assoc()) {
            $map[(int)$r['id']] = $r['nama'];
        }

        // isi nama_peserta dari urutan asli (jika ID ditemukan gunakan nama, jika tidak gunakan ID asli)
        foreach ($parts as $orig) {
            if (is_numeric($orig)) {
                $idint = (int)$orig;
                if (isset($map[$idint])) {
                    $peserta_names[] = $map[$idint];
                } else {
                    // fallback: tampilkan ID jika nama tidak ditemukan
                    $peserta_names[] = (string)$idint;
                }
            } else {
                // jika bukan numerik (misal: sudah nama tersimpan), langsung gunakan
                $peserta_names[] = $orig;
            }
        }
    } else {
        // Tidak ada ID numerik — kemungkinan peserta disimpan sebagai nama string
        foreach ($parts as $orig) {
            $peserta_names[] = $orig;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Rapat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.min.css">
    <style>
        .participant-badge {
            display: inline-block;
            margin: 4px 6px 4px 0;
            padding: 6px 10px;
            background: #f1f7f3;
            border-radius: 18px;
            font-size: 14px;
            color: #2d6a4f;
        }
        .btn.btn-back{
          background-color: #00C853 !important; 
          border-color: #00C853 !important;
          color: #ffffff !important;
          font-weight: bold;
          text-decoration: none !important;
        }
        .btn.btn-back:hover, .btn.btn-back:focus{
          background-color: #02913f !important; 
          border-color: #02913f !important;
        }
    </style>
</head>

<body>
    <!-- navbar -->
    <nav class="navbar navbar-light bg-white sticky-top px-3">
        <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
            <i class="bi bi-list"></i>
        </button>
    </nav>

    <!-- sidebar mobile -->
    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas"
        aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-body p-0">
            <div class="sidebar-content d-flex flex-column justify-content-between h-100">
                <div>
                    <h4 class="fw-bold mb-4 ms-3">SmartNote</h4>
                    <ul class="nav flex-column">
                        <li>
                            <a class="nav-link active" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                        </li>
                        <li>
                            <a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a>
                        </li>
                    </ul>
                </div>

                <div class="mt-auto px-3">
                    <ul class="nav flex-column mb-3">
                        <li>
                            <a class="nav-link" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
                        </li>
                        <li>
                            <a id="logoutBtnMobile" class="nav-link text-danger" href="#"><i class="bi bi-box-arrow-right me-2 text-danger"></i>Keluar</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Desktop -->
    <div class="sidebar-content d-none d-lg-flex flex-column justify-content-between position-fixed">
        <div>
            <h4 class="fw-bold mb-4 ms-3">SmartNote</h4>
            <ul class="nav flex-column">
                <li>
                    <a class="nav-link active" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                </li>
                <li>
                    <a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a>
                </li>
            </ul>
        </div>

        <div>
            <ul class="nav flex-column mb-3">
                <li>
                    <a class="nav-link" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
                </li>
                <li>
                    <a id="logoutBtn" class="nav-link text-danger" href="#"><i class="bi bi-box-arrow-right me-2 text-danger"></i>Keluar</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4><b>Dashboard Admin</b></h4>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end">
                    <span class="d-block fw-medium text-dark">Halo, <?= htmlspecialchars($userName) ?> 👋</span>
                </div>
                    <?php 
                    $filePath = "../file/" . $userPhoto; 
                    $hasPhoto = $userPhoto && file_exists($filePath);
                    ?>

                    <?php if ($hasPhoto): ?>
                        <img src="<?= htmlspecialchars($filePath) ?>" 
                            alt="Profile"
                            class="rounded-circle shadow-sm"
                            style="width: 45px; height: 45px; object-fit: cover; border: 2px solid #fff;">
                    <?php else: ?>
                        <!-- ICON formal (abu gelap, clean, profesional) -->
                        <i class="bi bi-person-circle"
                        style="font-size: 45px; color: #495057;"></i>
                    <?php endif; ?>
            </div>
        </div>

        <!-- Detail Rapat -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($notulen['judul']); ?></h4>
                    <p class="text-muted mb-2">Dibuat oleh: <?= htmlspecialchars($notulen['created_by'] ?? 'Admin'); ?>
                    </p>
                </div>
                <div class="text-end">
                    <p class="fw-semibold mb-0">Tanggal Rapat:</p>
                    <p class="mb-0"><?= $tanggal; ?></p>
                </div>
            </div>

            <hr>

            <div class="mb-4">
                <?= $notulen['hasil']; // Isi rapat biasanya HTML dari TinyMCE, jadi tidak di-escape ?>
            </div>

            <h6 class="fw-semibold mb-2">Peserta Rapat:</h6>
            <div class="mb-3">
                <?php if (!empty($peserta_names)): ?>
                    <?php foreach ($peserta_names as $pn): ?>
                        <span class="participant-badge"><i class="bi bi-person-fill me-1"></i>
                            <?= htmlspecialchars($pn); ?></span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">Belum ada peserta yang tercatat.</p>
                <?php endif; ?>
            </div>

            <h6 class="fw-semibold mb-2">Lampiran:</h6>
            <?php if (!empty($notulen['tindak_lanjut'])): 
                $files = explode('|', $notulen['tindak_lanjut']);
                foreach($files as $f):
                    $f = trim($f);
                    if (empty($f)) continue;
            ?>
                <div class="mb-2">
                    <a href="../file/<?= htmlspecialchars($f); ?>" class="btn btn-outline-primary btn-sm me-2" target="_blank">
                        <i class="bi bi-eye me-2"></i>Lihat Lampiran
                    </a>
                    <a href="../file/<?= htmlspecialchars($f); ?>" class="btn btn-outline-success btn-sm" download>
                        <i class="bi bi-download me-2"></i>Download Lampiran
                    </a>
                </div>
            <?php endforeach; ?>
            <?php if (empty($files)): ?>
                <p class="text-muted">Tidak ada lampiran.</p>
            <?php endif; ?>
            <?php else: ?>
                <p class="text-muted">Tidak ada lampiran.</p>
            <?php endif; ?>

            <div class="text-end mt-4">
                <a href="dashboard_admin.php" class="btn btn-back"></i> Kembali</a>
            </div>
        </div>
    </div>

    <script>
        // Handler Logout
        const logoutBtn = document.getElementById("logoutBtn");
        if (logoutBtn) {
            logoutBtn.addEventListener("click", async function (e) {
                e.preventDefault();
                const confirmed = await showConfirm("Apakah kamu yakin ingin logout?");
                if (confirmed) {
                    window.location.href = "../proses/proses_logout.php";
                }
            });
        }
        const logoutBtnMobile = document.getElementById("logoutBtnMobile");
        if (logoutBtnMobile) {
            logoutBtnMobile.addEventListener("click", async function (e) {
                e.preventDefault();
                const confirmed = await showConfirm("Apakah kamu yakin ingin logout?");
                if (confirmed) {
                    window.location.href = "../proses/proses_logout.php";
                }
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
</body>

</html>
