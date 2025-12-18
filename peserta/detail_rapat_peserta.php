<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek Login & Peran
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'peserta') {
    header("Location: ../login.php");
    exit;
}

$id_notulen = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id_notulen <= 0) {
    echo "<script>alert('ID Notulen tidak valid!'); window.location.href='dashboard_peserta.php';</script>";
    exit;
}

// Tandai sebagai dilihat dalam sesi
if (!isset($_SESSION['viewed_notulen'])) {
    $_SESSION['viewed_notulen'] = [];
}

if (!in_array($id_notulen, $_SESSION['viewed_notulen'])) {
    $_SESSION['viewed_notulen'][] = $id_notulen;
}

// Ambil data pengguna yang sedang login (nama + foto)
$userId = (int) ($_SESSION['user_id'] ?? 0);
if ($userId > 0) {
    $s = $conn->prepare("SELECT nama, foto FROM users WHERE id = ?");
    $s->bind_param("i", $userId);
    $s->execute();
    $r = $s->get_result();
    $u = $r->fetch_assoc();
    $s->close();
    $sessionUserName = $u['nama'] ?? null;
    $userPhoto = $u['foto'] ?? null;
    $userEmail = $u['email'] ?? null; // Ambil email pengguna

} else {
    $sessionUserName = null;
    $userPhoto = null;
    $userEmail = null;
}


// Ambil data notulen
$sql = "SELECT * FROM tambah_notulen WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_notulen); // Menggunakan "i" karena hanya id_notulen yang digunakan
$stmt->execute();
$result = $stmt->get_result();
$notulen = $result->fetch_assoc();

if (!$notulen) {
    // Jika tidak ketemu berdasarkan ID, atau jika ada filter tambahan yang tidak terpenuhi
    echo "<script>showToast('Data tidak ditemukan atau Anda tidak memiliki akses!', 'error'); setTimeout(() => window.location.href='dashboard_peserta.php', 2000);</script>";
    exit;
}

// Fetch Lampiran (tb_lampiran)
$stmtLampiran = $conn->prepare("SELECT * FROM tb_lampiran WHERE id_notulen = ?");
$stmtLampiran->bind_param("i", $id_notulen);
$stmtLampiran->execute();
$resLampiran = $stmtLampiran->get_result();
$lampiranList = [];

while ($row = $resLampiran->fetch_assoc()) {
    $lampiranList[] = $row;
}

// Siapkan variabel yang dipakai di HTML
$tanggal = !empty($notulen['tanggal']) ? date('d/m/Y', strtotime($notulen['tanggal'])) : '-';
$lampiran = $notulen['tindak_lanjut'] ?? '';
$created_by = $notulen['created_by'] ?? 'Admin';

// Uraikan peserta SEBELUM menutup koneksi
$peserta_raw = $notulen['peserta'] ?? '';
$peserta_names = [];
$peserta_details = []; // Initialize the array
if (trim($peserta_raw) !== '') {
    $parts = array_filter(array_map('trim', explode(',', $peserta_raw)), function($v){ return $v !== ''; });
    $peserta_ids = array_filter(array_map('intval', $parts), function($v){ return $v > 0; });
    if (!empty($peserta_ids)) {
        $ids_list = implode(',', $peserta_ids);
        $sql_users = "SELECT id, nama, email, nik, foto FROM users WHERE id IN ($ids_list)";
        $res_users = $conn->query($sql_users);
        $map = [];

        while ($r = $res_users->fetch_assoc()) {
            $map[(int)$r['id']] = $r;
        }

        foreach ($parts as $orig) {
            if (is_numeric($orig)) {
                $idint = (int)$orig;
                if (isset($map[$idint])) {
                    $peserta_details[] = $map[$idint];
                } else {
                     $peserta_details[] = ['nama' => (string)$idint, 'email' => '', 'nik' => ''];
                }

            } else {
                $peserta_details[] = ['nama' => $orig, 'email' => '', 'nik' => ''];
            }
            }
    }
}

