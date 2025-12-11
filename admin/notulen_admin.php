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
$q = $conn->prepare("SELECT id, nama, email FROM users WHERE role = 'peserta' ORDER BY nama ASC");
if ($q) {
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
    <nav class="navbar navbar-light bg-white sticky-top px-3">
        <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
            <i class="bi bi-list"></i>
        </button>
    </nav>

    <!-- Sidebar Mobile -->
    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas">
        <div class="offcanvas-body p-0">
            <div class="sidebar-content d-flex flex-column justify-content-between h-100">
                <div>
                    <h4 class="fw-bold mb-4 ms-3">SmartNote</h4>
                    <ul class="nav flex-column">
                        <li>
                            <a class="nav-link active" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                        </li>
                        <li>
                            <a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a>
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
            <h4 class="fw-bold mb-4 ms-3">SmartNote</h4>
            <ul class="nav flex-column">
                <li>
                    <a class="nav-link active" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                </li>
                <li>
                    <a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a>
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

    <!-- CONTENT -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4><b>Dashboard Admin</b></h4>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end">
                    <span class="d-block fw-medium text-dark">Halo, <?= htmlspecialchars($userName) ?> 👋</span>
                </div>
                    <?php 
                    $filePath = "../file/" . $userPhoto; 
                    $hasPhoto = $userPhoto && file_exists($filePath);
                    ?>

                    <?php if ($hasPhoto): ?>
                        <img src="<?= htmlspecialchars($filePath) ?>" 
                            alt="Profile"
                            class="rounded-circle shadow-sm"
                            style="width: 45px; height: 45px; object-fit: cover; border: 2px solid #fff;">
                    <?php else: ?>
                        <!-- ICON formal (abu gelap, clean, profesional) -->
                        <i class="bi bi-person-circle"
                        style="font-size: 45px; color: #495057;"></i>
                    <?php endif; ?>
            </div>
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard_admin.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Notulen</li>
            </ol>
        </nav>

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

                <!-- Isi/TinyMCE -->
                <div class="mb-3">
                    <label class="form-label">Isi Notulen</label>
                    <textarea name="isi" id="isi" rows="10"></textarea>
                </div>

                <!-- Upload file -->
                <div class="mb-3">
                    <label class="form-label">Upload File (Opsional)</label>
                    <input type="file" class="form-control" name="file" id="fileInput">
                </div>

                <!-- PESERTA -->
                <div class="mb-3">
                    <label class="form-label">Peserta Notulen</label>
                    <button type="button" class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#modalPeserta">
                        <i class="bi bi-people-fill me-2"></i> Pilih Peserta
                    </button>
                </div>

                <!-- List peserta (target) -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Peserta yang Telah Ditambahkan:</label>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th class="align-middle">Nama Peserta</th>
                                    <th style="width: 100px;" class="text-center align-middle">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="addedContainer">
                                <tr id="emptyRow">
                                    <td colspan="2" class="text-center text-muted py-3">Belum ada peserta yang ditambahkan</td>
                                </tr>
                            </tbody>
                        </table>
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
                                    <small class="text-muted d-block"><?= htmlspecialchars($u['email']) ?></small>
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
            addedContainer.innerHTML = '<tr id="emptyRow"><td colspan="2" class="text-center text-muted py-3">Belum ada peserta yang ditambahkan</td></tr>';
        } else {
            selected.forEach(cb => {
                const id = cb.value;
                const name = cb.dataset.name;
                
                const tr = document.createElement('tr');
                tr.className = 'added-item align-middle';
                tr.dataset.id = id; // Keep data-id for form submission logic
                tr.innerHTML = `
                    <td>
                        ${escapeHtml(name)}
                        <!-- Hidden input for form submission if needed, though existing submit logic uses dataset.id -->
                    </td>
                    <td class="text-center">
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
        
        if (addedContainer.querySelectorAll('.added-item').length === 0) {
            addedContainer.innerHTML = '<tr id="emptyRow"><td colspan="2" class="text-center text-muted py-3">Belum ada peserta yang ditambahkan</td></tr>';
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
        const confirmed = await showConfirm("Apakah kamu yakin ingin logout?");
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
        const confirmed = await showConfirm("Apakah kamu yakin ingin logout?");
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
