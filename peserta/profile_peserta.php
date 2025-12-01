<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek Login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'peserta') {
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
    // Jika user tidak ditemukan (misal dihapus admin saat sedang login)
    session_destroy();
    header("Location: ../login.php");
    exit;
}

// Set default foto jika kosong
$foto_profile = (!empty($user['foto']) ? '../file/' . $user['foto'] : '../file/user.jpg') . '?v=' . time();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #fdf9f4;
            font-family: "Poppins", sans-serif;
        }

        /* Sidebar */
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

        .sidebar-content .nav-link.active,
        .sidebar-content .nav-link:hover {
            background-color: #00c853;
            color: #fff;
        }

        .logout-btn {
            border: 1px solid #f8d7da;
            color: #dc3545;
            border-radius: .5rem;
            margin-top: 2rem;
        }

        /* Main content */
        .main-content {
            margin-left: 260px;
            padding: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0;
            }
        }

        .profile-box {
            background-color: #fff;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
        }

        .badge-role {
            background-color: #00c853;
            color: white;
            font-size: 0.8rem;
            border-radius: 0.5rem;
            padding: 0.3rem 0.7rem;
        }

        .btn-edit {
            background-color: #00c853;
            color: white;
            border: none;
            border-radius: .5rem;
            font-weight: 500;
            transition: 0.2s;
        }

        .btn-edit:hover {
            background-color: #02913f;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-weight: 500;
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
    <!-- sidebar moblie -->
    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas"
        aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-body p-0">
            <div class="sidebar-content d-flex flex-column justify-content-between h-100">
                <div>
                    <h5 class="fw-bold mb-4 ms-3">Menu</h5>
                    <ul class="nav flex-column">
                        <li><a class="nav-link" href="dashboard_peserta.php"><i
                                    class="bi bi-grid me-2"></i>Dashboard</a>
                        </li>
                        <li><a class="nav-link active" href="profile_peserta.php"><i
                                    class="bi bi-person-circle me-2"></i>Profile</a></li>
                    </ul>
                </div>

                <div class="text-center mt-4">
                    <button id="logoutBtnMobile" class="btn logout-btn px-4 py-2">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </button>
                </div>
            </div>
        </div>
    </div>



    <!-- Sidebar -->
    <div class="sidebar-content d-none d-lg-flex flex-column justify-content-between position-fixed">
        <div>
            <h5 class="fw-bold mb-4 ms-3">Menu</h5>
            <ul class="nav flex-column">
                <li><a class="nav-link" href="dashboard_peserta.php"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
                <li><a class="nav-link active" href="profile_peserta.php"><i class="bi bi-person me-2"></i>Profile</a>
                </li>
            </ul>
        </div>

        <div class="text-center">
            <button id="logoutBtn" class="btn logout-btn px-4 py-2"><i
                    class="bi bi-box-arrow-right me-2"></i>Logout</button>
        </div>
    </div>

    <!-- Main -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4><b>Profile Peserta</b></h4>
                <!-- Alert Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
            </div>
            <div class="profile">
                <span>Halo, <?= htmlspecialchars($user['nama']); ?> 👋</span>
                <img src="<?= htmlspecialchars($foto_profile); ?>" alt="Profile" class="rounded-circle"
                    style="width: 40px; height: 40px; object-fit: cover;">
            </div>
        </div>

        <div class="profile-box">
            <h5 class="fw-semibold mb-3"></h5>

            <table class="table">
                <tbody>
                    <tr>
                        <th style="width: 20%;">Nama:</th>
                        <td><?= htmlspecialchars($user['nama']); ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?= htmlspecialchars($user['email']); ?></td>
                    </tr>
                    <tr>
                        <th>NIK:</th>
                        <td><?= htmlspecialchars($user['nik']); ?></td>
                    </tr>
                    <tr>
                        <th>Role:</th>
                        <td><?= ucfirst($user['role']); ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="d-flex justify-content-end">
                <a href="edit_profile_peserta.php" class="btn btn-edit"><i class="bi bi-pencil me-2"></i>Edit Profil</a>
            </div>
        </div>
    </div>

    <script>
        // Logout function
        function confirmLogout() {
            if (confirm("Apakah kamu yakin ingin logout?")) {
                window.location.href = "../login.php";
            }
        }

        document.getElementById("logoutBtn").addEventListener("click", confirmLogout);

        const logoutBtnMobile = document.getElementById("logoutBtnMobile");
        if (logoutBtnMobile) {
            logoutBtnMobile.addEventListener("click", confirmLogout);
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>