-- Backbone MedWeb - Modul 1 Schema

-- Run this SQL to add Modul 1 specific tables and columns to your database.
-- Make sure the global config/schema.sql has been executed first.

USE backbone_medweb;

-- Modul 1 onboarding data table (self-contained, no changes to backbone tables)


-- 2. Create the Modul 1 onboarding data table
CREATE TABLE IF NOT EXISTS user_onboarding (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role VARCHAR(50),
    full_name VARCHAR(100),
    patient_name VARCHAR(100),
    operation_type VARCHAR(50),
    surgery_date DATE,
    pain_level INT,
    mobility VARCHAR(50),
    symptoms TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
