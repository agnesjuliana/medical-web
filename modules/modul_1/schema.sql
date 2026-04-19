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

-- 3. Create the Modul 1 daily monitoring data table
CREATE TABLE IF NOT EXISTS user_daily_monitoring (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    record_date DATE NOT NULL,
    
    -- Generic & CABG
    spo2 INT DEFAULT NULL,
    heart_rate INT DEFAULT NULL,
    pain_level INT DEFAULT NULL, -- Chest pain or general pain
    
    -- SC
    temp FLOAT DEFAULT NULL,
    blood_volume VARCHAR(50) DEFAULT NULL,
    blood_color VARCHAR(50) DEFAULT NULL,
    blood_clots VARCHAR(50) DEFAULT NULL,
    
    -- Amputation
    stump_pain INT DEFAULT NULL,
    phantom_pain INT DEFAULT NULL,
    wound_color VARCHAR(50) DEFAULT NULL,
    wound_swelling VARCHAR(50) DEFAULT NULL,
    wound_fluid VARCHAR(50) DEFAULT NULL,
    wound_odor VARCHAR(50) DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY user_date_unique (user_id, record_date),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
