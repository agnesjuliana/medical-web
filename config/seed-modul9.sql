-- ============================================================
-- SIMRS-TB — Seed Data
-- Sample data untuk testing CRUD
-- Jalankan setelah schema-modul9.sql
-- ============================================================

USE backbone_medweb;

-- -----------------------------------------------------------
-- Sample Pasien TB
-- -----------------------------------------------------------
INSERT IGNORE INTO tb_patients (no_rm, nik, nama, tanggal_lahir, jenis_kelamin, alamat, no_telepon, pekerjaan, kategori_tb, tipe_pasien, fase_pengobatan, tanggal_mulai_pengobatan, tanggal_target_selesai, status) VALUES
('RM-2026-0001', '3201010101900001', 'Ahmad Fauzi', '1990-05-15', 'L', 'Jl. Merdeka No. 45, Kec. A, Bandung', '081234567890', 'Pedagang', 'Paru', 'Baru', 'Intensif', '2026-03-01', '2026-09-01', 'Aktif'),
('RM-2026-0002', '3201010201850002', 'Siti Nurhaliza', '1985-08-22', 'P', 'Jl. Pahlawan No. 12, Kec. B, Bandung', '082345678901', 'Guru', 'Paru', 'Baru', 'Lanjutan', '2025-12-01', '2026-06-01', 'Aktif'),
('RM-2026-0003', '3201010301950003', 'Budi Santoso', '1995-01-10', 'L', 'Jl. Sudirman No. 78, Kec. A, Bandung', '083456789012', 'Karyawan Swasta', 'Paru', 'Kambuh', 'Intensif', '2026-02-15', '2026-10-15', 'Aktif'),
('RM-2026-0004', '3201010402000004', 'Dewi Lestari', '2000-12-05', 'P', 'Jl. Asia Afrika No. 100, Kec. C, Bandung', '084567890123', 'Mahasiswa', 'Ekstra Paru', 'Baru', 'Belum Mulai', NULL, NULL, 'Aktif'),
('RM-2026-0005', '3201010501880005', 'Hendra Gunawan', '1988-03-20', 'L', 'Jl. Dago No. 55, Kec. B, Bandung', '085678901234', 'Wiraswasta', 'Paru', 'Baru', 'Selesai', '2025-06-01', '2025-12-01', 'Sembuh'),
('RM-2026-0006', '3201010601920006', 'Rina Marlina', '1992-07-18', 'P', 'Jl. Cihampelas No. 30, Kec. A, Bandung', '086789012345', 'Ibu Rumah Tangga', 'Paru', 'Baru', 'Intensif', '2026-04-01', '2026-10-01', 'Aktif'),
('RM-2026-0007', '3201010701780007', 'Agus Prasetyo', '1978-11-02', 'L', 'Jl. Braga No. 22, Kec. C, Bandung', '087890123456', 'PNS', 'Paru', 'Putus Obat', 'Lanjutan', '2025-10-01', '2026-04-01', 'Putus Obat');