?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Rapat</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <style>
        body { font-family: 'Poppins', sans-serif !important; }

        .content-card { 
            background-color: #ffffff; 
            border-radius: 1rem; 
            padding: 1.5rem 2rem; 
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05); 
        }
        .content-card h4 { 
            font-weight: 600; margin-bottom: 0.25rem; 
            margin-bottom: 0.25rem; 
        }
        .content-card p { 
            margin-bottom: 0.25rem; 
        }
        .content-card hr { 
            margin: 1.5rem 0; 
            border: 0;
            border-top: 1px solid #dee2e6;
            opacity: 1;
        }
        .participant-badge { 
            background-color: #d1f3e0; 
            color: #15623d; 
            border-radius: 20px; 
            padding: 6px 15px; 
            font-size: 0.9rem; 
            display: inline-flex; 
            align-items: center; 
            margin-right: 8px; 
        }
        .participant-badge i { 
            margin-right: 5px; 
        }
        .btn.btn-back{
          background-color: #00C853 !important; 
          border-color: #00C853 !important;
          color: #ffffff !important;
          font-weight: bold;
          text-decoration: none !important;
        }
        .btn.btn-back:hover, .btn.btn-back:focus{
          background-color: #02913f !important; 
          border-color: #02913f !important;
        }
        /* Search peserta group styling */
        .search-peserta-group {
          border: 1.5px solid #dee2e6;
          border-radius: 8px;
          overflow: hidden;
          transition: border-color 0.2s ease;
        }
        .search-peserta-group:focus-within {
          border-color: #00C853;
        }
        .search-peserta-group:focus-within .bi-search {
          color: #00C853;
        }
    </style>
