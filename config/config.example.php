<?php
/**
 * ====================================================================
 * PANDUAN SETUP DATABASE UNTUK TIM DEVELOPER
 * ====================================================================
 * 1. DILARANG MENGUBAH FILE database.php!
 * 2. Copy file ini, lalu rename hasil copy-nya menjadi: config.local.php
 * 3. File config.local.php TIDAK AKAN ter-push ke GitHub.
 * 4. Buka config.local.php Anda, dan aktifkan DSN sesuai OS laptop Anda.
 */

// Base URL (Ganti jika folder htdocs/laragon Anda berbeda)
define('BASE_URL', '/medical-web');

// ==========================================
// PILIH SALAH SATU DSN DI BAWAH INI
// ==========================================

// -> OPSI 1: Untuk pengguna WINDOWS / MAC (XAMPP Biasa / Laragon)
// define('DB_DSN', 'mysql:host=127.0.0.1;dbname=backbone_medweb;charset=utf8mb4');

// -> OPSI 2: Untuk pengguna LINUX (XAMPP Linux/LAMPP)
// define('DB_DSN', 'mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=backbone_medweb;charset=utf8mb4');

// ==========================================
// KREDENSIAL DATABASE LOKAL
// ==========================================
define('DB_USER', 'root');
define('DB_PASS', ''); // Isi dengan password mysql laptop masing-masing (jika ada)