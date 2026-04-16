<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrowLife - Prenatal Intelligence</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <header>
        <div class="logo"><i class="fas fa-leaf"></i> GrowLife</div>
        <p>Your Intelligent Prenatal Companion</p>
    </header>

    <div class="grid-layout">
        <section class="card">
            <div class="card-header"><i class="fas fa-baby"></i> Milestone Janin (WHO)</div>
            <div class="card-body">
                <label>Pilih Minggu Kehamilan:</label>
                <select id="weekSelector" class="custom-input" onchange="updateFetalInfo()"></select>
                
                <div id="fetalDisplay" class="milestone-box">
                    <div class="badge-row">
                        <span id="fetalPhase" class="badge blue">Fase</span>
                    </div>
                    <h3 id="fetalTitle">Pilih Minggu</h3>
                    <p id="fetalDesc">Informasi perkembangan akan muncul di sini.</p>
                    <div class="size-tag">
                        <i class="fas fa-ruler-combined"></i> <span id="fetalSize">Estimasi Ukuran</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-header"><i class="fas fa-apple-alt"></i> Kalkulator Nutrisi Harian</div>
            <div class="card-body">
                <p class="daily-note">*Target ini adalah total kebutuhan dalam 24 jam.</p>
                
                <div class="stat-row">
                    <div class="stat-label">Asam Folat</div>
                    <div class="progress-bg"><div id="folicBar" class="progress-fill folic-color"></div></div>
                    <div class="status-container">
                        <small id="folicStatus">0 / 600 mcg</small>
                        <small class="daily-tag">Target Harian</small>
                    </div>
                </div>

                <div class="stat-row">
                    <div class="stat-label">Zat Besi</div>
                    <div class="progress-bg"><div id="ironBar" class="progress-fill iron-color"></div></div>
                    <div class="status-container">
                        <small id="ironStatus">0 / 18 mg</small>
                        <small class="daily-tag">Target Harian</small>
                    </div>
                </div>
                
                <div class="action-area">
                    <label>Pilih Tanggal Riwayat:</label>
                    <input type="date" id="historyDate" class="custom-input" onchange="changeDate()">
                    
                    <label>Catat Makanan (per 100g/porsi):</label>
                    <select id="foodSelect" class="custom-input" style="margin-bottom: 8px;"></select>
                    <div id="actionMessage" style="font-size: 0.85rem; display: none; margin-bottom: 15px; font-weight: 500;"></div>
                    
                    <button id="submitBtn" class="btn btn-pink" onclick="submitNutrition()">Tambah Konsumsi</button>
                    <button class="btn btn-outline" onclick="resetDay()" style="margin-top: 10px;">Bersihkan Hari Ini</button>
                </div>

                <div class="history-area" style="margin-top: 20px;">
                    <h4 style="font-size: 1rem; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; color: var(--pink-dark);">Riwayat Makanan</h4>
                    <ul id="historyList" class="history-list">
                        <!-- List Riwayat Makanan -->
                    </ul>
                </div>
            </div>
        </section>
    </div>

    <section class="card full-width">
        <div class="card-header"><i class="fas fa-heartbeat"></i> Screening Risiko Klinis</div>
        <div class="card-body">
            <div class="input-grid">
                <div class="input-field">
                    <label>Sistolik (mmHg)</label>
                    <input type="number" id="systolic" placeholder="Contoh: 120">
                </div>
                <div class="input-field">
                    <label>Gula Darah Sewaktu (mg/dL)</label>
                    <input type="number" id="glucose" placeholder="Contoh: 100">
                </div>
                <button class="btn btn-dark" onclick="checkRisk()">Analisis Medis</button>
            </div>
            <div id="riskResult"></div>
        </div>
    </section>
</div>

<script src="script.js"></script>
</body>
</html>

<div style="margin-bottom: 20px; padding-top: 20px;">
    <a href="../dashboardgrowlife.php" style="text-decoration: none; color: #fb6f92; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
    </a>
</div>

