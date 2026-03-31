-- Backbone MedWeb - Users Table Schema
-- Run this SQL in your MySQL database to create the users table.

CREATE DATABASE IF NOT EXISTS backbone_medweb
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE backbone_medweb;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
