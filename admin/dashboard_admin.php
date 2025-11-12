<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Admin</title>

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/admin.min.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-light bg-white sticky-top px-3">
        <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas" aria-label="Buka menu">
            <i class="bi bi-list"></i>
        </button>
    </nav>

    <!-- Sidebar mobile -->
    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas"
        aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-body p-0">
            <div class="sidebar-content d-flex flex-column justify-content-between h-100">
                <div>
                    <h5 class="fw-bold mb-4 ms-3">Menu</h5>
                    <ul class="nav flex-column">
                        <li><a class="nav-link" href="dashboard_admin.html"><i class="bi bi-grid me-2"></i>Dashboard</a>
                        </li>
                        <li><a class="nav-link" href="kelola_rapat_admin.html"><i class="bi bi-people me-2"></i>Kelola
                                Pengguna</a></li>
                        <li><a class="nav-link" href="profile.html"><i class="bi bi-person-circle me-2"></i>Profile</a>
                        </li>
                    </ul>
                </div>

                <div class="text-center mt-4">
                    <button id="logoutBtnMobile" class="btn logout-btn px-4 py-2"><i
                            class="bi bi-box-arrow-right me-2"></i>Logout</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar (desktop) -->
    <aside class="sidebar-content d-none d-lg-flex flex-column justify-content-between position-fixed">
        <div>
            <h5 class="fw-bold mb-4 ms-3">Menu</h5>
            <ul class="nav flex-column">
                <li><a class="nav-link active" href="dashboard_admin.html"><i class="bi bi-grid me-2"></i>Dashboard</a>
                </li>
                <li><a class="nav-link" href="kelola_rapat_admin.html"><i class="bi bi-people me-2"></i>Kelola
                        Pengguna</a></li>
                <li><a class="nav-link" href="profile.html"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
            </ul>
        </div>

        <div class="text-center">
            <button id="logoutBtn" class="btn logout-btn px-4 py-2"><i
                    class="bi bi-box-arrow-right me-2"></i>Logout</button>
        </div>
    </aside>

    <!-- Main content -->
    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4><b>Dashboard Admin</b></h4>
            </div>
            <div class="d-flex align-items-center gap-2"><span class="fw-medium">Halo, Admin 👋</span></div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="highlight-card">
                    <span class="text-muted">31/12/2025</span>
                    <h6 class="mt-1 mb-1">Rapat Akhir Tahun</h6>
                    <p>......</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="highlight-card">
                    <span class="text-muted">30/12/2025</span>
                    <h6 class="mt-1 mb-1">Evaluasi Kinerja</h6>
                    <p>......</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="highlight-card">
                    <span class="text-muted">29/12/2025</span>
                    <h6 class="mt-1 mb-1">Rapat Tim Proyek</h6>
                    <p>......</p>
                </div>
            </div>
        </div>

        <!-- TABLE AREA -->
        <section class="table-wrapper">
            <div class="table-header d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <h5 class="fw-semibold mb-2 mb-sm-0">Daftar Notulen</h5>

                <!-- Controls -->
                <div class="d-flex gap-2 flex-wrap controls align-items-center">
                    <div class="tambah-container">
                        <a href="notulen_admin.html" class="btn-tambah" role="button"><i
                                class="bi bi-plus-circle"></i>Tambah notulen</a>
                    </div>

                    <select id="filterPembuat" class="form-select form-select-sm border-success"
                        aria-label="Filter pembuat">
                        <option value="">Semua Pembuat</option>
                    </select>

                    <select id="rowsPerPage" class="form-select form-select-sm border-success"
                        aria-label="Jumlah tampil">
                        <option value="5">5 data</option>
                        <option value="10" selected>10 data</option>
                        <option value="20">20 data</option>
                        <option value="all">Semua</option>
                    </select>

                    <div class="search-table">
                        <input type="text" id="searchInput" class="form-control form-control-sm border-success"
                            placeholder="Cari notulen..." aria-label="Cari notulen" />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light border-0" style="background-color: #e8f6ee;">
                        <tr class="text-success">
                            <th scope="col">No</th>
                            <th scope="col">Judul Rapat</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Pembuat</th>
                            <th scope="col" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>

            <!-- Pagination & info -->
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <small class="text-muted" id="dataInfo"></small>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
                </nav>
            </div>
        </section>
    </main>

    <!-- Bootstrap JS bundle (inkl. Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Element refs
            const tableBody = document.getElementById("tableBody");
            const searchInput = document.getElementById("searchInput");
            const filterPembuat = document.getElementById("filterPembuat");
            const pagination = document.getElementById("pagination");
            const dataInfo = document.getElementById("dataInfo");
            const rowsPerPageSelect = document.getElementById("rowsPerPage");
            const logoutBtn = document.getElementById("logoutBtn");
            const logoutBtnMobile = document.getElementById("logoutBtnMobile");

            // Simulasi data
            const notulenData = [{
                    judul: "Rapat Akhir Tahun",
                    tanggal: "31/12/2025",
                    pembuat: "Rian"
                },
                {
                    judul: "Evaluasi Kinerja",
                    tanggal: "30/12/2025",
                    pembuat: "Didit"
                },
                {
                    judul: "Rapat Tim Proyek",
                    tanggal: "29/09/2025",
                    pembuat: "Sinta"
                },
                {
                    judul: "Rapat Divisi",
                    tanggal: "20/11/2025",
                    pembuat: "Admin"
                },
                {
                    judul: "Meeting Harian",
                    tanggal: "12/11/2025",
                    pembuat: "Rian"
                },
                {
                    judul: "Koordinasi Bulanan",
                    tanggal: "02/11/2025",
                    pembuat: "Admin"
                },
                {
                    judul: "Evaluasi Anggaran",
                    tanggal: "25/10/2025",
                    pembuat: "Didit"
                },
                {
                    judul: "Kick Off Proyek Baru",
                    tanggal: "15/10/2025",
                    pembuat: "Sinta"
                },
                {
                    judul: "Laporan Triwulan",
                    tanggal: "28/09/2025",
                    pembuat: "Rian"
                },
                {
                    judul: "Briefing Pagi",
                    tanggal: "07/09/2025",
                    pembuat: "Admin"
                },
                {
                    judul: "Rapat Koordinasi HR",
                    tanggal: "20/08/2025",
                    pembuat: "Didit"
                },
                {
                    judul: "Presentasi Hasil Survei",
                    tanggal: "13/08/2025",
                    pembuat: "Hana"
                },
                {
                    judul: "Rapat Pengembangan Produk Baru",
                    tanggal: "20/07/2025",
                    pembuat: "Hana"
                },
                {
                    judul: "Finalisasi Strategi",
                    tanggal: "29/06/2025",
                    pembuat: "Admin"
                },
                {
                    judul: "Laporan Keuangan dan Proyeksi",
                    tanggal: "12/05/2025",
                    pembuat: "Hana"
                },
                {
                    judul: "Tinjauan Keamanan Data",
                    tanggal: "09/04/2025",
                    pembuat: "Hana"
                }
            ];

            let currentPage = 1;
            let rowsPerPage = 10; // number or "all"

            // Render table rows (menerima startIndex untuk nomor berkelanjutan)
            function renderTable(data, startIndex = 0) {
                tableBody.innerHTML = "";
                data.forEach((item, index) => {
                    const nomorUrut = startIndex + index + 1;
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
            <td>${nomorUrut}</td>
            <td>${escapeHtml(item.judul)}</td>
            <td>${escapeHtml(item.tanggal)}</td>
            <td>${escapeHtml(item.pembuat)}</td>
            <td class="text-center">
              <a href="detail_rapat_admin.html?id=${nomorUrut - 1}" class="btn btn-sm text-primary" title="Lihat"><i class="bi bi-eye"></i></a>
              <a href="edit_rapat_admin.html?id=${nomorUrut - 1}" class="btn btn-sm text-success" title="Edit"><i class="bi bi-pencil"></i></a>
              <button class="btn btn-sm text-danger btn-delete" data-index="${nomorUrut - 1}" title="Hapus"><i class="bi bi-trash"></i></button>
            </td>
          `;
                    tableBody.appendChild(tr);
                });
            }

            // Escape teks sederhana untuk keamanan XSS minimal (untuk data static ini cukup)
            function escapeHtml(text) {
                return String(text)
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;");
            }

            // Isi opsi pembuat
            function populateFilterPembuat() {
                const pembuatUnik = [...new Set(notulenData.map(d => d.pembuat))];
                pembuatUnik.forEach(nama => {
                    const opt = document.createElement("option");
                    opt.value = nama;
                    opt.textContent = nama;
                    filterPembuat.appendChild(opt);
                });
            }

            // Filtering
            function getFilteredData() {
                const keyword = (searchInput.value || "").toLowerCase();
                const selectedPembuat = filterPembuat.value;

                return notulenData.filter(item => {
                    const cocokKeyword =
                        item.judul.toLowerCase().includes(keyword) ||
                        item.tanggal.toLowerCase().includes(keyword) ||
                        item.pembuat.toLowerCase().includes(keyword);

                    const cocokPembuat = selectedPembuat === "" || item.pembuat === selectedPembuat;
                    return cocokKeyword && cocokPembuat;
                });
            }

            // Pagination helpers
            function paginate(data) {
                if (rowsPerPage === "all") return data;
                const start = (currentPage - 1) * rowsPerPage;
                return data.slice(start, start + rowsPerPage);
            }

            function renderPagination(totalRows) {
                pagination.innerHTML = "";
                if (rowsPerPage === "all") return;
                const totalPages = Math.ceil(totalRows / rowsPerPage);
                for (let i = 1; i <= totalPages; i++) {
                    const li = document.createElement("li");
                    li.className = `page-item ${i === currentPage ? "active" : ""}`;
                    const a = document.createElement("a");
                    a.className = "page-link border-success text-success";
                    a.href = "#";
                    a.textContent = i;
                    a.addEventListener("click", (e) => {
                        e.preventDefault();
                        currentPage = i;
                        updateTable();
                        // scroll to top of table if on mobile
                        window.scrollTo({
                            top: tableBody.getBoundingClientRect().top + window.scrollY - 100,
                            behavior: "smooth"
                        });
                    });
                    li.appendChild(a);
                    pagination.appendChild(li);
                }
            }

            // Update table utama
            function updateTable() {
                const filteredData = getFilteredData();
                const totalRows = filteredData.length;
                const startIndex = (rowsPerPage === "all" || totalRows === 0) ? 0 : (currentPage - 1) *
                    rowsPerPage;
                const paginatedData = paginate(filteredData);
                renderTable(paginatedData, startIndex);
                renderPagination(totalRows);

                const start = totalRows === 0 ? 0 : startIndex + 1;
                const end = start + paginatedData.length - 1;
                dataInfo.textContent = `Menampilkan ${start}-${end} dari ${totalRows} data`;
            }

            // Event listeners - search/filter/rowsPerPage
            searchInput.addEventListener("input", () => {
                currentPage = 1;
                updateTable();
            });
            filterPembuat.addEventListener("change", () => {
                currentPage = 1;
                updateTable();
            });
            rowsPerPageSelect.addEventListener("change", () => {
                rowsPerPage = rowsPerPageSelect.value === "all" ? "all" : parseInt(rowsPerPageSelect
                    .value, 10);
                currentPage = 1;
                updateTable();
            });

            // Delete handler (event delegation)
            document.addEventListener("click", function (e) {
                const btn = e.target.closest(".btn-delete");
                if (!btn) return;

                const globalIndex = parseInt(btn.dataset.index, 10);
                if (Number.isNaN(globalIndex)) return;

                if (confirm("Yakin mau hapus data ini?")) {
                    // Hapus berdasarkan index dalam notulenData
                    notulenData.splice(globalIndex, 1);
                    // reset ke halaman 1 jika halaman sekarang melewati total halaman baru
                    currentPage = 1;
                    // re-populate filter options (bila pembuat berubah karena penghapusan)
                    filterPembuat.innerHTML = '<option value="">Semua Pembuat</option>';
                    populateFilterPembuat();
                    updateTable();
                }
            });

            // Logout buttons
            function setupLogoutButtons() {
                if (logoutBtn) {
                    logoutBtn.addEventListener("click", function () {
                        if (confirm("Apakah kamu yakin ingin logout?")) {
                            localStorage.removeItem("adminData");
                            window.location.href = "../login.html";
                        }
                    });
                }
                if (logoutBtnMobile) {
                    logoutBtnMobile.addEventListener("click", function () {
                        if (confirm("Apakah kamu yakin ingin logout?")) {
                            localStorage.removeItem("adminData");
                            window.location.href = "../login.html";
                        }
                    });
                }
            }

            // Initial setup
            populateFilterPembuat();
            updateTable();
            setupLogoutButtons();
        });
    </script>
</body>

</html>