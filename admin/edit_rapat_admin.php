<!DOCTYPE html>
<html lang="id">

<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Edit Rapat - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

    <script src="https://cdn.tiny.cloud/1/cl3yw8j9ej8nes9mctfudi2r0jysibdrbn3y932667p04jg5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
    background-color: #00b050;
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
        background-color: #00b050;
        color: #fff;
        border: none;
    }

    .btn-save:hover {
        background-color: #009443;
        color: #fff;
    }
    .btn-save.dropdown-toggle:focus,
    .btn-save.dropdown-toggle:active:focus,
    .btn-save.dropdown-toggle:hover:focus,
    .btn-save.dropdown-toggle:active {
        background-color: #00b050 !important; 
        color: #fff !important; 
        border-color: #00b050 !important; 
        box-shadow: none !important; 
    }
    .dropdown.show .btn-save.dropdown-toggle {
        background-color: #00b050 !important;
        color: #fff !important;
        border-color: #00b050 !important;
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
      background-color: #f8f9fa; /* abu-abu muda */
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

      <form action="dashboard_admin.php" method="post">
        <div class="mb-3">
          <label class="form-label">Judul</label>
          <input type="text" class="form-control" name="judul" value="Masukkan Judul Rapat" />
        </div>

        <div class="mb-3">
          <label class="form-label">Tanggal Rapat</label>
          <div class="input-group">
            <input type="text" class="form-control" name="tanggal" value="dd/mm/yyyy" />
            <span class="input-group-text"><i class="bi bi-calendar"></i></span>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Isi Notulen</label>
            <textarea id="isi" rows="10" placeholder="Tulis isi notulen..."></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Ganti Lampiran (Opsional)</label>
          <input type="file" class="form-control" name="lampiran" />
          <small class="text-muted d-block mt-1">Belum ada file terlampir.</small>
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
                <div class="form-check">
                  <input class="form-check-input notulen-checkbox" type="checkbox" value="Della Reska" id="n1">
                  <label class="form-check-label" for="n1">Della Reska</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input notulen-checkbox" type="checkbox" value="Andi Saputra" id="n2">
                  <label class="form-check-label" for="n2">Andi Saputra</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input notulen-checkbox" type="checkbox" value="Budi Santoso" id="n3">
                  <label class="form-check-label" for="n3">Budi Santoso</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input notulen-checkbox" type="checkbox" value="Citra Ayu" id="n4">
                  <label class="form-check-label" for="n4">Citra Ayu</label>
                </div>
              </div>
              <button type="button" class="btn btn-save w-100 mt-3" id="addButton">Tambah</button>
            </div>
          </div>

          <!-- List peserta -->
          <div id="addedList" class="added-list mt-3">
            <h6 class="fw-bold mb-2">Peserta yang Telah Ditambahkan:</h6>
            <div id="addedContainer">
              <p class="text-muted">Belum ada peserta yang ditambahkan</p>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
          <a href="dashboard_admin.php" class="btn btn-back">Kembali</a>
          <button id="simpan_perubahan" type="button" class="btn btn-save px-4 py-2">Simpan Perubahan</button>
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
            
            setup: function (editor) {
                editor.on('init', function (e) {
                    if (editor.getContent() === '') {
                        editor.setContent('<p>Tulis isi notulen...</p>');
                    }
                });
                editor.on('click', function (e) {
                    if (editor.getContent() === '<p>Tulis isi notulen...</p>') {
                        editor.setContent('');
                    }
                });
            }
        });

    // ===================
    // Logout dan Simpan
    // ===================
    document.getElementById("logoutBtn").addEventListener("click", function () {
      const confirmLogout = confirm("Apakah kamu yakin ingin logout?");
      if (confirmLogout) {
        localStorage.removeItem("userData");
        window.location.href = "../login.php";
      }
    });

    const logoutBtnMobile = document.getElementById("logoutBtnMobile");
    if (logoutBtnMobile) {
      logoutBtnMobile.addEventListener("click", function () {
        const konfirmasiLogout = confirm("Apakah kamu yakin ingin logout?");
        if (konfirmasiLogout) {
          localStorage.removeItem("adminData");
          window.location.href = "../login.php";
        }
      });
    }

    document.getElementById("simpan_perubahan").addEventListener("click", () => {
      if (confirm("Simpan perubahan?")) {
        window.location.href = "dashboard_admin.php";
      }
    });

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

    // Tambah peserta
    addButton.addEventListener('click', function () {
      const selected = document.querySelectorAll('.notulen-checkbox:checked');
      addedContainer.innerHTML = ''; // Kosongkan dulu

      if (selected.length === 0) {
        addedContainer.innerHTML = '<p class="text-muted">Belum ada peserta yang ditambahkan</p>';
        return;
      }

      selected.forEach(item => {
        const div = document.createElement('div');
        div.className = 'added-item';
        div.innerHTML = `${item.value} <button class="btn btn-sm btn-danger remove-btn">x</button>`;
        addedContainer.appendChild(div);
      });

      // Tombol hapus
      document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', function () {
          this.parentElement.remove();
          if (addedContainer.children.length === 0) {
            addedContainer.innerHTML = '<p class="text-muted">Belum ada peserta yang ditambahkan</p>';
          }
        });
      });
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
