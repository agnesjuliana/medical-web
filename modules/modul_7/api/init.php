<?php
/**
 * Modul 7 - Database Initialization
 * 
 * Creates the required tables for Modul 7 if they don't exist.
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = getModul7DBConnection();

    // Create screenings table
    $query = "
        CREATE TABLE IF NOT EXISTS screening_results (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            age INT NOT NULL,
            skin_type VARCHAR(100) NOT NULL,
            main_concern TEXT NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            ml_severity_level ENUM('Mild','Moderate','Severe','PAPULE','PUSTULE','BLACKHEAD') NOT NULL,
            ml_papule_count INT DEFAULT 0,
            ml_pustule_count INT DEFAULT 0,
            ml_blackhead_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_screening_patient FOREIGN KEY (patient_id) REFERENCES backbone_medweb.users(id) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $db->exec($query);
    echo "Tabel screening_results berhasil diciptakan/diverifikasi.";

} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}