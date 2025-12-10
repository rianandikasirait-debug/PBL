<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek login
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

// 1. AMBIL DATA PENGGUNA (HANYA PESERTA)
$all_users = [];
$sql = "SELECT id, foto, nama, nik, email, role FROM users WHERE LOWER(role) = 'peserta' ORDER BY nama ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    // Ambil semua data sekaligus
    $all_users = $result->fetch_all(MYSQLI_ASSOC);
}
$conn->close();

// Kita juga perlu tahu siapa ID admin yang sedang login
// (Meskipun sekarang tidak terlalu relevan karena kita hanya menampilkan peserta,
// ini tetap praktik yang baik untuk dijaga)
$current_admin_id = $_SESSION['user_id'] ?? 0;

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kelola Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:wght@400" />
    <link rel="stylesheet" href="../css/admin.min.css">

    <style>
        .mobile-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .mobile-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 1rem;
            position: relative;
        }
        .user-photo {
            object-fit: cover;
            border-radius: 50%;
            border: 1px solid #eee;
        }
        .mobile-card-title {
            font-weight: 600;
            font-size: 1rem;
            color: #333;
            margin-bottom: 0.2rem;
        }
    </style>

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
                    <h4 class="fw-bold mb-4 ms-3">MENU</h4>
                    <ul class="nav flex-column">
                        <li>
                            <a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                        </li>
                        <li>
                            <a class="nav-link active" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a>
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

    <!-- Sidebar Desktop -->
    <div class="sidebar-content d-none d-lg-flex flex-column justify-content-between position-fixed">
        <div>
            <h4 class="fw-bold mb-4 ms-3">MENU</h4>
            <ul class="nav flex-column">
                <li>
                    <a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                </li>
                <li>
                    <a class="nav-link active" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a>
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
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4><b>Kelola Pengguna Sistem</b></h4>
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

        <div class="table-wrapper">
            <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                   <span class="material-symbols-rounded" style="font-size:28px;">
                        person_search
                    </span>
                    <input type="text" id="searchInput" class="form-control search-box flex-grow-1" placeholder="Cari pengguna...">
                </div>
                <a href="tambah_peserta_admin.php" class="btn btn-success d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle"></i> Tambah Pengguna
                </a>
            </div>

            <div id="alertBox"></div>
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>FOTO</th>
                            <th>NAMA</th>
                            <th>NIK</th>
                            <th>EMAIL</th>
                            <th>ROLE</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <small class="text-muted" id="dataInfo"></small>
                <nav>
                    <ul class="pagination pagination-sm mb-0 green" id="pagination"></ul>
                </nav>
            </div>
        </div>
    </div>
    </div>

    <!-- DEBUG: tampilkan data JSON di source page (HTML comment) supaya bisa dicek lewat View Source -->
    <?php
    echo '<!-- DEBUG: $all_users = ' . htmlspecialchars(json_encode($all_users, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') . ' -->';
    ?>

    <script>
        // DATA SEKARANG HANYA BERISI PESERTA KARENA SUDAH DIFILTER OLEH PHP
        let users = <?php echo json_encode($all_users, JSON_UNESCAPED_UNICODE); ?>;

        // ID Admin (untuk perbandingan di fungsi hapus)
        const CURRENT_ADMIN_ID = <?php echo (int) $current_admin_id; ?>;

        const tbody = document.getElementById("userTableBody");
        const pagination = document.getElementById("pagination");
        const dataInfo = document.getElementById("dataInfo");
        const searchInput = document.getElementById("searchInput");
        const alertBox = document.getElementById("alertBox");

        let currentPage = 1;
        const itemsPerPage = 5;
        let filteredUsers = Array.isArray(users) ? [...users] : [];

        // Fungsi render tabel
        function renderTable(data) {
            tbody.innerHTML = "";
            
            // Handle Mobile List
            let mobileList = document.getElementById('mobileList');
            if (!mobileList) {
                const tableResp = document.querySelector('.table-responsive');
                if (tableResp) {
                     mobileList = document.createElement('div');
                     mobileList.id = 'mobileList';
                     mobileList.className = 'mobile-list d-block d-md-none';
                     tableResp.parentNode.insertBefore(mobileList, tableResp);
                }
            } else {
                mobileList.innerHTML = "";
            }
            
            if (!Array.isArray(data) || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data pengguna ditemukan.</td></tr>`;
                 if (mobileList) mobileList.innerHTML = `<div class="text-center text-muted py-4">Tidak ada data pengguna ditemukan.</div>`;
                dataInfo.textContent = "";
                pagination.innerHTML = "";
                return;
            }

            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const paginatedData = data.slice(start, end);

            const isMobile = window.innerWidth < 768;

            paginatedData.forEach((u, index) => {
                const photoPath = u.foto ? `../file/${encodeURIComponent(u.foto)}` : '../file/user.jpg';
                const nama = escapeHtml(u.nama || '');
                const nik = escapeHtml(u.nik || '-');
                const email = escapeHtml(u.email || '-');
                const role = escapeHtml(u.role || '');

                if (isMobile) {
                    if (!mobileList) return;
                    const card = document.createElement('div');
                    card.className = 'mobile-card';
                    card.innerHTML = `
                        <div class="mobile-card-inner">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge-role">${role}</span>
                                <button class="btn btn-sm text-danger p-0" onclick="deleteUser(${Number(u.id)}, this)" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3">
                                <img src="${photoPath}" alt="${nama}" class="user-photo me-3" style="width:50px;height:50px;min-width:50px;" onerror="this.onerror=null;this.src='../file/user.jpg';">
                                <div class="d-flex flex-column">
                                    <div class="mobile-card-title">${nama}</div>
                                    <small class="text-muted text-break">${email}</small>
                                </div>
                            </div>
                            
                            <div class="mobile-card-info border-top pt-2 mt-2">
                                <div class="mobile-card-info-row d-flex align-items-center text-muted small">
                                    <i class="bi bi-card-heading me-2"></i>
                                    <span>NIK: ${nik}</span>
                                </div>
                            </div>
                        </div>
                    `;
                    mobileList.appendChild(card);
                } else {
                     const row = `
                    <tr>
                        <td>${start + index + 1}</td>
                        <td><img src="${photoPath}" alt="${nama}" class="user-photo" style="width:48px;height:48px;object-fit:cover;border-radius:4px;"></td>
                        <td>${nama}</td>
                        <td>${nik}</td>
                        <td>${email}</td>
                        <td><span class="badge-role">${role}</span></td>
                        <td>
                            <button class="btn btn-sm text-danger" onclick="deleteUser(${Number(u.id)}, this)" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    `;
                    tbody.insertAdjacentHTML("beforeend", row);
                }
            });

            updatePagination(data);
        }

        // Fungsi update pagination
        function updatePagination(data) {
            pagination.innerHTML = "";
            const totalPages = Math.max(1, Math.ceil(data.length / itemsPerPage));

            const start = data.length === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
            const end = Math.min(start + itemsPerPage - 1, data.length);
            dataInfo.textContent = data.length === 0 ? '' : `Menampilkan ${start} -${end} dari ${data.length} pengguna`;

            pagination.insertAdjacentHTML(
                "beforeend",
                `<li class="page-item ${currentPage === 1 ? "disabled" : ""}" >
                    <a class="page-link" href="#" onclick="changePage(${currentPage - 1});return false;">Previous</a>
            </li> `
            );

            for (let i = 1; i <= totalPages; i++) {
                const active = i === currentPage ? "active" : "";
                pagination.insertAdjacentHTML(
                    "beforeend",
                    `<li class="page-item ${active}" >
                    <a class="page-link" href="#" onclick="changePage(${i});return false;">${i}</a>
                </li> `
            );
            }

            pagination.insertAdjacentHTML(
                "beforeend",
                `<li class="page-item ${currentPage === totalPages ? "disabled" : ""}" >
                    <a class="page-link" href="#" onclick="changePage(${currentPage + 1});return false;">Next</a>
            </li>`
            );
        }

        function changePage(page) {
            const totalPages = Math.ceil(filteredUsers.length / itemsPerPage);
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            renderTable(filteredUsers);
        }

        // AJAX delete user (versi non-reload)
        async function deleteUser(id, btn) {
            if (id === CURRENT_ADMIN_ID) {
                showAlert('Anda tidak dapat menghapus akun Anda sendiri.', 'danger');
                return;
            }

            if (!confirm("Yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.")) {
                return;
            }

            let originalHTML = null;
            if (btn) {
                originalHTML = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
            }

            try {
                const response = await fetch('../proses/proses_hapus_peserta.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    users = users.filter(u => u.id != id);
                    filteredUsers = filteredUsers.filter(u => u.id != id);

                    const totalPages = Math.max(1, Math.ceil(filteredUsers.length / itemsPerPage));
                    if (currentPage > totalPages) currentPage = totalPages;

                    renderTable(filteredUsers);
                    showAlert(result.message || 'Pengguna berhasil dihapus.', 'success');
                } else {
                    showAlert(result.message || 'Gagal menghapus pengguna.', 'danger');
                }
            } catch (err) {
                console.error('Error:', err);
                showAlert('Terjadi kesalahan saat menghubungi server.', 'danger');
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                }
            }
        }

        // Fungsi showAlert
        function showAlert(message, type = 'success') {
            alertBox.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            setTimeout(() => {
                const alertElement = alertBox.querySelector('.alert');
                if (alertElement) {
                    new bootstrap.Alert(alertElement).close();
                }
            }, 5000);
        }

        // Fitur search
        // small helper to escape HTML when injecting from JSON
        function escapeHtml(str) {
            if (!str && str !== 0) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        if (searchInput) {
            searchInput.addEventListener("input", function () {
                const keyword = this.value.toLowerCase();
                filteredUsers = users.filter(
                    (u) =>
                        (u.nama && String(u.nama).toLowerCase().includes(keyword)) ||
                        (u.email && String(u.email).toLowerCase().includes(keyword)) ||
                        (u.nik && String(u.nik).toLowerCase().includes(keyword)) ||
                        (u.role && String(u.role).toLowerCase().includes(keyword))
                );
                currentPage = 1;
                renderTable(filteredUsers);
            });
        }

        // Render awal
        renderTable(filteredUsers);
        
        // Resize listener
        window.addEventListener('resize', function () {
             renderTable(filteredUsers);
        });

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
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>