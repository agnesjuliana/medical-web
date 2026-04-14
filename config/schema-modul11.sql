USE backbone_medweb;

CREATE TABLE IF NOT EXISTS modul_11_pasien (
    id_pasien INT AUTO_INCREMENT PRIMARY KEY,
    nama_pasien VARCHAR(100) NOT NULL,
    nik VARCHAR(20) NOT NULL,
    usia INT NOT NULL,
    jenis_kelamin VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS modul_11_sefalometri (
    id_analisis INT AUTO_INCREMENT PRIMARY KEY,
    id_pasien INT NOT NULL,
    foto_rontgen VARCHAR(255) NOT NULL,
    waktu_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_landmark TEXT,
    hasil_diagnosis TEXT,
    FOREIGN KEY (id_pasien) REFERENCES modul_11_pasien(id_pasien) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
