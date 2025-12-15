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
    <!-- CSS Header & Sidebar -->
    <style>
        /* ===== SIDEBAR DESKTOP ===== */
        .sidebar-admin {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e6e6e6;
            padding: 20px 15px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            z-index: 999;
        }

        .sidebar-admin .title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 25px;
            padding-left: 10px;
        }

        .sidebar-admin a {
            display: block;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 8px;
            color: #222;
            font-weight: 500;
            text-decoration: none !important;
            display: flex;
            align-items: center;
        }

        .sidebar-admin a:hover,
        .sidebar-admin a.active {
            background: #00C853;
            color: #fff !important;
        }

        /* ===== HEADER (TOP BAR) ===== */
        .header-admin {
            position: fixed;
            top: 0;
            left: 250px;
            right: 0;
            height: 70px;
            background: white;
            border-bottom: 1px solid #e6e6e6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            z-index: 998;
        }

        .header-admin .page-title {
            font-size: 20px;
            font-weight: 700;
        }

        .header-admin .right-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* ===== MAIN CONTENT ADJUSTMENT ===== */
        .main-content {
            margin-left: 250px;
            padding: 90px 20px 20px 20px;
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        /* ===== MOBILE ONLY ===== */
        @media (max-width: 991px) {
            .sidebar-admin {
                display: none;
            }

            .header-admin {
                left: 0 !important;
            }

            .main-content {
                margin-left: 0;
                padding-top: 90px;
            }
        }
    </style>

    <!-- Sidebar Desktop -->
    <div class="sidebar-admin d-none d-lg-flex">
        <div class="sidebar-top">
            <div class="title text-success">SmartNote</div>
            
            <a href="dashboard_admin.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard_admin.php' ? 'active' : '' ?>">
                <i class="bi bi-grid me-2"></i> Dashboard
            </a>
            
            <a href="kelola_rapat_admin.php" class="<?= basename($_SERVER['PHP_SELF']) === 'kelola_rapat_admin.php' ? 'active' : '' ?>">
                <i class="bi bi-people me-2"></i> Kelola Pengguna
            </a>
        </div>

        <div class="sidebar-bottom">
            <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : '' ?>">
                <i class="bi bi-person-circle me-2"></i> Profile
            </a>
            <a href="#" id="logoutBtn" class="text-danger">
                <i class="bi bi-box-arrow-left me-2"></i> Keluar
            </a>
        </div>
    </div>

    <!-- Header / Top Bar -->
    <div class="header-admin">
        <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile">
            <i class="bi bi-list"></i>
        </button>

        <div class="page-title">Detail Notulen</div>

        <div class="right-section">
            <div class="d-none d-md-block text-end me-2">
                <div class="fw-bold small"><?= htmlspecialchars($userName) ?></div>
                <small class="text-muted" style="font-size: 0.75rem;">Administrator</small>
            </div>
            
            <?php if ($userPhoto && file_exists("../file/" . $userPhoto)): ?>
                <img src="../file/<?= htmlspecialchars($userPhoto) ?>" class="rounded-circle border" style="width:40px;height:40px;object-fit:cover;">
            <?php else: ?>
                <i class="bi bi-person-circle fs-2 text-secondary"></i>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar Mobile -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMobile">
         <div class="offcanvas-header">
            <h5 class="offcanvas-title fw-bold text-success">SmartNote</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column justify-content-between">
            <ul class="nav flex-column gap-2">
                <li class="nav-item">
                    <a class="nav-link text-dark fw-medium <?= basename($_SERVER['PHP_SELF']) === 'dashboard_admin.php' ? 'bg-success text-white rounded' : '' ?>" href="dashboard_admin.php">
                        <i class="bi bi-grid me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark fw-medium <?= basename($_SERVER['PHP_SELF']) === 'kelola_rapat_admin.php' ? 'bg-success text-white rounded' : '' ?>" href="kelola_rapat_admin.php">
                        <i class="bi bi-people me-2"></i> Kelola Pengguna
                    </a>
                </li>
            </ul>

            <ul class="nav flex-column gap-2 mt-4 border-top pt-3">
                 <li class="nav-item">
                    <a class="nav-link text-dark fw-medium <?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'bg-success text-white rounded' : '' ?>" href="profile.php">
                        <i class="bi bi-person-circle me-2"></i> Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a id="logoutBtnMobile" class="nav-link text-danger fw-medium" href="#">
                        <i class="bi bi-box-arrow-left me-2"></i> Keluar
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3"></div>

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
                const confirmed = await showConfirm("Yakin mau keluar?");
                if (confirmed) {
                    window.location.href = "../proses/proses_logout.php";
                }
            });
        }
        const logoutBtnMobile = document.getElementById("logoutBtnMobile");
        if (logoutBtnMobile) {
            logoutBtnMobile.addEventListener("click", async function (e) {
                e.preventDefault();
                const confirmed = await showConfirm("Yakin mau keluar?");
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
