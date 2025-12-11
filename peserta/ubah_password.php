<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil data pengguna yang sedang login
$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nama, foto, is_first_login FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userRes = $stmt->get_result();
$userData = $userRes->fetch_assoc();
$stmt->close();

if (!$userData) {
    header("Location: ../login.php");
    exit;
}

$userName = $userData['nama'] ?? 'Peserta';
$userPhoto = $userData['foto'] ?? null;
$isFirstLogin = $userData['is_first_login'] ?? false;

// Ambil pesan dari sesi
$success_msg = $_SESSION['success_message'] ?? '';
$error_msg = $_SESSION['error_message'] ?? '';

if ($success_msg) unset($_SESSION['success_message']);
if ($error_msg) unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.min.css">
    <style>
        .password-section {
            max-width: 500px;
            margin: 0 auto;
        }
        .password-strength {
            height: 5px;
            border-radius: 3px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }
        .password-strength.weak {
            background-color: #dc3545;
            width: 33%;
        }
        .password-strength.medium {
            background-color: #ffc107;
            width: 66%;
        }
        .password-strength.strong {
            background-color: #28a745;
            width: 100%;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
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
                            <a class="nav-link" href="dashboard_peserta.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
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
                    <a class="nav-link" href="dashboard_peserta.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
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

    <!-- Konten Utama -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-semibold"><i class="bi bi-key-fill me-2"></i>Ubah Password</h5>
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

        <?php if ($isFirstLogin): ?>
            <div class="alert alert-info" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Selamat datang!</strong> Anda harus mengganti password default sebelum melanjutkan.
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="password-section">
            <div class="card shadow-sm border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Ubah Password</h6>
                </div>
                <div class="card-body">
                    <form action="../proses/proses_ubah_password.php" method="POST" id="passwordForm">
                        <div class="mb-3">
                            <label class="form-label">Password Lama</label>
                            <div class="input-group">
                                <input type="password" id="oldPassword" name="old_password" class="form-control" 
                                       placeholder="Masukkan password lama" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleOldPwd">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <div class="input-group">
                                <input type="password" id="newPassword" name="new_password" class="form-control" 
                                       placeholder="Minimal 8 karakter" minlength="8" required
                                       onkeyup="checkPasswordStrength()">
                                <button class="btn btn-outline-secondary" type="button" id="toggleNewPwd">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength" id="passwordStrength"></div>
                            <small class="text-muted d-block mt-2" id="strengthText">Minimal 8 karakter</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <input type="password" id="confirmPassword" name="confirm_password" class="form-control" 
                                       placeholder="Konfirmasi password baru" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPwd">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-2"></i>Password baru harus berbeda dengan password lama
                            </small>
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <?php if (!$isFirstLogin): ?>
                                <a href="dashboard_peserta.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Kembali
                                </a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-2"></i>Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
    <script>
        // Beralih visibilitas password
        document.getElementById('toggleOldPwd').addEventListener('click', function() {
            togglePasswordVisibility('oldPassword');
        });

        document.getElementById('toggleNewPwd').addEventListener('click', function() {
            togglePasswordVisibility('newPassword');
        });

        document.getElementById('toggleConfirmPwd').addEventListener('click', function() {
            togglePasswordVisibility('confirmPassword');
        });

        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = event.target.closest('button').querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        // Pemeriksa kekuatan password
        function checkPasswordStrength() {
            const password = document.getElementById('newPassword').value;
            const strengthBar = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            
            // Hitung kekuatan password
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
            if (/\d/.test(password)) strength += 1;
            if (/[^a-zA-Z\d]/.test(password)) strength += 1;
            
            // Perbarui UI
            strengthBar.className = 'password-strength';
            
            if (strength <= 2) {
                strengthBar.classList.add('weak');
                strengthText.textContent = 'Kekuatan: Lemah (tambah huruf besar, angka, atau simbol)';
            } else if (strength <= 3) {
                strengthBar.classList.add('medium');
                strengthText.textContent = 'Kekuatan: Sedang (tambah kombinasi karakter)';
            } else {
                strengthBar.classList.add('strong');
                strengthText.textContent = 'Kekuatan: Kuat ✓';
            }
        }

        // Validasi formulir
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const oldPwd = document.getElementById('oldPassword').value;
            const newPwd = document.getElementById('newPassword').value;
            const confirmPwd = document.getElementById('confirmPassword').value;
            
            if (newPwd !== confirmPwd) {
                e.preventDefault();
                alert('Password baru dan konfirmasi tidak cocok!');
                return false;
            }
            
            if (oldPwd === newPwd) {
                e.preventDefault();
                alert('Password baru harus berbeda dengan password lama!');
                return false;
            }
        });

        // Keluar
        document.getElementById('logoutBtn')?.addEventListener('click', async function(e) {
            e.preventDefault();
            const confirmed = await showConfirm('Apakah kamu yakin ingin logout?');
            if (confirmed) {
                localStorage.removeItem("userData");
                window.location.href = '../proses/proses_logout.php';
            }
        });

        document.getElementById('logoutBtnMobile')?.addEventListener('click', async function(e) {
            e.preventDefault();
            const confirmed = await showConfirm('Apakah kamu yakin ingin logout?');
            if (confirmed) {
                localStorage.removeItem("userData");
                window.location.href = '../proses/proses_logout.php';
            }
        });
    </script>
</body>

</html>
