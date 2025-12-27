<?php
/**
 * Konfigurasi Aplikasi SmartNote
 * 
 * File ini berisi semua konfigurasi aplikasi termasuk:
 * - API Keys (Fonnte WhatsApp)
 * - Pengaturan fitur
 */

// =============================================
// FONNTE WHATSAPP API CONFIGURATION
// =============================================

// API Key dari Fonnte (dapatkan dari https://fonnte.com)
define('FONNTE_API_KEY', '5834MBGm36Uqb3bA1rCt');

// Endpoint API Fonnte
define('FONNTE_API_URL', 'https://api.fonnte.com/send');

// Toggle untuk mengaktifkan/menonaktifkan Fonnte API
// Set ke true untuk menggunakan Fonnte, false untuk fallback ke wa.me
define('USE_FONNTE_API', true);

// =============================================
// WHATSAPP NOTIFICATION SETTINGS
// =============================================

// Kirim notifikasi WhatsApp saat notulen baru dibuat
define('SEND_WA_ON_NOTULEN_CREATE', true);

// Kirim notifikasi WhatsApp saat peserta baru ditambahkan
define('SEND_WA_ON_PARTICIPANT_ADD', true);

?>
