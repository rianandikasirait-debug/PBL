<?php
include '../config_admin/db_notulen.admin.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Notulen</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/forms.css">
    <!-- Select2 for searchable dropdown -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <script src="https://cdn.tiny.cloud/1/ax0ia7o379mr6qirp1j81wj9ulw3j38l2tvuuwdktdpjxzcj/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        .btn-save {
            background-color: #00C853 !important; 
            border-color: #00C853 !important;
            color: #ffffff !important;
            font-weight: bold;
        }
        .btn-save:hover, .btn-save:focus {
            background-color: #02913f !important; 
            border-color: #02913f !important;
        }
        .sidebar-content .nav-link.active {
            background-color: #00C853 !important;
            color: #ffffff !important;
        }
        .btn.btn-sm.btn-light{
          background-color: #00C853 !important; 
          border-color: #00C853 !important;
          color: #ffffff !important;
        }
        .btn.btn-sm.btn-light:hover, .btn.btn-sm.btn-light:focus{
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
        .btn.btn-outline-success.w-100.py-2.border-dashed {
            background-color: #00C853 !important; 
            border-color: #00C853 !important;
            color: #ffffff !important;
        }
        .btn.btn-outline-success.w-100.py-2.border-dashed:hover, .btn.btn-outline-success.w-100.py-2.border-dashed:focus {
            background-color: #02913f !important; 
            border-color: #02913f !important;
        }
        .btn.btn-secondary {
            background-color: #00C853 !important; 
            border-color: #00C853 !important;
            color: #ffffff !important
        }
        .btn.btn-secondary:hover, .btn.btn-secondary:focus {
            background-color: #02913f !important; 
            border-color: #02913f !important;
        }
        /* List Group Item Style - Match Detail Page */
        .list-group-item.added-item {
            border: none !important;
            border-left: none !important;
            border-right: none !important;
            border-top: none !important;
            border-bottom: none !important;
            background-color: #fff !important;
            box-shadow: none !important;
        }
        .list-group-item.added-item:hover {
            background-color: #fafafa !important;
        }
        /* Select2 Green Theme Override */
        .select2-container--bootstrap-5 .select2-selection {
            border: 1px solid #dee2e6 !important;
        }
        .select2-container--bootstrap-5 .select2-selection:focus,
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border: 1px solid #00C853 !important;
            box-shadow: 0 0 0 2px rgba(0, 200, 83, 0.15) !important;
            outline: none !important;
        }
        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #00C853 !important;
            color: #fff !important;
        }
        .select2-container--bootstrap-5 .select2-results__option--selected {
            background-color: #ffffff !important;
            color: #212529 !important;
        }
        .select2-container--bootstrap-5 .select2-results__option--selected.select2-results__option--highlighted {
            background-color: #00C853 !important;
            color: #fff !important;
        }
        .select2-container--bootstrap-5 .select2-dropdown {
            border: 1px solid #00C853 !important;
        }
        .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
            border: 1px solid #00C853 !important;
        }
        .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field:focus {
            border: 1px solid #00C853 !important;
            box-shadow: none !important;
            outline: none !important;
        }
        /* Native Select Dropdown Green Styling */
        .form-control:focus, .form-select:focus {
            border-color: #00C853 !important;
            box-shadow: 0 0 0 2px rgba(0, 200, 83, 0.15) !important;
            outline: none !important;
        }
        .form-control option:checked, .form-select option:checked {
            background-color: #00C853 !important;
            color: #fff !important;
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
        
        /* Override admin.min.css untuk tabel peserta di mobile */
        .peserta-table-wrapper {
            overflow: visible !important;
        }
        .peserta-table-wrapper .card-body {
            max-height: 350px; /* Sekitar 10 row */
            overflow-y: auto;
        }
        .peserta-table-wrapper table {
            table-layout: fixed !important;
            width: 100% !important;
            min-width: 0 !important;
        }
        .peserta-table-wrapper table thead {
            display: table-header-group !important;
            position: sticky;
            top: 0;
            background: #f8f9fa;
            z-index: 1;
        }
        .peserta-table-wrapper table tbody {
            display: table-row-group !important;
        }
        .peserta-table-wrapper table tr {
            display: table-row !important;
        }
        .peserta-table-wrapper table th,
        .peserta-table-wrapper table td {
            display: table-cell !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
        .peserta-table-wrapper table th:nth-child(1),
        .peserta-table-wrapper table td:nth-child(1) {
            width: 35px !important;
            min-width: 35px !important;
        }
        .peserta-table-wrapper table th:nth-child(3),
        .peserta-table-wrapper table td:nth-child(3) {
            width: 60px !important;
            min-width: 60px !important;
            overflow: visible !important;
        }
        /* Tombol Hapus - pastikan tampil merah dengan icon */
        .peserta-table-wrapper .remove-btn {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #fff !important;
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
            border-radius: 0.25rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .peserta-table-wrapper .remove-btn:hover {
            background-color: #bb2d3b !important;
            border-color: #b02a37 !important;
        }
        .peserta-table-wrapper .remove-btn i {
            font-size: 0.875rem !important;
            color: #fff !important;
        }
    </style>

<?php 
    $pageTitle = "Tambah Notulen";
    // sidebar
    include '../Nav_Side_Bar/sidebar.php';
    
    // header
    include '../Nav_Side_Bar/header.php'; 
?>

    <!-- CONTENT -->
    <div class="main-content">
        <div class="form-section">
            <h5 class="fw-semibold mb-4">Tambah Notulen</h5>

            <!-- Success Toast Container -->
            <div class="toast-container position-fixed top-0 end-0 p-3">
                <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-check-circle-fill me-2"></i> Notulen berhasil disimpan!
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>

            <form id="notulenForm" method="POST" enctype="multipart/form-data" novalidate>
                
                <!-- Judul -->
                <div class="mb-3">
                    <label class="form-label">Judul</label>
                    <input type="text" class="form-control" name="judul" id="judul" placeholder="Masukkan judul rapat" required>
                </div>

                <!-- Tanggal -->
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" class="form-control" name="tanggal" id="tanggal" required>
                </div>

                <!-- Jam Mulai dan Jam Selesai -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Jam Mulai</label>
                        <input type="time" class="form-control" name="jam_mulai" id="jam_mulai" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jam Selesai</label>
                        <input type="time" class="form-control" name="jam_selesai" id="jam_selesai" required>
                    </div>
                </div>

                <!-- Lokasi -->
                <div class="mb-3">
                    <label class="form-label">Lokasi</label>
                    <input type="text" class="form-control" name="lokasi" id="lokasi" placeholder="Masukkan lokasi rapat" required>
                </div>

                <!-- PIC -->
                <div class="mb-3">
                    <label class="form-label">PIC</label>
                    <select class="form-select" name="penanggung_jawab" id="penanggungJawab" required>
                        <option value="">-- Pilih PIC --</option>
                        <?php foreach ($users as $u): 
                            $roleDisplay = ($u['role'] === 'admin') ? 'Notulis' : ucfirst($u['role']);
                        ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nama']) ?> (<?= $roleDisplay ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Notulen -->
                <div class="mb-3">
                    <label class="form-label">Status Notulen</label>
                    <select class="form-select" name="status" id="statusSelect">
                        <option value="draft">Draft (Dapat Diedit)</option>
                        <option value="final">Final (Tidak Dapat Diedit)</option>
                    </select>
                </div>

                <!-- Isi/TinyMCE -->
                <div class="mb-3">
                    <label class="form-label">Isi Notulen</label>
                    <textarea name="isi" id="isi" rows="10"></textarea>
                </div>

                <!-- Upload file -->
                <!-- Upload Lampiran -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Lampiran (Opsional)</label>
                    <div id="lampiranContainer">
                        <!-- Dynamic Rows will appear here -->
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addLampiranBtn">
                        <i class="bi bi-paperclip me-1"></i> Tambah Lampiran
                    </button>
                    <small class="text-muted d-block mt-2">Anda dapat menambahkan lebih dari satu lampiran dengan judul masing-masing.</small>
                </div>



                <!-- PESERTA -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Peserta Notulen</label>
                    <div class="row g-2 g-md-3">
                        <!-- Pilih Peserta (Kiri) -->
                        <div class="col-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center py-3 py-md-4 px-2 px-md-3">
                                    <div class="mb-2 mb-md-3">
                                        <i class="bi bi-people-fill text-success" style="font-size: 1.8rem;"></i>
                                    </div>
                                    <h6 class="fw-semibold mb-1 mb-md-2" style="font-size: 0.85rem;">Pilih<br class="d-sm-none"> Peserta</h6>
                                    <p class="text-muted small mb-2 mb-md-3 d-none d-md-block">Pilih dari daftar pengguna yang sudah ada</p>
                                    <button type="button" class="btn btn-outline-success btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalPeserta">
                                        <i class="bi bi-people-fill me-1"></i><span class="d-none d-sm-inline">Pilih </span>Peserta
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Tambah Pengguna Baru (Kanan) -->
                        <div class="col-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center py-3 py-md-4 px-2 px-md-3">
                                    <div class="mb-2 mb-md-3">
                                        <i class="bi bi-person-plus-fill text-success" style="font-size: 1.8rem;"></i>
                                    </div>
                                    <h6 class="fw-semibold mb-1 mb-md-2" style="font-size: 0.85rem;">Tambah Pengguna</h6>
                                    <p class="text-muted small mb-2 mb-md-3 d-none d-md-block">Buat akun peserta baru langsung dari sini</p>
                                    <button type="button" class="btn btn-outline-success btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalTambahPengguna">
                                        <i class="bi bi-person-plus-fill me-1"></i><span class="d-none d-sm-inline">Tambah </span>Pengguna
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List peserta (target) -->
                <div class="mb-4">
                    <label class="form-label fw-semibold mb-2">Daftar Peserta:</label>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                            <div class="list-group list-group-flush" id="addedContainer">
                                <div id="emptyRow" class="p-4 text-center text-muted">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-people text-secondary mb-2" style="font-size: 1.5rem; opacity: 0.5;"></i>
                                        <small>Belum ada peserta</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="dashboard_admin.php" class="btn btn-back">Kembali</a>
                    <button type="submit" class="btn btn-save px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Pilih Peserta -->
    <div class="modal fade" id="modalPeserta" tabindex="-1" aria-labelledby="modalPesertaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPesertaLabel">Pilih Peserta Rapat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari nama peserta...">
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label" for="selectAll">Pilih Semua</label>
                        </div>
                        <button type="button" id="clearSearchBtn" class="btn btn-sm btn-outline-secondary">Reset Pilihan</button>
                    </div>

                    <div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                        <div id="notulenList">
                            <?php foreach ($users as $u): ?>
                            <div class="form-check notulen-item py-1 border-bottom">
                                <input class="form-check-input notulen-checkbox"
                                    type="checkbox"
                                    value="<?= $u['id'] ?>"
                                    data-name="<?= htmlspecialchars($u['nama']) ?>"
                                    data-email="<?= htmlspecialchars(strtolower($u['email'])) ?>"
                                    id="u<?= $u['id'] ?>">
                                <label class="form-check-label w-100" for="u<?= $u['id'] ?>" style="cursor: pointer;">
                                    <?= htmlspecialchars($u['nama']) ?>
                                    <small class="text-muted d-block" style="text-transform: lowercase !important;"><?= htmlspecialchars(strtolower($u['email'])) ?></small>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div id="noResults" class="text-center text-muted py-3 d-none">
                            Peserta tidak ditemukan
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btnSimpanPeserta">Simpan Pilihan</button>
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
                            <input type="text" class="form-control" id="newNik" name="nik" 
                                placeholder="Masukkan NIK peserta (6-10 digit)" 
                                minlength="6" maxlength="10" pattern="\d{6,10}" 
                                title="NIK harus berupa 6-10 digit angka" required>
                            <small class="text-muted">⚠️ NIK harus 6-10 digit angka. Akan digunakan sebagai password default.</small>
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
                        <i class="bi bi-person-plus me-1"></i>Tambahkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery for Select2 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // Initialize Select2 for PIC dropdown
        $(document).ready(function() {
            $('#penanggungJawab').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Pilih PIC --',
                allowClear: true,
                width: '100%'
            });
            
            // Initialize Select2 for Status Notulen dropdown
            $('#statusSelect').select2({
                theme: 'bootstrap-5',
                minimumResultsForSearch: Infinity, // Hide search box for small dropdown
                width: '100%'
            });
        });
    </script>

    <script src="../js/notulen.js"></script>
    <script src="../js/admin.js"></script>
</body>
</html>
