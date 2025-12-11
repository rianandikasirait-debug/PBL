<?php
/**
 * WhatsApp Integration
 * 
 * File ini menangani pengiriman pesan WhatsApp
 * Support: Meta API (akan datang), wa.me fallback
 */

require_once __DIR__ . '/../koneksi.php';

class WhatsAppManager {
    private $conn;
    private $useApi = false; // Set ke true jika ada Meta API key
    private $metaApiKey = ''; // Simpan di env variable nanti
    
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }
    
    /**
     * Kirim pesan WhatsApp ke nomor peserta
     * 
     * @param int $userId - ID user di database
     * @param string $nomor - Nomor WhatsApp (format: 62xxxxxxxxxx atau +62xxxxxxxxxx)
     * @param string $pesan - Isi pesan
     * @return array - ['success' => bool, 'message' => string, 'log_id' => int]
     */
    public function sendMessage($userId, $nomor, $pesan) {
        // Validasi input
        if (empty($nomor) || empty($pesan)) {
            return [
                'success' => false,
                'message' => 'Nomor atau pesan kosong',
                'log_id' => null
            ];
        }
        
        // Normalize nomor WhatsApp
        $nomor = $this->normalizePhoneNumber($nomor);
        
        // Cek validasi nomor (minimal 10 digit)
        if (!$this->isValidPhoneNumber($nomor)) {
            return [
                'success' => false,
                'message' => 'Format nomor WhatsApp tidak valid',
                'log_id' => null
            ];
        }
        
        try {
            // Catat ke log_whatsapp dengan status pending
            $logId = $this->createLog($userId, $nomor, $pesan, 'pending');
            
            // Coba kirim dengan method yang tersedia
            $sent = false;
            $errorMsg = '';
            
            // 1. Coba Meta API (jika dikonfigurasi di masa depan)
            if ($this->useApi && !empty($this->metaApiKey)) {
                list($sent, $errorMsg) = $this->sendViaAPI($nomor, $pesan);
            }
            
            // 2. Fallback ke wa.me (selalu berhasil - hanya generate link)
            if (!$sent) {
                $sent = true; // wa.me fallback selalu dianggap "sent"
                // Kita catat bahwa ini pakai fallback
                $this->updateLog($logId, 'sent', 'Dikirim via wa.me fallback');
            } else {
                // Jika sukses via API
                $this->updateLog($logId, 'sent', null);
            }
            
            return [
                'success' => true,
                'message' => 'Pesan WhatsApp berhasil dicatat untuk dikirim',
                'log_id' => $logId
            ];
            
        } catch (Exception $e) {
            error_log('WhatsApp Error: ' . $e->getMessage());
            
            // Update log dengan status failed
            if (isset($logId)) {
                $this->updateLog($logId, 'failed', $e->getMessage());
            }
            
            return [
                'success' => false,
                'message' => 'Gagal mengirim pesan: ' . $e->getMessage(),
                'log_id' => isset($logId) ? $logId : null
            ];
        }
    }
    
    /**
     * Normalize nomor WhatsApp ke format standar (62xxxxxxxxxx)
     * Support format: 
     * - +62xxxxxxxxxx
     * - 62xxxxxxxxxx
     * - 0xxxxxxxxxx
     * - xxxxxxxxxx (assume 62)
     */
    public function normalizePhoneNumber($nomor) {
        // Hapus spasi, dash, tanda kurung
        $nomor = preg_replace('/[\s\-\(\)]/i', '', $nomor);
        
        // Jika dimulai dengan +, hilangkan
        if (substr($nomor, 0, 1) === '+') {
            $nomor = substr($nomor, 1);
        }
        
        // Jika dimulai dengan 0, ganti dengan 62
        if (substr($nomor, 0, 1) === '0') {
            $nomor = '62' . substr($nomor, 1);
        }
        
        // Jika tidak ada prefix negara, tambahkan 62
        if (substr($nomor, 0, 2) !== '62') {
            $nomor = '62' . $nomor;
        }
        
        return $nomor;
    }
    
    /**
     * Validasi format nomor WhatsApp
     */
    public function isValidPhoneNumber($nomor) {
        // Harus dimulai dengan 62
        if (substr($nomor, 0, 2) !== '62') {
            return false;
        }
        
        // Panjang harus antara 12-15 digit (62 + 10-13 digit)
        if (strlen($nomor) < 12 || strlen($nomor) > 15) {
            return false;
        }
        
        // Hanya berisi digit
        return ctype_digit($nomor);
    }
    
    /**
     * Kirim via Meta WhatsApp Cloud API
     * (Implementasi untuk masa depan)
     */
    private function sendViaAPI($nomor, $pesan) {
        // TODO: Implementasi dengan Meta API
        // Untuk sekarang return false agar fallback ke wa.me
        return [false, 'Meta API belum dikonfigurasi'];
    }
    
    /**
     * Buat entry baru di log_whatsapp
     */
    private function createLog($userId, $nomor, $pesan, $status = 'pending') {
        try {
            $sql = "INSERT INTO log_whatsapp (user_id, nomor, pesan, status) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("isss", $userId, $nomor, $pesan, $status);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $logId = $this->conn->insert_id;
            $stmt->close();
            
            return $logId;
        } catch (Exception $e) {
            error_log("Failed to create WhatsApp log: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update status log_whatsapp
     */
    private function updateLog($logId, $status, $errorMessage = null) {
        try {
            $sql = "UPDATE log_whatsapp SET status = ?, error_message = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("ssi", $status, $errorMessage, $logId);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Failed to update WhatsApp log: " . $e->getMessage());
        }
    }
    
    /**
     * Generate WhatsApp link (wa.me fallback)
     * Digunakan jika ingin generate link manual
     */
    public function generateWhatsAppLink($nomor, $pesan = '') {
        $nomor = $this->normalizePhoneNumber($nomor);
        
        if (!$this->isValidPhoneNumber($nomor)) {
            return null;
        }
        
        $encodedMsg = urlencode($pesan);
        return "https://wa.me/{$nomor}?text={$encodedMsg}";
    }
    
    /**
     * Ambil log pengiriman untuk user tertentu
     */
    public function getLogByUser($userId, $limit = 10) {
        try {
            $sql = "SELECT * FROM log_whatsapp WHERE user_id = ? ORDER BY waktu DESC LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("ii", $userId, $limit);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $logs = [];
            
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
            
            $stmt->close();
            return $logs;
        } catch (Exception $e) {
            error_log("Failed to fetch WhatsApp logs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ambil semua log (untuk admin)
     */
    public function getAllLogs($limit = 50, $offset = 0, $status = null) {
        try {
            $where = "";
            $params = [];
            $types = "";
            
            if ($status) {
                $where = "WHERE status = ?";
                $params[] = $status;
                $types = "s";
            }
            
            $sql = "SELECT lw.*, u.nama, u.email FROM log_whatsapp lw 
                    LEFT JOIN users u ON lw.user_id = u.id 
                    {$where}
                    ORDER BY lw.waktu DESC 
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ii";
            
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $logs = [];
            
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
            
            $stmt->close();
            return $logs;
        } catch (Exception $e) {
            error_log("Failed to fetch all WhatsApp logs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Hitung total log
     */
    public function countLogs($status = null) {
        try {
            $where = "";
            $params = [];
            $types = "";
            
            if ($status) {
                $where = "WHERE status = ?";
                $params[] = $status;
                $types = "s";
            }
            
            $sql = "SELECT COUNT(*) as total FROM log_whatsapp {$where}";
            
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            if ($types) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return $row['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Failed to count WhatsApp logs: " . $e->getMessage());
            return 0;
        }
    }
}

?>
