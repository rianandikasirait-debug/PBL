<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek Login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
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
    session_destroy();
    header("Location: ../login.php");
    exit;
}

// Buat variabel foto
$foto = $user['foto'] ?? null;
$filePath = "../file/" . $foto;
$hasPhoto = ($foto && file_exists($filePath));

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

<?php 
    $pageTitle = "Profile Admin";
    // sidebar
    include '../Nav_Side_Bar/sidebar.php'; 
    // navbar
    include '../Nav_Side_Bar/navbar.php';
?>

    <!-- Main -->
    <div class="main-content">


        <div class="profile-container">
            <div class="profile-card-modern">

                <!-- FOTO PROFIL + NAMA + ROLE -->
                <div class="profile-header-modern text-center">

                    <?php if ($hasPhoto): ?>
                        <img src="<?= htmlspecialchars($filePath) . '?v=' . time(); ?>"
                            alt="Profile"
                            class="profile-avatar-modern"
                            style="object-fit: cover;">
                    <?php else: ?>
                        <i class="bi bi-person-circle"
                           style="font-size: 95px; color: #495057;"></i>
                    <?php endif; ?>

                    <h3 class="profile-name-modern mt-3"><?= htmlspecialchars($user['nama']); ?></h3>
                    <span class="profile-role-badge"><?= ucfirst($user['role']); ?></span>
                </div>

                <!-- DETAIL INFORMASI -->
                <div class="profile-info-grid mt-4">
                    <div class="info-item">
                        <span class="info-label">Nama Lengkap</span>
                        <span class="info-value"><?= htmlspecialchars($user['nama']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email Address</span>
                        <span class="info-value"><?= htmlspecialchars($user['email']); ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Nomor Induk (NIK)</span>
                        <span class="info-value"><?= !empty($user['nik']) ? htmlspecialchars($user['nik']) : '-'; ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Role Akses</span>
                        <span class="info-value"><?= ucfirst($user['role']); ?></span>
                    </div>
                </div>

                <!-- ACTION BUTTON -->
                <div class="profile-actions-modern mt-4">
                    <a href="edit_profile_admin.php" class="btn-edit-modern">
                        <i class="bi bi-pencil-square"></i> Edit Profile
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- Logout Confirm -->
    <script>
        async function confirmLogout(e) {
            e.preventDefault();
            const confirmed = await showConfirm("Yakin mau keluar?");
            if (confirmed) window.location.href = "../proses/proses_logout.php";
        }

        document.getElementById("logoutBtn").addEventListener("click", confirmLogout);
        document.getElementById("logoutBtnMobile").addEventListener("click", confirmLogout);
    </script>
    <script src="../js/admin.js"></script>
    <?php if (isset($_SESSION['success_message'])): ?>
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                showToast(<?= json_encode($_SESSION['success_message']) ?>, 'success');
            });
        </script>
<?php unset($_SESSION['success_message']); endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
