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
            image_path VARCHAR(255) NOT NULL,
            ml_severity_level ENUM('Mild', 'Moderate', 'Severe') NOT NULL,
            ml_papule_count INT DEFAULT 0,
            ml_pustule_count INT DEFAULT 0,
            ml_blackhead_count INT DEFAULT 0,
            status ENUM('completed_by_ml', 'pending_doctor_review', 'reviewed_by_doctor') NOT NULL DEFAULT 'completed_by_ml',
            doctor_id INT NULL,
            doctor_notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_screening_patient FOREIGN KEY (patient_id) REFERENCES backbone_medweb.users(id) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $db->exec($query);
    echo "Tabel screening_results berhasil diciptakan/diverifikasi.";

} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}