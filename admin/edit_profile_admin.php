<?php
session_start();
require_once __DIR__ . '/../koneksi.php'; // sesuaikan path jika perlu

// pastikan login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];

// helper
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}
$csrf_token = $_SESSION['csrf_token'];

// ambil data user saat ini
$stmt = $conn->prepare("SELECT id, nama, email, role, foto FROM users WHERE id = ?");
if (!$stmt) {
    die("Gagal menyiapkan query: " . $conn->error);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "<script>alert('User tidak ditemukan. Silakan login ulang.'); window.location.href='../login.php';</script>";
    exit;
}

// jika page diakses setelah update (pakai ?updated=1), tambahkan cache-bust untuk preview
$updated = isset($_GET['updated']) ? (int)$_GET['updated'] : 0;

// variabel untuk pesan
$errors = [];
$success = '';

// PROSES FORM
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = 'Token CSRF tidak valid.';
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if ($nama === '') $errors[] = 'Nama tidak boleh kosong.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';

        // cek email bila berubah
        if (strcasecmp($email, $user['email']) !== 0) {
            $q = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
            if ($q) {
                $q->bind_param("si", $email, $userId);
                $q->execute();
                $rr = $q->get_result();
                if ($rr->num_rows > 0) {
                    $errors[] = 'Email sudah dipakai pengguna lain.';
                }
                $q->close();
            } else {
                $errors[] = 'Gagal memeriksa email.';
            }
        }

        // password opsional
        $newPasswordHash = null;
        if ($password !== '' || $password_confirm !== '') {
            if ($password !== $password_confirm) {
                $errors[] = 'Password dan konfirmasi tidak cocok.';
            } elseif (strlen($password) < 6) {
                $errors[] = 'Password minimal 6 karakter.';
            } else {
                $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);
            }
        }

        // PHOTO upload opsional
        $fotoName = $user['foto'] ?? '';
        if (!empty($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $f = $_FILES['foto'];

            if ($f['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Terjadi kesalahan saat upload foto. Kode error: ' . $f['error'];
                error_log("UPLOAD ERROR for user {$userId}: code={$f['error']}");
            } else {
                // whitelist mime & extension
                $allowedMime = ['image/jpeg' => ['jpg','jpeg'], 'image/png' => ['png'], 'image/webp' => ['webp']];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $f['tmp_name']);
                finfo_close($finfo);

                if (!array_key_exists($mime, $allowedMime)) {
                    $errors[] = 'Tipe file foto tidak diizinkan. (jpg/png/webp)';
                    error_log("DISALLOWED MIME upload user {$userId}: $mime");
                } elseif ($f['size'] > 2 * 1024 * 1024) {
                    $errors[] = 'Ukuran foto maksimal 2MB.';
                    error_log("FILE TOO LARGE user {$userId}: size={$f['size']}");
                } else {
                    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                    // cek kecocokan ekstensi dengan MIME
                    if (!in_array($ext, $allowedMime[$mime])) {
                        // untuk jpeg both jpg/jpeg ok
                        if (!($mime === 'image/jpeg' && in_array($ext, ['jpg','jpeg']))) {
                            $errors[] = 'Ekstensi file tidak cocok dengan tipe MIME.';
                            error_log("EXT/MIME mismatch user {$userId}: ext={$ext}, mime={$mime}");
                        }
                    }
                }

                // bila valid, simpan file
                if (empty($errors)) {
                    $newName = 'foto_' . $userId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $destDir = __DIR__ . '/../file/'; // pastikan folder ini ada & writable
                    if (!is_dir($destDir)) {
                        if (!mkdir($destDir, 0755, true)) {
                            $errors[] = 'Gagal membuat folder penyimpanan foto.';
                            error_log("Gagal mkdir $destDir");
                        }
                    }
                    $destPath = $destDir . $newName;

                    if (!move_uploaded_file($f['tmp_name'], $destPath)) {
                        $errors[] = 'Gagal menyimpan file foto. Pastikan folder memiliki permission write oleh webserver.';
                        error_log("move_uploaded_file gagal ke $destPath; tmp_name={$f['tmp_name']}");
                    } else {
                        @chmod($destPath, 0644);
                        // hapus file lama bila ada
                        if (!empty($user['foto'])) {
                            $oldPath = $destDir . $user['foto'];
                            if (file_exists($oldPath) && is_file($oldPath)) {
                                @unlink($oldPath);
                            }
                        }
                        $fotoName = $newName;
                    }
                }
            }
        }

        // update DB jika tidak ada error
        if (empty($errors)) {
            if ($newPasswordHash !== null) {
                $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, foto = ?, password = ? WHERE id = ?");
                if (!$stmt) {
                    $errors[] = 'Gagal menyiapkan query update: ' . $conn->error;
                    error_log("Prepare failed update user {$userId}: " . $conn->error);
                } else {
                    $stmt->bind_param("ssssi", $nama, $email, $fotoName, $newPasswordHash, $userId);
                }
            } else {
                $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, foto = ? WHERE id = ?");
                if (!$stmt) {
                    $errors[] = 'Gagal menyiapkan query update: ' . $conn->error;
                    error_log("Prepare failed update user {$userId}: " . $conn->error);
                } else {
                    $stmt->bind_param("sssi", $nama, $email, $fotoName, $userId);
                }
            }

            if (!empty($stmt) && empty($errors)) {
                if (!$stmt->execute()) {
                    $errors[] = 'Gagal menyimpan perubahan: ' . $stmt->error;
                    error_log("Execute failed update user {$userId}: " . $stmt->error);
                } else {
                    // update session & redirect supaya halaman reload dan gambar baru dimuat
                    $_SESSION['user_name'] = $nama;
                    $_SESSION['user_email'] = $email;
                    // redirect untuk mencegah resubmit dan memaksa reload gambar
                    header("Location: edit_profile_admin.php?updated=1");
                    exit;
                }
                $stmt->close();
            }
        }
    }
}

