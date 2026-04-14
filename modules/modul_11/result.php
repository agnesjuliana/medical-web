<?php
// Pastikan pengguna sudah login
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../config/database.php';

$userName = $_SESSION['user_name'] ?? 'Dokter';
$userEmail = $_SESSION['user_email'] ?? 'admin@medweb.com';

// 1. Tangkap ID Analisis dari URL (misal: result.php?id=5)
$id_analisis = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_analisis === 0) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='index.php';</script>";
    exit();
}

// 2. Tarik data pasien dan foto spesifik berdasarkan ID tersebut
$pdo = getDBConnection();
$sql = "SELECT p.*, s.foto_rontgen, s.waktu_upload, s.data_landmark, s.hasil_diagnosis 
        FROM modul_11_sefalometri s
        JOIN modul_11_pasien p ON s.id_pasien = p.id_pasien
        WHERE s.id_analisis = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_analisis]);
$data = $stmt->fetch();

if (!$data) {
    echo "<script>alert('Rekam medis tidak valid!'); window.location.href='index.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Diagnosis - Modul 11</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; background-color: #f8fafc; margin: 0; color: #334155; }
        .topbar { display: flex; justify-content: space-between; align-items: center; padding: 15px 40px; background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .topbar-brand { font-weight: 700; font-size: 1.2rem; color: #0f172a; }
        .topbar-user { display: flex; align-items: center; gap: 12px; }
        .avatar { background-color: #0ea5e9; color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; }
        .user-info { font-size: 0.9rem; text-align: right; }
        .user-name { font-weight: 600; color: #1e293b; }
        
        .container { max-width: 1000px; margin: 0 auto; padding: 0 20px 50px 20px; }
        .btn-back { color: #64748b; text-decoration: none; font-size: 0.95rem; display: inline-block; margin-bottom: 20px; font-weight: 500; transition: color 0.2s; }
        .btn-back:hover { color: #0f172a; }

        /* Card Pasien (Atas) */
        .patient-card { background: #ffffff; padding: 20px 30px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .patient-info h2 { margin: 0 0 5px 0; color: #0f172a; font-size: 1.3rem; }
        .patient-info p { margin: 0; color: #64748b; font-size: 0.95rem; }
        .badge-status { background-color: #fef3c7; color: #d97706; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; display: inline-flex; align-items: center; gap: 5px; }

        /* Grid Layout Utama (Kiri Kanan) */
        .result-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        
        /* Area Foto Kiri */
        .image-card { background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; text-align: center; }
        .image-title { font-weight: 600; margin-bottom: 15px; color: #1e293b; text-align: left; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; }
        .img-container { width: 100%; border-radius: 8px; overflow: hidden; background-color: #000; position: relative; }
        .img-container img { width: 100%; height: auto; display: block; opacity: 0.8; }
        /* Overlay Loading AI */
        .ai-scanning-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.4); display: flex; justify-content: center; align-items: center; color: white; font-weight: bold; flex-direction: column; }
        .spinner { border: 4px solid rgba(255,255,255,0.3); border-top: 4px solid white; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin-bottom: 10px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Area Metrik Kanan */
        .metrics-card { background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; }
        .angle-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 20px; }
        .angle-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; text-align: center; }
        .angle-name { font-size: 0.85rem; color: #64748b; font-weight: bold; margin-bottom: 5px; }
        .angle-value { font-size: 1.5rem; color: #94a3b8; font-weight: bold; } /* Warna abu-abu krn belum ada hasil */
        
        /* Asisten Pintar */
        .assistant-box { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 20px; }
        .assistant-header { display: flex; align-items: center; gap: 8px; color: #0284c7; font-weight: bold; margin-bottom: 10px; font-size: 1.1rem; }
        .assistant-content { color: #334155; font-size: 0.95rem; line-height: 1.5; }
        
        /* Tombol Eksekusi Python (Simulasi) */
        .btn-run-ai { background-color: #10b981; color: white; border: none; padding: 14px; border-radius: 8px; cursor: pointer; font-weight: 600; width: 100%; margin-top: 20px; font-size: 1rem; transition: background-color 0.2s; }
        .btn-run-ai:hover { background-color: #059669; }
    </style>
</head>
<body>

<div class="topbar">
    <div class="topbar-brand"><span style="background: #0ea5e9; color: white; padding: 4px 8px; border-radius: 6px; margin-right: 5px;">M</span> MedWeb</div>
    <div class="topbar-user">
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($userName) ?></div>
        </div>
        <div class="avatar">DR</div>
    </div>
</div>

<div class="container">
    <a href="index.php" class="btn-back">← Kembali ke Form Upload</a>
    
    <div class="patient-card">
        <div class="patient-info">
            <h2>Pasien: <?= htmlspecialchars($data['nama_pasien']) ?></h2>
            <p>NIK: <?= htmlspecialchars($data['nik']) ?> | <?= htmlspecialchars($data['usia']) ?> Tahun | <?= htmlspecialchars($data['jenis_kelamin']) ?></p>
        </div>
        <div class="badge-status">
            ⏳ Menunggu Proses AI
        </div>
    </div>

    <div class="result-grid">
        
        <div class="image-card">
            <div class="image-title">Citra Rontgen Sefalogram</div>
            <div class="img-container">
                <img src="uploads/<?= htmlspecialchars($data['foto_rontgen']) ?>" alt="Rontgen Pasien">
                
                <div class="ai-scanning-overlay">
                    <div class="spinner"></div>
                    <div>Sistem Siap Menandai Titik...</div>
                </div>
            </div>
        </div>

        <div class="metrics-card">
            <div class="image-title">Kalkulasi Geometris Otomatis</div>
            
            <div class="angle-grid">
                <div class="angle-box">
                    <div class="angle-name">SUDUT SNA</div>
                    <div class="angle-value">--°</div>
                </div>
                <div class="angle-box">
                    <div class="angle-name">SUDUT SNB</div>
                    <div class="angle-value">--°</div>
                </div>
                <div class="angle-box">
                    <div class="angle-name">SUDUT ANB</div>
                    <div class="angle-value">--°</div>
                </div>
            </div>

            <div class="assistant-box">
                <div class="assistant-header">
                    ✨ Asisten Pintar Diagnosis
                </div>
                <div class="assistant-content">
                    <p style="margin-top: 0; color: #64748b; font-style: italic;">Sistem menunggu perintah untuk menjalankan algoritma Auto-Landmarking. Silakan klik tombol di bawah untuk memulai proses ekstrasi fitur dan kalkulasi ortodonti.</p>
                </div>
            </div>

            <button class="btn-run-ai">Jalankan Analisis AI Sekarang</button>
        </div>

    </div>
</div>

</body>
</html>