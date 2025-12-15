<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil data pengguna yang sedang login
$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nama, foto FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userRes = $stmt->get_result();
$userData = $userRes->fetch_assoc();
$stmt->close();
$userName = $userData['nama'] ?? 'Admin';
$userPhoto = $userData['foto'] ?? null;

// Ambil daftar peserta
$users = [];
$q = $conn->prepare("SELECT id, nama, email FROM users WHERE role = 'peserta' AND id != ? ORDER BY nama ASC");
if ($q) {
    $q->bind_param("i", $userId);
    $q->execute();
    $res = $q->get_result();
    while ($r = $res->fetch_assoc()) {
        $users[] = $r;
    }
    $q->close();
}
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

    <script src="https://cdn.tiny.cloud/1/cl3yw8j9ej8nes9mctfudi2r0jysibdrbn3y932667p04jg5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
        
        /* FIX TABLE MOBILE ALIGNMENT */
        @media (max-width: 768px) {
            .mobile-table-fix .table-responsive {
                display: block !important;
                border: 0 !important;
            }
            .mobile-table-fix table, 
            .mobile-table-fix thead, 
            .mobile-table-fix tbody, 
            .mobile-table-fix tr, 
            .mobile-table-fix th, 
            .mobile-table-fix td {
                display: revert !important; /* Revert to browser default table behavior */
            }
            .mobile-table-fix table {
                display: table !important;
                width: 100% !important;
            }
            .mobile-table-fix thead {
                display: table-header-group !important;
            }
            .mobile-table-fix tbody {
                display: table-row-group !important;
            }
            .mobile-table-fix tr {
                display: table-row !important;
            }
            .mobile-table-fix th, .mobile-table-fix td {
                display: table-cell !important;
            }
            
            /* FORCE DELETE BUTTON STYLE ON MOBILE */
            .mobile-table-fix .remove-btn {
                background-color: #dc3545 !important; /* Bootstrap Danger Red */
                color: #ffffff !important;
                border: 1px solid #dc3545 !important;
                width: 30px !important;
                height: 30px !important;
                padding: 0 !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                font-size: 0.8rem !important;
            }
            .mobile-table-fix .remove-btn:hover {
                background-color: #bb2d3b !important;
                border-color: #b02a37 !important;
            }
        }
    </style>

    <!-- Sidebar Desktop -->
    <div class="sidebar-admin d-none d-lg-flex">
        <div class="sidebar-top">
            <div class="title text-success">SmartNote</div>
            
            <a href="dashboard_admin.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard_admin.php' ? 'active' : '' ?>">
                <i class="bi bi-grid me-2"></i> Dashboard
            </a>
            
            <a href="kelola_rapat_admin.php" class="<?= basename($_SERVER['PHP_SELF']) === 'kelola_rapat_admin.php' ? 'active' : '' ?>">
                <i class="bi bi-people me-2"></i> Kelola Pengguna
            </a>
        </div>

        <div class="sidebar-bottom">
            <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : '' ?>">
                <i class="bi bi-person-circle me-2"></i> Profile
            </a>
            <a href="#" id="logoutBtn" class="text-danger">
                <i class="bi bi-box-arrow-left me-2"></i> Keluar
            </a>
        </div>
    </div>

    <!-- Header / Top Bar -->
    <div class="header-admin">
        <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile">
            <i class="bi bi-list"></i>
        </button>

        <div class="page-title">Tambah Notulen</div>

        <div class="right-section">
            <div class="d-none d-md-block text-end me-2">
                <div class="fw-bold small"><?= htmlspecialchars($userName) ?></div>
                <small class="text-muted" style="font-size: 0.75rem;">Administrator</small>
            </div>
            
            <?php if ($userPhoto && file_exists("../file/" . $userPhoto)): ?>
                <img src="../file/<?= htmlspecialchars($userPhoto) ?>" class="rounded-circle border" style="width:40px;height:40px;object-fit:cover;">
            <?php else: ?>
                <i class="bi bi-person-circle fs-2 text-secondary"></i>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar Mobile -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMobile">
         <div class="offcanvas-header">
            <h5 class="offcanvas-title fw-bold text-success">SmartNote</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column justify-content-between">
            <ul class="nav flex-column gap-2">
                <li class="nav-item">
                    <a class="nav-link text-dark fw-medium <?= basename($_SERVER['PHP_SELF']) === 'dashboard_admin.php' ? 'bg-success text-white rounded' : '' ?>" href="dashboard_admin.php">
                        <i class="bi bi-grid me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark fw-medium <?= basename($_SERVER['PHP_SELF']) === 'kelola_rapat_admin.php' ? 'bg-success text-white rounded' : '' ?>" href="kelola_rapat_admin.php">
                        <i class="bi bi-people me-2"></i> Kelola Pengguna
                    </a>
                </li>
            </ul>

            <ul class="nav flex-column gap-2 mt-4 border-top pt-3">
                 <li class="nav-item">
                    <a class="nav-link text-dark fw-medium <?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'bg-success text-white rounded' : '' ?>" href="profile.php">
                        <i class="bi bi-person-circle me-2"></i> Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a id="logoutBtnMobile" class="nav-link text-danger fw-medium" href="#">
                        <i class="bi bi-box-arrow-left me-2"></i> Keluar
                    </a>
                </li>
            </ul>
        </div>
    </div>

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

            <form id="notulenForm" method="POST" enctype="multipart/form-data">
                
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

                <!-- Status Notulen -->
                <div class="mb-3">
                    <label class="form-label">Status Notulen</label>
                    <select class="form-control" name="status" id="statusSelect">
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

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const container = document.getElementById('lampiranContainer');
                        const addBtn = document.getElementById('addLampiranBtn');

                        function addRow() {
                            const row = document.createElement('div');
                            row.className = 'card mb-2 p-3 border-light bg-light shadow-sm lampiran-row';
                            row.innerHTML = `
                                <div class="row align-items-center g-2">
                                    <div class="col-md-5">
                                        <input type="text" name="judul_lampiran[]" class="form-control form-control-sm" placeholder="Judul Lampiran" required>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="file" name="file_lampiran[]" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <button type="button" class="btn btn-sm btn-soft-danger remove-lampiran">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                            container.appendChild(row);

                            // Handle remove
                            row.querySelector('.remove-lampiran').addEventListener('click', function() {
                                row.remove();
                            });
                        }

                        addBtn.addEventListener('click', addRow);
                    });
                </script>

                <!-- PESERTA -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Peserta Notulen</label>
                    <button type="button" class="btn btn-outline-success w-100 py-2 border-dashed" data-bs-toggle="modal" data-bs-target="#modalPeserta" style="border-style: dashed;">
                        <i class="bi bi-plus-circle me-2"></i> Pilih Peserta
                    </button>
                    <small class="text-muted d-block mt-2">Klik tombol di atas untuk memilih peserta rapat.</small>
                </div>

                <!-- List peserta (target) -->
                <div class="mb-4">
                    <label class="form-label fw-semibold mb-2">Daftar Peserta:</label>
                    <div class="card border-0 shadow-sm mobile-table-fix">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0 align-middle" style="white-space: nowrap;">
                                    <thead class="bg-light">
                                        <tr style="border-bottom: 1px solid #dee2e6;">
                                            <th style="width: 50px;" class="px-2 px-md-4 py-3 text-secondary small fw-bold text-uppercase border-bottom-0 text-center">No</th>
                                            <th class="px-2 px-md-4 py-3 text-secondary small fw-bold text-uppercase border-bottom-0 text-start">Nama Peserta</th>
                                            <th style="width: 100px;" class="text-center px-2 px-md-4 py-3 text-secondary small fw-bold text-uppercase border-bottom-0">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="addedContainer">
                                        <tr id="emptyRow" style="border-bottom: 1px solid #dee2e6;">
                                            <td colspan="3" class="text-center text-muted py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="bi bi-people text-secondary mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                                                    <small>Belum ada peserta yang ditambahkan</small>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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

    <!-- SCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* =======================
   TINYMCE - DENGAN API KEY
======================= */
tinymce.init({
    selector: '#isi',
    height: 350,
    menubar: false,
    api_key: 'cl3yw8j9ej8nes9mctfudi2r0jysibdrbn3y932667p04jg5',
    plugins: "lists link table code",
    toolbar: "undo redo | bold italic underline | bullist numlist | link",
});

/* =======================
   PENGIRIMAN FORMULIR AJAX
======================= */
document.getElementById("notulenForm").addEventListener("submit", async function (e) {
    e.preventDefault();
    
    try {
        // Sinkronisasi konten TinyMCE ke textarea sebelum membuat FormData
        if (typeof tinymce !== 'undefined' && tinymce.get("isi")) {
            tinymce.triggerSave();
        }

        const fd = new FormData(this);

        // Ambil data peserta yang dipilih
        document.querySelectorAll('.added-item').forEach(item => {
            fd.append("peserta[]", item.dataset.id);
        });

        const res = await fetch("../proses/proses_simpan_notulen.php", {
            method: "POST",
            body: fd
        });

        const text = await res.text(); // Ambil teks mentah terlebih dahulu untuk debugging
        let json;
        try {
            json = JSON.parse(text);
        } catch (err) {
            console.error("Invalid JSON:", text);
            alert("Terjadi kesalahan server: " + text.substring(0, 100));
            return;
        }

        if (json.success) {
            // Tampilkan notifikasi toast kustom
            showToast('Notulen berhasil disimpan!', 'success');

            // Nonaktifkan tombol untuk mencegah pengiriman ganda
            const submitBtn = document.querySelector('button[type="submit"]');
            if(submitBtn) submitBtn.disabled = true;

            setTimeout(() => {
                location.href = 'dashboard_admin.php';
            }, 1000);
        } else {
            showToast(json.message || "Gagal menyimpan data.", 'error');
        }
    } catch (error) {
        console.error(error);
        showToast("Terjadi kesalahan: " + error.message, 'error');
    }
});

/* =======================
   PENGELOLAAN PESERTA (MODAL)
======================= */

// Fungsi bantuan untuk escape karakter HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

const selectAll = document.getElementById('selectAll');
const btnSimpanPeserta = document.getElementById('btnSimpanPeserta');
const clearSearchBtn = document.getElementById('clearSearchBtn');
const addedContainer = document.getElementById('addedContainer');
const notulenCheckboxes = document.querySelectorAll('.notulen-checkbox');
const searchInput = document.getElementById('searchInput');
const notulenItems = document.querySelectorAll('.notulen-item');
const noResults = document.getElementById('noResults');
const modalPeserta = document.getElementById('modalPeserta');

// Search Functionality
if (searchInput) {
    searchInput.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        let hasVisible = false;

        notulenItems.forEach(item => {
            const label = item.querySelector('label').innerText.toLowerCase();
            if (label.includes(filter)) {
                item.classList.remove('d-none');
                hasVisible = true;
            } else {
                item.classList.add('d-none');
            }
        });

        if (noResults) {
            noResults.classList.toggle('d-none', hasVisible);
        }
    });
}

// Checkbox Pilih Semua
if (selectAll) {
    selectAll.addEventListener('change', function () {
        // Hanya select yang visible jika sedang search
        const visibleCheckboxes = Array.from(notulenCheckboxes).filter(cb => !cb.closest('.notulen-item').classList.contains('d-none'));
        
        visibleCheckboxes.forEach(cb => {
            cb.checked = this.checked;
        });
    });
}

// Tombol Bersihkan/Reset
if (clearSearchBtn) {
    clearSearchBtn.addEventListener('click', function () {
        notulenCheckboxes.forEach(cb => cb.checked = false);
        if (selectAll) selectAll.checked = false;
        if (searchInput) {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('keyup')); // Trigger search reset
        }
    });
}

// Tombol Simpan Pilihan (Dari Modal)
if (btnSimpanPeserta) {
    btnSimpanPeserta.addEventListener('click', function () {
        const selected = document.querySelectorAll('.notulen-checkbox:checked');
        
        // Clear container
        addedContainer.innerHTML = '';

        if (selected.length === 0) {
            addedContainer.innerHTML = '<tr id="emptyRow" style="border-bottom: 1px solid #dee2e6 !important;"><td colspan="3" class="text-center text-muted py-3">Belum ada peserta yang ditambahkan</td></tr>';
        } else {
            selected.forEach((cb, index) => {
                const id = cb.value;
                const name = cb.dataset.name;
                
                const tr = document.createElement('tr');
                tr.className = 'added-item align-middle border-bottom'; // Use class for border
                tr.dataset.id = id; 
                // Removed manual style.cssText
                
                tr.innerHTML = `
                    <td class="px-2 px-md-4 text-center text-muted small">${index + 1}</td>
                    <td class="px-2 px-md-4 text-start">
                        ${escapeHtml(name)}
                        <!-- Hidden input for form submission if needed, though existing submit logic uses dataset.id -->
                    </td>
                    <td class="text-center px-2 px-md-4">
                        <button type="button" class="btn btn-sm btn-danger remove-btn text-white" data-id="${id}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
                addedContainer.appendChild(tr);
            });
        }

        // Tutup Modal
        const modalInstance = bootstrap.Modal.getInstance(modalPeserta);
        if (modalInstance) {
            modalInstance.hide();
        }
        
        showToast('Daftar peserta diperbarui', 'success');
    });
}

