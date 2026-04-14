<?php
/**
 * Database Configuration
 * 
 * Centralized database connection using PDO.
 * Update credentials below to match your MySQL setup.
 * This file is shared across all modules.
 */

// Base URL — update this if the project moves to a different subdirectory
define('BASE_URL', '/medical-web');

// Database dosen — untuk autentikasi login
define('DB_HOST', 'localhost');
define('DB_NAME', 'backbone_medweb');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Database proyekmu — untuk data fitur kelompok
define('DB_NAME_APP', 'med_solve_lab');

/**
 * Koneksi ke backbone_medweb (punya dosen)
 * Dipakai untuk: login, autentikasi user
 */
function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }

    return $pdo;
}

/**
 * Koneksi ke med_solve_lab (punya kelompokmu)
 * Dipakai untuk: semua fitur proyekmu
 */
function getAppDBConnection(): PDO
{
    static $pdo_app = null;

    if ($pdo_app === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME_APP . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo_app = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("App database connection failed: " . $e->getMessage());
            die("App database connection failed. Please check your configuration.");
        }
    }

    return $pdo_app;
}