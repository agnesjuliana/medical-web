-- Database Schema untuk Modul 3: Deteksi TBC
-- Database: medical_web3

CREATE DATABASE IF NOT EXISTS medical_web3
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE medical_web3;

-- Pastikan tabel users juga ada di database lokal kamu 
-- supaya relasi user_id tidak error saat testing
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Riwayat Deteksi TBC
CREATE TABLE IF NOT EXISTS modul3_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    confidence_score INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;