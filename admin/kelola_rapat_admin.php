<?php
include '../config_admin/db_kelola_rapat_admin.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kelola Pengguna</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:wght@400" />
    <link rel="stylesheet" href="../css/admin.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">

</head>

<body>
    <!-- CSS Header & Sidebar -->
    <style>
        /* ===== SIDEBAR DESKTOP ===== */
        .sidebar-admin {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e6e6e6;
            padding: 20px 15px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            z-index: 999;
        }

        .sidebar-admin .title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 25px;
            padding-left: 10px;
        }

        .sidebar-admin a {
            display: block;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 8px;
            color: #222;
            font-weight: 500;
            text-decoration: none !important;
            display: flex;
            align-items: center;
        }

        .sidebar-admin a:hover,
        .sidebar-admin a.active {
            background: #00C853;
            color: #fff !important;
        }

        /* ===== HEADER (TOP BAR) ===== */
        .header-admin {
            position: fixed;
            top: 0;
            left: 250px;
            right: 0;
            height: 70px;
            background: white;
            border-bottom: 1px solid #e6e6e6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            z-index: 998;
        }

        .header-admin .page-title {
            font-size: 20px;
            font-weight: 700;
        }

        .header-admin .right-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Soft Delete Button Style */
        .btn-soft-danger {
            background-color: #fee2e2;
            color: #dc3545;
            border-radius: 8px;
            width: 45px; /* Slightly larger */
            height: 45px; /* Slightly larger */
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            border: none;
            font-size: 1.1rem;
        }

        .btn-soft-danger:hover {
            background-color: #fca5a5;
            color: #b91c1c;
        }

        .main-content {
            margin-left: 250px;
            padding: 90px 20px 20px 20px;
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        /* ===== MOBILE ONLY ===== */
        @media (max-width: 991px) {
            .sidebar-admin {
                display: none;
            }

            .header-admin {
                left: 0 !important;
            }

            .main-content {
                margin-left: 0;
                padding-top: 90px;
            }
        }

        .badge-role-custom {
            background-color: #e8f5e9;
            color: #1b5e20;
            padding: 6px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-block;
            letter-spacing: 0.5px;
        }

        /* OVERRIDE MOBILE TABLE STYLES */
        @media (max-width: 767.98px) {
            .table-responsive {
                display: block !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
            }
            .table-responsive table {
                display: table !important;
                width: max-content !important; /* Allow table to grow beyond screen width */
                table-layout: auto !important; /* Let columns size themselves */
            }
            .table-responsive table thead {
                display: table-header-group !important;
            }
            .table-responsive table tbody tr {
                display: table-row !important;
            }
            .table-responsive table tbody tr td, 
            .table-responsive table thead tr th {
                display: table-cell !important;
                border-bottom: 1px solid #dee2e6 !important;
                padding: 1rem 2rem !important;
                white-space: nowrap !important;
                vertical-align: middle !important; /* Ensure vertical centering */
                min-width: 150px !important;
                width: auto !important; /* Override global fixed widths */
            }
            /* Specific Alignment for Action and No columns */
            .table-responsive table tbody tr td:last-child, 
            .table-responsive table thead tr th:last-child,
            .table-responsive table tbody tr td:first-child, 
            .table-responsive table thead tr th:first-child {
                text-align: center !important;
                width: 1% !important; /* Shrink to fit content + padding */
                white-space: nowrap !important;
                min-width: 80px !important; /* Explicit smaller width for these columns */
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
        }
        #rowsPerPage {
            color: #495057;
        }
    </style>

<?php 
    $pageTitle = "Kelola Pengguna";
    // sidebar
    include '../Nav_Side_Bar/sidebar.php';
    // header
    include '../Nav_Side_Bar/header.php'; 
?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="table-wrapper">
            <div class="toolbar-admin mb-3">
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    <span class="material-symbols-rounded" style="font-size:28px;">
                        person_search
                    </span>
                    <input type="text" id="searchInput" class="form-control search-box flex-grow-1" placeholder="Cari pengguna...">
                </div>

                <!-- DROPDOWN ROWS PER PAGE -->
                <style>
                    .form-select-green-outline {
                        border: 1px solid #198754 !important;
                        /* Bootstrap Success Green */
                        color: #198754;
                        border-radius: 8px;
                        font-weight: 500;
                        padding-right: 2.5rem;
                        /* Space for arrow */
                        width: auto;
                    }

                    .form-select-green-outline:focus {
                        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
                        border-color: #198754;
                    }

                    .toolbar-admin {
                        display: flex;
                        gap: 10px;
                        flex-wrap: wrap;
                    }

                    /* SEARCH FULL */
                    .toolbar-admin .search-box {
                        flex: 1 1 100%;
                    }

                    /* DATA + BUTTON */
                    @media (max-width: 576px) {
                        #rowsPerPage {
                            width: 120px;
                        }

                        .toolbar-admin .btn-success {
                            flex: 1;
                        }
                    }
                </style>
                <select id="rowsPerPage" class="form-select form-select-green-outline">
                    <option value="5">5 Data</option>
                    <option value="10" selected>10 Data</option>
                    <option value="25">25 Data</option>
                    <option value="50">50 Data</option>
                    <option value="all">Semua Data</option>
                </select>

                <a href="tambah_peserta_admin.php" class="btn btn-success d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle"></i> Tambah Pengguna
                </a>
            </div>

                <div class="table-responsive" style="overflow-x: auto; white-space: nowrap;">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">NO</th>
                            <th>FOTO</th>
                            <th>NAMA</th>
                            <th>NIK</th>
                            <th>EMAIL</th>
                            <th class="text-center">ROLE</th>
                            <th class="text-center">AKSI</th>
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
    <!-- Toast Notification -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="successToast"
            class="toast align-items-center text-bg-success border-0"
            role="alert"
            aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Pesan
                </div>
                <button type="button"
                    class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast"></button>
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

        // ID Admin (untuk perbandingan dalam fungsi hapus)
        const CURRENT_ADMIN_ID = <?php echo (int) $current_admin_id; ?>;

        const tbody = document.getElementById("userTableBody");
        const pagination = document.getElementById("pagination");
        const dataInfo = document.getElementById("dataInfo");
        const searchInput = document.getElementById("searchInput");
        const rowsPerPageSelect = document.getElementById("rowsPerPage"); // New Element

        let currentPage = 1;
        let itemsPerPage = parseInt(rowsPerPageSelect.value); // Dynamic Init
        let filteredUsers = Array.isArray(users) ? [...users] : [];

        // Update itemsPerPage dynamically
        rowsPerPageSelect.addEventListener('change', function() {
            if (this.value === 'all') {
                itemsPerPage = 1000000; // Show all data
            } else {
                itemsPerPage = parseInt(this.value);
            }
            currentPage = 1; // Reset to page 1
            renderTable(filteredUsers);
        });

        // Fungsi render tabel
        function renderTable(data) {
            tbody.innerHTML = "";

            if (!Array.isArray(data) || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data pengguna ditemukan.</td></tr>`;
                dataInfo.textContent = "";
                pagination.innerHTML = "";
                return;
            }

            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const paginatedData = data.slice(start, end);

            paginatedData.forEach((u, index) => {
                const nama = escapeHtml(u.nama || '');
                const nik = escapeHtml(u.nik || '-');
                const email = escapeHtml(u.email || '-');
                const role = escapeHtml(u.role || '');

                // Logic Foto vs Default Icon
                let photoHtml = '';
                if (u.foto && u.foto !== '') {
                    // Jika ada foto
                    const photoPath = `../file/${encodeURIComponent(u.foto)}`;
                    photoHtml = `<img src="${photoPath}" alt="${nama}" 
                                    class="rounded-circle shadow-sm" 
                                    style="width: 45px; height: 45px; object-fit: cover;"
                                    onerror="handleImageError(this)">`;
                } else {
                    // Default Icon
                    photoHtml = `<i class="bi bi-person-circle text-secondary" style="font-size: 45px;"></i>`;
                }

                const row = `
                    <tr>
                        <td class="align-middle text-center text-dark fw-bold">${start + index + 1}</td>
                        <td class="align-middle">
                            ${photoHtml}
                        </td>
                        <td class="align-middle fw-medium mb-1">${nama}</td>
                        <td class="align-middle">${nik}</td>
                        <td class="align-middle">${email}</td>
                        <td class="align-middle text-center"><span class="badge-role-custom">${role}</span></td>
                        <td class="align-middle text-center">
                            <button class="btn btn-sm btn-soft-danger btn-delete" onclick="deleteUser(${Number(u.id)}, this)" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML("beforeend", row);
            });

            updatePagination(data);
        }

        // Fungsi pembaruan paginasi
        function updatePagination(data) {
            pagination.innerHTML = "";
            const totalPages = Math.max(1, Math.ceil(data.length / itemsPerPage));

            const start = data.length === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
            const end = Math.min(start + itemsPerPage - 1, data.length);
            dataInfo.textContent = data.length === 0 ? '' : `Menampilkan ${start} -${end} dari ${data.length} pengguna`;

            pagination.insertAdjacentHTML(
                "beforeend",
                `<li class="page-item ${currentPage === 1 ? "disabled" : ""}" >
                    <a class="page-link" href="#" onclick="changePage(${currentPage - 1});return false;">Sebelumnya</a>
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
                    <a class="page-link" href="#" onclick="changePage(${currentPage + 1});return false;">Selanjutnya</a>
            </li>`
            );
        }

        function changePage(page) {
            const totalPages = Math.ceil(filteredUsers.length / itemsPerPage);
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            renderTable(filteredUsers);
        }

        // Hapus pengguna via AJAX (tanpa muat ulang)
        async function deleteUser(id, btn) {
            if (id === CURRENT_ADMIN_ID) {
                showToast('Anda tidak dapat menghapus akun Anda sendiri.', 'warning');
                return;
            }

            const confirmed = await showConfirm("Yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.");
            if (!confirmed) {
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
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    users = users.filter(u => u.id != id);
                    filteredUsers = filteredUsers.filter(u => u.id != id);

                    const totalPages = Math.max(1, Math.ceil(filteredUsers.length / itemsPerPage));
                    if (currentPage > totalPages) currentPage = totalPages;

                    renderTable(filteredUsers);
                    showToast(result.message || 'Pengguna berhasil dihapus.', 'success');
                } else {
                    showToast(result.message || 'Gagal menghapus pengguna.', 'error');
                }
            } catch (err) {
                console.error('Error:', err);
                showToast('Terjadi kesalahan saat menghubungi server.', 'error');
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                }
            }
        }

        // Fungsi tampilkan notifikasi
        function showToast(message, type = 'success') {
            const toastEl = document.getElementById('successToast');
            if (!toastEl) return;

            toastEl.className = `toast align-items-center text-bg-${type} border-0`;

            toastEl.querySelector('.toast-body').innerHTML = `
        <i class="bi bi-check-circle-fill me-2"></i> ${message}
    `;

            const toast = new bootstrap.Toast(toastEl, {
                delay: 3000
            });
            toast.show();
        }


        // Global function to handle image errors
        function handleImageError(img) {
            img.onerror = null; // Prevent infinite loop
            // Replace the image with the default icon
            const icon = document.createElement('i');
            icon.className = 'bi bi-person-circle text-secondary';
            icon.style.fontSize = '45px';
            img.parentNode.replaceChild(icon, img);
        }

        // Fitur pencarian
        // bantuan kecil untuk escape HTML saat menyuntikkan dari JSON
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
            searchInput.addEventListener("input", function() {
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
        <?php if (isset($_SESSION['success_message'])): ?>
            window.addEventListener('DOMContentLoaded', function() {
                showToast(<?= json_encode($_SESSION['success_message']) ?>, 'success');
            });
        <?php unset($_SESSION['success_message']);
        endif; ?>




        // Buka WhatsApp otomatis jika tautan ada
        <?php if ($wa_link): ?>
            window.addEventListener('DOMContentLoaded', function() {
                const waLink = <?= json_encode($wa_link) ?>;
                const waNomor = <?= json_encode($wa_nomor) ?>;

                // Tampilkan dialog konfirmasi
                showConfirm('Peserta berhasil ditambahkan! Buka WhatsApp untuk mengirim informasi login ke ' + waNomor + '?')
                    .then(function(confirmed) {
                        if (confirmed) {
                            // Buka WhatsApp di tab baru
                            window.open(waLink, '_blank');
                            <?php if ($wa_message): ?>
                                showToast(<?= json_encode($wa_message) ?>, 'success');
                            <?php endif; ?>
                        }
                    });
            });
        <?php endif; ?>

        // Handler Logout
        const logoutBtn = document.getElementById("logoutBtn");
        if (logoutBtn) {
            logoutBtn.addEventListener("click", async function(e) {
                e.preventDefault();
                const confirmed = await showConfirm("Yakin mau keluar?");
                if (confirmed) {
                    window.location.href = "../proses/proses_logout.php";
                }
            });
        }
        const logoutBtnMobile = document.getElementById("logoutBtnMobile");
        if (logoutBtnMobile) {
            logoutBtnMobile.addEventListener("click", async function(e) {
                e.preventDefault();
                const confirmed = await showConfirm("Yakin mau keluar?");
                if (confirmed) {
                    window.location.href = "../proses/proses_logout.php";
                }
            });
        }
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
</body>

</html>