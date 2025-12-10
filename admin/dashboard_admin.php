<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Pastikan login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil data user login
$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nama, foto FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userRes = $stmt->get_result();
$userData = $userRes->fetch_assoc();
$stmt->close();
$userName = $userData['nama'] ?? 'Admin';
$userPhoto = $userData['foto'] ?? null;

// Initialize viewed notulen session if not exists
if (!isset($_SESSION['viewed_notulen'])) {
    $_SESSION['viewed_notulen'] = [];
}

// Ambil data untuk highlight cards
// 1. Total Peserta
$sqlPeserta = "SELECT COUNT(*) as total FROM users WHERE role = 'peserta'";
$resPeserta = $conn->query($sqlPeserta);
$totalPeserta = $resPeserta ? $resPeserta->fetch_assoc()['total'] : 0;

// 2. Total Notulen
$sqlNotulen = "SELECT COUNT(*) as total FROM tambah_notulen";
$resNotulen = $conn->query($sqlNotulen);
$totalNotulen = $resNotulen ? $resNotulen->fetch_assoc()['total'] : 0;

// 3. Total Notulen Belum Dilihat (Unread)
$viewedIds = $_SESSION['viewed_notulen'] ?? [];
$viewedIds = array_map('intval', $viewedIds); // Sanitize to ints
$viewedIds = array_filter($viewedIds); // Remove 0s if any

if (empty($viewedIds)) {
    // If no viewed notulens, unread = total
    $totalUnread = $totalNotulen;
} else {
    $idsStr = implode(',', $viewedIds);
    $sqlUnread = "SELECT COUNT(*) as total FROM tambah_notulen WHERE id NOT IN ($idsStr)";
    $resUnread = $conn->query($sqlUnread);
    $totalUnread = $resUnread ? $resUnread->fetch_assoc()['total'] : $totalNotulen;
}

