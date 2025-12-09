<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Pastikan login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil data user login
$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nama FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userRes = $stmt->get_result();
$userData = $userRes->fetch_assoc();
$stmt->close();
$userName = $userData['nama'] ?? 'Admin';

// Ambil peserta
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
            <h4 class="fw-bold mb-3 ms-3">MENU</h4>
            <ul class="nav flex-column">
                <li><a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
                <li><a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a></li>
                <li><a class="nav-link" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
            </ul>
            <div class="text-center mt-4">
                <button id="logoutBtnMobile" class="btn logout-btn px-4 py-2">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </button>
            </div>
        </div>
    </div>

    <!-- Sidebar Desktop -->
    <div class="sidebar-content d-none d-lg-flex flex-column justify-content-between position-fixed">
        <div>
            <h4 class="fw-bold mb-4 ms-3">MENU</h4>
            <ul class="nav flex-column">
                <li><a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
                <li><a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a></li>
                <li><a class="nav-link" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
            </ul>
        </div>

        <div class="text-center">
            <button id="logoutBtn" class="btn logout-btn px-4 py-2">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </button>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4><b>Dashboard Notulis</b></h4>
            </div>
            <div class="profile"><span>Halo, <?= htmlspecialchars($userName) ?> 👋</span></div>
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard_admin.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Notulen</li>
            </ol>
        </nav>

        <div class="form-section">
            <h5 class="fw-semibold mb-4">Tambah Notulen</h5>

            <div id="alertBox" class="alert alert-success" style="display:none;">Notulen berhasil disimpan!</div>

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
                    
                    <!-- Trigger button -->
                    <div class="dropdown w-100" data-bs-auto-close="false">
                        <button id="dropdownToggle" class="btn btn-save w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false">Pilih Peserta</button>

                        <ul class="dropdown-menu w-100">
                            <li class="px-3 py-2">
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                    <label class="form-check-label" for="selectAll">Pilih Semua</label>
                                </div>
                                <hr>
                            </li>

                            <li id="notulenList" class="px-3">
                                <?php foreach ($users as $u): ?>
                                <div class="form-check notulen-item py-1">
                                    <input class="form-check-input notulen-checkbox"
                                        type="checkbox"
                                        value="<?= $u['id'] ?>"
                                        data-name="<?= htmlspecialchars($u['nama']) ?>"
                                        id="u<?= $u['id'] ?>">
                                    <label class="form-check-label" for="u<?= $u['id'] ?>">
                                        <?= htmlspecialchars($u['nama']) ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            <li class="px-3 py-2">
                                <div class="d-flex justify-content-between gap-2">
                                    <button type="button" id="clearSearchBtn" class="btn btn-light btn-sm flex-grow-1">Reset</button>
                                    <button type="button" id="addButton" class="btn btn-success btn-sm flex-grow-1">Tambah</button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- List peserta (target) -->
                <div id="addedList" class="added-list mt-3">
                    <h6 class="fw-bold mb-2">Peserta yang Telah Ditambahkan:</h6>
                    <div id="addedContainer">
                        <p class="text-muted">Belum ada peserta yang ditambahkan</p>
                    </div>
                </div>

                <!-- Submit -->
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-save px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- SCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* =======================
   TINYMCE - WITH API KEY
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
   FORM SUBMIT AJAX
======================= */
document.getElementById("notulenForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const fd = new FormData(this);

    // Ambil peserta
    document.querySelectorAll('.added-item').forEach(item => {
        fd.append("peserta[]", item.dataset.id);
    });

    const res = await fetch("../proses/proses_simpan_notulen.php", {
        method: "POST",
        body: fd
    });

    const json = await res.json();

    if (json.success) {
        const alertBox = document.getElementById("alertBox");
        alertBox.style.display = "block";
        alertBox.textContent = json.message;

        setTimeout(() => {
            alertBox.style.display = "none";
            location.reload();
        }, 1500);
    } else {
        alert(json.message);
    }
});

/* =======================
   PESERTA HANDLING
======================= */

// Helper function untuk escape HTML
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
const addButton = document.getElementById('addButton');
const clearSearchBtn = document.getElementById('clearSearchBtn');
const addedContainer = document.getElementById('addedContainer');
const notulenCheckboxes = document.querySelectorAll('.notulen-checkbox');

// Select All checkbox
if (selectAll) {
    selectAll.addEventListener('change', function () {
        notulenCheckboxes.forEach(cb => {
            cb.checked = this.checked;
        });
    });
}

// Clear/Reset button
if (clearSearchBtn) {
    clearSearchBtn.addEventListener('click', function () {
        notulenCheckboxes.forEach(cb => cb.checked = false);
        selectAll.checked = false;
    });
}

// Add participants button
if (addButton) {
    addButton.addEventListener('click', function () {
        const selected = document.querySelectorAll('.notulen-checkbox:checked');
        
        if (selected.length === 0) {
            alert('Pilih minimal 1 peserta');
            return;
        }

        // Get existing participant IDs
        const existingIds = new Set();
        addedContainer.querySelectorAll('.added-item').forEach(item => {
            existingIds.add(item.dataset.id);
        });
        
        // Clear empty message
        const emptyMsg = addedContainer.querySelector('.text-muted');
        if (emptyMsg) {
            emptyMsg.remove();
        }

        // Add selected participants
        selected.forEach(cb => {
            const id = cb.value;
            const name = cb.dataset.name || cb.nextElementSibling?.textContent?.trim() || 'Unknown';
            
            // Prevent duplicates
            if (existingIds.has(id)) return;

            const div = document.createElement('div');
            div.className = 'added-item d-flex align-items-center justify-content-between gap-2 mb-2 p-2 bg-light rounded';
            div.dataset.id = id;
            div.innerHTML = `
                <span>${escapeHtml(name)}</span>
                <button type="button" class="btn btn-sm btn-outline-danger remove-btn" data-id="${id}">
                    <i class="bi bi-x"></i>
                </button>
            `;
            addedContainer.appendChild(div);
            existingIds.add(id);
            
            // Remove button click handler
            const removeBtn = div.querySelector('.remove-btn');
            removeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.dataset.id;
                const cb = document.querySelector(`.notulen-checkbox[value="${id}"]`);
                if (cb) cb.checked = false;
                div.remove();
                
                // Show empty message if no participants
                if (addedContainer.children.length === 0) {
                    addedContainer.innerHTML = '<p class="text-muted">Belum ada peserta yang ditambahkan</p>';
                }
            });
        });
    });
}

// Logout handlers
const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn) {
    logoutBtn.addEventListener("click", function () {
        if (confirm("Apakah kamu yakin ingin logout?")) {
            localStorage.removeItem("adminData");
            window.location.href = "../proses/proses_logout.php";
        }
    });
}

const logoutBtnMobile = document.getElementById("logoutBtnMobile");
if (logoutBtnMobile) {
    logoutBtnMobile.addEventListener("click", function () {
        if (confirm("Apakah kamu yakin ingin logout?")) {
            localStorage.removeItem("adminData");
            window.location.href = "../proses/proses_logout.php";
        }
    });
}
    </script>
</body>
</html>
