<?php
// Final complete fix for dashboard_peserta.php

$file = 'c:/laragon/www/smartnote/peserta/dashboard_peserta.php';
$content = file_get_contents($file);

// Step 1: Update PHP queries
$oldQueries = '// Ambil data untuk highlight cards
// 1. Total Peserta
$sqlPeserta = "SELECT COUNT(*) as total FROM users WHERE role = \'peserta\'";
$resPeserta = $conn->query($sqlPeserta);
$totalPeserta = $resPeserta ? $resPeserta->fetch_assoc()[\'total\'] : 0;

// 2. Total Notulen
$sqlNotulen = "SELECT COUNT(*) as total FROM tambah_notulen";
$resNotulen = $conn->query($sqlNotulen);
$totalNotulen = $resNotulen ? $resNotulen->fetch_assoc()[\'total\'] : 0;

// 3. Total Notulen Belum Dilihat (Unread)
$viewedIds = $_SESSION[\'viewed_notulen\'] ?? [];
$viewedIds = array_map(\'intval\', $viewedIds); // Sanitize to ints
$viewedIds = array_filter($viewedIds); // Remove 0s if any

if (empty($viewedIds)) {
    // If no viewed notulens, unread = total
    $totalUnread = $totalNotulen;
} else {
    $idsStr = implode(\',\', $viewedIds);
    $sqlUnread = "SELECT COUNT(*) as total FROM tambah_notulen WHERE id NOT IN ($idsStr)";
    $resUnread = $conn->query($sqlUnread);
    $totalUnread = $resUnread ? $resUnread->fetch_assoc()[\'total\'] : $totalNotulen;
}';

$newQueries = '// Ambil data untuk highlight cards - HANYA untuk peserta ini
$currentUserId = $_SESSION[\'user_id\'];

// 1. Total Notulen untuk peserta ini
$sqlNotulen = "SELECT COUNT(*) as total FROM tambah_notulen WHERE FIND_IN_SET(?, peserta) > 0";
$stmtNotulen = $conn->prepare($sqlNotulen);
$stmtNotulen->bind_param("i", $currentUserId);
$stmtNotulen->execute();
$resNotulen = $stmtNotulen->get_result();
$totalNotulen = $resNotulen ? $resNotulen->fetch_assoc()[\'total\'] : 0;
$stmtNotulen->close();

// 2. Total Notulen by Status (Draft and Final) untuk peserta ini
$sqlDraft = "SELECT COUNT(*) as total FROM tambah_notulen WHERE FIND_IN_SET(?, peserta) > 0 AND (status = \'draft\' OR status IS NULL)";
$stmtDraft = $conn->prepare($sqlDraft);
$stmtDraft->bind_param("i", $currentUserId);
$stmtDraft->execute();
$resDraft = $stmtDraft->get_result();
$totalDraft = $resDraft ? $resDraft->fetch_assoc()[\'total\'] : 0;
$stmtDraft->close();

$sqlFinal = "SELECT COUNT(*) as total FROM tambah_notulen WHERE FIND_IN_SET(?, peserta) > 0 AND status = \'final\'";
$stmtFinal = $conn->prepare($sqlFinal);
$stmtFinal->bind_param("i", $currentUserId);
$stmtFinal->execute();
$resFinal = $stmtFinal->get_result();
$totalFinal = $resFinal ? $resFinal->fetch_assoc()[\'total\'] : 0;
$stmtFinal->close();';

$content = str_replace($oldQueries, $newQueries, $content);

// Step 2: Update notulen query
$oldNotulenQuery = '// Query untuk mengambil semua notulen (untuk sementara tidak filter, agar kita bisa debug)
$sql = "SELECT id, judul_rapat, tanggal_rapat, created_by, Lampiran, peserta, created_at 
        FROM tambah_notulen 
        ORDER BY tanggal_rapat DESC";

$result = $conn->query($sql);

// Konversi ke format array dan tambahkan status is_viewed
$dataNotulen = [];
if ($result) {
    $viewedIdsSession = $_SESSION[\'viewed_notulen\'] ?? [];
    while ($row = $result->fetch_assoc()) {
        $row[\'is_viewed\'] = in_array((int)$row[\'id\'], $viewedIdsSession);
        $dataNotulen[] = $row;
    }
}';

$newNotulenQuery = '// Query untuk mengambil notulen yang peserta ini terdaftar di dalamnya
$currentUserId = $_SESSION[\'user_id\'];
$sql = "SELECT id, judul, tanggal, tempat, peserta, tindak_lanjut as Lampiran, created_at,
                COALESCE(status, \'draft\') as status
        FROM tambah_notulen 
        WHERE FIND_IN_SET(?, peserta) > 0
        ORDER BY tanggal DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

// Konversi ke format array dan tambahkan status is_viewed
$dataNotulen = [];
if ($result) {
    $viewedIdsSession = $_SESSION[\'viewed_notulen\'] ?? [];
    while ($row = $result->fetch_assoc()) {
        $row[\'is_viewed\'] = in_array((int)$row[\'id\'], $viewedIdsSession);
        // Tambahkan alias untuk kompatibilitas dengan JavaScript
        $row[\'judul_rapat\'] = $row[\'judul\'];
        $row[\'tanggal_rapat\'] = $row[\'tanggal\'];
        $row[\'created_by\'] = $row[\'tempat\'];
        $dataNotulen[] = $row;
    }
}
$stmt->close();';

$content = str_replace($oldNotulenQuery, $newNotulenQuery, $content);

// Step 3: Update HTML cards
$oldHTML = '        <!-- Highlight Cards -->
        <div class="row g-3 mb-4 row-cols-1 row-cols-md-3">
            <!-- Card 1: Total Peserta -->
            <div class="col">
                <div class="highlight-card h-100 p-3 rounded-3 border-success shadow-sm d-flex flex-column justify-content-center align-items-center text-center bg-white" style="border: 1px solid #198754;">
                    <h6 class="text-secondary mb-2">Total Peserta</h6>
                    <h2 id="totalPesertaCard" class="fw-bold text-success mb-0"><?php echo $totalPeserta; ?></h2>
                    <small class="text-muted">Orang</small>
                </div>
            </div>

            <!-- Card 2: Total Notulen -->
            <div class="col">
                <div class="highlight-card h-100 p-3 rounded-3 border-success shadow-sm d-flex flex-column justify-content-center align-items-center text-center bg-white" style="border: 1px solid #198754;">
                    <h6 class="text-secondary mb-2">Total Notulen</h6>
                    <h2 id="totalNotulenCard" class="fw-bold text-success mb-0"><?php echo $totalNotulen; ?></h2>
                    <small class="text-muted">Dokumen</small>
                </div>
            </div>

            <!-- Card 3: Belum Dilihat -->
            <div class="col">
                <div class="highlight-card h-100 p-3 rounded-3 border-success shadow-sm d-flex flex-column justify-content-center align-items-center text-center bg-white" style="border: 1px solid #198754;">
                    <h6 class="text-secondary mb-2">Belum Dilihat</h6>
                    <h2 id="totalUnreadCard" class="fw-bold text-danger mb-0"><?php echo $totalUnread; ?></h2>
                    <small class="text-muted">Notulen</small>
                </div>
            </div>
        </div>';

$newHTML = '        <!-- Highlight Cards -->
        <div class="row g-3 mb-4 row-cols-1 row-cols-md-2">
            <!-- Card 1: Total Notulen -->
            <div class="col">
                <div class="highlight-card h-100 p-3 rounded-3 border-success shadow-sm d-flex flex-column justify-content-center align-items-center text-center bg-white" style="border: 1px solid #198754;">
                    <h6 class="text-secondary mb-2">Total Notulen</h6>
                    <h2 id="totalNotulenCard" class="fw-bold text-success mb-0"><?php echo $totalNotulen; ?></h2>
                    <small class="text-muted">Dokumen</small>
                </div>
            </div>

            <!-- Card 2: Status Notulen -->
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
        </div>';

$content = str_replace($oldHTML, $newHTML, $content);

// Save
file_put_contents($file, $content);

echo "Dashboard peserta fixed successfully!\n";
