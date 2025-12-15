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
                'message' => 'Nomor atau pesan kosong'
            ];
        }
        
        // Normalize nomor WhatsApp
        $nomor = $this->normalizePhoneNumber($nomor);
        
        // Cek validasi nomor (minimal 10 digit)
        if (!$this->isValidPhoneNumber($nomor)) {
            return [
                'success' => false,
                'message' => 'Format nomor WhatsApp tidak valid'
            ];
        }
        
        try {
            // Coba kirim dengan method yang tersedia
            $sent = false;
            $errorMsg = '';
            
            // 1. Coba Meta API (jika dikonfigurasi di masa depan)
            if ($this->useApi && !empty($this->metaApiKey)) {
                list($sent, $errorMsg) = $this->sendViaAPI($nomor, $pesan);
            }
            
            // 2. Fallback ke wa.me (selalu berhasil - hanya generate link)
            if (!$sent) {
                // wa.me fallback selalu dianggap "sent" karena hanya generate link
                // Tidak ada logging database lagi per request user
                $sent = true; 
            }
            
            return [
                'success' => true,
                'message' => 'Pesan WhatsApp berhasil disiapkan'
            ];
            
        } catch (Exception $e) {
            error_log('WhatsApp Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal mengirim pesan: ' . $e->getMessage()
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
     * DEPRECATED: Logging database dinonaktifkan
     */
    public function getLogByUser($userId, $limit = 10) {
        return [];
    }
    
    /**
     * Ambil semua log (untuk admin)
     * DEPRECATED: Logging database dinonaktifkan
     */
    public function getAllLogs($limit = 50, $offset = 0, $status = null) {
        return [];
    }
    
    /**
     * Hitung total log
     * DEPRECATED: Logging database dinonaktifkan
     */
    public function countLogs($status = null) {
        return 0;
    }
}

?>
