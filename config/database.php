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

define('DB_HOST', 'localhost');
define('DB_NAME', 'backbone_medweb');
define('DB_NAME_MRI', 'modul6_mri');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO database connection (MAIN DB - login)
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
 * Get PDO database connection (MRI DB - modul 6)
 */
function getDBConnectionMRI(): PDO
{
    static $pdo_mri = null;

    if ($pdo_mri === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME_MRI . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo_mri = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("MRI Database connection failed: " . $e->getMessage());
            die("MRI Database connection failed.");
        }
    }

    return $pdo_mri;
}