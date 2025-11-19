<?php
session_start();

// Jika belum login, kirim ke login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

/*
 * Ambil data user dari session tapi selalu fallback ke string kosong
 * supaya tidak ada "undefined array key" dan htmlspecialchars tidak dikasih null.
 */
$nama  = isset($_SESSION['user_name'])  ? $_SESSION['user_name']  : '';
$email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
$role  = isset($_SESSION['user_role'])  ? $_SESSION['user_role']  : '';
$foto  = isset($_SESSION['user_foto'])   && !empty($_SESSION['user_foto']) ? $_SESSION['user_foto'] : 'user.jpg';

// Pastikan semua yang akan di-echo lewat htmlspecialchars selalu string
$nama_html  = htmlspecialchars((string)$nama, ENT_QUOTES, 'UTF-8');
$email_html = htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8');
$role_html  = htmlspecialchars((string)$role, ENT_QUOTES, 'UTF-8');
$foto_html  = htmlspecialchars((string)$foto, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Pengguna</title>
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
                        <li><a class="nav-link" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
                        </li>
                    </ul>
                </div>

                <div class="text-center mt-4">
                    <button id="logoutBtnMobile" class="btn logout-btn px-4 py-2" x>
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
                    <a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                </li>
                <li>
                    <a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola
                        Pengguna</a>
                </li>
                <li>
                    <a class="nav-link active" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
                </li>
            </ul>
        </div>

        <div class="text-center">
            <button id="logoutBtn" class="btn logout-btn px-4 py-2">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </button>
        </div>
    </div>

    <!-- Main -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
            </div>
            <div class="profile">
                <span>Halo, Admin 👋</span>
            </div>
        </div>

        <div class="profile-box">
            <h5 class="fw-semibold mb-4"><i class="bi bi-pencil-square me-2"></i>Edit Profil Pengguna</h5>

            <form action="../proses/proses_edit_profile.php" method="POST" enctype="multipart/form-data">
                <div class="text-center mb-4">
                    <img src="../file/<?= $foto_html ?>" width="100" height="100" class="rounded-circle mb-2" alt="Foto Profil">

                    <div>
                        <input type="file" name="foto" class="form-control w-auto mx-auto" accept=".jpg,.png,.gif">
                        <small class="text-muted d-block mt-1">Kosongkan jika tidak ingin mengubah foto</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input id="nama" name="nama" type="text" class="form-control" value="<?= $nama_html ?>"
                        class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input id="email" name="email" type="email" class="form-control" disabled value="<?= $email_html ?>"
                        class="form-control" disabled>
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
                    <button type="submit" id="simpan_perubahan" class="btn btn-save">
                        <i class="bi bi-check2-circle me-1"></i>Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById("logoutBtn").addEventListener("click", function () {
            const confirmLogout = confirm("Apakah kamu yakin ingin logout?");
            if (confirmLogout) {
                localStorage.removeItem("adminData");
                window.location.href = "../login.php";
            }
        });
        // logout mobile
        const logoutBtnMobile = document.getElementById("logoutBtnMobile");
        if (logoutBtnMobile) {
            logoutBtnMobile.addEventListener("click", function () {
                const konfirmasiLogout = confirm("Apakah kamu yakin ingin logout?");
                if (konfirmasiLogout) {
                    localStorage.removeItem("adminData");
                    window.location.href = "../login.php";
                }
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>