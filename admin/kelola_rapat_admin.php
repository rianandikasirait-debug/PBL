<?php
include '../config_admin/db_kelola_rapat_admin.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kelola Pengguna</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:wght@400" />
    <link rel="stylesheet" href="../css/admin.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">

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

        /* Soft Delete Button Style */
        .btn-soft-danger {
            background-color: #fee2e2;
            color: #dc3545;
            border-radius: 8px;
            width: 45px; /* Slightly larger */
            height: 45px; /* Slightly larger */
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            border: none;
            font-size: 1.1rem;
        }

        .btn-soft-danger:hover {
            background-color: #fca5a5;
            color: #b91c1c;
        }

        /* Soft Edit Button Style */
        .btn-soft-success {
            background-color: #e8f5e9;
            color: #198754;
            border-radius: 8px;
            width: 45px;
            height: 45px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            border: none;
            font-size: 1.1rem;
        }

        .btn-soft-success:hover {
            background-color: #c8e6c9;
            color: #1b5e20;
        }

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

        .badge-role-custom {
            background-color: #e8f5e9;
            color: #1b5e20;
            padding: 6px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-block;
            letter-spacing: 0.5px;
        }

        /* OVERRIDE MOBILE TABLE STYLES */
        @media (max-width: 767.98px) {
            .table-responsive {
                display: block !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
            }
            .table-responsive table {
                display: table !important;
                width: max-content !important; /* Allow table to grow beyond screen width */
                table-layout: auto !important; /* Let columns size themselves */
            }
            .table-responsive table thead {
                display: table-header-group !important;
            }
            .table-responsive table tbody tr {
                display: table-row !important;
            }
            .table-responsive table tbody tr td, 
            .table-responsive table thead tr th {
                display: table-cell !important;
                border-bottom: 1px solid #dee2e6 !important;
                padding: 1rem 2rem !important;
                white-space: nowrap !important;
                vertical-align: middle !important; /* Ensure vertical centering */
                min-width: 150px !important;
                width: auto !important; /* Override global fixed widths */
            }
            /* Specific Alignment for Action and No columns */
            .table-responsive table tbody tr td:last-child, 
            .table-responsive table thead tr th:last-child,
            .table-responsive table tbody tr td:first-child, 
            .table-responsive table thead tr th:first-child {
                text-align: center !important;
                width: 1% !important; /* Shrink to fit content + padding */
                white-space: nowrap !important;
                min-width: 80px !important; /* Explicit smaller width for these columns */
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
        }
        #rowsPerPage {
            color: #495057;
        }

        /* Custom Tambah Pengguna Button Style */
        .btn-tambah-pengguna {
            border: 1.5px solid #198754;
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 500;
            font-size: 0.95rem;
            background-color: #fff;
            color: #198754;
            transition: all 0.25s ease;
        }

        .btn-tambah-pengguna:hover {
            background-color: #198754;
            border-color: #198754;
            color: #fff;
        }
    </style>

<?php 
    $pageTitle = "Kelola Pengguna";
    // sidebar
    include '../Nav_Side_Bar/sidebar.php';
    // header
    include '../Nav_Side_Bar/header.php'; 
