<?php
/**
 * Database Configuration (Original MySQL XAMPP)
 */

date_default_timezone_set('Asia/Jakarta');

// Base URL — update this if the project moves to a different subdirectory
define('BASE_URL', '/medical-web');

function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host = 'localhost';
        $dbname = 'backbone_medweb';
        $user = 'root';
        $pass = '';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Koneksi Database Gagal: " . $e->getMessage());
        }
    }

    return $pdo;
}
?>
