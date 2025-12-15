<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek Login & Role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

// Ambil data user login (nama + foto)
$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nama, foto FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userRes = $stmt->get_result();
$userData = $userRes->fetch_assoc();
$stmt->close();
$userName = $userData['nama'] ?? 'Admin';
$userPhoto = $userData['foto'] ?? null;

$id_notulen = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_notulen <= 0) {
  echo "<script>showToast('ID Notulen tidak valid!', 'error'); setTimeout(() => window.location.href='dashboard_admin.php', 2000);</script>";
  exit;
}

// Ambil data notulen
$sql = "SELECT * FROM tambah_notulen WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_notulen);
$stmt->execute();
$result = $stmt->get_result();
$notulen = $result->fetch_assoc();


if (!$notulen) {
  echo "<script>showToast('Data notulen tidak ditemukan!', 'error'); setTimeout(() => window.location.href='dashboard_admin.php', 2000);</script>";
  exit;
}

// Ambil daftar semua user untuk dropdown peserta (kecuali saya sendiri)
$sql_users = "SELECT id, nama, email FROM users WHERE id != ? ORDER BY nama ASC";
$stmt_users = $conn->prepare($sql_users);
$stmt_users->bind_param("i", $userId);
$stmt_users->execute();
$res_users = $stmt_users->get_result();
$all_users = []; // array of arrays: [ ['id'=>..,'nama'=>..], ... ]
while ($row = $res_users->fetch_assoc()) {
  $all_users[] = $row;
}

