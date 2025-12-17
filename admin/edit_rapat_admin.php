<?php
include '../config_admin/db_edit_rapat_admin.php';
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

  <script src="https://cdn.tiny.cloud/1/mnqdvqiep8rrq6ozk4hrfn9d8734oxaqe4cyps522sfrd8y3/tinymce/6/tinymce.min.js"
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
<?php 
    $pageTitle = "Edit Notulen";
    // sidebar
    include '../Nav_Side_Bar/sidebar.php'; 
    // header
    include '../Nav_Side_Bar/header.php';
?>

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
                    <div class="list-group-item d-flex justify-content-between align-items-center" id="lampiran-row-<?= $lamp['id'] ?>">
                        <div class="d-flex align-items-center flex-grow-1 me-3">
                             <a href="../file/<?= htmlspecialchars($lamp['file_lampiran']) ?>" target="_blank" class="text-decoration-none text-dark d-flex align-items-center me-2" id="lampiran-link-<?= $lamp['id'] ?>">
                                <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                             </a>
                             <span id="lampiran-title-<?= $lamp['id'] ?>" class="fw-medium"><?= htmlspecialchars($lamp['judul_lampiran']) ?></span>
                             
                             <!-- Edit Input (Hidden by default) -->
                             <div id="lampiran-edit-container-<?= $lamp['id'] ?>" class="d-none w-100">
                                <input type="text" id="lampiran-input-<?= $lamp['id'] ?>" class="form-control form-control-sm" value="<?= htmlspecialchars($lamp['judul_lampiran']) ?>">
                             </div>
                        </div>
                        
                        <div class="d-flex gap-1 align-items-center">
                            <!-- Action Buttons -->
                            <div id="lampiran-actions-<?= $lamp['id'] ?>">
                                <?php if (($notulen['status'] ?? 'draft') === 'draft'): ?>
                                <button type="button" class="btn btn-sm btn-soft-primary" onclick="editLampiran(<?= $lamp['id'] ?>)" title="Edit Judul">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-soft-danger" onclick="deleteLampiran(<?= $lamp['id'] ?>)" title="Hapus Lampiran">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>

                            <!-- Save/Cancel Buttons (Hidden by default) -->
                            <div id="lampiran-save-actions-<?= $lamp['id'] ?>" class="d-none">
                                <button type="button" class="btn btn-sm btn-success" onclick="saveLampiran(<?= $lamp['id'] ?>)" title="Simpan">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="cancelEditLampiran(<?= $lamp['id'] ?>)" title="Batal">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
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
                    if (!listGroup) return; 

                    const div = document.createElement('div');
                    div.className = 'list-group-item d-flex justify-content-between align-items-center';
                    div.id = 'lampiran-row-' + data.id;
                    
                    // Check draft status from global variable or PHP injection
                    // Since this page is loaded, we can use the window.notulenStatus variable defined at bottom
                    const isDraft = (window.notulenStatus === 'draft');

                    div.innerHTML = `
                        <div class="d-flex align-items-center flex-grow-1 me-3">
                             <a href="../file/${data.file_lampiran}" target="_blank" class="text-decoration-none text-dark d-flex align-items-center me-2" id="lampiran-link-${data.id}">
                                <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                             </a>
                             <span id="lampiran-title-${data.id}" class="fw-medium">${data.judul_lampiran}</span>
                             
                             <div id="lampiran-edit-container-${data.id}" class="d-none w-100">
                                <input type="text" id="lampiran-input-${data.id}" class="form-control form-control-sm" value="${data.judul_lampiran}">
                             </div>
                        </div>
                        
                        <div class="d-flex gap-1 align-items-center">
                            <div id="lampiran-actions-${data.id}">
                                ${isDraft ? `
                                <button type="button" class="btn btn-sm btn-soft-primary" onclick="editLampiran(${data.id})" title="Edit Judul">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                ` : ''}
                                <button type="button" class="btn btn-sm btn-soft-danger" onclick="deleteLampiran(${data.id})" title="Hapus Lampiran">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>

                            <div id="lampiran-save-actions-${data.id}" class="d-none">
                                <button type="button" class="btn btn-sm btn-success" onclick="saveLampiran(${data.id})" title="Simpan">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="cancelEditLampiran(${data.id})" title="Batal">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
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
                        const item = document.getElementById('lampiran-row-' + id);
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

            // === EDIT LAMPIRAN LOGIC ===
            function editLampiran(id) {
                // Hide display elements
                document.getElementById(`lampiran-title-${id}`).classList.add('d-none');
                document.getElementById(`lampiran-actions-${id}`).classList.add('d-none');
                
                // Show edit elements
                document.getElementById(`lampiran-edit-container-${id}`).classList.remove('d-none');
                document.getElementById(`lampiran-save-actions-${id}`).classList.remove('d-none');
                
                // Focus input
                document.getElementById(`lampiran-input-${id}`).focus();
            }

            function cancelEditLampiran(id) {
                // Reset input value to original title
                const originalTitle = document.getElementById(`lampiran-title-${id}`).innerText;
                document.getElementById(`lampiran-input-${id}`).value = originalTitle;

                // Revert UI
                document.getElementById(`lampiran-title-${id}`).classList.remove('d-none');
                document.getElementById(`lampiran-actions-${id}`).classList.remove('d-none');
                
                document.getElementById(`lampiran-edit-container-${id}`).classList.add('d-none');
                document.getElementById(`lampiran-save-actions-${id}`).classList.add('d-none');
            }

            async function saveLampiran(id) {
                const newTitle = document.getElementById(`lampiran-input-${id}`).value.trim();
                
                if (!newTitle) {
                    showToast("Judul lampiran tidak boleh kosong", "error");
                    return;
                }

                // Show loading state (opt)
                const saveBtn = document.querySelector(`#lampiran-save-actions-${id} .btn-success`);
                const originalBtnContent = saveBtn.innerHTML;
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                try {
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('judul_lampiran', newTitle);

                    const response = await fetch('../proses/proses_edit_lampiran.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Update UI with new title
                        document.getElementById(`lampiran-title-${id}`).innerText = newTitle;
                        
                        // Revert to display mode
                        cancelEditLampiran(id); 
                        
                        // Revert "cancel" effect which reset the input, we want the input to have new value next time or just valid sync
                        document.getElementById(`lampiran-input-${id}`).value = newTitle; // Sync input

                        showToast("Judul lampiran berhasil diperbarui", "success");
                    } else {
                        showToast(result.message || "Gagal memperbarui judul", "error");
                    }
                } catch (error) {
                    console.error(error);
                    showToast("Terjadi kesalahan sistem", "error");
                } finally {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalBtnContent;
                }
            }
        </script>

        <!-- Dropdown Peserta -->
        <!-- Dropdown Peserta REPLACED WITH MODAL TRIGGER + TAMBAH PENGGUNA -->
        <div class="mb-4">
          <label class="form-label fw-semibold">Peserta Notulen</label>
          <div class="row g-3">
              <!-- Pilih Peserta (Kiri) -->
              <div class="col-md-6">
                  <div class="card h-100 border-0 shadow-sm">
                      <div class="card-body text-center py-4">
                          <div class="mb-3">
                              <i class="bi bi-people-fill text-success" style="font-size: 2.5rem;"></i>
                          </div>
                          <h6 class="fw-semibold mb-2">Pilih Peserta</h6>
                          <p class="text-muted small mb-3">Pilih dari daftar pengguna yang sudah ada</p>
                          <button type="button" class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#modalPeserta">
                              <i class="bi bi-list-ul me-2"></i>Pilih Peserta
                          </button>
                      </div>
                  </div>
              </div>
              <!-- Tambah Pengguna Baru (Kanan) -->
              <div class="col-md-6">
                  <div class="card h-100 border-0 shadow-sm">
                      <div class="card-body text-center py-4">
                          <div class="mb-3">
                              <i class="bi bi-person-plus-fill text-success" style="font-size: 2.5rem;"></i>
                          </div>
                          <h6 class="fw-semibold mb-2">Tambah Pengguna Baru</h6>
                          <p class="text-muted small mb-3">Buat akun peserta baru langsung dari sini</p>
                          <button type="button" class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#modalTambahPengguna">
                              <i class="bi bi-person-plus me-2"></i>Tambah Pengguna
                          </button>
                      </div>
                  </div>
              </div>
          </div>
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
                        <i class="bi bi-person-plus me-1"></i>Tambahkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Data Status dari PHP untuk JS
        window.notulenStatus = "<?= $notulen['status'] ?? 'draft' ?>";

        // === TINYMCE INITIALIZATION ===
        tinymce.init({
          selector: '#isi',
          height: 350,
          menubar: false,
          api_key: 'mnqdvqiep8rrq6ozk4hrfn9d8734oxaqe4cyps522sfrd8y3',
          plugins: "lists link table code",
          toolbar: "undo redo | bold italic underline | bullist numlist | link",
          readonly: <?= ($notulen['status'] ?? 'draft') === 'final' ? 'true' : 'false' ?>
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
    <script src="../js/edit_rapat.js"></script>
</body>
</html>