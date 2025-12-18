<?php
include '../config_admin/db_detail_rapat_admin.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Rapat</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <style>
        .participant-badge {
            display: inline-block;
            margin: 4px 6px 4px 0;
            padding: 6px 10px;
            background: #f1f7f3;
            border-radius: 18px;
            font-size: 14px;
            color: #2d6a4f;
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
        /* Section title with accent border */
        .section-title {
          border-left: 4px solid #00C853;
          padding-left: 12px;
          margin-bottom: 1rem;
          font-weight: 600;
        }
        .section-divider {
          border: 0;
          border-top: 1px solid #e9ecef;
          margin: 1.5rem 0;
        }
    </style>
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
    $pageTitle = "Detail Notulen";
    // sidebar
    include '../Nav_Side_Bar/sidebar.php'; 
    
    // header
    include '../Nav_Side_Bar/header.php';
?>

    <!-- Main -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3"></div>

        <!-- Detail Rapat -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($notulen['judul']); ?></h4>
                    <p class="text-muted mb-2">Dibuat oleh: <?= htmlspecialchars($notulen['created_by'] ?? 'Admin'); ?>
                    </p>
                </div>
                <div class="text-end">
                    <p class="fw-semibold mb-0">Tanggal Rapat:</p>
                    <p class="mb-0"><?= $tanggal; ?></p>
                </div>
            </div>

            <hr>

            <h6 class="fw-semibold mb-3">Isi Notulen:</h6>
            <div class="mb-4">
                <?= $notulen['hasil']; // Isi rapat biasanya HTML dari TinyMCE, jadi tidak di-escape ?>
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
                <!-- Fallback to legacy check if needed, or just show empty -->
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
                <a href="dashboard_admin.php" class="btn btn-back"></i> Kembali</a>
            </div>
        </div>
    </div>

    <script>
        // Data Peserta dari PHP
        window.detailRapatParticipants = <?= json_encode($peserta_details ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE); ?> || [];
    </script>
    
    <!-- Bootstrap JS bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
    <script src="../js/detail_rapat.js"></script>
</body>

</html>
