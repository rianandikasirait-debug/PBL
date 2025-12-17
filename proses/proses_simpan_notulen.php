<?php
session_start();
// Mulai session untuk akses data session (mis. user_name)

// Sertakan file koneksi database ($conn diasumsikan tersedia)
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config.php';
error_reporting(0); // Suppress errors to allow JSON response

// Pastikan request menggunakan metode POST — endpoint ini hanya menerima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

// Ambil field form dan beri nilai default jika tidak ada
$judul = trim($_POST['judul'] ?? '');
$tanggal = $_POST['tanggal'] ?? '';
$isi = $_POST['isi'] ?? '';
// Peserta bisa dikirim sebagai array (peserta[]) atau satu nilai
$peserta_ids = $_POST['peserta'] ?? [];

// Validasi wajib: judul, tanggal, dan isi harus diisi
if ($judul === '' || $tanggal === '' || $isi === '') {
    echo json_encode(['success' => false, 'message' => 'Judul, tanggal, isi wajib diisi']);
    exit;
}

// File upload handled after insertion into tb_lampiran

// ---------- Prepare peserta CSV ----------
// ---------- Prepare peserta CSV ----------
$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$clean = [];

if (is_array($peserta_ids) && count($peserta_ids) > 0) {
    // Input Provided: Sanitize
    $clean = array_map('intval', $peserta_ids);
} else {
    // No Input: Fallback fetch all 'peserta'
    $stmtAll = $conn->prepare("SELECT id FROM users WHERE role = 'peserta'");
    $stmtAll->execute();
    $resAll = $stmtAll->get_result();
    while ($row = $resAll->fetch_assoc()) {
        $clean[] = (int)$row['id'];
    }
}

// FinalSanitization
$clean = array_unique(array_filter($clean, function($v) { return $v > 0; }));
$peserta_csv = implode(',', $clean);

// Ensure data limits match database schema to prevent errors
// title varchar(50), peserta varchar(255)
// Limit validation for title
if (strlen($judul) > 50) {
    $judul = substr($judul, 0, 50);
}
// Peserta limit removed (LONGTEXT supported)

// ---------- Insert notulen ----------
// Siapa yang membuat notulen — ambil dari session (jika tersedia), fallback 'Admin'
$created_by = $_SESSION['user_name'] ?? 'Admin';

// Siapkan statement INSERT
$userId = (int) $_SESSION['user_id'];
$status = $_POST['status'] ?? 'draft'; 
$tempat = ''; 
// Legacy: 'tindak_lanjut' column was used for single file. We leave it empty now.
$legacyFileCol = ''; 

$stmt = $conn->prepare("INSERT INTO tambah_notulen (id_user, judul, tanggal, tempat, peserta, hasil, tindak_lanjut, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('isssssss', $userId, $judul, $tanggal, $tempat, $peserta_csv, $isi, $legacyFileCol, $status);

// Eksekusi dan berikan respons JSON sesuai hasil
if ($stmt->execute()) {
    $notulenId = $stmt->insert_id;
    
    // ---------- Handle Multiple Attachments (tb_lampiran) ----------
    $uploadErrors = [];
    
    if (isset($_FILES['file_lampiran']) && isset($_POST['judul_lampiran'])) {
        $files = $_FILES['file_lampiran'];
        $titles = $_POST['judul_lampiran'];
        $count = count($files['name']);
        
        // Prepare insert statement for lampiran
        $stmtLampiran = $conn->prepare("INSERT INTO tb_lampiran (id_notulen, judul_lampiran, file_lampiran) VALUES (?, ?, ?)");
        
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmp = $files['tmp_name'][$i];
                $originalName = basename($files['name'][$i]);
                $title = trim($titles[$i]);
                if (empty($title)) $title = $originalName; // Fallback title
                
                $safeName = time() . '_' . $i . '_' . preg_replace('/[^a-z0-9\-_.]/i', '_', $originalName);
                $dest = __DIR__ . '/../file/' . $safeName;
                
                if (move_uploaded_file($tmp, $dest)) {
                    $stmtLampiran->bind_param('iss', $notulenId, $title, $safeName);
                    $stmtLampiran->execute();
                } else {
                    $uploadErrors[] = "Gagal upload: $originalName";
                }
            }
        }
    }

    // ---------- Send WhatsApp Notifications to Participants ----------
    $waErrors = [];
    
    if (defined('SEND_WA_ON_NOTULEN_CREATE') && SEND_WA_ON_NOTULEN_CREATE === true && !empty($clean)) {
        require_once __DIR__ . '/../includes/whatsapp.php';
        $waManager = new WhatsAppManager($conn);
        
        // Ambil informasi peserta yang dipilih
        $participantIds = implode(',', $clean);
        $stmtParticipants = $conn->prepare("SELECT id, nama, nomor_whatsapp FROM users WHERE id IN ($participantIds) AND nomor_whatsapp IS NOT NULL AND nomor_whatsapp != ''");
        
        if ($stmtParticipants) {
            $stmtParticipants->execute();
            $participants = $stmtParticipants->get_result();
            
            // Format pesan WhatsApp
            $pesanTemplate = "📋 *Undangan Rapat - SmartNote*\n\n";
            $pesanTemplate .= "Anda diundang untuk menghadiri rapat:\n";
            $pesanTemplate .= "📌 Judul: {$judul}\n";
            $pesanTemplate .= "📅 Tanggal: " . date('d F Y', strtotime($tanggal)) . "\n\n";
            $pesanTemplate .= "Silakan cek detail lengkap di aplikasi SmartNote.\n\n";
            $pesanTemplate .= "_Terima kasih atas partisipasinya_ 🙏";
            
            // Kirim ke setiap peserta
            while ($participant = $participants->fetch_assoc()) {
                $pesan = str_replace('{nama}', $participant['nama'], $pesanTemplate);
                $result = $waManager->sendMessage(
                    $participant['id'],
                    $participant['nomor_whatsapp'],
                    $pesan
                );
                
                if (!$result['success']) {
                    $waErrors[] = "Gagal kirim ke {$participant['nama']}: {$result['message']}";
                }
            }
            
            $stmtParticipants->close();
        }
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Notulen berhasil disimpan', 
        'upload_errors' => $uploadErrors,
        'wa_errors' => $waErrors,
        'wa_sent' => count($clean) - count($waErrors)
    ]);
} else {
    // Jika gagal, kembalikan pesan error (berisi $stmt->error)
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan notulen: ' . $stmt->error]);
}
exit;
?>
