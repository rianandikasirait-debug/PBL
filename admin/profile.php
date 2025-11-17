<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.min.css">
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
                        <li><a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                        </li>
                        <li><a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola
                                Pengguna</a></li>
                        <li><a class="nav-link" href="tambah_peserta_admin.php"><i
                                    class="bi bi-person-plus me-2"></i>Tambah Pengguna</a></li>
                        <li><a class="nav-link" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
                        </li>
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
                <li>
                    <a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
                <li>
                    <a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola
                        Pengguna</a></li>
                <li>
                    <a class="nav-link active" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
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
                <h4><b>Profile Admin</b></h4>
            </div>
            <div class="profile">
                <span>Halo, Admin 👋</span>
            </div>
        </div>

        <div class="profile-box">
            <h5 class="fw-semibold mb-3"></h5>

            <table class="table">
                <tbody>
                    <tr>
                        <th style="width: 20%;">Nama:</th>
                        <td id="nama">didit</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td id="email">didit25@gmail.com</td>
                    </tr>
                    <tr>
                        <th>Role:</th>
                        <td><span class="badge-role" id="role">Admin</span></td>
                    </tr>
                </tbody>
            </table>

            <div class="d-flex justify-content-end">
                <a href="edit_profile_admin.php" class="btn btn-edit"><i id="editprofile" class="bi bi-pencil me-2"></i>Edit
                    Profil</a>
            </div>
        </div>
    </div>
    <script>
        
        document.addEventListener('DOMContentLoaded', function () {
            const btnTambah = document.getElementById('editprofile');
            if (btnTambah) {
                btnTambah.addEventListener('click', function () {
                    window.location.href = 'edit_profile_admin.php';
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>