-- -----------------------------------------------------------
-- Sample PMO Logs (untuk testing monitoring kepatuhan)
-- -----------------------------------------------------------
INSERT IGNORE INTO tb_pmo_logs (id_pasien, tanggal, waktu_minum, status_minum, metode_verifikasi, catatan) VALUES
-- Ahmad Fauzi — kepatuhan tinggi
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '07:30:00', 'Diminum', 'Langsung', NULL),
(1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '07:15:00', 'Diminum', 'Langsung', NULL),
(1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '08:00:00', 'Diminum', 'Video Call', NULL),
(1, DATE_SUB(CURDATE(), INTERVAL 4 DAY), '07:45:00', 'Diminum', 'Langsung', NULL),
(1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), '07:30:00', 'Diminum', 'Langsung', NULL),
(1, DATE_SUB(CURDATE(), INTERVAL 6 DAY), NULL, 'Tidak Diminum', 'Laporan Keluarga', 'Pasien lupa'),
(1, DATE_SUB(CURDATE(), INTERVAL 7 DAY), '07:30:00', 'Diminum', 'Langsung', NULL),
(1, DATE_SUB(CURDATE(), INTERVAL 8 DAY), '07:30:00', 'Diminum', 'Langsung', NULL),
(1, DATE_SUB(CURDATE(), INTERVAL 9 DAY), '07:30:00', 'Diminum', 'Foto', NULL),
(1, DATE_SUB(CURDATE(), INTERVAL 10 DAY), '07:30:00', 'Diminum', 'Langsung', NULL),
-- Siti Nurhaliza — kepatuhan sedang
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '08:00:00', 'Diminum', 'Langsung', NULL),
(2, DATE_SUB(CURDATE(), INTERVAL 2 DAY), NULL, 'Tidak Diminum', 'Laporan Keluarga', NULL),
(2, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '07:30:00', 'Diminum', 'Video Call', NULL),
(2, DATE_SUB(CURDATE(), INTERVAL 4 DAY), NULL, 'Tidak Diminum', 'Langsung', 'Pasien pergi keluar kota'),
(2, DATE_SUB(CURDATE(), INTERVAL 5 DAY), '09:00:00', 'Diminum', 'Langsung', NULL),
(2, DATE_SUB(CURDATE(), INTERVAL 6 DAY), '08:30:00', 'Diminum', 'Langsung', NULL),
(2, DATE_SUB(CURDATE(), INTERVAL 7 DAY), '07:30:00', 'Diminum', 'Foto', NULL),
(2, DATE_SUB(CURDATE(), INTERVAL 8 DAY), NULL, 'Tidak Diminum', 'Langsung', NULL),
-- Budi Santoso — kepatuhan rendah
(3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), NULL, 'Tidak Diminum', 'Langsung', NULL),
(3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), NULL, 'Tidak Diminum', 'Laporan Keluarga', NULL),
(3, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '10:00:00', 'Diminum', 'Langsung', NULL),
(3, DATE_SUB(CURDATE(), INTERVAL 4 DAY), NULL, 'Tidak Diminum', 'Langsung', NULL),
(3, DATE_SUB(CURDATE(), INTERVAL 5 DAY), NULL, 'Efek Samping', 'Langsung', 'Mual dan pusing'),
(3, DATE_SUB(CURDATE(), INTERVAL 6 DAY), '07:30:00', 'Diminum', 'Langsung', NULL),
(3, DATE_SUB(CURDATE(), INTERVAL 7 DAY), NULL, 'Tidak Diminum', 'Langsung', NULL),
-- Rina Marlina — baru mulai
(6, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '07:30:00', 'Diminum', 'Langsung', NULL),
(6, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '07:30:00', 'Diminum', 'Langsung', NULL),
(6, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '08:00:00', 'Diminum', 'Video Call', NULL);

-- -----------------------------------------------------------
-- Sample Appointments
-- -----------------------------------------------------------
INSERT IGNORE INTO tb_appointments (id_pasien, tanggal_jadwal, jenis_kontrol, status, catatan) VALUES
(1, CONCAT(CURDATE(), ' 09:00:00'), 'Kontrol Rutin', 'Terjadwal', 'Kontrol rutin bulanan'),
(2, CONCAT(CURDATE(), ' 10:30:00'), 'Pemeriksaan Lab', 'Terjadwal', 'Cek BTA bulan ke-5'),
(3, CONCAT(CURDATE(), ' 14:00:00'), 'Evaluasi Fase', 'Terjadwal', 'Evaluasi transisi ke lanjutan'),
(1, DATE_ADD(CONCAT(CURDATE(), ' 09:00:00'), INTERVAL 7 DAY), 'Kontrol Rutin', 'Terjadwal', NULL),
(6, DATE_ADD(CONCAT(CURDATE(), ' 11:00:00'), INTERVAL 3 DAY), 'Rontgen', 'Terjadwal', 'Rontgen thorax awal'),
(4, DATE_ADD(CONCAT(CURDATE(), ' 08:30:00'), INTERVAL 5 DAY), 'Konsultasi', 'Terjadwal', 'Konsultasi awal pengobatan');

