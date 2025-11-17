<?php
// Mulai session untuk bisa menampilkan pesan
session_start();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengguna Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.min.css">
</head>

<body>
    <nav class="navbar navbar-light bg-white sticky-top px-3">
        <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
            <i class="bi bi-list"></i>
        </button>
    </nav>

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

                        <li><a class="nav-link active" href="tambah_peserta_admin.php"><i
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

    <div class="sidebar-content d-none d-lg-flex flex-column justify-content-between position-fixed">
        <div>
            <h5 class="fw-bold mb-4 ms-3">Menu</h5>
            <ul class="nav flex-column">
                <li><a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
                <li><a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola
                        Pengguna</a></li>


                <li><a class="nav-link" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
            </ul>
        </div>

        <div class="text-center">
            <button id="logoutBtn" class="btn logout-btn px-4 py-2"><i
                    class="bi bi-box-arrow-right me-2"></i>Logout</button>
        </div>
    </div>

    <div class="main-content">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="kelola_rapat_admin.php">Kelola Pengguna</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Pengguna</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-semibold"><i class="bi bi-person-plus-fill me-2"></i>Tambah Pengguna Baru</h5>
            <div class="profile">
                <span>Halo, Admin 👋</span>
            </div>
        </div>

        <div class="form-section">
            <div class="card shadow-sm border-success">
                <div class="card-body">

                    <?php
                    // Menampilkan pesan sukses (jika ada) dari session
                    if (isset($_SESSION['success_message'])) {
                        echo '<div class="alert alert-success" role="alert">';
                        echo htmlspecialchars($_SESSION['success_message']);
                        echo '</div>';
                        unset($_SESSION['success_message']); // Hapus pesan setelah ditampilkan
                    }
                    
                    // Menampilkan pesan error (jika ada) dari session
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
                            <label class="form-label">Username</label>
                            <input type="text" id="username" name="username" class="form-control"
                                placeholder="Masukkan username unik" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control"
                                placeholder="Minimal 8 karakter" minlength="8" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select id="role" name="role" class="form-select" required disabled>
                                <option value="peserta" selected>Peserta</option>
                            </select>
                            <small class="text-muted">Role akan otomatis diatur sebagai 'Peserta'.</small>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">Batal</button>
                            <button type="submit" class="btn btn-success"><i
                                    class="bi bi-person-plus me-2"></i>Tambahkan
                                Pengguna</button>
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
            alert("Pengguna berhasil ditambahkan!");
            // hapus param biar gak muncul lagi saat refresh
            params.delete("added");
            window.history.replaceState({}, "", window.location.pathname);
        }

        if (params.get("added") === "0") {
            alert("Gagal menambahkan pengguna!");
            params.delete("added");
            window.history.replaceState({}, "", window.location.pathname);
        }
    </script>
    <script>
        // Logout function
        document.getElementById("logoutBtn").addEventListener("click", function () {
            const confirmLogout = confirm("Apakah kamu yakin ingin logout?");
            if (confirmLogout) {
                // Path logout ini SUDAH BENAR
                window.location.href = "../login.php";
            }
        });

        const logoutBtnMobile = document.getElementById("logoutBtnMobile");
        if (logoutBtnMobile) {
            logoutBtnMobile.addEventListener("click", function () {
                const konfirmasiLogout = confirm("Apakah kamu yakin ingin logout?");
                if (konfirmasiLogout) {
                    // Path logout ini SUDAH BENAR
                    window.location.href = "../login.php";
                }
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>