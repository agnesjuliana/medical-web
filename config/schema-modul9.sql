-- ============================================================
-- SIMRS-TB Database Schema
-- Sistem Informasi Manajemen Rumah Sakit Tuberkulosis
-- Modul 9 — Medical Web
-- ============================================================

-- Menggunakan database yang sudah ada
USE backbone_medweb;

-- -----------------------------------------------------------
-- 1. tb_patients — Data Pasien TB
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS tb_patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_rm VARCHAR(20) UNIQUE NOT NULL COMMENT 'Nomor Rekam Medis',
    nik VARCHAR(16) UNIQUE COMMENT 'Nomor Induk Kependudukan',
    nama VARCHAR(150) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    alamat TEXT,
    no_telepon VARCHAR(20),
    pekerjaan VARCHAR(100),
    kategori_tb ENUM('Paru', 'Ekstra Paru') DEFAULT 'Paru',
    tipe_pasien ENUM('Baru', 'Kambuh', 'Gagal', 'Putus Obat', 'Pindahan', 'Lain-lain') DEFAULT 'Baru',
    fase_pengobatan ENUM('Intensif', 'Lanjutan', 'Selesai', 'Belum Mulai') DEFAULT 'Belum Mulai',
    tanggal_mulai_pengobatan DATE,
    tanggal_target_selesai DATE,
    status ENUM('Aktif', 'Sembuh', 'Pengobatan Lengkap', 'Gagal', 'Meninggal', 'Putus Obat', 'Pindah') DEFAULT 'Aktif',
    id_dokter INT COMMENT 'FK ke users.id (dokter penanggung jawab)',
    id_pmo INT COMMENT 'FK ke users.id (Pengawas Menelan Obat)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_fase (fase_pengobatan),
    INDEX idx_dokter (id_dokter)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 2. tb_screenings — Skrining Suara Batuk AI
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS tb_screenings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pasien INT,
    nama_file_audio VARCHAR(255) NOT NULL,
    durasi_detik DECIMAL(5,2),
    confidence_score DECIMAL(5,2) COMMENT 'Skor kepercayaan AI (0-100)',
    hasil ENUM('Positif Indikasi', 'Negatif Indikasi', 'Tidak Dapat Ditentukan') NOT NULL,
    catatan TEXT,
    dirujuk TINYINT(1) DEFAULT 0 COMMENT '1 = dirujuk ke dokter',
    id_petugas INT COMMENT 'FK ke users.id',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pasien (id_pasien),
    INDEX idx_hasil (hasil),
    FOREIGN KEY (id_pasien) REFERENCES tb_patients(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 3. tb_medical_records — Rekam Medis Digital
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS tb_medical_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pasien INT NOT NULL,
    tanggal_periksa DATETIME NOT NULL,
    keluhan TEXT,
    pemeriksaan_fisik TEXT,
    berat_badan DECIMAL(5,2),
    tinggi_badan DECIMAL(5,2),
    tekanan_darah VARCHAR(10) COMMENT 'mis: 120/80',
    suhu DECIMAL(4,1),
    diagnosis TEXT,
    tindakan TEXT,
    catatan_dokter TEXT,
    id_dokter INT NOT NULL COMMENT 'FK ke users.id',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pasien (id_pasien),
    INDEX idx_tanggal (tanggal_periksa),
    FOREIGN KEY (id_pasien) REFERENCES tb_patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 4. tb_lab_results — Hasil Laboratorium
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS tb_lab_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pasien INT NOT NULL,
    id_rekam_medis INT,
    jenis_pemeriksaan ENUM('BTA', 'GeneXpert', 'Rontgen', 'Kultur', 'TCM', 'Lainnya') NOT NULL,
    tanggal_pemeriksaan DATE NOT NULL,
    hasil VARCHAR(255) NOT NULL COMMENT 'mis: BTA +1, GeneXpert MTB Detected',
    nilai_kuantitatif VARCHAR(100),
    satuan VARCHAR(50),
    interpretasi TEXT,
    nama_file_lampiran VARCHAR(255) COMMENT 'Hasil scan/foto',
    id_petugas_lab INT COMMENT 'FK ke users.id',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pasien (id_pasien),
    INDEX idx_jenis (jenis_pemeriksaan),
    FOREIGN KEY (id_pasien) REFERENCES tb_patients(id) ON DELETE CASCADE,
    FOREIGN KEY (id_rekam_medis) REFERENCES tb_medical_records(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 5. tb_drug_inventory — Inventaris Obat TB
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS tb_drug_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_obat VARCHAR(20) UNIQUE NOT NULL,
    nama_obat VARCHAR(150) NOT NULL,
    kategori ENUM('FDC', 'Lini 1', 'Lini 2', 'Sisipan', 'Lainnya') DEFAULT 'FDC',
    satuan VARCHAR(30) DEFAULT 'Tablet',
    stok_tersedia INT DEFAULT 0,
    stok_minimum INT DEFAULT 50 COMMENT 'Alert jika stok di bawah ini',
    tanggal_kadaluarsa DATE,
    lokasi_penyimpanan VARCHAR(100),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_stok (stok_tersedia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 6. tb_prescriptions — Resep / Distribusi Obat
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS tb_prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pasien INT NOT NULL,
    id_obat INT NOT NULL,
    id_rekam_medis INT,
    dosis VARCHAR(100) NOT NULL COMMENT 'mis: 2 tablet/hari',
    frekuensi VARCHAR(100) COMMENT 'mis: 1x sehari',
    durasi_hari INT,
    jumlah_diberikan INT NOT NULL,
    tanggal_distribusi DATE NOT NULL,
    status_ambil ENUM('Sudah Diambil', 'Belum Diambil', 'Terlambat') DEFAULT 'Belum Diambil',
    id_apoteker INT COMMENT 'FK ke users.id',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pasien (id_pasien),
    INDEX idx_status (status_ambil),
    FOREIGN KEY (id_pasien) REFERENCES tb_patients(id) ON DELETE CASCADE,
    FOREIGN KEY (id_obat) REFERENCES tb_drug_inventory(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 7. tb_pmo_logs — Catatan Pengawas Menelan Obat
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS tb_pmo_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pasien INT NOT NULL,
    id_pmo INT COMMENT 'FK ke users.id',
    tanggal DATE NOT NULL,
    waktu_minum TIME,
    status_minum ENUM('Diminum', 'Tidak Diminum', 'Dimuntahkan', 'Efek Samping') NOT NULL,
    efek_samping TEXT,
    catatan TEXT,
    metode_verifikasi ENUM('Langsung', 'Video Call', 'Foto', 'Laporan Keluarga') DEFAULT 'Langsung',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pasien_tanggal (id_pasien, tanggal),
    UNIQUE KEY unique_pasien_tanggal (id_pasien, tanggal),
    FOREIGN KEY (id_pasien) REFERENCES tb_patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 8. tb_appointments — Jadwal Kontrol
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS tb_appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pasien INT NOT NULL,
    id_dokter INT COMMENT 'FK ke users.id',
    tanggal_jadwal DATETIME NOT NULL,
    jenis_kontrol ENUM('Kontrol Rutin', 'Pemeriksaan Lab', 'Rontgen', 'Evaluasi Fase', 'Konsultasi', 'Lainnya') DEFAULT 'Kontrol Rutin',
    status ENUM('Terjadwal', 'Selesai', 'Tidak Hadir', 'Dibatalkan', 'Dijadwalkan Ulang') DEFAULT 'Terjadwal',
    catatan TEXT,
    reminder_sent TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tanggal (tanggal_jadwal),
    INDEX idx_pasien (id_pasien),
    INDEX idx_status (status),
    FOREIGN KEY (id_pasien) REFERENCES tb_patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 9. tb_compliance_logs — Log Kepatuhan Harian
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS tb_compliance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pasien INT NOT NULL,
    tanggal DATE NOT NULL,
    status ENUM('Patuh', 'Tidak Patuh', 'Izin Medis') NOT NULL,
    sumber_data ENUM('PMO', 'Self Report', 'Klinik', 'Sistem') DEFAULT 'Sistem',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_pasien_tanggal (id_pasien, tanggal),
    INDEX idx_pasien (id_pasien),
    INDEX idx_status (status),
    FOREIGN KEY (id_pasien) REFERENCES tb_patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 10. tb_notifications — Notifikasi/Alarm Terpusat
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS tb_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pasien INT,
    id_penerima INT COMMENT 'FK ke users.id',
    tipe ENUM('Kontrol', 'Obat', 'Drop Out Risk', 'Lab', 'Stok Obat', 'SITB', 'Umum') NOT NULL,
    judul VARCHAR(255) NOT NULL,
    pesan TEXT,
    prioritas ENUM('Rendah', 'Sedang', 'Tinggi', 'Kritis') DEFAULT 'Sedang',
    dibaca TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_penerima (id_penerima),
    INDEX idx_dibaca (dibaca),
    INDEX idx_tipe (tipe)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 11. tb_sitb_sync_logs — Log Sinkronisasi SITB
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS tb_sitb_sync_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal_sync DATETIME NOT NULL,
    jenis_data ENUM('Pasien Baru', 'Update Status', 'Hasil Lab', 'Pengobatan Selesai', 'Laporan Bulanan') NOT NULL,
    jumlah_record INT DEFAULT 0,
    status ENUM('Berhasil', 'Gagal', 'Partial', 'Pending') NOT NULL,
    response_code VARCHAR(10),
    response_message TEXT,
    id_petugas INT COMMENT 'FK ke users.id',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tanggal (tanggal_sync),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- INSERT DATA DUMMY
-- -----------------------------------------------------------

-- Obat TB
INSERT INTO tb_drug_inventory (kode_obat, nama_obat, kategori, satuan, stok_tersedia, stok_minimum, tanggal_kadaluarsa, lokasi_penyimpanan) VALUES
('FDC-4', 'FDC 4 Kombinasi (RHZE)', 'FDC', 'Tablet', 1200, 100, '2027-06-30', 'Rak A-1'),
('FDC-2', 'FDC 2 Kombinasi (RH)', 'FDC', 'Tablet', 800, 100, '2027-08-15', 'Rak A-2'),
('INH-300', 'Isoniazid 300mg', 'Lini 1', 'Tablet', 500, 50, '2027-05-20', 'Rak B-1'),
('RIF-450', 'Rifampisin 450mg', 'Lini 1', 'Kapsul', 350, 50, '2027-04-10', 'Rak B-2'),
('PZA-500', 'Pirazinamid 500mg', 'Lini 1', 'Tablet', 600, 50, '2027-09-01', 'Rak B-3'),
('EMB-400', 'Etambutol 400mg', 'Lini 1', 'Tablet', 45, 50, '2027-07-15', 'Rak B-4'),
('STREP-1G', 'Streptomisin 1g Injeksi', 'Lini 1', 'Vial', 30, 20, '2027-03-30', 'Lemari Pendingin'),
('B6-10', 'Vitamin B6 10mg', 'Sisipan', 'Tablet', 2000, 200, '2028-01-01', 'Rak C-1');