// tentukan path foto untuk preview awal (cache-bust kalau updated)
$baseFotoPath = (!empty($user['foto']) && file_exists(__DIR__ . '/../file/' . $user['foto'])) ? ('../file/' . rawurlencode($user['foto'])) : '../file/user.jpg';
if ($updated === 1) {
    // tambahkan param waktu supaya browser fetch ulang
    $baseFotoPath .= '?v=' . time();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Edit Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.min.css">
    <style>
        .profile-box { max-width:900px; margin:0 auto; background: #fff; padding:1.25rem; border-radius:.5rem; box-shadow: 0 6px 18px rgba(0,0,0,0.04);}
        .foto-preview { width:96px; height:96px; border-radius:8px; object-fit:cover; border:1px solid #e9e9e9; }
    </style>
</head>
<body>
    <!-- navbar -->
    <nav class="navbar navbar-light bg-white sticky-top px-3">
        <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
            <i class="bi bi-list"></i>
        </button>
    </nav>

    <!-- offcanvas sidebar mobile -->
    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas"
        aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-body p-0">
            <div class="sidebar-content d-flex flex-column justify-content-between h-100">
                <div>
                    <h5 class="fw-bold mb-4 ms-3">Menu</h5>
                    <ul class="nav flex-column">
                        <li><a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
                        <li><a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a></li>
                        <li><a class="nav-link" href="tambah_peserta_admin.php"><i class="bi bi-person-plus me-2"></i>Tambah Pengguna</a></li>
                        <li><a class="nav-link active" href="edit_profile_admin.php"><i class="bi bi-person-circle me-2"></i>Edit Profil</a></li>
                    </ul>
                </div>
                <div class="text-center mt-4">
                    <button id="logoutBtnMobile" class="btn logout-btn px-4 py-2"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                </div>
            </div>
        </div>
    </div>

    <!-- sidebar desktop -->
    <div class="sidebar-content d-none d-lg-flex flex-column justify-content-between position-fixed">
        <div>
            <h5 class="fw-bold mb-4 ms-3">Menu</h5>
            <ul class="nav flex-column">
                <li><a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
                <li><a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a></li>
                <li><a class="nav-link active" href="edit_profile_admin.php"><i class="bi bi-person-circle me-2"></i>Edit Profil</a></li>
            </ul>
        </div>
        <div class="text-center">
            <button id="logoutBtn" class="btn logout-btn px-4 py-2"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
        </div>
    </div>

    <!-- main -->
    <div class="main-content" style="padding:1.5rem;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div><h4><b>Edit Profil</b></h4></div>
            <div class="profile"><span>Halo, <?= e($user['nama']) ?> 👋</span></div>
        </div>

        <div class="profile-box">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>

            <form action="edit_profile_admin.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input name="nama" type="text" class="form-control" required value="<?= e($user['nama']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input name="email" type="email" class="form-control" required value="<?= e($user['email']) ?>">
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">Ganti Password (opsional)</label>
                            <input name="password" type="password" class="form-control" placeholder="Password baru (kosongkan jika tidak ganti)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input name="password_confirm" type="password" class="form-control" placeholder="Konfirmasi password baru">
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="profile.php" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="text-center mb-3">
                            <label class="form-label d-block">Foto</label>

                            <!-- preview image -->
                            <img id="fotoPreview" src="<?= e($baseFotoPath) ?>" alt="foto" class="foto-preview mb-2">

                            <div class="mb-2">
                                <input id="fotoInput" name="foto" type="file" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                                <small class="text-muted">Tipe jpg/png/webp. Maks 2MB.</small>
                            </div>

                            <div class="mt-3 text-muted small">
                                <div><strong>Role:</strong> <?= e($user['role']) ?></div>
                                <div class="mt-2"><strong>Email Tersembunyi:</strong>
                                    <?php
                                        $parts = explode('@', $user['email']);
                                        if (count($parts) === 2) {
                                            $u = $parts[0]; $d = $parts[1]; $len = mb_strlen($u);
                                            $masked = ($len <= 1) ? '*' : mb_substr($u,0,1) . str_repeat('*', max(1,$len-1));
                                            echo e($masked . '@' . $d);
                                        } else echo e($user['email']);
                                    ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
    const input = document.getElementById('fotoInput');
    const preview = document.getElementById('fotoPreview');
    let currentObjectUrl = null;
    if (!input || !preview) return;

    input.addEventListener('change', function(e){
        const file = input.files && input.files[0];
        if (!file) {
            // nothing selected or user canceled, keep current preview
            return;
        }

        // simple client-side validation
        const allowedTypes = ['image/jpeg','image/png','image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Tipe file tidak diizinkan. Gunakan JPG / PNG / WEBP.');
            input.value = '';
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar (maks 2MB).');
            input.value = '';
            return;
        }

        // revoke previous
        if (currentObjectUrl) {
            URL.revokeObjectURL(currentObjectUrl);
            currentObjectUrl = null;
        }

        currentObjectUrl = URL.createObjectURL(file);
        preview.src = currentObjectUrl;

        preview.onload = function() {
            // release memory if desired
            if (currentObjectUrl) {
                URL.revokeObjectURL(currentObjectUrl);
                currentObjectUrl = null;
            }
        };
    });
})();
</script>
</body>
</html>