-- -----------------------------------------------------------
-- Sample Lab Results
-- -----------------------------------------------------------
INSERT IGNORE INTO tb_lab_results (id_pasien, jenis_pemeriksaan, tanggal_pemeriksaan, hasil, interpretasi) VALUES
(1, 'BTA', '2026-03-01', 'BTA +2', 'Positif, perlu pengobatan segera'),
(1, 'Rontgen', '2026-03-02', 'Infiltrat paru kanan atas', 'Sesuai gambaran TB'),
(1, 'BTA', '2026-04-01', 'BTA +1', 'Perbaikan, lanjutkan pengobatan'),
(2, 'GeneXpert', '2025-12-01', 'MTB Detected, Rif Sensitive', 'Positif TB, sensitif Rifampisin'),
(2, 'BTA', '2026-02-01', 'BTA Negatif', 'Konversi BTA, respons baik'),
(3, 'BTA', '2026-02-15', 'BTA +3', 'Positif kuat, pengobatan ulang'),
(5, 'BTA', '2025-10-01', 'BTA Negatif', 'Hasil akhir — sembuh');

-- -----------------------------------------------------------
-- Sample Prescriptions
-- -----------------------------------------------------------
INSERT IGNORE INTO tb_prescriptions (id_pasien, id_obat, dosis, frekuensi, jumlah_diberikan, tanggal_distribusi, status_ambil) VALUES
(1, 1, '3 tablet/hari', '1x sehari pagi', 90, '2026-03-01', 'Sudah Diambil'),
(1, 8, '1 tablet/hari', '1x sehari', 30, '2026-03-01', 'Sudah Diambil'),
(2, 2, '3 tablet/hari', '1x sehari pagi', 90, '2026-03-01', 'Sudah Diambil'),
(3, 1, '3 tablet/hari', '1x sehari pagi', 90, '2026-02-15', 'Sudah Diambil'),
(6, 1, '3 tablet/hari', '1x sehari pagi', 60, '2026-04-01', 'Belum Diambil');

-- -----------------------------------------------------------
-- Sample Screenings
-- -----------------------------------------------------------
INSERT IGNORE INTO tb_screenings (id_pasien, nama_file_audio, durasi_detik, confidence_score, hasil, catatan, dirujuk) VALUES
(1, 'recording_ahmad_001.wav', 4.5, 87.5, 'Positif Indikasi', 'Batuk berdahak terdeteksi', 1),
(4, 'recording_dewi_001.wav', 3.2, 72.3, 'Tidak Dapat Ditentukan', 'Perlu evaluasi lanjutan', 1),
(NULL, 'recording_walkin_001.wav', 5.1, 35.0, 'Negatif Indikasi', 'Tidak ada indikasi TB', 0);

-- -----------------------------------------------------------
-- Sample SITB Sync Logs
-- -----------------------------------------------------------
INSERT INTO tb_sitb_sync_logs (tanggal_sync, jenis_data, jumlah_record, status, response_code) VALUES
(DATE_SUB(NOW(), INTERVAL 1 DAY), 'Pasien Baru', 3, 'Berhasil', '200'),
(DATE_SUB(NOW(), INTERVAL 3 DAY), 'Update Status', 12, 'Berhasil', '200'),
(DATE_SUB(NOW(), INTERVAL 5 DAY), 'Hasil Lab', 8, 'Berhasil', '200'),
(DATE_SUB(NOW(), INTERVAL 7 DAY), 'Laporan Bulanan', 1, 'Gagal', '503'),
(DATE_SUB(NOW(), INTERVAL 10 DAY), 'Pasien Baru', 5, 'Berhasil', '200');