// Event delegation for remove buttons
addedContainer.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-btn') || e.target.closest('.remove-btn')) {
        e.preventDefault();
        const btn = e.target.classList.contains('remove-btn') ? e.target : e.target.closest('.remove-btn');
        const id = btn.dataset.id;
        
        // Uncheck di modal
        const modalCb = document.querySelector(`.notulen-checkbox[value="${id}"]`);
        if (modalCb) modalCb.checked = false;
        
        const item = btn.closest('.added-item');
        if (item) item.remove();
        
        // Re-numbering
        const remainingItems = addedContainer.querySelectorAll('.added-item');
        remainingItems.forEach((row, index) => {
            row.querySelector('td').innerText = index + 1;
        });

        if (remainingItems.length === 0) {
            addedContainer.innerHTML = '<tr id="emptyRow" style="border-bottom: 1px solid #dee2e6 !important;"><td colspan="3" class="text-center text-muted py-3">Belum ada peserta yang ditambahkan</td></tr>';
        }
    }
});

// Sinkronisasi saat modal dibuka (opsional, jika ingin list luar mempengaruhi modal)
// Karena kita me-rebuild list luar dari modal setiap kali simpan, maka state modal adalah "master"
// Tapi jika user hapus dari luar, kita harus uncheck di modal (sudah dihandle di remove-btn click)


// Handler Logout
const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn) {
    logoutBtn.addEventListener("click", async function (e) {
        e.preventDefault();
        const confirmed = await showConfirm("Yakin mau keluar?");
        if (confirmed) {
            localStorage.removeItem("adminData");
            window.location.href = "../proses/proses_logout.php";
        }
    });
}

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
<script src="../js/admin.js"></script>
</body>
</html>
