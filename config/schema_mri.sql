-- NeuroAI MRI Module - Database Schema
-- Run this SQL in your MySQL to create the modul6_mri database and tables.

CREATE DATABASE IF NOT EXISTS modul6_mri
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE modul6_mri;

-- MRI Scan records table
CREATE TABLE IF NOT EXISTS mri_scans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'FK to backbone_medweb.users.id',
    patient_name VARCHAR(150) NOT NULL,
    patient_age INT UNSIGNED DEFAULT NULL,
    patient_gender ENUM('Laki-laki','Perempuan') DEFAULT NULL,
    scan_type ENUM('T1','T2','FLAIR','DWI','SWI','Other') NOT NULL DEFAULT 'T1',
    description TEXT DEFAULT NULL,
    file_name VARCHAR(255) NOT NULL COMMENT 'Original uploaded filename',
    file_path VARCHAR(500) NOT NULL COMMENT 'Server path to stored file',
    file_size INT UNSIGNED NOT NULL COMMENT 'File size in bytes',
    file_type VARCHAR(100) NOT NULL COMMENT 'MIME type',
    diagnosis_status ENUM('pending','processing','completed') NOT NULL DEFAULT 'pending',
    diagnosis_result TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_user_id (user_id),
    INDEX idx_status (diagnosis_status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
