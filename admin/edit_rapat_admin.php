<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek Login & Role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$id_notulen = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_notulen <= 0) {
  echo "<script>alert('ID Notulen tidak valid!'); window.location.href='dashboard_admin.php';</script>";
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
  echo "<script>alert('Data notulen tidak ditemukan!'); window.location.href='dashboard_admin.php';</script>";
  exit;
}

// Ambil daftar semua user untuk dropdown peserta
$sql_users = "SELECT id, nama FROM users ORDER BY nama ASC";
$res_users = $conn->query($sql_users);
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
  <title>Edit Rapat - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

  <script src="https://cdn.tiny.cloud/1/cl3yw8j9ej8nes9mctfudi2r0jysibdrbn3y932667p04jg5/tinymce/6/tinymce.min.js"
    referrerpolicy="origin"></script>
  <link rel="stylesheet" href="../css/admin.min.css">
</head>

<body>
  <!-- navbar -->
  <nav class="navbar navbar-light bg-white sticky-top px-3">
    <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas"
      data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
      <i class="bi bi-list"></i>
    </button>
  </nav>

  <!-- Sidebar -->
  <div class="sidebar-content d-none d-lg-flex flex-column justify-content-between position-fixed">
    <div>
      <h4 class="fw-bold mb-4 ms-3">MENU</h4>
      <ul class="nav flex-column">
        <li><a class="nav-link active" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
        <li><a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a></li>
        <li><a class="nav-link" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
      </ul>
    </div>
    <div class="text-center">
      <button id="logoutBtn" class="btn logout-btn px-4 py-2"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="topbar"><span>Halo, Admin 👋</span></div>

    <div class="form-wrapper">
      <h5 class="fw-semibold mb-4">Edit Notulen</h5>

      <form action="../proses/proses_edit_notulen.php" method="post" enctype="multipart/form-data" id="editForm">
        <input type="hidden" name="id" value="<?= $id_notulen ?>">

        <div class="mb-3">
          <label class="form-label">Judul</label>
          <input type="text" class="form-control" name="judul" value="<?= htmlspecialchars($notulen['judul_rapat']) ?>"
            required />
        </div>

        <div class="mb-3">
          <label class="form-label">Tanggal Rapat</label>
          <div class="input-group">
            <input type="date" class="form-control" name="tanggal" value="<?= $notulen['tanggal_rapat'] ?>" required />
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Isi Notulen</label>
          <textarea id="isi" name="isi" rows="10"><?= htmlspecialchars($notulen['isi_rapat']) ?></textarea>
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
            <small class="text-muted d-block mt-1">File saat ini: <a href="../file/<?= $notulen['Lampiran'] ?>"
                target="_blank"><?= $notulen['Lampiran'] ?></a></small>
          <?php else: ?>
            <small class="text-muted d-block mt-1">Belum ada file terlampir.</small>
          <?php endif; ?>
        </div>

        <!-- Dropdown Peserta -->
        <div class="mb-3">
          <label class="form-label">Peserta Notulen</label>
          <div class="dropdown w-50" data-bs-auto-close="false">
            <button class="btn btn-save w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              Pilih Peserta
            </button>

            <div class="dropdown-menu p-3 w-100" style="max-height: 360px; overflow:auto;">
              <div class="mb-2">
                <input type="text" class="form-control" id="searchInput" placeholder="Cari peserta..." />
              </div>

              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="selectAll">
                <label class="form-check-label" for="selectAll">Pilih Semua</label>
              </div>

              <hr />

              <div id="notulenList">
                <?php if (empty($all_users)): ?>
                    <div class="text-muted">Belum ada peserta.</div>
                <?php else: ?>
                    <?php foreach ($all_users as $user): ?>
                        <div class="form-check notulen-item py-1">
                            <input class="form-check-input notulen-checkbox" type="checkbox"
                                value="<?= htmlspecialchars($user['id']) ?>" id="u<?= $user['id'] ?>"
                                data-name="<?= htmlspecialchars($user['nama']) ?>">
                            <label class="form-check-label" for="u<?= $user['id'] ?>"><?= htmlspecialchars($user['nama']) ?></label>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
              </div>

              <hr />

              <div class="d-flex justify-content-between">
                <button id="clearSearchBtn" type="button" class="btn btn-sm btn-light">Reset</button>
                <button id="addButton" type="button" class="btn btn-sm btn-success">Tambah</button>
              </div>
            </div>
          </div>

          <!-- List peserta -->
          <div id="addedList" class="added-list mt-3">
            <h6 class="fw-bold mb-2">Peserta yang Telah Ditambahkan:</h6>
            <div id="addedContainer">
              <!-- Pre-fill participants -->
              <?php foreach ($current_participant_items as $item): ?>
                <div class="added-item d-flex align-items-center gap-2 mb-2">
                  <span class="flex-grow-1"><?= htmlspecialchars($item['nama']) ?></span>
                  <input type="hidden" name="peserta[]" value="<?= htmlspecialchars($item['id']) ?>">
                  <button type="button" class="btn btn-sm btn-outline-danger remove-btn">Hapus</button>
                </div>
              <?php endforeach; ?>
              <?php if (empty($current_participants) || (count($current_participants) == 1 && empty($current_participants[0]))): ?>
                <p class="text-muted">Belum ada peserta yang ditambahkan</p>
              <?php endif; ?>
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

  <script>
    // === TINYMCE INITIALIZATION ===
    tinymce.init({
      selector: '#isi',
      height: 350,
      menubar: 'edit view insert format tools table help',
      plugins: [
        "advlist", "anchor", "autolink", "charmap", "code", "fullscreen",
        "help", "image", "insertdatetime", "link", "lists", "media",
        "preview", "searchreplace", "table", "visualblocks", "wordcount"
      ],
      toolbar: "undo redo | styles | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
    });

    document.addEventListener("DOMContentLoaded", function() {
        // Logout handlers
        const logoutBtn = document.getElementById("logoutBtn");
        if (logoutBtn) {
            logoutBtn.addEventListener("click", function () {
                if (confirm("Apakah kamu yakin ingin logout?")) {
                    window.location.href = "../proses/proses_logout.php";
                }
            });
        }
        const logoutBtnMobile = document.getElementById("logoutBtnMobile");
        if (logoutBtnMobile) {
            logoutBtnMobile.addEventListener("click", function () {
                if (confirm("Apakah kamu yakin ingin logout?")) {
                    window.location.href = "../proses/proses_logout.php";
                }
            });
        }

        // ===================
        // Fungsi Dropdown Peserta
        // ===================
        
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
        
        const searchInput = document.getElementById('searchInput');
        const notulenItems = document.querySelectorAll('#notulenList .form-check');
        const selectAll = document.getElementById('selectAll');
        const addButton = document.getElementById('addButton');
        const addedContainer = document.getElementById('addedContainer');
        const clearSearchBtn = document.getElementById('clearSearchBtn');

        // Search
        if (searchInput) {
            searchInput.addEventListener('keyup', () => {
                const filter = searchInput.value.toLowerCase();
                notulenItems.forEach(item => {
                    const text = item.innerText.toLowerCase();
                    item.style.display = text.includes(filter) ? '' : 'none';
                });
            });
        }

        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', () => {
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.dispatchEvent(new Event('keyup'));
                }
            });
        }

        // Select all
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const allCheckboxes = document.querySelectorAll('.notulen-checkbox');
                allCheckboxes.forEach(cb => cb.checked = this.checked);
            });
        }

        // Tambah peserta
        if (addButton) {
            addButton.addEventListener('click', function() {
                const selected = document.querySelectorAll('.notulen-checkbox:checked');
                
                if (selected.length === 0) {
                    return;
                }

                // Ambil peserta yang sudah ada untuk mencegah duplikat
                const existingIds = new Set();
                addedContainer.querySelectorAll('.added-item').forEach(item => {
                    const inputs = item.querySelectorAll('input[type="hidden"]');
                    inputs.forEach(inp => {
                        existingIds.add(inp.value);
                    });
                });
                
                // Hapus pesan "Belum ada peserta" jika ada
                const emptyMsg = addedContainer.querySelector('.text-muted');
                if (emptyMsg && emptyMsg.innerText.includes('Belum ada peserta')) {
                    emptyMsg.remove();
                }

                selected.forEach(cb => {
                    const id = cb.value;
                    // Get name from data-name attribute or label text
                    let name = cb.dataset.name;
                    if (!name) {
                        const label = document.querySelector(`label[for="${cb.id}"]`);
                        name = label ? label.textContent.trim() : 'Unknown';
                    }
                    
                    // Cek jika id sudah ada untuk mencegah duplikat
                    if (existingIds.has(id)) return;

                    const div = document.createElement('div');
                    div.className = 'added-item d-flex align-items-center gap-2 mb-2';
                    div.innerHTML = `
                        <span class="flex-grow-1">${escapeHtml(name)}</span>
                        <input type="hidden" name="peserta[]" value="${escapeHtml(id)}">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-btn">Hapus</button>
                    `;
                    addedContainer.appendChild(div);
                    
                    // Uncheck setelah ditambah
                    cb.checked = false;
                });

                if (selectAll) selectAll.checked = false;
                attachRemoveEvent();
            });
        }

        function attachRemoveEvent() {
            document.querySelectorAll('.remove-btn').forEach(btn => {
                // Remove existing listeners to avoid duplicates (though simple onclick replacement works too)
                btn.onclick = function(e) {
                    e.preventDefault();
                    const parent = this.closest('.added-item'); // Use closest to be safe
                    if (parent) parent.remove();
                    
                    // cek apakah masih ada .added-item tersisa
                    if (addedContainer.querySelectorAll('.added-item').length === 0) {
                        addedContainer.innerHTML = '<p class="text-muted">Belum ada peserta yang ditambahkan</p>';
                    }
                };
            });
        }

        // Attach event on load for existing items
        attachRemoveEvent();
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>