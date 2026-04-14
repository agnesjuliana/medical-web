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

define('DB_HOST', 'localhost'); // Default XAMPP MySQL port is 3306
define('DB_NAME', 'backbone_medweb');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO database connection
 * 
 * @return PDO
 * @throws PDOException
 */
function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            // Connect to MySQL server first without selecting DB
            $temp_dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
            $pdo = new PDO($temp_dsn, DB_USER, DB_PASS, $options);
            
            // Create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
            
            // Select the database and re-connect with the proper DSN
            $pdo->exec("USE `" . DB_NAME . "`");
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check your configuration (Pastikan Apache & MySQL menyala di XAMPP). Detail: " . $e->getMessage());
        }
    }

    return $pdo;
}
