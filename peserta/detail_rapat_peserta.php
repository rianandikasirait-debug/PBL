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
} else {
    $sessionUserName = null;
    $userPhoto = null;
}

// Ambil data notulen
$sql = "SELECT * FROM tambah_notulen WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_notulen);
$stmt->execute();
$result = $stmt->get_result();
$notulen = $result->fetch_assoc();

if (!$notulen) {
    echo "<script>alert('Data notulen tidak ditemukan!'); window.location.href='dashboard_peserta.php';</script>";
    exit;
}

// Siapkan variabel yang dipakai di HTML
$tanggal = !empty($notulen['tanggal']) ? date('d/m/Y', strtotime($notulen['tanggal'])) : '-';
$lampiran = $notulen['tindak_lanjut'] ?? '';
$created_by = $notulen['tempat'] ?? 'Admin'; // Menggunakan tempat sebagai created_by

// Uraikan peserta SEBELUM menutup koneksi
$peserta_raw = $notulen['peserta'] ?? '';
$peserta_names = [];
if (trim($peserta_raw) !== '') {
    $parts = array_filter(array_map('trim', explode(',', $peserta_raw)), function($v){ return $v !== ''; });
    $peserta_ids = array_filter(array_map('intval', $parts), function($v){ return $v > 0; });
    
    if (!empty($peserta_ids)) {
        $ids_list = implode(',', $peserta_ids);
        $sql_users = "SELECT id, nama FROM users WHERE id IN ($ids_list)";
        $res_users = $conn->query($sql_users);
        $map = [];
        while ($r = $res_users->fetch_assoc()) {
            $map[(int)$r['id']] = $r['nama'];
        }
        
        foreach ($parts as $orig) {
            if (is_numeric($orig)) {
                $idint = (int)$orig;
                if (isset($map[$idint])) {
                    $peserta_names[] = $map[$idint];
                }
            } else {
                $peserta_names[] = $orig;
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Rapat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            background-color: #fdf9f4; 
            font-family: "Poppins", sans-serif; }
        /* ... tetapkan gaya Anda ... */
        .sidebar-content { 
            min-width: 250px; 
            background: #fff; 
            height: 100%; 
            border-right: 1px solid #eee; 
            padding: 1.5rem 1rem; 
        }
        .sidebar-content .nav-link { 
            color: #555; 
            font-weight: 500; 
            margin-bottom: 0.5rem; 
            border-radius: 0.5rem; 
        }
        .sidebar-content .nav-link.active, .sidebar-content .nav-link:hover { 
            background-color: #00b050; 
            color: #fff; 
        }
        .logout-btn { 
            border: 1px solid #f8d7da; 
            color: #dc3545; 
            border-radius: .5rem; 
            margin-top: 2rem; 
            transition: 0.3s; 
        }
        .logout-btn:hover { 
            background-color: #f8d7da; 
        }
        .main-content { 
            margin-left: 260px; 
            padding: 1.5rem; 
        }
        @media (max-width: 991.98px) { 
            .main-content { margin-left: 0; } 
        }
        .topbar { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            margin-bottom: 1.5rem; 
        }
        .content-card { 
            background-color: #ffffff; 
            border-radius: 1rem; 
            padding: 1.5rem 2rem; 
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05); 
        }
        .content-card h4 { 
            font-weight: 600; margin-bottom: 0.25rem; 
            margin-bottom: 0.25rem; 
        }
        .content-card p { 
            margin-bottom: 0.25rem; 
        }
        .content-card hr { 
            margin: 1rem 0; 
            color: #ddd; 
        }
        .participant-badge { 
            background-color: #d1f3e0; 
            color: #15623d; 
            border-radius: 20px; 
            padding: 6px 15px; 
            font-size: 0.9rem; 
            display: inline-flex; 
            align-items: center; 
            margin-right: 8px; 
        }
        .participant-badge i { 
            margin-right: 5px; 
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
    <nav class="navbar navbar-light bg-white sticky-top px-3">
        <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
            <i class="bi bi-list"></i>
        </button>
    </nav>

    <!-- Mobile -->
    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-body p-0">
            <div class="sidebar-content d-flex flex-column justify-content-between h-100">
                <div>
                    <h4 class="fw-bold mb-4 ms-3">SmartNote</h4>
                    <ul class="nav flex-column">
                        <li>
                            <a class="nav-link active" href="dashboard_peserta.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                        </li>
                    </ul>
                </div>

                <div class="mt-auto px-3">
                    <ul class="nav flex-column mb-3">
                        <li>
                            <a class="nav-link" href="profile_peserta.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
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
                    <a class="nav-link active" href="dashboard_peserta.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                </li>
            </ul>
        </div>
        
        <div>
            <ul class="nav flex-column mb-3">
                <li>
                    <a class="nav-link" href="profile_peserta.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
                </li>
                <li>
                    <a id="logoutBtn" class="nav-link text-danger" href="#"><i class="bi bi-box-arrow-right me-2 text-danger"></i>Keluar</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4><b>Dashboard Peserta</b></h4>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end">
                    <span class="d-block fw-medium text-dark">Halo, <?= htmlspecialchars($sessionUserName ?? ($_SESSION['user_name'] ?? 'Peserta')) ?> 👋</span>
                </div>
                <img src="<?= isset($userPhoto) && $userPhoto ? '../file/' . htmlspecialchars($userPhoto) : '../file/user.jpg' ?>"
                     alt="Profile"
                     class="rounded-circle shadow-sm"
                     style="width: 45px; height: 45px; object-fit: cover; border: 2px solid #fff;"
                     onerror="this.onerror=null;this.src='../file/user.jpg';">
            </div>
        </div>

        <!-- Detail Rapat -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4><?= htmlspecialchars($notulen['judul']); ?></h4>
                    <p class="text-muted">Dibuat oleh: <?= htmlspecialchars($created_by); ?></p>
                </div>
                <div class="text-end">
                    <p class="fw-semibold mb-0">Tanggal Rapat:</p>
                    <p class="mb-0"><?= htmlspecialchars($tanggal); ?></p>
                </div>
            </div>

            <hr>

            <div class="mb-4">
                <?= $notulen['hasil'] ?? ''; // Isi rapat ?>
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
            <?php if (!empty($lampiran)): 
                $files = explode('|', $lampiran);
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
                <a href="dashboard_peserta.php" class="btn btn-back"></i> Kembali</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi Logout
        function confirmLogout() {
            if (confirm("Apakah kamu yakin ingin logout?")) {
                window.location.href = "../proses/proses_logout.php";
            }
        }

        document.getElementById("logoutBtn").addEventListener("click", confirmLogout);

        const logoutBtnMobile = document.getElementById("logoutBtnMobile");
        if (logoutBtnMobile) {
            logoutBtnMobile.addEventListener("click", confirmLogout);
        }
    </script>
</body>
</html>
