<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil id notulen dari query
$id_notulen = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id_notulen <= 0) {
    echo "<script>alert('ID notulen tidak valid'); window.location.href='dashboard_admin.php';</script>";
    exit;
}

// Ambil data notulen
$stmt = $conn->prepare("SELECT * FROM tambah_notulen WHERE id = ?");
$stmt->bind_param("i", $id_notulen);
$stmt->execute();
$res = $stmt->get_result();
$notulen = $res->fetch_assoc();
$stmt->close();

if (!$notulen) {
    echo "<script>alert('Notulen tidak ditemukan'); window.location.href='dashboard_admin.php';</script>";
    exit;
}

// Ambil semua peserta (users role = peserta)
$users = [];
$q = $conn->prepare("SELECT id, nama, email FROM users WHERE role = 'peserta' ORDER BY nama ASC");
if ($q) {
    $q->execute();
    $r = $q->get_result();
    while ($row = $r->fetch_assoc()) {
        $users[] = $row;
    }
    $q->close();
}

// Siapkan data peserta yang sudah dipilih di notulen
// Asumsi: di DB kolom 'peserta' menyimpan id peserta dipisah koma seperti "2,5,7"
// Jika format berbeda, sesuaikan parsing ini
$existingPeserta = [];
if (!empty($notulen['peserta'])) {
    $parts = array_filter(array_map('trim', explode(',', $notulen['peserta'])));
    foreach ($parts as $p) {
        if (ctype_digit($p)) $existingPeserta[] = (string)(int)$p;
    }
}

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Notulen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* sedikt styling supaya tampil rapi */
        .added-item { display:flex; align-items:center; gap:.5rem; padding:.25rem 0; }
    </style>
    <script src="https://cdn.tiny.cloud/1/cl3yw8j9ej8nes9mctfudi2r0jysibdrbn3y932667p04jg5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
<nav class="navbar navbar-light bg-white sticky-top px-3">
    <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas"
        data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
        <i class="bi bi-list"></i>
    </button>
</nav>

