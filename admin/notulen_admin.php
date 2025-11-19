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
        <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
            <i class="bi bi-list"></i>
        </button>
    </nav>

    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas"
        aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-body p-0">
            <div class="sidebar-content d-flex flex-column justify-content-between h-100">
                <div>
                    <h5 class="fw-bold mb-4 ms-3">Menu</h5>
                    <ul class="nav flex-column">
                        <li><a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
                        <li><a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a></li>
                        <li><a class="nav-link" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
                    </ul>
                </div>

                <div class="text-center mt-4">
                    <button id="logoutBtnMobile" class="btn logout-btn px-4 py-2">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar-content d-none d-lg-flex flex-column justify-content-between position-fixed">
        <div>
            <h5 class="fw-bold mb-4 ms-3">Menu</h5>
            <ul class="nav flex-column">
                <li><a class="nav-link active" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                <li><a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a></li>
                <li><a class="nav-link" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
            </ul>
        </div>

        <div class="text-center">
            <button id="logoutBtn" class="btn logout-btn px-4 py-2"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
            </div>
            <div class="profile">
                <span>Halo, Admin 👋</span>
            </div>
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard_admin.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah notulen</li>
            </ol>
        </nav>

        <div class="form-section">
            <h5 class="fw-semibold mb-4">Tambah Notulen</h5>

            <div id="alertBox" class="alert alert-success" role="alert" style="display: none;">
                Notulen berhasil disimpan!!!
            </div>

            <form id="notulenForm">
                <div class="mb-3">
                    <label class="form-label">Judul</label>
                    <input type="text" class="form-control" id="judul" placeholder="Masukkan judul rapat" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" class="form-control" id="tanggal" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Isi</label>
                    <textarea id="isi" rows="10" placeholder="Tulis isi notulen..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Upload file (opsional)</label>
                    <input type="file" class="form-control" id="fileInput">
                </div>

            <!-- Dropdown Peserta -->
        <div class="mb-3">
        <label class="form-label">Peserta Notulen</label>
        <div class="dropdown w-50">
            <button class="btn btn-save w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown"
            aria-expanded="false">Pilih Peserta</button>

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
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-save px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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

        // Simulasi penyimpanan form
        document.getElementById("notulenForm").addEventListener("submit", function (e) {
            e.preventDefault();

            const judul = document.getElementById("judul").value;
            const tanggal = document.getElementById("tanggal").value;
            const isi = tinymce.get('isi').getContent(); 

            const peserta = [];
            document.querySelectorAll(".form-check-input:checked").forEach(cb => {
                peserta.push(cb.value);
            });

            console.log({
                judul, tanggal, isi, peserta
            });

            const alertBox = document.getElementById("alertBox");
            alertBox.style.display = "block"; // Tampilkan alert

            // Reset form dan editor setelah simpan
            setTimeout(() => {
                alertBox.style.display = "none";
                document.getElementById("notulenForm").reset();
                tinymce.get('isi').setContent(''); 
            }, 2000);
        });

        // Logout Desktop
        document.getElementById("logoutBtn").addEventListener("click", function () {
            if (confirm("Apakah kamu yakin ingin logout?")) {
                localStorage.removeItem("userData");
                window.location.href = "../login.php";
            }
        });

        // Logout Mobile
        const logoutBtnMobile = document.getElementById("logoutBtnMobile");
        if (logoutBtnMobile) {
            logoutBtnMobile.addEventListener("click", function () {
                if (confirm("Apakah kamu yakin ingin logout?")) {
                    localStorage.removeItem("adminData");
                    window.location.href = "../login.php";
                }
            });
        }
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
</body>
</html>