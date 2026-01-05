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

  <script src="https://cdn.tiny.cloud/1/ax0ia7o379mr6qirp1j81wj9ulw3j38l2tvuuwdktdpjxzcj/tinymce/6/tinymce.min.js"
    referrerpolicy="origin"></script>
  <link rel="stylesheet" href="../css/admin.min.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/forms.css">
  <!-- Select2 for searchable dropdown -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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

      <form id="editForm" method="POST" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="id" value="<?= $id_notulen ?>">

        <div class="mb-3">
          <label class="form-label">Judul</label>
          <input type="text" class="form-control" name="judul" value="<?= htmlspecialchars($notulen['judul'] ?? '') ?>"
            />
        </div>

        <div class="mb-3">
          <label class="form-label">Tanggal Rapat</label>
          <div class="input-group">
            <input type="date" class="form-control" name="tanggal" value="<?= $notulen['tanggal'] ?? '' ?>" />
          </div>
        </div>

        <!-- Jam Mulai dan Jam Selesai -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Jam Mulai</label>
                <input type="time" class="form-control" name="jam_mulai" id="jam_mulai" value="<?= $notulen['jam_mulai'] ?? '' ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Jam Selesai</label>
                <input type="time" class="form-control" name="jam_selesai" id="jam_selesai" value="<?= $notulen['jam_selesai'] ?? '' ?>" required>
            </div>
        </div>

        <!-- Penanggung Jawab -->
        <div class="mb-3">
          <label class="form-label">Penanggung Jawab</label>
          <select class="form-select" name="penanggung_jawab" id="penanggungJawab" required>
            <option value="">-- Pilih Penanggung Jawab --</option>
            <?php foreach ($all_users as $u): 
                $roleDisplay = ($u['role'] === 'admin') ? 'Notulis' : ucfirst($u['role']);
            ?>
            <option value="<?= $u['id'] ?>" <?= (isset($notulen['penanggung_jawab']) && $notulen['penanggung_jawab'] == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['nama']) ?> (<?= $roleDisplay ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Status Notulen</label>
          <select class="form-select" name="status" id="statusSelect">
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
                             <a href="../uploads/<?= htmlspecialchars($lamp['file_lampiran']) ?>" target="_blank" class="text-decoration-none text-dark d-flex align-items-center me-2" id="lampiran-link-<?= $lamp['id'] ?>">
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

        <!-- Lampiran logic is handled in js/edit_rapat.js -->

        <!-- Dropdown Peserta -->
        <!-- Dropdown Peserta REPLACED WITH MODAL TRIGGER + TAMBAH PENGGUNA -->
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

        <!-- List peserta (List View like Detail Page) -->
        <div class="mb-4">
          <label class="form-label fw-semibold mb-2">Daftar Peserta:</label>
          <div class="card border-0 shadow-sm">
            <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
              <div class="list-group list-group-flush" id="addedContainer">
                <?php if (empty($current_participant_items)): ?>
                <div id="emptyRow" class="p-4 text-center text-muted">
                  <div class="d-flex flex-column align-items-center">
                    <i class="bi bi-people text-secondary mb-2" style="font-size: 1.5rem; opacity: 0.5;"></i>
                    <small>Belum ada peserta</small>
                  </div>
                </div>
                <?php else: ?>
                <?php $no = 1; foreach ($current_participant_items as $item): ?>
                <div class="list-group-item d-flex align-items-center py-3 px-3 border-bottom-0 border-top-0 border-end-0 border-start-0 added-item" data-id="<?= htmlspecialchars($item['id']) ?>">
                  <span class="me-3 fw-bold text-secondary small" style="min-width: 25px;"><?= $no++ ?>.</span>
                  <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3 border" style="width: 38px; height: 38px; flex-shrink: 0;"><i class="bi bi-person-fill text-secondary fs-5"></i></div>
                  <div class="flex-grow-1">
                    <div class="fw-medium text-dark"><?= htmlspecialchars($item['nama']) ?></div>
                    <?php if (!empty($item['email'])): ?>
                    <div class="text-muted small" style="font-size: 0.75rem;"><?= htmlspecialchars(strtolower($item['email'])) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
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
          <button id="simpan_perubahan" type="submit" class="btn btn-save px-4">Simpan</button>
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
                                  data-email="<?= htmlspecialchars(strtolower($u['email'])) ?>"
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

    <script>
        // Data Status dari PHP untuk JS
        window.notulenStatus = "<?= $notulen['status'] ?? 'draft' ?>";

        // === TINYMCE INITIALIZATION ===
        tinymce.init({
          selector: '#isi',
          height: 350,
          menubar: false,
          api_key: 'ax0ia7o379mr6qirp1j81wj9ulw3j38l2tvuuwdktdpjxzcj',
          plugins: "lists link table code",
          toolbar: "undo redo | bold italic underline | bullist numlist | link",
          readonly: <?= ($notulen['status'] ?? 'draft') === 'final' ? 'true' : 'false' ?>
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery for Select2 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // Initialize Select2 for Penanggung Jawab dropdown
        $(document).ready(function() {
            $('#penanggungJawab').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Pilih Penanggung Jawab --',
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
    
    <script src="../js/admin.js"></script>
    <script src="../js/edit_rapat.js"></script>
</body>
</html>