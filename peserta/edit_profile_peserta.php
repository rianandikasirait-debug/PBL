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
    session_destroy();
    header("Location: ../login.php");
    exit;
}

$foto_path = !empty($user['foto']) ? '../uploads/' . $user['foto'] : '';
$foto_profile = (!empty($foto_path) && file_exists($foto_path)) ? $foto_path : '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Pengguna</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
</head>

<body>

    <!-- sidebar -->
    <?php include __DIR__ . '/../Nav_Side_Bar/sidebar_peserta.php'; ?>

    <!-- Header / Top Bar -->
    <?php 
    $pageTitle = 'Edit Profil';
    $userName = $user['nama'];
    $userPhoto = $user['foto'] ?? null;
    include __DIR__ . '/../Nav_Side_Bar/header_peserta.php'; 
    ?>

    <!-- Main -->
    <div class="main-content">
        <div class="mb-3">
            <div class="w-100">
                <!-- Alert Error -->
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="profile-box">
            <h5 class="fw-semibold mb-4">
                <span class="material-symbols-outlined me-2" style="font-size:23px; line-height: 1; vertical-align: -4px;">person_edit</span>
                Edit Profile Pengguna
            </h5>

            <form action="../proses/proses_edit_profile.php" method="POST" enctype="multipart/form-data">
                <div class="d-flex flex-column align-items-center mb-4">
                    <?php if (!empty($foto_profile)): ?>
                        <div class="position-relative d-inline-block" id="photoWrapper">
                            <img src="<?= htmlspecialchars($foto_profile); ?>" width="100" height="100"
                                class="rounded-circle mb-2" style="object-fit: cover; border: 2px solid #ddd;"
                                alt="Foto Profil" id="previewImage">
                            <button type="button" 
                                class="btn btn-danger position-absolute bottom-0 end-0 rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                                style="width: 32px; height: 32px; bottom: 8px !important;" 
                                title="Hapus Foto" onclick="confirmDeletePhoto()">
                                <i class="bi bi-trash-fill" style="font-size: 14px;"></i>
                            </button>
                        </div>
                    <?php else: ?>
                         <div id="defaultIcon" class="mb-2 d-inline-block bg-light rounded-circle border d-flex align-items-center justify-content-center" style="width:100px; height:100px;">
                           <i class="bi bi-person-fill text-secondary" style="font-size: 50px;"></i>
                        </div>
                        <img id="previewImage" src="#" width="100" height="100" class="rounded-circle mb-2 d-none" style="object-fit: cover; border: 2px solid #ddd;" alt="Preview Foto">
                    <?php endif; ?>
                    <div>
                        <input type="file" name="foto" id="fotoInput" class="form-control w-auto mx-auto" accept=".jpg,.png,.jpeg">
                        <small class="text-muted d-block mt-1">Kosongkan jika tidak ingin mengubah foto (Maks. 2MB)</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" disabled>
                    <small class="text-muted">Email tidak dapat diubah</small>
                </div>

                <hr>

                <h6 class="fw-semibold mb-3">Ubah Password (Opsional)</h6>

                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="password_baru" class="form-control" placeholder="Kosongkan jika tidak ganti password">
                </div>

                <div class="mb-4">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="password_konfirmasi" class="form-control" placeholder="Ulangi password baru">
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="profile_peserta.php" class="btn btn-cancel">Batal</a>
                    <button type="submit" class="btn btn-save"><i class="bi bi-check2-circle me-1"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Preview Image Logic
        const fotoInput = document.getElementById('fotoInput');
        const previewImage = document.getElementById('previewImage');
        const defaultIcon = document.getElementById('defaultIcon');

        if(fotoInput) {
            fotoInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if(previewImage) {
                            previewImage.src = e.target.result;
                            previewImage.classList.remove('d-none');
                        }
                        if(defaultIcon) defaultIcon.classList.add('d-none');
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        // Fungsi Hapus Foto
        async function confirmDeletePhoto() {
            const confirmed = await showConfirm("Hapus foto profil ini?");
            if (confirmed) {
                const form = document.querySelector('form');
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_photo';
                input.value = '1';
                form.appendChild(input);
                form.submit();
            }
        }

        // Logout function
        async function confirmLogout(e) {
            e.preventDefault();
            const confirmed = await showConfirm("Yakin mau keluar?");
            if (confirmed) {
                window.location.href = "../proses/proses_logout.php";
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
</body>

</html>