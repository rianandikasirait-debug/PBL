<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek Login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['user_id'];

// Ambil data terbaru dari database
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

$foto_profile = !empty($user['foto']) ? '../file/' . $user['foto'] : '../file/user.jpg';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" />    
    <link rel="stylesheet" href="../css/admin.min.css">

</head>

<body>
    <!-- navbar -->
    <!-- navbar -->
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
                            <a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a>
                        </li>
                    </ul>
                </div>

                <div class="mt-auto px-3">
                    <ul class="nav flex-column mb-3">
                        <li>
                            <a class="nav-link active" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
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
                    <a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a>
                </li>
            </ul>
        </div>

        <div>
            <ul class="nav flex-column mb-3">
                <li>
                    <a class="nav-link active" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
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
            <h4><b>Profile Admin</b></h4>
            <div class="w-50">
                <!-- Peringatan Error -->
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
            </div>
            <div class="profile">
                <span>Halo, <?= htmlspecialchars($user['nama']); ?> 👋</span>
                <img src="<?= htmlspecialchars($foto_profile); ?>" alt="Profile" class="rounded-circle"
                    style="width: 40px; height: 40px; object-fit: cover;">
            </div>
        </div>

        <div class="profile-box">
            <h5 class="fw-semibold mb-4">
                <span class="material-symbols-outlined me-2" style="
                    font-size:23px;
                    line-height: 1;
                    vertical-align: -4px;">
                    person_edit
                </span>
                Edit Profil Pengguna
            </h5>

            <form action="../proses/proses_edit_profile.php" method="POST" enctype="multipart/form-data">
                <div class="text-center mb-4">
                    <img src="<?= htmlspecialchars($foto_profile); ?>" width="100" height="100"
                        class="rounded-circle mb-2" style="object-fit: cover; border: 2px solid #ddd;"
                        alt="Foto Profil">
                    <div>
                        <input type="file" name="foto" class="form-control w-auto mx-auto" accept=".jpg,.png,.jpeg">
                        <small class="text-muted d-block mt-1">Kosongkan jika tidak ingin mengubah foto (Maks.
                            2MB)</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']); ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" disabled>
                    <small class="text-muted">Email tidak dapat diubah</small>
                </div>

                <hr>

                <h6 class="fw-semibold mb-3">Ubah Password (Opsional)</h6>

                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="password_baru" class="form-control"
                        placeholder="Kosongkan jika tidak ganti password">
                </div>

                <div class="mb-4">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="password_konfirmasi" class="form-control"
                        placeholder="Ulangi password baru">
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="profile.php" class="btn btn-cancel">Batal</a>
                    <button type="submit" class="btn btn-save"><i class="bi bi-check2-circle me-1"></i>Simpan
                        Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fungsi Logout
        async function confirmLogout(e) {
            e.preventDefault();
            const confirmed = await showConfirm("Apakah kamu yakin ingin logout?");
            if (confirmed) {
                window.location.href = "../proses/proses_logout.php";
            }
        }

        document.getElementById("logoutBtn").addEventListener("click", confirmLogout);

        const logoutBtnMobile = document.getElementById("logoutBtnMobile");
        if (logoutBtnMobile) {
            logoutBtnMobile.addEventListener("click", confirmLogout);
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
</body>

</html>