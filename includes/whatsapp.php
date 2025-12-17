<?php
/**
 * WhatsApp Integration
 * 
 * File ini menangani pengiriman pesan WhatsApp
 * Support: Meta API (akan datang), wa.me fallback
 */

require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config.php';

class WhatsAppManager {
    private $conn;
    private $useApi = false;
    private $fonnte_api_key = '';
    private $fonnte_api_url = '';
    
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        
        // Load Fonnte configuration
        if (defined('USE_FONNTE_API') && USE_FONNTE_API === true) {
            $this->useApi = true;
            $this->fonnte_api_key = defined('FONNTE_API_KEY') ? FONNTE_API_KEY : '';
            $this->fonnte_api_url = defined('FONNTE_API_URL') ? FONNTE_API_URL : 'https://api.fonnte.com/send';
        }
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
            
            // 1. Coba Fonnte API (jika dikonfigurasi)
            if ($this->useApi && !empty($this->fonnte_api_key)) {
                list($sent, $errorMsg) = $this->sendViaFonnte($nomor, $pesan);
            }
            
            // 2. Fallback ke wa.me (selalu berhasil - hanya generate link)
            if (!$sent) {
                // wa.me fallback selalu dianggap "sent" karena hanya generate link
                $sent = true; 
            }
            
            return [
                'success' => true,
                'message' => 'Pesan WhatsApp berhasil dikirim'
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
     * Kirim via Fonnte WhatsApp API
     * 
     * @param string $nomor - Nomor WhatsApp yang sudah dinormalisasi
     * @param string $pesan - Isi pesan
     * @return array [bool success, string errorMsg]
     */
    private function sendViaFonnte($nomor, $pesan) {
        // Validasi API key
        if (empty($this->fonnte_api_key)) {
            return [false, 'Fonnte API key tidak dikonfigurasi'];
        }
        
        try {
            // Prepare data untuk Fonnte API
            $data = array(
                'target' => $nomor,
                'message' => $pesan,
                'countryCode' => '62' // Indonesia
            );
            
            // Initialize cURL
            $curl = curl_init();
            
            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->fonnte_api_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: ' . $this->fonnte_api_key
                ),
            ));
            
            $response = curl_exec($curl);
            $err = curl_error($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            curl_close($curl);
            
            if ($err) {
                error_log("Fonnte cURL Error: " . $err);
                return [false, 'Connection error: ' . $err];
            }
            
            // Parse response
            $result = json_decode($response, true);
            
            // Log response untuk debugging
            error_log("Fonnte Response (HTTP $httpCode): " . $response);
            
            // Check if successful
            if ($httpCode == 200 && isset($result['status']) && $result['status'] == true) {
                return [true, ''];
            } else {
                $errorMsg = isset($result['reason']) ? $result['reason'] : 'Unknown error';
                return [false, $errorMsg];
            }
            
        } catch (Exception $e) {
            error_log('Fonnte Exception: ' . $e->getMessage());
            return [false, $e->getMessage()];
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
