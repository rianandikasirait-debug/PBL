<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek Login & Peran
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'peserta') {
    header("Location: ../login.php");
    exit;
}

// Inisialisasi sesi notulen yang dilihat jika belum ada
if (!isset($_SESSION['viewed_notulen'])) {
    $_SESSION['viewed_notulen'] = [];
}

// Ambil semua notulen dari database
// Ambil data untuk kartu sorotan - HANYA untuk peserta ini
$currentUserId = $_SESSION['user_id'];

// 1. Total Notulen untuk peserta ini
$sqlNotulen = "SELECT COUNT(*) as total FROM tambah_notulen WHERE FIND_IN_SET(?, peserta) > 0";
$stmtNotulen = $conn->prepare($sqlNotulen);
$stmtNotulen->bind_param("i", $currentUserId);
$stmtNotulen->execute();
$resNotulen = $stmtNotulen->get_result();
$totalNotulen = $resNotulen ? $resNotulen->fetch_assoc()['total'] : 0;
$stmtNotulen->close();

// 2. Total Notulen berdasarkan Status (Draft dan Final) untuk peserta ini
$sqlDraft = "SELECT COUNT(*) as total FROM tambah_notulen WHERE FIND_IN_SET(?, peserta) > 0 AND (status = 'draft' OR status IS NULL)";
$stmtDraft = $conn->prepare($sqlDraft);
$stmtDraft->bind_param("i", $currentUserId);
$stmtDraft->execute();
$resDraft = $stmtDraft->get_result();
$totalDraft = $resDraft ? $resDraft->fetch_assoc()['total'] : 0;
$stmtDraft->close();

$sqlFinal = "SELECT COUNT(*) as total FROM tambah_notulen WHERE FIND_IN_SET(?, peserta) > 0 AND status = 'final'";
$stmtFinal = $conn->prepare($sqlFinal);
$stmtFinal->bind_param("i", $currentUserId);
$stmtFinal->execute();
$resFinal = $stmtFinal->get_result();
$totalFinal = $resFinal ? $resFinal->fetch_assoc()['total'] : 0;
$stmtFinal->close();