// Ambil 10 notulen terbaru untuk tabel (tetap sama)
$sql = "SELECT id, judul_rapat, tanggal_rapat, Lampiran, peserta, created_by, created_at FROM tambah_notulen ORDER BY created_at DESC LIMIT 10";
$result = $conn->query($sql);
// Konversi ke format array dan tambahkan status is_viewed
$dataNotulen = [];
$viewedIdsSession = $_SESSION['viewed_notulen'] ?? [];
while ($row = $result->fetch_assoc()) {
    $row['is_viewed'] = in_array((int)$row['id'], $viewedIdsSession);
    $dataNotulen[] = $row;
}
?>
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
    <style>
        .mobile-card {
            border: 1px solid #198754; /* Bootstrap success color */
            transition: all 0.2s ease-in-out;
            background-color: white; /* Ensure background is white */
        }
        .mobile-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
            /* Green border on hover to match */
            border-color: #198754!important; 
        }
        @media (min-width: 768px) {
            .border-start-md {
                border-left: 1px solid #dee2e6 !important;
            }
        }
        /* Make offcanvas (mobile sidebar) wider and more usable on small screens */
        @media (max-width: 991.98px) {
            .offcanvas.offcanvas-start {
                width: 320px !important;
                max-width: 90% !important;
            }
            /* Ensure sidebar content inside offcanvas uses full width and padding */
            .offcanvas.offcanvas-start .sidebar-content {
                min-width: 0 !important;
                padding: 1.25rem !important;
            }
        }

        /* Keep desktop layout unchanged: apply min-width only on large screens */
        @media (min-width: 992px) {
            .sidebar-content { min-width: 250px; }
        }
    </style>
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
                    <h4 class="fw-bold mb-4 ms-3">MENU</h4>
                    <ul class="nav flex-column">
                        <li>
                            <a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                        </li>
                        <li>
                            <a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a>
                        </li>
                    </ul>
                </div>

                <div class="mt-auto px-3">
                    <ul class="nav flex-column mb-3">
                        <li>
                            <a class="nav-link" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
                        </li>
                        <li>
                            <a id="logoutBtnMobile" class="nav-link text-danger" href="#"><i class="bi bi-box-arrow-right me-2 text-danger"></i>Keluar</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar (desktop) -->
    <aside class="sidebar-content d-none d-lg-flex flex-column justify-content-between position-fixed">
        <div>
            <h4 class="fw-bold mb-4 ms-3">MENU</h4>
            <ul class="nav flex-column">
                <li>
                    <a class="nav-link active" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                </li>
                <li>
                    <a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a>
                </li>
            </ul>
        </div>

        <div>
            <ul class="nav flex-column mb-3">
                <li>
                    <a class="nav-link" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
                </li>
                <li>
                    <a id="logoutBtn" class="nav-link text-danger" href="#"><i class="bi bi-box-arrow-right me-2 text-danger"></i>Keluar</a>
                </li>
            </ul>
        </div>
    </aside>

    <!-- Main content -->
    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4><b>Dashboard Notulis</b></h4>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end">
                    <span class="d-block fw-medium text-dark">Halo, <?= htmlspecialchars($userName) ?> 👋</span>
                </div>
                <img src="<?= $userPhoto ? '../file/' . htmlspecialchars($userPhoto) : '../file/user.jpg' ?>" 
                     alt="Profile" 
                     class="rounded-circle shadow-sm"
                     style="width: 45px; height: 45px; object-fit: cover; border: 2px solid #fff;"
                     onerror="this.onerror=null;this.src='../file/user.jpg';">
            </div>
        </div>

        <!-- Highlight Cards -->
        <div class="row g-3 mb-4 row-cols-1 row-cols-md-3">
            <!-- Card 1: Total Peserta -->
            <div class="col">
                <div class="highlight-card h-100 p-3 rounded-3 border-success shadow-sm d-flex flex-column justify-content-center align-items-center text-center bg-white" style="border: 1px solid #198754;">
                    <h6 class="text-secondary mb-2">Total Peserta</h6>
                    <h2 class="fw-bold text-success mb-0"><?php echo $totalPeserta; ?></h2>
                    <small class="text-muted">Orang</small>
                </div>
            </div>

            <!-- Card 2: Total Notulen -->
            <div class="col">
                <div class="highlight-card h-100 p-3 rounded-3 border-success shadow-sm d-flex flex-column justify-content-center align-items-center text-center bg-white" style="border: 1px solid #198754;">
                    <h6 class="text-secondary mb-2">Total Notulen</h6>
                    <h2 class="fw-bold text-success mb-0"><?php echo $totalNotulen; ?></h2>
                    <small class="text-muted">Dokumen</small>
                </div>
            </div>

            <!-- Card 3: Belum Dilihat -->
            <div class="col">
                <div class="highlight-card h-100 p-3 rounded-3 border-success shadow-sm d-flex flex-column justify-content-center align-items-center text-center bg-white" style="border: 1px solid #198754;">
                    <h6 class="text-secondary mb-2">Belum Dilihat</h6>
                    <h2 class="fw-bold text-danger mb-0"><?php echo $totalUnread; ?></h2>
                    <small class="text-muted">Notulen</small>
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
                        <a href="notulen_admin.php" class="btn-tambah" role="button"><i
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
            <!-- List Container -->
            <!-- List Container -->
            <div id="notulenList" class="row g-3 row-cols-1 row-cols-md-3 row-cols-xl-5"></div>

            <!-- Pagination & info -->
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <small class="text-muted" id="dataInfo"></small>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
                </nav>
            </div>
        </section>
    </main>

    <!-- Bootstrap JS bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const tableBody = document.getElementById("tableBody");
            const searchInput = document.getElementById("searchInput");
            const filterPembuat = document.getElementById("filterPembuat");
            const pagination = document.getElementById("pagination");
            const dataInfo = document.getElementById("dataInfo");
            const rowsPerPageSelect = document.getElementById("rowsPerPage");
            const logoutBtn = document.getElementById("logoutBtn");
            const logoutBtnMobile = document.getElementById("logoutBtnMobile");

            const notulenData = <?= json_encode($dataNotulen, JSON_UNESCAPED_UNICODE) ?>;

            let currentPage = 1;
            let rowsPerPage = 10;

            function escapeHtml(text) {
                return String(text)
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;");
            }

            function renderTable(data, startIndex = 0) {
                const notulenList = document.getElementById("notulenList");
                notulenList.innerHTML = "";

                if (data.length === 0) {
                    notulenList.innerHTML = `<div class="text-center text-muted py-4">Belum ada data notulen.</div>`;
                    return;
                }

                data.forEach((item, index) => {
                    const nomorUrut = startIndex + index + 1;
                    const judul = escapeHtml(item.judul_rapat || '');
                    const tanggal = escapeHtml(item.tanggal_rapat || '');
                    const pembuat = escapeHtml(item.created_by || 'Admin');
                    const pesertaCount = item.peserta ? item.peserta.split(',').length : 0;

                    const card = document.createElement('div');
                    card.className = 'col'; // Grid column
                    
                    // Badge Baru (New) if not viewed
                     const badge = !item.is_viewed ? 
                        `<span class="position-absolute top-0 start-0 m-2 badge rounded-pill bg-danger border border-light shadow-sm" style="z-index: 10;">
                            Baru
                            <span class="visually-hidden">unread messages</span>
                        </span>` : '';

                    card.innerHTML = `
                        <div class="highlight-card h-100 p-3 rounded-3 position-relative shadow-sm d-flex flex-column justify-content-between bg-white text-dark" style="background: #fff; cursor: pointer;" onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location.href='detail_rapat_admin.php?id=${encodeURIComponent(item.id)}'">
                            ${badge}
                            
                            <!-- Header: Actions (Badge removed) -->
                            <div class="d-flex justify-content-end align-items-center mb-2">
                                    <div class="d-flex gap-2">
                                     <a href="edit_rapat_admin.php?id=${encodeURIComponent(item.id)}" class="btn btn-sm text-dark p-0" title="Edit"><i class="bi bi-pencil-square fs-5"></i></a>
                                    <button class="btn btn-sm text-secondary p-0 btn-delete" data-id="${encodeURIComponent(item.id)}" title="Hapus"><i class="bi bi-trash fs-5"></i></button>
                                </div>
                            </div>

                            <!-- Body: Title & Metadata -->
                            <div>
                                <h5 class="fw-bold text-dark mb-3 text-truncate" title="${judul}">${judul}</h5>
                                
                                <div class="d-flex flex-column gap-2 text-secondary small">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-calendar-event"></i>
                                        <span>${tanggal} • 09:00</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person"></i>
                                        <span class="text-truncate" style="max-width: 200px;">PIC: ${pembuat}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-people"></i>
                                        <span>${pesertaCount} Peserta</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    notulenList.appendChild(card);
                });
            }

            function populateFilterPembuat() {
                const pembuatUnik = [...new Set(notulenData.map(d => d.created_by || 'Admin'))];
                pembuatUnik.forEach(nama => {
                    const opt = document.createElement("option");
                    opt.value = nama;
                    opt.textContent = nama;
                    filterPembuat.appendChild(opt);
                });
            }

            function getFilteredData() {
                const keyword = (searchInput.value || "").toLowerCase();
                const selectedPembuat = filterPembuat.value;

                return notulenData.filter(item => {
                    const judul = (item.judul_rapat || '').toLowerCase();
                    const tanggal = (item.tanggal_rapat || '').toLowerCase();
                    const pembuat = (item.created_by || 'Admin').toLowerCase();

                    const cocokKeyword = judul.includes(keyword) || tanggal.includes(keyword) || pembuat.includes(keyword);
                    const cocokPembuat = selectedPembuat === "" || (item.created_by || 'Admin') === selectedPembuat;
                    return cocokKeyword && cocokPembuat;
                });
            }

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
                        const notulenList = document.getElementById("notulenList");
                        window.scrollTo({
                            top: notulenList.getBoundingClientRect().top + window.scrollY - 100,
                            behavior: "smooth"
                        });
                    });
                    li.appendChild(a);
                    pagination.appendChild(li);
                }
            }

            function updateTable() {
                const filteredData = getFilteredData();
                const totalRows = filteredData.length;
                const startIndex = (rowsPerPage === "all" || totalRows === 0) ? 0 : (currentPage - 1) * rowsPerPage;
                const paginatedData = paginate(filteredData);

                renderTable(paginatedData, startIndex);
                renderPagination(totalRows);

                const start = totalRows === 0 ? 0 : startIndex + 1;
                const end = start + paginatedData.length - 1;
                dataInfo.textContent = `Menampilkan ${start}-${end} dari ${totalRows} data`;
            }

            searchInput.addEventListener("input", () => {
                currentPage = 1;
                updateTable();
            });
            filterPembuat.addEventListener("change", () => {
                currentPage = 1;
                updateTable();
            });
            rowsPerPageSelect.addEventListener("change", () => {
                rowsPerPage = rowsPerPageSelect.value === "all" ? "all" : parseInt(rowsPerPageSelect.value, 10);
                currentPage = 1;
                updateTable();
            });

            document.addEventListener("click", async function (e) {
                const btn = e.target.closest(".btn-delete");
                if (!btn) return;
                const id = btn.dataset.id;
                if (!id) return;

                if (!confirm("Yakin mau hapus data ini?")) return;

                try {
                    const res = await fetch('../proses/proses_hapus_notulen.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id })
                    });
                    const json = await res.json();
                    if (json.success) {
                        const idx = notulenData.findIndex(n => String(n.id) === String(id));
                        if (idx !== -1) notulenData.splice(idx, 1);
                        filterPembuat.innerHTML = '<option value="">Semua Pembuat</option>';
                        populateFilterPembuat();
                        updateTable();
                    } else {
                        alert(json.message || 'Gagal menghapus notulen.');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Terjadi kesalahan saat menghapus.');
                }
            });

            function setupLogoutButtons() {
                if (logoutBtn) {
                    logoutBtn.addEventListener("click", function () {
                        if (confirm("Apakah kamu yakin ingin logout?")) {
                            localStorage.removeItem("adminData");
                            window.location.href = "../proses/proses_logout.php";
                        }
                    });
                }
                if (logoutBtnMobile) {
                    logoutBtnMobile.addEventListener("click", function () {
                        if (confirm("Apakah kamu yakin ingin logout?")) {
                            localStorage.removeItem("adminData");
                            window.location.href = "../proses/proses_logout.php";
                        }
                    });
                }
            }

            populateFilterPembuat();
            updateTable();

            // Re-render table when viewport changes (debounced)
            window.addEventListener('resize', function () {
                if (window._dashResizeTimer) clearTimeout(window._dashResizeTimer);
                window._dashResizeTimer = setTimeout(() => {
                    updateTable();
                }, 120);
            });

            setupLogoutButtons();

            // Handle highlight card clicks to mark as viewed
            document.querySelectorAll('.highlight-card').forEach(card => {
                const link = card.closest('a');
                if (link) {
                    link.addEventListener('click', function(e) {
                        const href = this.getAttribute('href');
                        const urlParams = new URLSearchParams(new URL(href, window.location.origin).search);
                        const id = urlParams.get('id');
                        
                        if (id) {
                            fetch('../proses/proses_mark_viewed.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id: id })
                            }).catch(err => console.error('Error marking as viewed:', err));
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>