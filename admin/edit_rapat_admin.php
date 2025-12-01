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
$current_participants = array_filter(array_map('trim', explode(',', $notulen['peserta'])), function($v){ return $v !== ''; });
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
    $current_participant_items[] = ['id'=>$pid_int, 'nama'=>$participants_map[$pid_int]];
  } elseif ($pid !== '') {
    // fallback: jika DB tidak punya, tampilkan apa yang ada (biasanya not expected)
    $current_participant_items[] = ['id'=>$pid, 'nama'=>$pid];
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Rapat - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

  <script src="https://cdn.tiny.cloud/1/cl3yw8j9ej8nes9mctfudi2r0jysibdrbn3y932667p04jg5/tinymce/6/tinymce.min.js"
    referrerpolicy="origin"></script>
  <style>
    body {
      background-color: #faf8f5;
      font-family: "Poppins", sans-serif;
    }

    .sidebar-content {
      min-width: 250px;
      background: #fff;
      height: 100%;
      border-right: 1px solid #eee;
      padding: 1.5rem 1rem;
    }

    .sidebar-content .nav-link {
      color: #555;
      font-weight: 500;
      margin-bottom: 0.5rem;
      border-radius: 0.5rem;
    }

    .sidebar-content .nav-link.active,
    .sidebar-content .nav-link:hover {
      background-color: #00c853;
      color: #fff;
    }

    .logout-btn {
      border: 1px solid #f8d7da;
      color: #dc3545;
      border-radius: 0.5rem;
    }

    .main-content {
      margin-left: 260px;
      padding: 1.5rem;
    }

    @media (max-width: 991.98px) {
      .main-content {
        margin-left: 0;
      }
    }

    .form-wrapper {
      background: #fff;
      border-radius: 1rem;
      padding: 2rem;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
    }

    .btn-save {
      background-color: #00c853;
      color: #fff;
      border: none;
    }

    .btn-save:hover {
      background-color: #00c853;
      color: #fff;
    }

    .btn-save.dropdown-toggle:focus,
    .btn-save.dropdown-toggle:active:focus,
    .btn-save.dropdown-toggle:hover:focus,
    .btn-save.dropdown-toggle:active {
      background-color: #00c853 !important;
      color: #fff !important;
      border-color: #00c853 !important;
      box-shadow: none !important;
    }

    .dropdown.show .btn-save.dropdown-toggle {
      background-color: #00c853 !important;
      color: #fff !important;
      border-color: #00c853 !important;
    }

    .btn-back {
      background-color: #f8f9fa;
      border: 1px solid #ccc;
      border-radius: .5rem;
      font-weight: 500;
    }

    .btn-back:hover {
      background-color: #e9ecef;
    }

    input[type="file"] {
      border: 1px solid #dee2e6;
      border-radius: 0.375rem;
      padding: 0.4rem;
      width: 100%;
    }

    .form-check-label {
      margin-left: 4px;
      font-size: 15px;
      cursor: pointer;
    }

    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    .form-label {
      font-weight: 500;
    }

    .dropdown-menu {
      width: 100%;
      max-height: 250px;
      overflow-y: auto;
    }

    .search-box {
      padding: 8px;
    }

    .added-list {
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 10px 15px;
      margin-top: 10px;
      width: 50%;
    }

    .added-item {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 6px 10px;
      margin: 5px 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .select-all-box {
      background-color: #f8f9fa;
      /* abu-abu muda */
      border-radius: 8px;
      padding: 6px 10px;
      margin-top: 5px;
      margin-bottom: 8px;
      border: 1px solid #e0e0e0;
    }
  </style>
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
      <h5 class="fw-bold mb-4 ms-3">Menu</h5>
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
          <label class="form-label">Ganti Lampiran (Opsional)</label>
          <input type="file" class="form-control" name="lampiran" />
          <?php if (!empty($notulen['Lampiran'])): ?>
          <small class="text-muted d-block mt-1">File saat ini: <a href="../file/<?= $notulen['Lampiran'] ?>"
              target="_blank"><?= $notulen['Lampiran'] ?></a></small>
          <?php else: ?>
          <small class="text-muted d-block mt-1">Belum ada file terlampir.</small>
          <?php endif; ?>
        </div>

        <!-- Dropdown Peserta -->
        <div class="mb-3">
          <label class="form-label">Peserta Notulen</label>
          <div class="dropdown w-50">
            <button class="btn btn-save w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              Pilih Peserta
            </button>

            <div class="dropdown-menu p-2">
              <input type="text" class="form-control search-box" id="searchInput" placeholder="Cari nama notulen...">
              <div class="select-all-box">
                <div class="form-check m-0">
                  <input class="form-check-input" type="checkbox" id="selectAll">
                  <label class="form-check-label fw-semibold" for="selectAll">Pilih Semua</label>
                </div>
              </div>
              <div id="notulenList" class="mt-2">
                <?php foreach ($all_users as $user): ?>
                <div class="form-check">
                  <input class="form-check-input notulen-checkbox" type="checkbox" value="<?= (int)$user['id'] ?>"
                    id="user_<?= md5($user['id']) ?>">
                  <label class="form-check-label"
                    for="user_<?= md5($user['id']) ?>"><?= htmlspecialchars($user['nama']) ?></label>
                </div>
                <?php endforeach; ?>
              </div>
              <button type="button" class="btn btn-save w-100 mt-3" id="addButton">Tambah</button>
            </div>
          </div>

          <!-- List peserta -->
          <div id="addedList" class="added-list mt-3">
            <h6 class="fw-bold mb-2">Peserta yang Telah Ditambahkan:</h6>
            <div id="addedContainer">
              <!-- Pre-fill participants -->
              <?php foreach ($current_participant_items as $item): ?>
              <div class="added-item">
                <?= htmlspecialchars($item['nama']) ?>
                <input type="hidden" name="peserta[]" value="<?= htmlspecialchars($item['id']) ?>">
                <button type="button" class="btn btn-sm btn-danger remove-btn">x</button>
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

    // ===================
    // Logout
    // ===================
    function confirmLogout() {
      if (confirm("Apakah kamu yakin ingin logout?")) {
        window.location.href = "../proses/proses_logout.php";
      }
    }
    document.getElementById("logoutBtn").addEventListener("click", confirmLogout);

    // ===================
    // Fungsi Dropdown Peserta
    // ===================
    const searchInput = document.getElementById('searchInput');
    const notulenItems = document.querySelectorAll('#notulenList .form-check');
    const selectAll = document.getElementById('selectAll');
    const addButton = document.getElementById('addButton');
    const addedContainer = document.getElementById('addedContainer');

    // Search
    searchInput.addEventListener('keyup', () => {
      const filter = searchInput.value.toLowerCase();
      notulenItems.forEach(item => {
        const text = item.innerText.toLowerCase();
        item.style.display = text.includes(filter) ? '' : 'none';
      });
    });

    // Select all
    selectAll.addEventListener('change', function () {
      const allCheckboxes = document.querySelectorAll('.notulen-checkbox');
      allCheckboxes.forEach(cb => cb.checked = this.checked);
    });

    // Tambah peserta (JS)
    addButton.addEventListener('click', function () {
      const selected = document.querySelectorAll('.notulen-checkbox:checked');

      // Hapus placeholder jika ada
      const placeholder = addedContainer.querySelector('.text-muted');
      if (placeholder) placeholder.remove();

      selected.forEach(cb => {
        const val = cb.value; // ini ID (string)
        const label = cb.nextElementSibling ? cb.nextElementSibling.textContent.trim() : val;

        // Cek duplicate berdasarkan hidden input value (ID)
        const existingInputs = addedContainer.querySelectorAll('input[name="peserta[]"]');
        let exists = false;
        existingInputs.forEach(inp => {
          if (inp.value === val) exists = true;
        });

        if (!exists) {
          const div = document.createElement('div');
          div.className = 'added-item';

          const textNode = document.createTextNode(label + ' ');
          div.appendChild(textNode);

          const hidden = document.createElement('input');
          hidden.type = 'hidden';
          hidden.name = 'peserta[]';
          hidden.value = val;
          div.appendChild(hidden);

          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'btn btn-sm btn-danger remove-btn';
          btn.textContent = 'x';
          div.appendChild(btn);

          addedContainer.appendChild(div);
        }
        cb.checked = false; // Uncheck setelah ditambah
      });

      selectAll.checked = false;
      attachRemoveEvent();
    });

    function attachRemoveEvent() {
      document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.onclick = function () {
          const parent = this.parentElement;
          parent.remove();
          // cek apakah masih ada .added-item tersisa
          if (addedContainer.querySelectorAll('.added-item').length === 0) {
            addedContainer.innerHTML = '<p class="text-muted">Belum ada peserta yang ditambahkan</p>';
          }
        };
      });
    }


    // Attach event on load for existing items
    attachRemoveEvent();
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>