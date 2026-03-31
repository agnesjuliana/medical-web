-- ============================================================
-- Modul 0 — BMI Calculator Database Schema
-- ============================================================
-- Database terpisah dari backbone_medweb (SSO).
-- Kolom `user_id` merujuk ke `backbone_medweb.users.id`.
--
-- Jalankan file ini di MySQL / phpMyAdmin untuk setup awal.
-- ============================================================

CREATE DATABASE IF NOT EXISTS modul0_bmi
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE modul0_bmi;

-- -------------------------------------------------
-- Tabel: bmi_records
-- Menyimpan setiap perhitungan BMI per user
-- -------------------------------------------------
CREATE TABLE IF NOT EXISTS bmi_records (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL                        COMMENT 'FK → backbone_medweb.users.id',
    nama            VARCHAR(100) NOT NULL               COMMENT 'Nama subjek pengukuran',
    usia            INT NOT NULL                        COMMENT 'Usia subjek (tahun)',
    jenis_kelamin   ENUM('L','P') NOT NULL              COMMENT 'L = Laki-laki, P = Perempuan',
    tinggi_badan    DECIMAL(5,2) NOT NULL               COMMENT 'Tinggi badan dalam cm',
    berat_badan     DECIMAL(5,2) NOT NULL               COMMENT 'Berat badan dalam kg',
    bmi_value       DECIMAL(5,2) NOT NULL               COMMENT 'Hasil kalkulasi BMI (berat / (tinggi_m)^2)',
    bmi_category    VARCHAR(30) NOT NULL                COMMENT 'Underweight | Normal | Overweight | Obese',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pencatatan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index untuk query berdasarkan user
CREATE INDEX idx_bmi_user_id ON bmi_records (user_id);