?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="table-wrapper">
            <div class="toolbar-admin mb-3">
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    <span class="material-symbols-rounded" style="font-size:28px;">
                        person_search
                    </span>
                    <input type="text" id="searchInput" class="form-control search-box flex-grow-1" placeholder="Cari pengguna...">
                </div>

                <!-- DROPDOWN ROWS PER PAGE -->
                <style>
                    /* Konsistensi Border untuk Toolbar */
                    .toolbar-admin .search-box {
                        border: 1.5px solid #dee2e6;
                        border-radius: 8px;
                        transition: all 0.25s ease;
                    }

                    .toolbar-admin .search-box:focus {
                        border-color: #198754;
                        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
                    }

                    .form-select-green-outline {
                        border: 1.5px solid #198754 !important;
                        color: #198754;
                        border-radius: 8px;
                        font-weight: 500;
                        padding-right: 2.5rem;
                        width: auto;
                        background-color: #fff;
                        transition: all 0.25s ease;
                    }

                    .form-select-green-outline:hover {
                        background-color: #f8f9fa;
                    }

                    .form-select-green-outline:focus {
                        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
                        border-color: #198754;
                    }

                    .toolbar-admin {
                        display: flex;
                        gap: 10px;
                        flex-wrap: wrap;
                    }

                    /* SEARCH FULL */
                    .toolbar-admin .search-box {
                        flex: 1 1 100%;
                    }

                    /* DATA + BUTTON */
                    @media (max-width: 576px) {
                        #rowsPerPage {
                            width: 120px;
                        }

                        .toolbar-admin .btn-success {
                            flex: 1;
                        }
                    }
                </style>
                <select id="rowsPerPage" class="form-select form-select-green-outline">
                    <option value="5">5 Data</option>
                    <option value="10" selected>10 Data</option>
                    <option value="25">25 Data</option>
                    <option value="50">50 Data</option>
                    <option value="all">Semua Data</option>
                </select>

                <button type="button" class="btn btn-outline-success d-flex align-items-center gap-2 btn-tambah-pengguna" data-bs-toggle="modal" data-bs-target="#modalTambahPengguna">
                    <i class="bi bi-person-plus"></i> Tambah Pengguna
                </button>
            </div>

                <div class="table-responsive" style="overflow-x: auto; white-space: nowrap;">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">NO</th>
                            <th>FOTO</th>
                            <th>NAMA</th>
                            <th>NIK</th>
                            <th>EMAIL</th>
                            <th class="text-center">ROLE</th>
                            <th class="text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <small class="text-muted" id="dataInfo"></small>
                <nav>
                    <ul class="pagination pagination-sm mb-0 green" id="pagination"></ul>
                </nav>
            </div>
        </div>
    </div>
    </div>
    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0 pe-4 pt-4">
                    <h5 class="modal-title fw-bold text-dark fs-4">Edit Info Pengguna</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-3">
                    <form id="editUserForm">
                        <input type="hidden" id="edit_id" name="id">
                        
                        <div class="mb-3">
                            <label for="edit_nama" class="form-label fw-medium text-dark">Nama Lengkap</label>
                            <input type="text" class="form-control form-control-lg rounded-3 fs-6" id="edit_nama" name="nama" required placeholder="Masukkan nama lengkap">
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_email" class="form-label fw-medium text-dark">Email</label>
                            <input type="email" class="form-control form-control-lg rounded-3 fs-6" id="edit_email" name="email" required placeholder="nama@email.com">
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="edit_nik" class="form-label fw-medium text-dark">NIK</label>
                                <input type="text" class="form-control form-control-lg rounded-3 fs-6" id="edit_nik" name="nik" required placeholder="Nomor Induk">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_whatsapp" class="form-label fw-medium text-dark">WhatsApp</label>
                                <input type="text" class="form-control form-control-lg rounded-3 fs-6" id="edit_whatsapp" name="nomor_whatsapp" placeholder="08xxxxxxxxxx">
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded-3 border border-light-subtle mb-4">
                            <div class="d-flex gap-3">
                                <div class="form-check pt-1">
                                    <input class="form-check-input" type="checkbox" id="reset_password" name="reset_password" value="1" style="width: 1.25em; height: 1.25em; cursor: pointer;">
                                </div>
                                <div>
                                    <label class="form-check-label fw-semibold text-dark mb-1" for="reset_password" style="cursor: pointer;">
                                        Reset Password
                                    </label>
                                    <div class="text-secondary small">Password akan diatur ulang sama dengan NIK pengguna.</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-1">
                            <button type="button" class="btn btn-light border bg-white px-4 fw-medium rounded-3 py-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success px-4 fw-medium rounded-3 py-2" id="btnSaveEdit">
                                <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                                Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Pengguna Baru -->
    <div class="modal fade" id="modalTambahPengguna" tabindex="-1" aria-labelledby="modalTambahPenggunaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahPenggunaLabel">
                        <i class="bi bi-person-plus-fill me-2 text-success"></i>Tambah Pengguna Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formTambahPengguna">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="newNama" name="nama" placeholder="Masukkan nama pengguna baru" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="newEmail" name="email" placeholder="Masukkan email pengguna baru" required>
                            <div id="emailSuggestionModal" class="mt-2" style="display: none;"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">NIK</label>
                            <input type="text" class="form-control" id="newNik" name="nik" placeholder="Masukkan NIK peserta" required>
                            <small class="text-muted">⚠️ NIK akan digunakan sebagai password default. Peserta wajib mengganti password saat login pertama.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor WhatsApp <span class="badge bg-info">Opsional</span></label>
                            <input type="text" class="form-control" id="newWhatsapp" name="nomor_whatsapp" placeholder="Contoh: 62812345678 atau 0812345678">
                            <small class="text-muted">Jika diisi, akun peserta akan dikirim otomatis via WhatsApp</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" disabled>
                                <option value="peserta" selected>Peserta</option>
                            </select>
                            <small class="text-muted">Role akan otomatis diatur sebagai 'Peserta'.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btnSimpanPengguna">
                        <i class="bi bi-person-plus me-1"></i>Simpan Pengguna
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="successToast"
            class="toast align-items-center text-bg-success border-0"
            role="alert"
            aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Pesan
                </div>
                <button type="button"
                    class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    <!-- DEBUG: tampilkan data JSON di source page (HTML comment) supaya bisa dicek lewat View Source -->
    <?php
    echo '<!-- DEBUG: $all_users = ' . htmlspecialchars(json_encode($all_users, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') . ' -->';
    ?>

    <script>
        // DATA SEKARANG HANYA BERISI PESERTA KARENA SUDAH DIFILTER OLEH PHP
        window.kelolaRapatUsers = <?php echo json_encode($all_users, JSON_UNESCAPED_UNICODE); ?>;
        
        // ID Admin
        window.currentAdminId = <?php echo (int) $current_admin_id; ?>;

        // Session Message
        <?php if (isset($_SESSION['success_message'])): ?>
            window.sessionSuccessMessage = <?= json_encode($_SESSION['success_message']) ?>;
        <?php unset($_SESSION['success_message']); endif; ?>

        // WA Data
        <?php if ($wa_link): ?>
            window.waData = {
                link: <?= json_encode($wa_link) ?>,
                nomor: <?= json_encode($wa_nomor) ?>,
                message: <?= json_encode($wa_message) ?>
            };
        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
    <script src="../js/kelola_rapat.js"></script>
</body>

</html>