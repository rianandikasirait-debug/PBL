<?php
include '../config_admin/db_tambah_peserta.php';
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
            
        <?php endif; ?>
    $_SESSION['success_message'] = 'Pengguna berhasil ditambahkan';
    header('Location: ../admin/kelola_rapat_admin.php');
    exit;
        </script>

    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tambah Peserta - SmartNote</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
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

<?php 
    $pageTitle = "Tambah Pengguna";
    // sidebar
    include '../Nav_Side_Bar/sidebar.php'; 
    // header
    include '../Nav_Side_Bar/header.php';
?>

<!-- Main Content -->
        <div class="main-content">
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
                                <div id="emailSuggestionContainer" class="mt-2" style="display: none;"></div>
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
                            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2 mobile-action-container">
                                <a href="kelola_rapat_admin.php" class="btn btn-back btn-sm-mobile">Kembali</a>
                                <div class="d-flex gap-2">
                                    <button type="reset" class="btn btn-secondary btn-sm-mobile">Batal</button>
                                    <button type="submit" class="btn btn-success btn-sm-mobile">
                                        <i class="bi bi-person-plus me-1"></i> <span class="d-none d-sm-inline">Tambahkan Pengguna</span><span class="d-inline d-sm-none">Simpan</span>
                                    </button>
                                </div>
                            </div>
                            
                            <style>
                                @media (max-width: 576px) {
                                    .btn-sm-mobile {
                                        font-size: 0.85rem;
                                        padding: 0.375rem 0.75rem;
                                    }
                                    /* Make green button smaller as requested */
                                    .btn-success.btn-sm-mobile {
                                        padding-left: 1rem;
                                        padding-right: 1rem;
                                    }
                                }
                            </style>
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
                const confirmed = await showConfirm("Yakin mau keluar?");
                if (confirmed) {
                    localStorage.removeItem("adminData");
                    window.location.href = "../proses/proses_logout.php";
                }
            });

            const logoutBtnMobile = document.getElementById("logoutBtnMobile");
            if (logoutBtnMobile) {
                logoutBtnMobile.addEventListener("click", async function (e) {
                    e.preventDefault();
                    const confirmed = await showConfirm("Yakin mau keluar?");
                    if (confirmed) {
                        localStorage.removeItem("adminData");
                        window.location.href = "../proses/proses_logout.php";
                    }
                });
            }
        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../js/admin.js"></script>
    <script>
        // Click-to-Fill Email Suggestion Logic
        document.addEventListener('DOMContentLoaded', function() {
            const namaInput = document.getElementById('nama');
            const emailInput = document.getElementById('email');
            const suggestionContainer = document.getElementById('emailSuggestionContainer');

            namaInput.addEventListener('input', function() {
                const name = this.value;
                // Basic sanitization: lowercase, remove special chars, replace spaces with nothing
                const cleanName = name.toLowerCase().replace(/[^a-z0-9]/g, '');
                
                if (cleanName.length > 0) {
                    const candidateEmail = cleanName + '@gmail.com';
                    // Show clickable badge
                    suggestionContainer.style.display = 'block';
                    suggestionContainer.innerHTML = `
                        <small class="text-muted d-block mb-1">Rekomendasi:</small>
                        <span class="badge bg-success-subtle text-success border border-success cur-pointer" 
                              style="cursor: pointer; font-size: 0.9rem;"
                              onclick="fillEmail('${candidateEmail}')">
                            <i class="bi bi-magic me-1"></i> ${candidateEmail}
                        </span>
                    `;
                } else {
                    suggestionContainer.style.display = 'none';
                    suggestionContainer.innerHTML = '';
                }
            });
            
            // Function to handle the click (attached to window or defined here if simple span onclick)
            // Ideally define it globally or inside the span onclick as inline JS wrapper access
            window.fillEmail = function(email) {
                emailInput.value = email;
                // Optional: visual feedback
                emailInput.classList.add('is-valid');
                setTimeout(() => emailInput.classList.remove('is-valid'), 1000);
            };
        });
    </script>
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