<?php
include '../config_admin/db_dashboard.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Notulis</title>

    <!-- Bootstrap CSS & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/admin.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <style>
    #filterPembuat {
        padding-left: 2px !important;
    }
</style>
</head>

<body>
<?php 
    $pageTitle = "Dashboard Notulis";
    // sidebar
    include '../Nav_Side_Bar/sidebar.php';
    // header
    include '../Nav_Side_Bar/header.php';
?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Highlight Cards -->

        <!-- Card 1: Total Peserta -->
        <div class="row g-3 mb-4 row-cols-1 row-cols-md-3">
            <div class="col">
                <div class="highlight-card h-100 p-3 rounded-3 border-success shadow-sm d-flex align-items-center justify-content-center gap-3 bg-white" style="border: 1px solid #198754;">
                    <i class="bi bi-people text-success" style="font-size: 3.5rem;"></i>
                    <div class="text-center">
                        <h6 class="text-secondary mb-1">Total Peserta</h6>
                        <h2 id="totalPesertaCard" class="fw-bold text-success mb-0"><?php echo $totalPeserta; ?></h2>
                        <small class="text-muted">Orang</small>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Notulen -->
            <div class="col">
                <div class="highlight-card h-100 p-3 rounded-3 border-success shadow-sm d-flex align-items-center justify-content-center gap-3 bg-white" style="border: 1px solid #198754;">
                    <i class="bi bi-file-earmark-text text-success" style="font-size: 3.5rem;"></i>
                    <div class="text-center">
                        <h6 class="text-secondary mb-1">Total Notulen</h6>
                        <h2 id="totalNotulenCard" class="fw-bold text-success mb-0"><?php echo $totalNotulen; ?></h2>
                        <small class="text-muted">Dokumen</small>
                    </div>
                </div>
            </div>

            <!-- Card 3: Status Notulen -->
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

        <!-- TABLE AREA -->
        <section class="table-wrapper">
            <div class="table-header d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <h5 class="fw-semibold mb-2 mb-sm-0">Daftar Notulen</h5>

                <!-- Controls -->
                <div class="d-flex gap-2 flex-wrap controls align-items-center">
                    <div class="tambah-container">
                        <a href="notulen_admin.php" class="btn-tambah" role="button"><i
                                class="bi bi-plus-circle"></i>Tambah notulen</a>
                    </div>
                    
                    <select id="filterPembuat" class="form-select form-select-sm border-success"
                        aria-label="Filter pembuat">
                        <option value="">Semua Pembuat</option>
                    </select>

                    <select id="rowsPerPage" class="form-select form-select-sm border-success"
                        aria-label="Jumlah tampil">
                        <option value="5">5 data</option>
                        <option value="10" selected>10 data</option>
                        <option value="20">20 data</option>
                        <option value="all">Semua</option>
                    </select>

                    <div class="search-table">
                        <input type="text" id="searchInput" class="form-control form-control-sm border-success"
                            placeholder="Cari notulen..." aria-label="Cari notulen" />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <!-- List Container -->
            <!-- List Container -->
            <div id="notulenList" class="row g-3 row-cols-1 row-cols-md-3 row-cols-xl-5"></div>

            <!-- Pagination & info -->
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <small class="text-muted" id="dataInfo"></small>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
                </nav>
            </div>
        </section>
    </main>

    <!-- Bootstrap JS bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
    <script>
        // Pass PHP data to JavaScript
        window.notulenDataFromPHP = <?= json_encode($dataNotulen, JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="../js/dashboard.js"></script>
</body>
</html>