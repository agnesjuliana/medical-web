<?php
/**
 * Database Connection — Modul 0 (BMI Calculator)
 * 
 * Koneksi PDO ke database `modul0_bmi`.
 * Menggunakan credential yang sama dengan database utama (backbone_medweb)
 * yang didefinisikan di config/database.php.
 * 
 * Usage:
 *   require_once __DIR__ . '/../config/db_modul0.php';
 *   $db = getModul0Connection();
 */

require_once __DIR__ . '/database.php'; // Memuat DB_HOST, DB_USER, DB_PASS, DB_CHARSET

define('DB_NAME_MODUL0', 'modul0_bmi');

/**
 * Get PDO connection to modul0_bmi database
 * 
 * @return PDO
 * @throws PDOException
 */
function getModul0Connection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME_MODUL0 . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Modul 0 DB connection failed: " . $e->getMessage());
            die("Database connection failed for Modul 0. Please check your configuration.");
        }
    }

    return $pdo;
}