// Ambil data pengguna (nama & foto)
$stmt = $conn->prepare("SELECT nama, foto FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$resUser = $stmt->get_result();
$userData = $resUser->fetch_assoc();
$userName = $userData['nama'] ?? 'Peserta';
$userPhoto = $userData['foto'] ?? null;
$stmt->close();

// Query untuk mengambil notulen yang peserta ini terdaftar di dalamnya
$currentUserId = $_SESSION['user_id'];
$sql = "SELECT id, judul, tanggal, tempat, peserta, tindak_lanjut as Lampiran, created_at,
                COALESCE(status, 'draft') as status
        FROM tambah_notulen 
        WHERE FIND_IN_SET(?, peserta) > 0
        ORDER BY tanggal DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

// Konversi ke format array dan tambahkan status is_viewed
$dataNotulen = [];
if ($result) {
    $viewedIdsSession = $_SESSION['viewed_notulen'] ?? [];
    while ($row = $result->fetch_assoc()) {
        $row['is_viewed'] = in_array((int)$row['id'], $viewedIdsSession);
        // Tambahkan alias untuk kompatibilitas dengan JavaScript
        $row['judul_rapat'] = $row['judul'];
        $row['tanggal_rapat'] = $row['tanggal'];
        $row['created_by'] = $row['tempat'];
        $dataNotulen[] = $row;
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Peserta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/admin.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>

    <nav class="navbar navbar-light bg-white sticky-top px-3">
        <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
            <i class="bi bi-list"></i>
        </button>
    </nav>

    <!-- Sidebar Mobile -->
    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas"
        aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-body p-0">
            <div class="sidebar-content d-flex flex-column justify-content-between h-100">
                <div>
                    <h4 class="fw-bold mb-4 ms-3">SmartNote</h4>
                    <ul class="nav flex-column">
                        <li>
                            <a class="nav-link active" href="dashboard_peserta.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                        </li>
                    </ul>
                </div>
                
                <div class="mt-auto px-3">
                    <ul class="nav flex-column mb-3">
                        <li>
                            <a class="nav-link" href="profile_peserta.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
                        </li>
                        <li>
                            <a id="logoutBtnMobile" class="nav-link text-danger" href="#"><i class="bi bi-box-arrow-right me-2 text-danger"></i>Keluar</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Desktop -->
    <div class="sidebar-content d-none d-lg-flex flex-column justify-content-between position-fixed">
        <div>
            <h4 class="fw-bold mb-4 ms-3">SmartNote</h4>
            <ul class="nav flex-column">
                <li>
                    <a class="nav-link active" href="dashboard_peserta.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                </li>
            </ul>
        </div>

        <div>
            <ul class="nav flex-column mb-3">
                <li>
                    <a class="nav-link" href="profile_peserta.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
                </li>
                <li>
                    <a id="logoutBtn" class="nav-link text-danger" href="#"><i class="bi bi-box-arrow-right me-2 text-danger"></i>Keluar</a>
                </li>
            </ul>
        </div>
    </div>


    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4><b>Dashboard Peserta</b></h4>
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
        <div class="row g-3 mb-4 row-cols-1 row-cols-md-2">
            <!-- Card 1: Total Notulen -->
            <div class="col">
                <div class="highlight-card h-100 p-3 rounded-3 border-success shadow-sm d-flex flex-column justify-content-center align-items-center text-center bg-white" style="border: 1px solid #198754;">
                    <h6 class="text-secondary mb-2">Total Notulen</h6>
                    <h2 id="totalNotulenCard" class="fw-bold text-success mb-0"><?php echo $totalNotulen; ?></h2>
                    <small class="text-muted">Dokumen</small>
                </div>
            </div>

            <!-- Card 2: Status Notulen -->
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

            <div class="table-wrapper">
            <div class="table-header d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <h5 class="fw-semibold mb-2 mb-sm-0">Daftar Notulen</h5>

                <div class="d-flex gap-2 flex-wrap controls align-items-center">
                    <select id="filterPembuat" class="form-select form-select-sm border-success" style="width: 180px;">
                        <option value="">Semua Pembuat</option>
                    </select>

                    <select id="rowsPerPage" class="form-select form-select-sm border-success" style="width: 140px;">
                        <option value="5">5 data</option>
                        <option value="10" selected>10 data</option>
                        <option value="20">20 data</option>
                        <option value="all">Semua</option>
                    </select>

                    <div class="search-table">
                        <input type="text" id="searchInput" class="form-control form-control-sm border-success"
                            placeholder="Cari notulen..." />
                    </div>
                </div>
            </div>

                <!-- List Container -->
                <div id="notulenList" class="row g-3 row-cols-1 row-cols-md-3 row-cols-xl-5"></div>

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <small class="text-muted" id="dataInfo"></small>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
                </nav>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const tableBody = document.getElementById("tableBody");
            const searchInput = document.getElementById("searchInput");
            const filterPembuat = document.getElementById("filterPembuat");
            const pagination = document.getElementById("pagination");
            const dataInfo = document.getElementById("dataInfo");
            const rowsPerPageSelect = document.getElementById("rowsPerPage");

            // Data dari PHP
            const notulenData = <?= json_encode($dataNotulen, JSON_UNESCAPED_UNICODE) ?>;

            let currentPage = 1;
            let rowsPerPage = 10;

            function escapeHtml(text) {
                return String(text || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function renderTable(data, startIndex = 0) {
                const notulenList = document.getElementById("notulenList");
                notulenList.innerHTML = "";

                if (data.length === 0) {
                    notulenList.innerHTML = `<div class="text-center text-muted py-4">Tidak ada data notulen.</div>`;
                    return;
                }

                data.forEach((item, index) => {
                    const judul = escapeHtml(item.judul_rapat || '');
                    const tanggal = escapeHtml(item.tanggal_rapat || '');
                    const pembuat = escapeHtml(item.created_by || 'Admin');
                    const pesertaCount = item.peserta ? item.peserta.split(',').length : 0;
                    const status = escapeHtml(item.status || 'draft');
                    
                    // Format tanggal dengan jam
                    let tanggalDenganJam = tanggal;
                    if (item.created_at) {
                        const dateObj = new Date(item.created_at);
                        const jam = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                        tanggalDenganJam = `${tanggal} • ${jam}`;
                    }
                    
                    // Lencana status - FINAL WARNA HIJAU!
                    const statusBadge = status === 'final' 
                        ? '<span class="badge d-flex align-items-center gap-1" style="background-color: #198754 !important; color: white;"><i class="bi bi-check-circle"></i> Final</span>'
                        : '<span class="badge bg-secondary d-flex align-items-center gap-1"><i class="bi bi-pencil-square"></i> Draft</span>';

                    const card = document.createElement('div');
                    card.className = 'col'; // Kolom Grid

                    // Sesuaikan Gaya Admin (Tata Letak Grid)
                    card.innerHTML = `
                        <div class="mobile-card h-100 p-3 rounded-3 position-relative shadow-sm" style="cursor: pointer;" onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location.href='detail_rapat_peserta.php?id=${encodeURIComponent(item.id)}'">
                            <!-- Header: Lencana Status & Aksi -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                ${statusBadge}
                                <div class="d-flex gap-2">
                                    ${item.Lampiran ? `<a href="../file/${encodeURIComponent(item.Lampiran)}" class="btn btn-sm text-secondary p-0" title="Download" download><i class="bi bi-download fs-5"></i></a>` : ''}
                                </div>
                            </div>

                            <!-- Body: Judul & Metadata -->
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

            // Isi filter pembuat
            const pembuatUnik = [...new Set(notulenData.map((d) => d.created_by || 'Admin'))];
            pembuatUnik.forEach((nama) => {
                const opt = document.createElement("option");
                opt.value = nama;
                opt.textContent = nama;
                filterPembuat.appendChild(opt);
            });

            function getFilteredData() {
                const keyword = searchInput.value.toLowerCase();
                const selectedPembuat = filterPembuat.value;

                return notulenData.filter((item) => {
                    const judul = (item.judul_rapat || '').toLowerCase();
                    const tanggal = (item.tanggal_rapat || '').toLowerCase();
                    const pembuat = (item.created_by || 'Admin').toLowerCase();

                    const cocokKeyword =
                        judul.includes(keyword) ||
                        tanggal.includes(keyword) ||
                        pembuat.includes(keyword);

                    const cocokPembuat =
                        selectedPembuat === "" || (item.created_by || 'Admin') === selectedPembuat;

                    return cocokKeyword && cocokPembuat;
                });
            }

            function paginate(data) {
                if (rowsPerPage === "all") return data;
                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                return data.slice(start, end);
            }

            function renderPagination(totalRows) {
                pagination.innerHTML = "";
                if (rowsPerPage === "all") return;

                const totalPages = Math.ceil(totalRows / rowsPerPage);

                for (let i = 1; i <= totalPages; i++) {
                    const li = document.createElement("li");
                    li.className = `page-item ${i === currentPage ? "active" : ""}`;
                    li.innerHTML = `<a class="page-link border-success text-success" href="#">${i}</a>`;
                    li.addEventListener("click", (e) => {
                        e.preventDefault();
                        currentPage = i;
                        updateTable();
                        const notulenList = document.getElementById("notulenList");
                        window.scrollTo({
                            top: notulenList.getBoundingClientRect().top + window.scrollY - 100,
                            behavior: "smooth"
                        });
                    });
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
                rowsPerPage = rowsPerPageSelect.value === "all" ? "all" : parseInt(rowsPerPageSelect.value);
                currentPage = 1;
                updateTable();
            });

            // Fungsi Logout
            function confirmLogout() {
                if (confirm("Apakah kamu yakin ingin logout?")) {
                    window.location.href = "../proses/proses_logout.php";
                }
            }

            document.getElementById("logoutBtn").addEventListener("click", confirmLogout);

            const logoutBtnMobile = document.getElementById("logoutBtnMobile");
            if (logoutBtnMobile) {
                logoutBtnMobile.addEventListener("click", confirmLogout);
            }

            // Tangani klik kartu sorotan untuk menandai sebagai dilihat
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

            updateTable();

            // Render ulang tabel saat viewport berubah (debounced)
            window.addEventListener('resize', function () {
                if (window._dashResizeTimer) clearTimeout(window._dashResizeTimer);
                window._dashResizeTimer = setTimeout(() => {
                    updateTable();
                }, 120);
            });
        });
    </script>
</body>
</html>