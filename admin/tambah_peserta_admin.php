<?php
session_start(); // PASTIKAN hanya 1x di file ini

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil data pengguna yang sedang login
require_once __DIR__ . '/../koneksi.php';
$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nama, foto FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userRes = $stmt->get_result();
$userData = $userRes->fetch_assoc();
$stmt->close();
$userName = $userData['nama'] ?? 'Admin';
$userPhoto = $userData['foto'] ?? null;

// Ambil pesan dan kosongkan sesi supaya tidak tampil lagi setelah muat ulang
$success_msg = $_SESSION['success_message'] ?? '';
$error_msg = $_SESSION['error_message'] ?? '';

if ($success_msg)
    unset($_SESSION['success_message']);
if ($error_msg)
    unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
</head>

<body>

    <?php if ($success_msg): ?>
        <script>
            // json_encode agar aman untuk kutip & karakter khusus
            window.addEventListener('DOMContentLoaded', function () {
                alert(<?= json_encode($success_msg) ?>);
            });
        </script>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                alert(<?= json_encode($error_msg) ?>);
            });
        </script>
    <?php endif; ?>
    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tambah Pengguna Baru</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="../css/admin.min.css">
        <style>
            .btn.btn-secondary{
                background-color: #00C853 !important; 
                border-color: #00C853 !important;
                color: #ffffff !important;
                font-weight: bold;
                text-decoration: none !important;
            }
            .btn.btn-secondary:hover, .btn.btn-secondary:focus{
                background-color: #02913f !important; 
                border-color: #02913f !important;
            }
            .btn.btn-success{
                background-color: #00C853 !important; 
                border-color: #00C853 !important;
                color: #ffffff !important;
                font-weight: bold;
                text-decoration: none !important;
            }
            .btn.btn-success:hover, .btn.btn-success:focus{
                background-color: #02913f !important; 
                border-color: #02913f !important;
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
    
    <!-- Sidebar Mobile -->
    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas"
        aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-body p-0">
            <div class="sidebar-content d-flex flex-column justify-content-between h-100">
                <div>
                    <h4 class="fw-bold mb-4 ms-3">MENU</h4>
                    <ul class="nav flex-column">
                        <li>
                            <a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                        </li>
                        <li>
                            <a class="nav-link active" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a>
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
            <h4 class="fw-bold mb-4 ms-3">MENU</h4>
            <ul class="nav flex-column">
                <li>
                    <a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                </li>
                <li>
                    <a class="nav-link active" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a>
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
<!-- Main Content -->
        <div class="main-content">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-semibold"><i class="bi bi-person-plus-fill me-2"></i>Tambah Pengguna Baru</h5>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-end">
                        <span class="d-block fw-medium text-dark">Halo, <?= htmlspecialchars($userName) ?> 👋</span>
                    </div>
                    <img src="<?= $userPhoto ? '../file/' . htmlspecialchars($userPhoto) : '../file/user.jpg' ?>"
                         alt="Profile"
                         class="rounded-circle shadow-sm"
                         style="width: 45px; height: 45px; object-fit: cover; border: 2px solid #fff;"
                         onerror="this.onerror=null;this.src='../file/user.jpg';">
                </div>
            </div>
            
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="kelola_rapat_admin.php">Kelola Pengguna</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah Pengguna</li>
                </ol>
            </nav>
            <div class="form-section">
                <div class="card shadow-sm border-success">
                    <div class="card-body">

                        <?php
                        // Menampilkan pesan sukses (jika ada) dari sesi
                        if (isset($_SESSION['success_message'])) {
                            echo '<div class="alert alert-success" role="alert">';
                            echo htmlspecialchars($_SESSION['success_message']);
                            echo '</div>';
                            unset($_SESSION['success_message']); // Hapus pesan setelah ditampilkan
                        }

                        // Menampilkan pesan error (jika ada) dari sesi
                        if (isset($_SESSION['error_message'])) {
                            echo '<div class="alert alert-danger" role="alert">';
                            echo htmlspecialchars($_SESSION['error_message']);
                            echo '</div>';
                            unset($_SESSION['error_message']); // Hapus pesan setelah ditampilkan
                        }
                        ?>

                        <form id="addUserForm" action="../proses/proses_tambah_peserta.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" id="nama" name="nama" class="form-control"
                                    placeholder="Masukkan nama pengguna baru" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control"
                                    placeholder="Masukkan email pengguna baru" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">NIK</label>
                                <input type="text" id="nik" name="nik" class="form-control"
                                    placeholder="Masukkan NIK peserta" required>
                                <small class="text-muted d-block mt-1">⚠️ NIK akan digunakan sebagai password default. Peserta wajib mengganti password saat login pertama.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nomor WhatsApp <span class="badge bg-info">Opsional</span></label>
                                <input type="text" id="nomor_whatsapp" name="nomor_whatsapp" class="form-control"
                                    placeholder="Contoh: 62812345678 atau 0812345678">
                                <small class="text-muted d-block mt-1">Jika diisi, akun peserta akan dikirim otomatis via WhatsApp</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select id="role" name="role" class="form-select" required disabled>
                                    <option value="peserta" selected>Peserta</option>
                                </select>
                                <small class="text-muted">Role akan otomatis diatur sebagai 'Peserta'.</small>
                            </div>

                            <hr>
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <a href="kelola_rapat_admin.php" class="btn btn-back">Kembali</a>
                                <div class="d-flex gap-3">
                                    <button type="reset" class="btn btn-secondary">Batal</button>
                                    <button type="submit" class="btn btn-success"><i class="bi bi-person-plus me-2"></i>Tambahkan Pengguna</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
        <script>
            // cek URL apakah ada ?added=1
            const params = new URLSearchParams(window.location.search);

            if (params.get("added") === "1") {
                showToast("Pengguna berhasil ditambahkan!", 'success');
                // hapus parameter agar tidak muncul lagi saat muat ulang
                params.delete("added");
                window.history.replaceState({}, "", window.location.pathname);
            }

            if (params.get("added") === "0") {
                showToast("Gagal menambahkan pengguna!", 'error');
                params.delete("added");
                window.history.replaceState({}, "", window.location.pathname);
            }
        </script>
        <script>
            // Fungsi Logout
            document.getElementById("logoutBtn").addEventListener("click", async function (e) {
                e.preventDefault();
                const confirmed = await showConfirm("Apakah kamu yakin ingin logout?");
                if (confirmed) {
                    localStorage.removeItem("adminData");
                    window.location.href = "../proses/proses_logout.php";
                }
            });

            const logoutBtnMobile = document.getElementById("logoutBtnMobile");
            if (logoutBtnMobile) {
                logoutBtnMobile.addEventListener("click", async function (e) {
                    e.preventDefault();
                    const confirmed = await showConfirm("Apakah kamu yakin ingin logout?");
                    if (confirmed) {
                        localStorage.removeItem("adminData");
                        window.location.href = "../proses/proses_logout.php";
                    }
                });
            }
        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../js/admin.js"></script>
        <?php
        if (session_status() === PHP_SESSION_NONE)
            session_start();

        if (!empty($_SESSION['success_message'])) {
            $msg = $_SESSION['success_message'];
            unset($_SESSION['success_message']); // supaya tidak muncul lagi jika dimuat ulang
            echo "<script>showToast('$msg', 'success');</script>";
        }
        ?>
    </body>

    </html>