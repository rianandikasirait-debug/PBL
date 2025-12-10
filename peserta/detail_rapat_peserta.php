<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek Login & Role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'peserta') {
    header("Location: ../login.php");
    exit;
}

$id_notulen = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_notulen <= 0) {
    echo "<script>alert('ID Notulen tidak valid!'); window.location.href='dashboard_peserta.php';</script>";
    exit;
}

// Mark as viewed in session
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
    echo "<script>alert('Data notulen tidak ditemukan!'); window.location.href='dashboard_peserta.php';</script>";
    exit;
}

// Siapkan variabel yang dipakai di HTML
$tanggal = !empty($notulen['tanggal_rapat']) ? date('d M Y', strtotime($notulen['tanggal_rapat'])) : '-';
$lampiran = $notulen['Lampiran'] ?? '';
$created_by = $notulen['created_by'] ?? 'Admin';
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
        body { background-color: #fdf9f4; font-family: "Poppins", sans-serif; }
        /* ... tetapkan style kamu ... */
        .sidebar-content { min-width: 250px; background: #fff; height: 100%; border-right: 1px solid #eee; padding: 1.5rem 1rem; }
        .sidebar-content .nav-link { color: #555; font-weight: 500; margin-bottom: 0.5rem; border-radius: 0.5rem; }
        .sidebar-content .nav-link.active, .sidebar-content .nav-link:hover { background-color: #00b050; color: #fff; }
        .logout-btn { border: 1px solid #f8d7da; color: #dc3545; border-radius: .5rem; margin-top: 2rem; transition: 0.3s; }
        .logout-btn:hover { background-color: #f8d7da; }
        .main-content { margin-left: 260px; padding: 1.5rem; }
        @media (max-width: 991.98px) { .main-content { margin-left: 0; } }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .content-card { background-color: #ffffff; border-radius: 1rem; padding: 1.5rem 2rem; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05); }
        .content-card h4 { font-weight: 600; margin-bottom: 0.25rem; }
        .content-card p { margin-bottom: 0.25rem; }
        .content-card hr { margin: 1rem 0; color: #ddd; }
        .participant-badge { background-color: #d1f3e0; color: #15623d; border-radius: 20px; padding: 6px 15px; font-size: 0.9rem; display: inline-flex; align-items: center; margin-right: 8px; }
        .participant-badge i { margin-right: 5px; }
        .btn-back { color: #00b050; text-decoration: none; font-weight: 500; }
        .btn-back:hover { text-decoration: underline; }
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
                    <h4 class="fw-bold mb-4 ms-3">MENU</h4>
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
            <h4 class="fw-bold mb-4 ms-3">MENU</h4>
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
            <div></div>
            <div class="profile">
                <span>Halo, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Peserta') ?>👋</span>
            </div>
        </div>

        <!-- Detail Rapat -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4><?= htmlspecialchars($notulen['judul_rapat']); ?></h4>
                    <p class="text-muted">Dibuat oleh: <?= htmlspecialchars($created_by); ?></p>
                </div>
                <div class="text-end">
                    <p class="fw-semibold mb-0">Tanggal Rapat:</p>
                    <p class="mb-0"><?= htmlspecialchars($tanggal); ?></p>
                </div>
            </div>

            <hr>

            <div class="mb-4">
            <?php if (!empty($lampiran) && file_exists(__DIR__ . '/../uploads/' . $lampiran)): ?>
                <a href="<?= '../uploads/' . rawurlencode($lampiran) ?>" class="btn btn-outline-secondary" download>
                    <i class="bi bi-download me-2"></i>Download Lampiran
                </a>
            <?php else: ?>
                <p class="text-muted">Tidak ada lampiran.</p>
            <?php endif; ?>
            </div>

            <div class="text-end mt-4">
                <a href="dashboard_peserta.php" class="btn-back"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Logout function
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
