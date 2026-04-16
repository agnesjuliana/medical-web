-- phpMyAdmin SQL Dump
-- Version 5.2.0
-- Host: 127.0.0.1
-- Waktu pembuatan: Hari ini
-- Versi server: 10.4.24-MariaDB
-- Versi PHP: 8.1.6
-- Database: `db_growlife_kel2`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `db_growlife_kel2` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_growlife_kel2`;

-- Struktur dari tabel `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `pass` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Struktur dari tabel `children`
CREATE TABLE `children` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('anak','janin') NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Struktur dari tabel `nutrition_history`
CREATE TABLE `nutrition_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `child_id` int(11) NOT NULL,
  `record_date` date NOT NULL,
  `food_index` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `folic` float NOT NULL,
  `iron` float NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`child_id`) REFERENCES `children`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Struktur dari tabel `stunting_data`
CREATE TABLE `stunting_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `child_id` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `bb` float DEFAULT NULL,
  `tb` float DEFAULT NULL,
  `lk` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_child_month` (`child_id`,`month`),
  FOREIGN KEY (`child_id`) REFERENCES `children`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Struktur dari tabel `reminder_data`
CREATE TABLE `reminder_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `child_id` int(11) NOT NULL,
  `tgl_patokan` date NOT NULL,
  `tugas` varchar(255) NOT NULL,
  `start_date_str` varchar(100),
  `end_date_str` varchar(100),
  `start_date_obj` varchar(100),
  `end_date_obj` varchar(100),
  `planned_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`child_id`) REFERENCES `children`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
