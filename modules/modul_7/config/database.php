<?php
/**
 * Modul 7 - Database Helper (Isolated)
 * Menangani koneksi ke database spesifik: dermalyzeai
 */

function getModul7DBConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $db_host = 'localhost';
        $db_name = 'dermalyzeai';
        $db_user = 'root';
        $db_pass = '';
        $db_charset = 'utf8mb4';

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            // Check Server Connection
            $temp_dsn = "mysql:host=$db_host;charset=$db_charset";
            $pdo = new PDO($temp_dsn, $db_user, $db_pass, $options);

            // Auto Create DB if missing
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
            $pdo->exec("USE `$db_name`");
        } catch (PDOException $e) {
            error_log("Modul 7 DB connection failed: " . $e->getMessage());
            die("Modul 7 Database connection failed. Detail: " . $e->getMessage());
        }
    }

    return $pdo;
}

function getPatientScreeningsHistory($patient_id)
{
    try {
        $db = getModul7DBConnection();

        $check = $db->query("SHOW TABLES LIKE 'screening_results'");
        if ($check->rowCount() == 0)
            return [];

        $stmt = $db->prepare("
            SELECT * FROM screening_results 
            WHERE patient_id = :pid 
            ORDER BY created_at DESC
        ");
        $stmt->execute(['pid' => $patient_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}
