<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Pastikan login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
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

// 3. Total Notulen berdasarkan Status (Draft dan Final)
$sqlDraft = "SELECT COUNT(*) as total FROM tambah_notulen WHERE status = 'draft' OR status IS NULL";
$resDraft = $conn->query($sqlDraft);
$totalDraft = $resDraft ? $resDraft->fetch_assoc()['total'] : 0;

$sqlFinal = "SELECT COUNT(*) as total FROM tambah_notulen WHERE status = 'final'";
$resFinal = $conn->query($sqlFinal);
$totalFinal = $resFinal ? $resFinal->fetch_assoc()['total'] : 0;

// Ambil 10 notulen terbaru untuk tabel (dengan status)
$sql = "SELECT id, judul, tanggal, tempat, peserta, status, created_at
         FROM tambah_notulen 
         ORDER BY created_at DESC LIMIT 10";
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/admin.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <style>
    #filterPembuat {
        padding-left: 2px !important;
    }
</style>
</head>

<body>
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
        <!-- Mobile Toggle -->
        <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile">
            <i class="bi bi-list"></i>
        </button>

        <div class="page-title">Dashboard Admin</div>

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
    <main class="main-content">
        <!-- Highlight Cards -->

        <div class="row g-3 mb-4 row-cols-1 row-cols-md-3">
            <!-- Card 1: Total Peserta -->
            <div class="col">
                <div class="highlight-card h-100 p-3 rounded-3 border-success shadow-sm d-flex flex-column justify-content-center align-items-center text-center bg-white" style="border: 1px solid #198754;">
                    <h6 class="text-secondary mb-2">Total Peserta</h6>
                    <h2 id="totalPesertaCard" class="fw-bold text-success mb-0"><?php echo $totalPeserta; ?></h2>
                    <small class="text-muted">Orang</small>
                </div>
            </div>

            <!-- Card 2: Total Notulen -->
            <div class="col">
                <div class="highlight-card h-100 p-3 rounded-3 border-success shadow-sm d-flex flex-column justify-content-center align-items-center text-center bg-white" style="border: 1px solid #198754;">
                    <h6 class="text-secondary mb-2">Total Notulen</h6>
                    <h2 id="totalNotulenCard" class="fw-bold text-success mb-0"><?php echo $totalNotulen; ?></h2>
                    <small class="text-muted">Dokumen</small>
                </div>
            </div>

            <!-- Card 3: Status Notulen -->
            <div class="col">
                <div class="highlight-card h-100 p-3 rounded-3 border-success shadow-sm bg-white" style="border: 1px solid #198754;">
                    <h6 class="text-secondary mb-3 text-center">Status Notulen</h6>
                    
                    <!-- Draft Count -->
                    <div class="d-flex align-items-center justify-content-between mb-2 p-2 rounded" style="background-color: #f8f9fa;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-pencil-square text-secondary" style="font-size: 1.2rem;"></i>
                            <span class="text-secondary">Draft</span>
                        </div>
                        <h4 id="totalDraftCard" class="fw-bold text-secondary mb-0"><?php echo $totalDraft; ?></h4>
                    </div>
                    
                    <!-- Final Count -->
                    <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background-color: #f8f9fa;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle text-success" style="font-size: 1.2rem;"></i>
                            <span class="text-success">Final</span>
                        </div>
                        <h4 id="totalFinalCard" class="fw-bold text-success mb-0"><?php echo $totalFinal; ?></h4>
                    </div>
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
                    const judul = escapeHtml(item.judul || '');
                    const tanggal = escapeHtml(item.tanggal || '');
                    const pembuat = escapeHtml(item.tempat || 'Admin');
                    const pesertaCount = item.peserta ? item.peserta.split(',').length : 0;
                    const status = escapeHtml(item.status || 'draft');
                    
                    // Format tanggal dengan jam
                    let tanggalDenganJam = tanggal;
                    if (item.created_at) {
                        const dateObj = new Date(item.created_at);
                        const jam = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                        tanggalDenganJam = `${tanggal} • ${jam}`;
                    }

                    // Map status ke badge color
                    const statusBadge = status === 'final' 
                        ? '<span class="badge d-flex align-items-center gap-1" style="background-color: #198754 !important; color: white;"><i class="bi bi-check-circle"></i> Final</span>'
                        : '<span class="badge bg-secondary d-flex align-items-center gap-1"><i class="bi bi-pencil-square"></i> Draft</span>';

                    const card = document.createElement('div');
                    card.className = 'col'; // Grid column
                    
                    card.innerHTML = `
                        <div class="highlight-card interact-card h-100 p-3 rounded-3 position-relative shadow-sm d-flex flex-column justify-content-between bg-white text-dark" style="background: #fff; cursor: pointer;" onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location.href='detail_rapat_admin.php?id=${encodeURIComponent(item.id)}'">
                            
                            <!-- Header: Actions & Status Badge -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                ${statusBadge}
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
                                        <span>${tanggalDenganJam}</span>
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
                const pembuatUnik = [...new Set(notulenData.map(d => d.tempat || 'Admin'))];
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
                    const judul = (item.judul || '').toLowerCase();
                    const tanggal = (item.tanggal || '').toLowerCase();
                    const pembuat = (item.tempat || 'Admin').toLowerCase();

                    const cocokKeyword = judul.includes(keyword) || tanggal.includes(keyword) || pembuat.includes(keyword);
                    const cocokPembuat = selectedPembuat === "" || (item.tempat || 'Admin') === selectedPembuat;
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
                
                // Helper untuk scroll
                const doScroll = () => {
                     const notulenList = document.getElementById("notulenList");
                     window.scrollTo({
                        top: notulenList.getBoundingClientRect().top + window.scrollY - 100,
                        behavior: "smooth"
                    });
                };

                // Tombol Sebelumnya
                const prevLi = document.createElement("li");
                prevLi.className = `page-item ${currentPage === 1 ? "disabled" : ""}`;
                prevLi.innerHTML = `<a class="page-link border-success text-success" href="#">Sebelumnya</a>`;
                prevLi.querySelector("a").addEventListener("click", (e) => {
                    e.preventDefault();
                    if (currentPage > 1) {
                        currentPage--;
                        updateTable();
                        doScroll();
                    }
                });
                pagination.appendChild(prevLi);

                // Angka Halaman
                for (let i = 1; i <= totalPages; i++) {
                    const li = document.createElement("li");
                    li.className = `page-item ${i === currentPage ? "active" : ""}`;
                    
                    // Note: Active state styling needs to override text-success usually, but bootstrap handles .page-item.active .page-link
                    li.innerHTML = `<a class="page-link border-success text-success" href="#">${i}</a>`;
                    
                    li.querySelector("a").addEventListener("click", (e) => {
                        e.preventDefault();
                        currentPage = i;
                        updateTable();
                        doScroll();
                    });
                    pagination.appendChild(li);
                }

                // Tombol Selanjutnya
                const nextLi = document.createElement("li");
                nextLi.className = `page-item ${currentPage === totalPages ? "disabled" : ""}`;
                nextLi.innerHTML = `<a class="page-link border-success text-success" href="#">Selanjutnya</a>`;
                nextLi.querySelector("a").addEventListener("click", (e) => {
                    e.preventDefault();
                    if (currentPage < totalPages) {
                        currentPage++;
                        updateTable();
                        doScroll();
                    }
                });
                pagination.appendChild(nextLi);
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

                const confirmed = await showConfirm("Yakin mau menghapus data ini? Tindakan ini tidak dapat dibatalkan.");
                if (!confirmed) return;

                try {
                    const res = await fetch('../proses/proses_hapus_notulen.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id })
                    });
                    const json = await res.json();
                    if (json.success) {
                        // Reload page to update counters and table
                        window.location.reload();
                    } else {
                        showToast(json.message || 'Gagal menghapus notulen.', 'error');
                    }
                } catch (err) {
                    console.error(err);
                    showToast('Terjadi kesalahan saat menghapus.', 'error');
                }
            });

            function setupLogoutButtons() {
                if (logoutBtn) {
                    logoutBtn.addEventListener("click", async function (e) {
                        e.preventDefault();
                        const confirmed = await showConfirm("Yakin mau keluar?");
                        if (confirmed) {
                            localStorage.removeItem("adminData");
                            window.location.href = "../proses/proses_logout.php";
                        }
                    });
                }
                if (logoutBtnMobile) {
                    logoutBtnMobile.addEventListener("click", async function (e) {
                        e.preventDefault();
                        const confirmed = await showConfirm("Yakin mau keluar?");
                        if (confirmed) {
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