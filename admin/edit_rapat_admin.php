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

// Fetch Attachments (tb_lampiran)
$stmtLampiran = $conn->prepare("SELECT * FROM tb_lampiran WHERE id_notulen = ?");
$stmtLampiran->bind_param("i", $id_notulen);
$stmtLampiran->execute();
$resultLampiran = $stmtLampiran->get_result();
$lampiranList = [];
while ($row = $resultLampiran->fetch_assoc()) {
    $lampiranList[] = $row;
}
$hasLampiran = count($lampiranList) > 0;

// Ambil daftar semua user untuk modal peserta (Admin + Peserta)
$sql_users = "SELECT id, nama, email FROM users WHERE role IN ('admin', 'peserta') ORDER BY nama ASC";
$stmt_users = $conn->prepare($sql_users);
$stmt_users->execute();
$res_users = $stmt_users->get_result();
$all_users = [];
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
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/forms.css">
  <style>
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
    </style>
</head>
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

        </div>

        <!-- LAMPIRAN SECTION -->
        <div class="mb-4">
          <label class="form-label fw-semibold">Lampiran</label>
          
          <!-- Existing Attachments -->
          <?php if ($hasLampiran): ?>
            <div class="mb-3">
              <label class="small text-muted mb-2">Lampiran Saat Ini:</label>
              <div class="list-group">
                <?php foreach ($lampiranList as $lamp): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center" id="lampiran-<?= $lamp['id'] ?>">
                        <div class="d-flex align-items-center">
                             <a href="../file/<?= htmlspecialchars($lamp['file_lampiran']) ?>" target="_blank" class="text-decoration-none text-dark d-flex align-items-center">
                                <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                                <span><?= htmlspecialchars($lamp['judul_lampiran']) ?></span>
                             </a>
                        </div>
                        <button type="button" class="btn btn-sm btn-soft-danger" onclick="deleteLampiran(<?= $lamp['id'] ?>)" title="Hapus Lampiran">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Add New Attachments -->
          <label class="small text-muted mb-2">Tambah Lampiran Baru:</label>
          <div id="lampiranContainer"></div>
          <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addLampiranBtn">
            <i class="bi bi-paperclip me-1"></i> Tambah Lampiran
          </button>
        </div>

        <script>
            // Add New Lampiran Logic
            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('lampiranContainer');
                const addBtn = document.getElementById('addLampiranBtn');

                if (addBtn && container) {
                    function addRow() {
                        const row = document.createElement('div');
                        row.className = 'card mb-2 p-3 border-light bg-light shadow-sm lampiran-row';
                        row.innerHTML = `
                            <div class="row align-items-center g-2">
                                <div class="col-md-5">
                                    <input type="text" name="judul_lampiran[]" class="form-control form-control-sm title-input" placeholder="Judul Lampiran">
                                </div>
                                <div class="col-md-5">
                                    <input type="file" name="file_lampiran[]" class="form-control form-control-sm file-input">
                                </div>
                                <div class="col-md-2 text-end">
                                    <button type="button" class="btn btn-sm btn-success upload-lampiran me-1" title="Upload & Simpan">
                                        <i class="bi bi-cloud-upload"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-soft-danger remove-lampiran">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                        container.appendChild(row);

                        // Delete row event
                        row.querySelector('.remove-lampiran').addEventListener('click', function() {
                            row.remove();
                        });

                        // Upload event
                        const uploadBtn = row.querySelector('.upload-lampiran');
                        uploadBtn.addEventListener('click', async function() {
                            const titleInput = row.querySelector('.title-input');
                            const fileInput = row.querySelector('.file-input');
                            const file = fileInput.files[0];

                            if (!file) {
                                showToast('Silakan pilih file terlebih dahulu', 'error');
                                return;
                            }

                            // Show loading
                            const originalContent = uploadBtn.innerHTML;
                            uploadBtn.disabled = true;
                            uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

                            try {
                                const formData = new FormData();
                                formData.append('id_notulen', document.querySelector('input[name="id"]').value);
                                formData.append('judul_lampiran', titleInput.value);
                                formData.append('file_lampiran', file);

                                const res = await fetch('../proses/proses_upload_lampiran.php', {
                                    method: 'POST',
                                    body: formData
                                });
                                const json = await res.json();

                                if (json.success) {
                                    showToast('Lampiran berhasil diupload!', 'success');
                                    
                                    // Move to existing list
                                    addExistingLampiranRow(json.data);
                                    
                                    // Remove this input row
                                    row.remove();
                                } else {
                                    showToast(json.message || 'Gagal upload', 'error');
                                    uploadBtn.disabled = false;
                                    uploadBtn.innerHTML = originalContent;
                                }
                            } catch (err) {
                                console.error(err);
                                showToast('Terjadi kesalahan sistem', 'error');
                                uploadBtn.disabled = false;
                                uploadBtn.innerHTML = originalContent;
                            }
                        });
                    }
                    addBtn.addEventListener('click', addRow);
                }

                // Helper to add row to existing list (visual only)
                function addExistingLampiranRow(data) {
                    const listGroup = document.querySelector('.list-group');
                    if (!listGroup) return; // Should exist if checked correctly, or create if not exist

                    const div = document.createElement('div');
                    div.className = 'list-group-item d-flex justify-content-between align-items-center';
                    div.id = 'lampiran-' + data.id;
                    div.innerHTML = `
                        <div class="d-flex align-items-center">
                             <a href="../file/${data.file_lampiran}" target="_blank" class="text-decoration-none text-dark d-flex align-items-center">
                                <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                                <span>${data.judul_lampiran}</span>
                             </a>
                        </div>
                        <button type="button" class="btn btn-sm btn-soft-danger" onclick="deleteLampiran(${data.id})" title="Hapus Lampiran">
                            <i class="bi bi-trash"></i>
                        </button>
                    `;
                    listGroup.appendChild(div);
                }
            });

            // Delete Existing Lampiran Logic
            async function deleteLampiran(id) {
                const confirmed = await showConfirm("Yakin ingin menghapus lampiran ini?");
                if (!confirmed) return;

                try {
                    const response = await fetch('../proses/proses_hapus_lampiran.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id })
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        const item = document.getElementById('lampiran-' + id);
                        if(item) item.remove();
                        showToast('Lampiran berhasil dihapus', 'success');
                    } else {
                        showToast(result.message || 'Gagal menghapus lampiran', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan sistem', 'error');
                }
            }
        </script>

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
        <!-- List peserta (Table View) -->
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
                      <th style="width: 100px;"
                        class="text-center px-2 px-md-4 py-3 text-secondary small fw-bold text-uppercase border-bottom-0">Aksi
                      </th>
                    </tr>
                  </thead>
                  <tbody id="addedContainer">
                    <?php if (empty($current_participant_items)): ?>
                    <tr id="emptyRow" style="border-bottom: 1px solid #dee2e6;">
                      <td colspan="3" class="text-center text-muted py-5">
                        <div class="d-flex flex-column align-items-center">
                          <i class="bi bi-people text-secondary mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                          <small>Belum ada peserta yang ditambahkan</small>
                        </div>
                      </td>
                    </tr>
                    <?php else: ?>
                    <?php $no = 1; foreach ($current_participant_items as $item): ?>
                    <tr class="added-item align-middle border-bottom" data-id="<?= htmlspecialchars($item['id']) ?>">
                      <td class="px-2 px-md-4 text-center text-muted small"><?= $no++ ?></td>
                      <td class="px-2 px-md-4 text-start">
                        <?= htmlspecialchars($item['nama']) ?>
                      </td>
                      <td class="text-center px-2 px-md-4">
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

        <!-- HIDDEN INPUTS CONTAINER -->
        <div id="hiddenPesertaContainer" class="d-none">
            <?php foreach ($current_participant_items as $item): ?>
                <input type="hidden" name="peserta[]" value="<?= htmlspecialchars($item['id']) ?>" id="input-peserta-<?= htmlspecialchars($item['id']) ?>">
            <?php endforeach; ?>
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
                
                // CRITICAL FIX: Manually append participant IDs
                // Get all participant rows from the table
                const participantRows = document.querySelectorAll('#addedContainer .added-item');
                participantRows.forEach(row => {
                    const participantId = row.dataset.id;
                    if (participantId) {
                        fd.append('peserta[]', participantId);
                    }
                });
                
                // DEBUG: Log FormData contents
                console.log('=== FORM DATA DEBUG ===');
                for (let [key, value] of fd.entries()) {
                    console.log(key, value);
                }
                console.log('=== END DEBUG ===');

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

        const hiddenPesertaContainer = document.getElementById('hiddenPesertaContainer');

        // Tombol Simpan Pilihan (Dari Modal)
        if (btnSimpanPeserta) {
            btnSimpanPeserta.addEventListener('click', function () {
                const selected = document.querySelectorAll('.notulen-checkbox:checked');
                
                // Clear containers
                addedContainer.innerHTML = '';
                if(hiddenPesertaContainer) hiddenPesertaContainer.innerHTML = '';

                if (selected.length === 0) {
                    addedContainer.innerHTML = '<tr id="emptyRow" style="border-bottom: 1px solid #dee2e6 !important;"><td colspan="3" class="text-center text-muted py-5"><div class="d-flex flex-column align-items-center"><i class="bi bi-people text-secondary mb-2" style="font-size: 2rem; opacity: 0.5;"></i><small>Belum ada peserta yang ditambahkan</small></div></td></tr>';
                } else {
                    selected.forEach((cb, index) => {
                        const id = cb.value;
                        const name = cb.dataset.name;
                        
                        // Visual Row
                        const tr = document.createElement('tr');
                        tr.className = 'added-item align-middle border-bottom'; 
                        tr.dataset.id = id;
                        tr.innerHTML = `
                            <td class="px-2 px-md-4 text-center text-muted small">${index + 1}</td>
                            <td class="px-2 px-md-4 text-start">
                                ${escapeHtml(name)}
                            </td>
                            <td class="text-center px-2 px-md-4">
                                <button type="button" class="btn btn-sm btn-danger remove-btn text-white" data-id="${id}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        `;
                        addedContainer.appendChild(tr);

                        // Hidden Input
                        if(hiddenPesertaContainer) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'peserta[]';
                            input.value = id;
                            input.id = 'input-peserta-' + id;
                            hiddenPesertaContainer.appendChild(input);
                        }
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
                    
                    // Remove Visual Row
                    const item = btn.closest('.added-item');
                    if (item) item.remove();
                    
                    // Remove Hidden Input
                    const hiddenInput = document.getElementById('input-peserta-' + id);
                    if(hiddenInput) hiddenInput.remove();

                    // Re-numbering
                    const remainingItems = addedContainer.querySelectorAll('.added-item');
                    remainingItems.forEach((row, index) => {
                        row.querySelector('td').innerText = index + 1;
                    });
                    
                    if (remainingItems.length === 0) {
                        addedContainer.innerHTML = '<tr id="emptyRow" style="border-bottom: 1px solid #dee2e6 !important;"><td colspan="3" class="text-center text-muted py-5"><div class="d-flex flex-column align-items-center"><i class="bi bi-people text-secondary mb-2" style="font-size: 2rem; opacity: 0.5;"></i><small>Belum ada peserta yang ditambahkan</small></div></td></tr>';
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