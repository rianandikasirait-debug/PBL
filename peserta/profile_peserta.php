<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek Login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'peserta') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['user_id'];

// Ambil data terbaru dari database
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    // Jika pengguna tidak ditemukan (misal dihapus admin saat sedang login)
    session_destroy();
    header("Location: ../login.php");
    exit;
}

// Atur foto default jika kosong
$foto_profile = (!empty($user['foto']) ? '../file/' . $user['foto'] : '../file/user.jpg') . '?v=' . time();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="../css/admin.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
</head>

<body>
    <!-- navbar -->
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

        /* ===== MAIN CONTENT ADJUSTMENT ===== */
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
    </style>

    <!-- Sidebar Desktop -->
    <div class="sidebar-admin d-none d-lg-flex">
        <div class="sidebar-top">
            <div class="title text-success">SmartNote</div>
            
            <a href="dashboard_peserta.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard_peserta.php' ? 'active' : '' ?>">
                <i class="bi bi-grid me-2"></i> Dashboard
            </a>
        </div>

        <div class="sidebar-bottom">
            <a href="profile_peserta.php" class="<?= basename($_SERVER['PHP_SELF']) === 'profile_peserta.php' ? 'active' : '' ?>">
                <i class="bi bi-person-circle me-2"></i> Profile
            </a>
            <a href="#" id="logoutBtn" class="text-danger">
                <i class="bi bi-box-arrow-left me-2"></i> Keluar
            </a>
        </div>
    </div>

    <!-- Header / Top Bar -->
    <div class="header-admin">
        <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile">
            <i class="bi bi-list"></i>
        </button>

        <div class="page-title">Profil Peserta</div>

        <div class="right-section">
            <div class="d-none d-md-block text-end me-2">
                <div class="fw-bold small"><?= htmlspecialchars($user['nama']) ?></div>
                <small class="text-muted" style="font-size: 0.75rem;">Peserta</small>
            </div>
            
            <?php if (!empty($user['foto']) && file_exists('../file/' . $user['foto'])): ?>
                <img src="../file/<?= htmlspecialchars($user['foto']) ?>" class="rounded-circle border" style="width:40px;height:40px;object-fit:cover;">
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
                    <a class="nav-link text-dark fw-medium <?= basename($_SERVER['PHP_SELF']) === 'dashboard_peserta.php' ? 'bg-success text-white rounded' : '' ?>" href="dashboard_peserta.php">
                        <i class="bi bi-grid me-2"></i> Dashboard
                    </a>
                </li>
            </ul>

            <ul class="nav flex-column gap-2 mt-4 border-top pt-3">
                 <li class="nav-item">
                    <a class="nav-link text-dark fw-medium <?= basename($_SERVER['PHP_SELF']) === 'profile_peserta.php' ? 'bg-success text-white rounded' : '' ?>" href="profile_peserta.php">
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

    <!-- Main -->
    <div class="main-content">

        <!-- Pesan Peringatan (struktur dipertahankan) -->
         <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-3">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <div class="profile-container">
            <div class="profile-card-modern">
                <!-- Header: Foto, Nama, Peran -->
                <div class="profile-header-modern">
                    <?php if (!empty($user['foto']) && file_exists('../file/' . $user['foto'])): ?>
                        <img src="../file/<?= htmlspecialchars($user['foto']) . '?v=' . time() ?>" alt="Profile" class="profile-avatar-modern">
                    <?php else: ?>
                        <i class="bi bi-person-circle text-secondary" style="font-size: 80px;"></i>
                    <?php endif; ?>
                    <h3 class="profile-name-modern"><?= htmlspecialchars($user['nama']); ?></h3>
                    <span class="profile-role-badge"><?= ucfirst($user['role']); ?></span>
                </div>

                <!-- Info Grid 2 Kolom -->
                <div class="profile-info-grid">
                    <div class="info-item">
                        <span class="info-label">Nama Lengkap</span>
                        <span class="info-value"><?= htmlspecialchars($user['nama']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email Address</span>
                        <span class="info-value"><?= htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Nomor Induk Kependudukan (NIK)</span>
                        <span class="info-value"><?= htmlspecialchars($user['nik']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Nomor HP / WhatsApp</span>
                        <span class="info-value"><?= !empty($user['nomor_whatsapp']) ? htmlspecialchars($user['nomor_whatsapp']) : '-'; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status Akun</span>
                        <span class="info-value"><?= ucfirst($user['role']); ?></span>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="profile-actions-modern mt-4">
                    <button type="button" class="btn-edit-modern border-0" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="bi bi-pencil-square"></i> Edit Profil
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0 pe-4 pt-4">
                    <h5 class="modal-title fw-bold text-dark fs-4">Edit Profil</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-3">
                    <form id="editProfileForm" enctype="multipart/form-data">
                        <!-- Photo Section -->
                        <div class="d-flex flex-column align-items-center mb-4">
                            <div class="position-relative" style="width: 100px; height: 100px;">
                                <img src="<?= htmlspecialchars($foto_profile); ?>" 
                                     id="modalProfilePreview"
                                     class="rounded-circle border border-2 border-white shadow-sm w-100 h-100" 
                                     style="object-fit: cover;">
                                
                                <label for="modalFotoInput" 
                                       class="position-absolute bottom-0 end-0 bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center border"
                                       style="width: 32px; height: 32px; cursor: pointer;">
                                    <i class="bi bi-camera-fill text-secondary small"></i>
                                </label>
                            </div>
                            <input type="file" id="modalFotoInput" name="foto" class="d-none" accept=".jpg,.png,.jpeg,.webp">
                            
                            <!-- Delete Photo Option -->
                            <div class="mt-2" id="deletePhotoContainer" style="<?= !empty($user['foto']) ? '' : 'display:none;' ?>">
                                <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0" id="btnDeletePhoto">
                                    <small><i class="bi bi-trash me-1"></i>Hapus Foto</small>
                                </button>
                                <input type="hidden" name="delete_photo" id="deletePhotoInput" value="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark">Nama Lengkap</label>
                            <input type="text" class="form-control form-control-lg rounded-3 fs-6" name="nama" value="<?= htmlspecialchars($user['nama']); ?>" required placeholder="Nama lengkap">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark">Nomor HP / WhatsApp</label>
                            <input type="tel" class="form-control form-control-lg rounded-3 fs-6" name="nomor_whatsapp" value="<?= htmlspecialchars($user['nomor_whatsapp'] ?? ''); ?>" placeholder="Contoh: 081234567890">
                        </div>

                        <div class="p-3 bg-light rounded-3 border border-light-subtle mb-4">
                            <h6 class="fw-semibold text-dark mb-3" style="font-size: 0.9rem;">Ubah Password (Opsional)</h6>
                            <div class="mb-3">
                                <input type="password" class="form-control form-control-lg rounded-3 fs-6" name="password_baru" placeholder="Password Baru">
                            </div>
                            <div>
                                <input type="password" class="form-control form-control-lg rounded-3 fs-6" name="password_konfirmasi" placeholder="Konfirmasi Password Baru">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-1">
                            <button type="button" class="btn btn-light border bg-white px-4 fw-medium rounded-3 py-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success px-4 fw-medium rounded-3 py-2" id="btnSaveProfile">
                                <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fungsi Logout
        async function confirmLogout(e) {
            e.preventDefault();
            const confirmed = await showConfirm("Yakin mau keluar?");
            if (confirmed) {
                window.location.href = "../login.php";
            }
        }

        document.getElementById("logoutBtn").addEventListener("click", confirmLogout);

        const logoutBtnMobile = document.getElementById("logoutBtnMobile");
        if (logoutBtnMobile) {
            logoutBtnMobile.addEventListener("click", confirmLogout);
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
    <script src="../js/profile.js"></script>
</body>

</html>