// Parse peserta yang sudah ada di notulen
$current_participants = array_filter(array_map('trim', explode(',', $notulen['peserta'])), function ($v) {
  return $v !== '';
});
// Jika peserta disimpan sebagai ID, ambil nama-nama peserta dari DB
$participants_map = []; // id => nama
if (!empty($current_participants)) {
  // sanitize ke int
  $ids = array_map('intval', $current_participants);
  $ids_list = implode(',', array_unique($ids));
  if ($ids_list !== '') {
    $sql_part = "SELECT id, nama FROM users WHERE id IN ($ids_list)";
    $res_part = $conn->query($sql_part);
    while ($r = $res_part->fetch_assoc()) {
      $participants_map[(int)$r['id']] = $r['nama'];
    }
  }
}
// for display, build array of ['id'=>..,'nama'=>..]
$current_participant_items = [];
foreach ($current_participants as $pid) {
  $pid_int = (int)$pid;
  if ($pid_int > 0 && isset($participants_map[$pid_int])) {
    $current_participant_items[] = ['id' => $pid_int, 'nama' => $participants_map[$pid_int]];
  } elseif ($pid !== '') {
    // fallback: jika DB tidak punya, tampilkan apa yang ada (biasanya not expected)
    $current_participant_items[] = ['id' => $pid, 'nama' => $pid];
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Notulen</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

  <script src="https://cdn.tiny.cloud/1/cl3yw8j9ej8nes9mctfudi2r0jysibdrbn3y932667p04jg5/tinymce/6/tinymce.min.js"
    referrerpolicy="origin"></script>
  <link rel="stylesheet" href="../css/admin.min.css">
  <style>
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

        <div class="page-title">Edit Notulen</div>

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

  <!-- Main Content -->
    <div class="main-content">
    <div class="form-wrapper">
      <h5 class="fw-semibold mb-4">Edit Notulen</h5>

      <!-- Success Toast Container -->
      <div class="toast-container position-fixed top-0 end-0 p-3">
          <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
              <div class="d-flex">
                  <div class="toast-body">
                      <i class="bi bi-check-circle-fill me-2"></i> Notulen berhasil diperbarui!
                  </div>
                  <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
              </div>
          </div>
      </div>

      <form id="editForm" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $id_notulen ?>">

        <div class="mb-3">
          <label class="form-label">Judul</label>
          <input type="text" class="form-control" name="judul" value="<?= htmlspecialchars($notulen['judul'] ?? '') ?>"
            required />
        </div>

        <div class="mb-3">
          <label class="form-label">Tanggal Rapat</label>
          <div class="input-group">
            <input type="date" class="form-control" name="tanggal" value="<?= $notulen['tanggal'] ?? '' ?>" required />
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Status Notulen</label>
          <select class="form-control" name="status" id="statusSelect">
            <option value="draft" <?= ($notulen['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft (Dapat Diedit)</option>
            <option value="final" <?= ($notulen['status'] ?? 'draft') === 'final' ? 'selected' : '' ?>>Final (Tidak Dapat Diedit)</option>
          </select>
          <small class="text-muted d-block mt-1">Ubah ke "Final" untuk mengunci notulen agar tidak dapat diedit</small>
        </div>

        <div class="mb-3">
          <label class="form-label">Isi Notulen</label>
          <textarea id="isi" name="isi" rows="10" <?= ($notulen['status'] ?? 'draft') === 'final' ? 'disabled' : '' ?>><?= htmlspecialchars($notulen['hasil'] ?? '') ?></textarea>
          <?php if (($notulen['status'] ?? 'draft') === 'final'): ?>
            <small class="text-danger d-block mt-2"><strong>⚠️ Notulen sudah Final - Tidak dapat diedit!</strong></small>
          <?php endif; ?>
        </div>

        <div class="mb-3">
          <label class="form-label">Tambah Lampiran (Opsional)</label>
          <input type="file" class="form-control" name="lampiran" />
          <?php if (!empty($notulen['Lampiran'])): ?>
            <small class="text-muted d-block mt-2"><strong>File yang sudah terlampir:</strong></small>
            <div class="mt-1">
              <?php 
                $files = array_filter(array_map('trim', explode('|', $notulen['Lampiran'])), function($v){ return $v !== ''; });
                if (!empty($files)):
                  foreach ($files as $file):
              ?>
                <div class="mb-1">
                  <a href="../file/<?= htmlspecialchars($file) ?>" target="_blank" class="text-decoration-none">
                    <i class="bi bi-file"></i> <?= htmlspecialchars($file) ?>
                  </a>
                </div>
              <?php 
                  endforeach;
                else:
              ?>
                <small class="text-muted">Belum ada file terlampir.</small>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <small class="text-muted d-block mt-1">Belum ada file terlampir.</small>
          <?php endif; ?>
        </div>

        <!-- Dropdown Peserta -->
        <!-- Dropdown Peserta REPLACED WITH MODAL TRIGGER -->
        <div class="mb-4">
          <label class="form-label fw-semibold">Peserta Notulen</label>
          <button type="button" class="btn btn-outline-success w-100 py-2 border-dashed" data-bs-toggle="modal"
            data-bs-target="#modalPeserta" style="border-style: dashed;">
            <i class="bi bi-plus-circle me-2"></i> Pilih Peserta
          </button>
          <small class="text-muted d-block mt-2">Klik tombol di atas untuk mengubah daftar peserta.</small>
        </div>

        <!-- List peserta (Table View) -->
        <div class="mb-4">
          <label class="form-label fw-semibold mb-2">Daftar Peserta:</label>
          <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                  <thead class="bg-light">
                    <tr>
                      <th class="px-4 py-3 text-secondary small fw-bold text-uppercase border-bottom-0">Nama Peserta</th>
                      <th style="width: 100px;"
                        class="text-center px-4 py-3 text-secondary small fw-bold text-uppercase border-bottom-0">Aksi
                      </th>
                    </tr>
                  </thead>
                  <tbody id="addedContainer">
                    <?php if (empty($current_participant_items)): ?>
                    <tr id="emptyRow">
                      <td colspan="2" class="text-center text-muted py-5">
                        <div class="d-flex flex-column align-items-center">
                          <i class="bi bi-people text-secondary mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                          <small>Belum ada peserta yang ditambahkan</small>
                        </div>
                      </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($current_participant_items as $item): ?>
                    <tr class="added-item align-middle" data-id="<?= htmlspecialchars($item['id']) ?>">
                      <td class="px-4">
                        <?= htmlspecialchars($item['nama']) ?>
                        <input type="hidden" name="peserta[]" value="<?= htmlspecialchars($item['id']) ?>">
                      </td>
                      <td class="text-center px-4">
                        <button type="button" class="btn btn-sm btn-danger remove-btn text-white"
                          data-id="<?= htmlspecialchars($item['id']) ?>">
                          <i class="bi bi-trash"></i>
                        </button>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
          <a href="dashboard_admin.php" class="btn btn-back">Kembali</a>
          <button id="simpan_perubahan" type="submit" class="btn btn-save px-4 py-2">Simpan Perubahan</button>
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
                          <?php foreach ($all_users as $u): ?>
                          <?php 
                              // Cek apakah user sudah ada di daftar peserta saat ini
                              $isChecked = in_array($u['id'], $current_participants ?? []) ? 'checked' : '';
                          ?>
                          <div class="form-check notulen-item py-1 border-bottom">
                              <input class="form-check-input notulen-checkbox"
                                  type="checkbox"
                                  value="<?= $u['id'] ?>"
                                  data-name="<?= htmlspecialchars($u['nama']) ?>"
                                  id="u<?= $u['id'] ?>"
                                  <?= $isChecked ?>>
                              <label class="form-check-label w-100" for="u<?= $u['id'] ?>" style="cursor: pointer;">
                                  <?= htmlspecialchars($u['nama']) ?>
                                  <small class="text-muted d-block"><?= htmlspecialchars(strtolower($u['email'])) ?></small>
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

  <script>
    // === TINYMCE INITIALIZATION ===
    tinymce.init({
      selector: '#isi',
      height: 350,
      menubar: false,
      api_key: 'cl3yw8j9ej8nes9mctfudi2r0jysibdrbn3y932667p04jg5',
      plugins: "lists link table code",
      toolbar: "undo redo | bold italic underline | bullist numlist | link",
      readonly: <?= ($notulen['status'] ?? 'draft') === 'final' ? 'true' : 'false' ?>
    });

    document.addEventListener("DOMContentLoaded", function() {
        // ===== LOCK FORM JIKA STATUS FINAL =====
        const currentStatus = "<?= $notulen['status'] ?? 'draft' ?>";
        
        if (currentStatus === 'final') {
            // Disable semua input field kecuali status dan tombol kembali
            document.querySelectorAll('input[name="judul"], input[name="tanggal"], #isi, input[name="peserta[]"]').forEach(field => {
                field.disabled = true;
            });
            
            // Disable dropdown peserta
            document.querySelectorAll('.dropdown-toggle, .form-check-input, input[type="file"]').forEach(field => {
                if (field.name !== 'status') field.disabled = true;
            });
            
            // Ubah warna tombol simpan ke abu-abu dan disable
            const submitBtn = document.getElementById('simpan_perubahan');
            submitBtn.disabled = true;
            submitBtn.classList.remove('btn-save');
            submitBtn.classList.add('btn-secondary');
            submitBtn.innerHTML = '⚠️ Notulen Sudah Final (Tidak dapat diedit)';
        }

        /* =======================
          FORM SUBMIT AJAX
        ======================= */
        document.getElementById("editForm").addEventListener("submit", async function (e) {
            e.preventDefault();
            
            // Cegah submit jika Final
            if (currentStatus === 'final') {
                alert('Notulen sudah Final! Tidak dapat diedit.');
                return;
            }
            
            try {
                // Sync TinyMCE content
                if (typeof tinymce !== 'undefined' && tinymce.get("isi")) {
                    tinymce.triggerSave();
                }

                const fd = new FormData(this);
                // Peserta input type hidden already in form, formData picks them up automatically

                const res = await fetch("../proses/proses_edit_notulen.php", {
                    method: "POST",
                    body: fd
                });

                const text = await res.text();
                let json;
                try {
                    json = JSON.parse(text);
                } catch (err) {
                    console.error("Invalid JSON:", text);
                    showToast("Terjadi kesalahan server: " + text.substring(0, 50), 'error');
                    return;
                }

                if (json.success) {
                    showToast('Notulen berhasil diperbarui!', 'success');

                    // Disable button
                    const submitBtn = document.querySelector('button[type="submit"]');
                    if(submitBtn) submitBtn.disabled = true;

                    setTimeout(() => {
                        window.location.href = 'dashboard_admin.php';
                    }, 1500);
                } else {
                    showToast(json.message || "Gagal menyimpan data.", 'error');
                }
            } catch (error) {
                console.error(error);
                showToast("Terjadi kesalahan: " + error.message, 'error');
            }
        });

        // Logout handlers
        const logoutBtn = document.getElementById("logoutBtn");
        if (logoutBtn) {
            logoutBtn.addEventListener("click", async function (e) {
                e.preventDefault();
                const confirmed = await showConfirm("Yakin mau keluar?");
                if (confirmed) {
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
                    window.location.href = "../proses/proses_logout.php";
                }
            });
        }

        // ===================
        // Fungsi Modal Peserta (Updated)
        // ===================
        
        // Helper function untuk escape HTML
        function escapeHtml(text) {
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
        
        const searchInput = document.getElementById('searchInput');
        const notulenItems = document.querySelectorAll('.notulen-item');
        const notulenCheckboxes = document.querySelectorAll('.notulen-checkbox');
        const selectAll = document.getElementById('selectAll');
        const btnSimpanPeserta = document.getElementById('btnSimpanPeserta');
        const addedContainer = document.getElementById('addedContainer');
        const clearSearchBtn = document.getElementById('clearSearchBtn');
        const noResults = document.getElementById('noResults');
        const modalPeserta = document.getElementById('modalPeserta');

        // Fitur Pencarian di Modal
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
                    addedContainer.innerHTML = '<tr id="emptyRow"><td colspan="2" class="text-center text-muted py-5"><div class="d-flex flex-column align-items-center"><i class="bi bi-people text-secondary mb-2" style="font-size: 2rem; opacity: 0.5;"></i><small>Belum ada peserta yang ditambahkan</small></div></td></tr>';
                } else {
                    selected.forEach(cb => {
                        const id = cb.value;
                        const name = cb.dataset.name;
                        
                        const tr = document.createElement('tr');
                        tr.className = 'added-item align-middle';
                        tr.dataset.id = id;
                        tr.innerHTML = `
                            <td class="px-4">
                                ${escapeHtml(name)}
                                <input type="hidden" name="peserta[]" value="${escapeHtml(id)}">
                            </td>
                            <td class="text-center px-4">
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

        // Event delegation for remove buttons (Hapus dari tabel)
        if (addedContainer) {
            addedContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-btn') || e.target.closest('.remove-btn')) {
                    e.preventDefault();
                    const btn = e.target.classList.contains('remove-btn') ? e.target : e.target.closest('.remove-btn');
                    const id = btn.dataset.id;
                    
                    // Uncheck di modal (agar sinkron jika dibuka lagi)
                    const modalCb = document.querySelector(`.notulen-checkbox[value="${id}"]`);
                    if (modalCb) modalCb.checked = false;
                    
                    const item = btn.closest('.added-item');
                    if (item) item.remove();
                    
                    if (addedContainer.querySelectorAll('.added-item').length === 0) {
                        addedContainer.innerHTML = '<tr id="emptyRow"><td colspan="2" class="text-center text-muted py-5"><div class="d-flex flex-column align-items-center"><i class="bi bi-people text-secondary mb-2" style="font-size: 2rem; opacity: 0.5;"></i><small>Belum ada peserta yang ditambahkan</small></div></td></tr>';
                    }
                }
            });
        }
        

    });

    // Handle pre-existing remove buttons (if any hardcoded) - Delegation above handles it
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../js/admin.js"></script>
</body>
</html>