</head>
<body>
    
    <!-- sidebar -->
    <?php include __DIR__ . '/../Nav_Side_Bar/sidebar_peserta.php'; ?>

    <!-- Header / Top Bar -->
    <?php 
    $pageTitle = 'Detail Notulen';
    $userName = $sessionUserName ?? ($_SESSION['user_name'] ?? 'Peserta');
    $userPhoto = $userPhoto ?? null;
    include __DIR__ . '/../Nav_Side_Bar/header_peserta.php'; 
    ?>

    <div class="main-content">


        <!-- Detail Notulen -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($notulen['judul']); ?></h4>
                    <p class="text-muted mb-2">Dibuat oleh: <?= htmlspecialchars($created_by); ?></p>
                </div>
                <div class="text-end">
                    <p class="fw-semibold mb-0">Tanggal Rapat:</p>
                    <p class="mb-0"><?= htmlspecialchars($tanggal); ?></p>
                </div>
            </div>

            <hr>

            <h6 class="fw-semibold mb-3">Isi Notulen:</h6>
            <div class="mb-4">
                <?= $notulen['hasil'] ?? ''; // Isi rapat ?>
            </div>

            <hr>

            <h6 class="fw-semibold mb-3">Lampiran:</h6>
            <?php if (!empty($lampiranList)): ?>
                <?php foreach($lampiranList as $lamp): ?>
                    <div class="mb-4">
                        <h6 class="fw-bold mb-2 text-dark"><?= htmlspecialchars($lamp['judul_lampiran']) ?></h6>
                        <div class="d-flex gap-2">
                             <a href="../uploads/<?= htmlspecialchars($lamp['file_lampiran']); ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="bi bi-eye me-2"></i>Lihat Lampiran
                            </a>
                            <a href="../uploads/<?= htmlspecialchars($lamp['file_lampiran']); ?>" class="btn btn-outline-success btn-sm" download>
                                <i class="bi bi-download me-2"></i>Unduh Lampiran
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback Legacy -->
                <?php if (!empty($notulen['tindak_lanjut'])): 
                     $files = explode('|', $notulen['tindak_lanjut']);
                     $files = array_filter(array_map('trim', $files));
                     if (!empty($files)):
                        foreach($files as $f):
                ?>
                    <div class="mb-4">
                        <div class="d-flex gap-2">
                             <a href="../uploads/<?= htmlspecialchars($f); ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="bi bi-eye me-2"></i>Lihat Lampiran
                            </a>
                            <a href="../uploads/<?= htmlspecialchars($f); ?>" class="btn btn-outline-success btn-sm" download>
                                <i class="bi bi-download me-2"></i>Unduh Lampiran
                            </a>
                        </div>
                    </div>
                <?php endforeach; 
                     else: ?>
                        <p class="text-muted">Tidak ada lampiran.</p>
                     <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted">Tidak ada lampiran.</p>
                <?php endif; ?>
            <?php endif; ?>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-people-fill me-2 text-success"></i>Peserta Rapat</h6>
                    <div class="input-group search-peserta-group" style="width: 280px;">
                        <span class="input-group-text bg-white border-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchPeserta" class="form-control border-0 shadow-none ps-2" placeholder="Cari peserta..." style="font-size: 0.9rem;">
                    </div>
                </div>
                <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                    <div class="list-group list-group-flush" id="participantList">
                       <!-- Data will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <a href="dashboard_peserta.php" class="btn btn-back"></i> Kembali</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
    <script>
        // Fungsi Logout
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

        // Data Peserta dari PHP
        // Data Peserta dari PHP
        const participants = <?= json_encode($peserta_details ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE); ?> || [];
        const participantList = document.getElementById('participantList');
        const searchInput = document.getElementById('searchPeserta');

        // Render Function
        function renderPeserta(data) {
            participantList.innerHTML = '';

            if (data.length === 0) {
                participantList.innerHTML = '<div class="p-4 text-center text-muted small">Peserta tidak ditemukan.</div>';
                return;
            }

            // Render loop
            data.forEach((pd, index) => {
                const nama = escapeHtml(pd.nama || '');
                const email = escapeHtml(pd.email || '');
                // NIK tidak ditampilkan di UI tapi bisa dicari
                
                // Foto Logic
                let photoHtml = '';
                if (pd.foto && pd.foto !== '') {
                    const photoPath = `../uploads/${encodeURIComponent(pd.foto)}`;
                    photoHtml = `<img src="${photoPath}" class="rounded-circle me-3 border" style="width: 38px; height: 38px; object-fit: cover; flex-shrink: 0;" onerror="handleImageError(this)">`;
                } else {
                    photoHtml = `<div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3 border" style="width: 38px; height: 38px; flex-shrink: 0;"><i class="bi bi-person-fill text-secondary fs-5"></i></div>`;
                }

                const item = document.createElement('div');
                item.className = 'list-group-item d-flex align-items-center py-3 px-3 border-bottom-0 border-top-0 border-end-0 border-start-0';
                item.innerHTML = `
                    <span class="me-3 fw-bold text-secondary small" style="min-width: 25px;">${index + 1}.</span>
                    ${photoHtml}
                    <div class="flex-grow-1">
                        <div class="fw-medium text-dark name-text">${nama}</div>
                        ${email ? `<div class="text-muted small" style="font-size: 0.75rem;">${email}</div>` : ''}
                    </div>
                `;
                participantList.appendChild(item);
            });
        }

        // Image Error Handler
        function handleImageError(img) {
            img.onerror = null;
            const fallback = document.createElement('div');
            fallback.className = 'bg-light rounded-circle d-flex align-items-center justify-content-center me-3 border';
            fallback.style.width = '38px';
            fallback.style.height = '38px';
            fallback.style.flexShrink = '0';
            fallback.innerHTML = '<i class="bi bi-person-fill text-secondary fs-5"></i>';
            img.parentNode.replaceChild(fallback, img);
        }

        // Escape HTML helper
        function escapeHtml(text) {
             if (!text) return '';
             return String(text)
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        }

        // Filter Function
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const keyword = this.value.toLowerCase();
                const filtered = participants.filter(pd => {
                    const nama = (pd.nama || '').toLowerCase();
                    const email = (pd.email || '').toLowerCase();
                    const nik = (pd.nik || '').toLowerCase();
                    
                    return nama.includes(keyword) || email.includes(keyword) || nik.includes(keyword);
                });
                renderPeserta(filtered);
            });
        }

        // Initial Render
        renderPeserta(participants);
    </script>
</body>
</html>