<div class="container my-4">
    <h4 class="mb-3">Edit Notulen</h4>

    <form id="editNotulenForm" action="../proses/proses_edit_notulen.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= e($notulen['id']) ?>">

        <div class="mb-3">
            <label class="form-label">Judul</label>
            <input name="judul" id="judul" type="text" class="form-control" required value="<?= e($notulen['judul_rapat']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Tanggal</label>
            <input name="tanggal" id="tanggal" type="date" class="form-control" required value="<?= e($notulen['tanggal_rapat']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Isi</label>
            <textarea id="isi" name="isi" rows="10"><?= e($notulen['isi'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Lampiran saat ini</label>
            <div class="mb-2">
                <?php if (!empty($notulen['Lampiran']) && file_exists(__DIR__ . '/../uploads/' . $notulen['Lampiran'])): ?>
                    <a class="btn btn-outline-secondary btn-sm" href="../uploads/<?= rawurlencode($notulen['Lampiran']) ?>" target="_blank">
                        <i class="bi bi-eye me-1"></i>Lihat Lampiran
                    </a>
                    <span class="ms-2 text-muted"><?= e($notulen['Lampiran']) ?></span>
                <?php else: ?>
                    <p class="text-muted mb-0">Tidak ada lampiran.</p>
                <?php endif; ?>
            </div>
            <label class="form-label">Ganti Lampiran (opsional)</label>
            <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png">
            <small class="text-muted">Kosongkan jika tidak ingin mengganti.</small>
        </div>

        <!-- Advanced multiselect peserta -->
        <div class="mb-3">
            <label class="form-label">Peserta Notulen</label>
            <div class="dropdown w-100">
                <button id="dropdownToggle" class="btn btn-outline-success w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">Pilih Peserta</button>

                <div class="dropdown-menu p-3 w-100" style="max-height:360px; overflow:auto;">
                    <div class="mb-2">
                        <input id="searchInput" type="search" class="form-control" placeholder="Cari peserta...">
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="selectAll">
                        <label class="form-check-label" for="selectAll">Pilih Semua</label>
                    </div>

                    <hr>

                    <div id="notulenList">
                        <?php if (empty($users)): ?>
                            <div class="text-muted">Belum ada peserta.</div>
                        <?php else: ?>
                            <?php foreach ($users as $u): 
                                $uid = (string)$u['id'];
                                $checked = in_array($uid, $existingPeserta) ? 'checked' : '';
                            ?>
                                <div class="form-check notulen-item py-1">
                                    <input class="form-check-input notulen-checkbox" type="checkbox"
                                        value="<?= e($u['id']) ?>" id="u<?= e($u['id']) ?>"
                                        data-name="<?= e($u['nama']) ?>" <?= $checked ?>>
                                    <label class="form-check-label" for="u<?= e($u['id']) ?>"><?= e($u['nama']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <button id="clearSearchBtn" type="button" class="btn btn-sm btn-light">Reset</button>
                        <button id="addButton" type="button" class="btn btn-sm btn-success">Tambah</button>
                    </div>
                </div>
            </div>

            <div id="addedList" class="mt-3">
                <h6 class="fw-bold mb-2">Peserta yang Telah Ditambahkan:</h6>
                <div id="addedContainer">
                    <!-- Jika sudah ada peserta pada notulen, JS akan memasukkan itemnya -->
                    <p class="text-muted">Belum ada peserta yang ditambahkan</p>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="dashboard_admin.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // tinymce
    tinymce.init({
        selector: '#isi',
        height: 300,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | removeformat | code'
    });

    // helper
    function escapeHtml(unsafe) {
        return String(unsafe)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    const searchInput = document.getElementById('searchInput');
    const notulenList = document.getElementById('notulenList');
    const notulenItems = () => notulenList.querySelectorAll('.notulen-item');
    const selectAll = document.getElementById('selectAll');
    const addButton = document.getElementById('addButton');
    const addedContainer = document.getElementById('addedContainer');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const dropdownToggle = document.getElementById('dropdownToggle');

    // On page load: jika ada checkbox yang sudah checked (existingPeserta), langsung tambahkan ke addedContainer
    window.addEventListener('DOMContentLoaded', () => {
        let any = false;
        notulenList.querySelectorAll('.notulen-checkbox:checked').forEach(cb => {
            addToContainer(cb);
            any = true;
        });
        if (!any) {
            addedContainer.innerHTML = '<p class="text-muted">Belum ada peserta yang ditambahkan</p>';
        }
    });

    function addToContainer(cb) {
        const id = cb.value;
        const name = cb.dataset.name || cb.nextElementSibling?.textContent?.trim() || 'Unknown';
        if (addedContainer.querySelector(`.added-item[data-id="${id}"]`)) return;
        if (addedContainer.querySelector('.text-muted')) addedContainer.innerHTML = '';
        const div = document.createElement('div');
        div.className = 'added-item';
        div.dataset.id = id;
        div.innerHTML = `<span class="flex-grow-1">${escapeHtml(name)}</span>
                         <button type="button" class="btn btn-sm btn-outline-danger remove-btn">Hapus</button>`;
        addedContainer.appendChild(div);

        // attach remove handler
        div.querySelector('.remove-btn').addEventListener('click', function () {
            // uncheck original
            const original = document.querySelector(`.notulen-checkbox[value="${id}"]`);
            if (original) original.checked = false;
            div.remove();
            if (addedContainer.children.length === 0) {
                addedContainer.innerHTML = '<p class="text-muted">Belum ada peserta yang ditambahkan</p>';
            }
        });
    }

    // Search filter
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.trim().toLowerCase();
            notulenItems().forEach(item => {
                const txt = item.textContent.trim().toLowerCase();
                item.style.display = txt.includes(q) ? '' : 'none';
            });
        });
    }

    // Clear search
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', () => {
            searchInput.value = '';
            notulenItems().forEach(i => i.style.display = '');
            searchInput.focus();
        });
    }

    // Select all visible
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            const visibleCheckboxes = notulenList.querySelectorAll('.notulen-item:not([style*="display: none"]) .notulen-checkbox');
            visibleCheckboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    // Add selected
    if (addButton) {
        addButton.addEventListener('click', () => {
            const selected = notulenList.querySelectorAll('.notulen-checkbox:checked');
            if (selected.length === 0) {
                // jika kosong, beri pesan ringan
                if (!addedContainer.querySelector('.added-item')) {
                    addedContainer.innerHTML = '<p class="text-muted">Belum ada peserta yang ditambahkan</p>';
                }
                return;
            }
            selected.forEach(cb => addToContainer(cb));
            // tutup dropdown
            const dd = bootstrap.Dropdown.getInstance(dropdownToggle);
            if (dd) dd.hide();
        });
    }

    // Sync: bila user uncheck di list, hapus dari container
    notulenList.addEventListener('change', (e) => {
        if (e.target && e.target.classList.contains('notulen-checkbox')) {
            const id = e.target.value;
            if (!e.target.checked) {
                const existing = addedContainer.querySelector(`.added-item[data-id="${id}"]`);
                if (existing) existing.remove();
                if (addedContainer.children.length === 0) {
                    addedContainer.innerHTML = '<p class="text-muted">Belum ada peserta yang ditambahkan</p>';
                }
            }
        }
    });

    // saat submit, tambahkan peserta[] ke FormData secara normal (form submit biasa)
    // Jika ingin pakai AJAX, adaptasikan sesuai proses kamu
    document.getElementById('editNotulenForm').addEventListener('submit', function (e) {
        // sebelum submit, kita harus membuat input hidden untuk setiap peserta yang ada di addedContainer
        // hapus field peserta[] lama jika ada
        const old = document.querySelectorAll('input[name="peserta[]"]');
        old.forEach(x => x.remove());
        const items = addedContainer.querySelectorAll('.added-item');
        items.forEach(div => {
            const id = div.dataset.id;
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'peserta[]';
            inp.value = id;
            this.appendChild(inp);
        });
        // lanjut submit normal (POST multi-part)
    });
</script>
</body>
</html>
