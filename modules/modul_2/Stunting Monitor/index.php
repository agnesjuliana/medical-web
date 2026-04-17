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
    <title>GrowLife - Stunting Prevention Monitor v2</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Pustaka Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <header class="page-header">
        <div class="logo"><i class="fas fa-chart-area"></i> Smart Stunting Monitor</div>
        <p>Pantau status <b>Berat, Tinggi (Risiko Stunting), dan Lingkar Kepala</b> dibandingkan Z-Score WHO.</p>
    </header>

    <div class="monitor-layout">
        <!-- Form Input Data Series -->
        <section class="card input-section">
            <h3 class="card-title">Catat Pertumbuhan Bulan Ini</h3>
            <p style="font-size: 0.85rem; color: #888; margin-bottom: 20px;">Lengkapi 3 data ukur dari Posyandu/Klinik.</p>
            
            <div class="input-group">
                <label>Umur Anak Saat Diukur (Bulan ke-)</label>
                <select id="monthInput" class="custom-input"></select>
            </div>
            
            <div class="input-row">
                <div class="input-group" style="flex:1;">
                    <label>Berat Badan (kg)</label>
                    <input type="number" id="weightInput" class="custom-input" placeholder="Misal: 6.5" step="0.1">
                </div>
                <div class="input-group" style="flex:1;">
                    <label>Tinggi/Panjang (cm)</label>
                    <input type="number" id="heightInput" class="custom-input" placeholder="Misal: 62" step="0.1">
                </div>
            </div>
            
            <div class="input-group">
                <label>Lingkar Kepala (cm) <small style="color:#aaa; font-weight:normal;">*Opsional</small></label>
                <input type="number" id="headInput" class="custom-input" placeholder="Misal: 41" step="0.1">
            </div>

            <button class="btn btn-pink" onclick="recordData()">Analisis & Simpan Data</button>
            <button class="btn btn-outline" onclick="hapusBulanIni()" style="margin-top: 10px;">Hapus Data Bulan Ini</button>
            <button class="btn btn-outline" onclick="resetGraphic()" style="margin-top: 5px; color: #d00000; border-color: #d00000;">Kosongkan Semua Rekaman</button>
            
            <!-- Dashboard Kesimpulan & AI Insight (Ringan) -->
            <div id="conclusionBox" class="conclusion-box">
                Belum ada data bulanan. Silakan input dari atas.
            </div>
            
            <div id="nutritionBox" class="nutrition-box" style="display:none;"></div>
            
        </section>

        <!-- Area Grafik Dinamis -->
        <section class="card chart-section">
            <h3 class="card-title">Kurva Standar WHO Interaktif</h3>
            
            <!-- Sistem Multi-Tab -->
            <div class="chart-tabs">
                <button id="btnTabBB" class="tab-btn active" onclick="switchTab('BB')">Berat Badan (BB)</button>
                <button id="btnTabTB" class="tab-btn" onclick="switchTab('TB')">Tinggi Badan (TB)</button>
                <button id="btnTabLK" class="tab-btn" onclick="switchTab('LK')">Lingkar Kepala</button>
            </div>

            <div class="chart-wrapper">
                <canvas id="stuntingChart"></canvas>
            </div>
            
            <div class="simple-legend">
                <div class="legend-item"><span class="color-box" style="background:#4caf50;"></span> Ideal / Normal WHO</div>
                <div class="legend-item"><span class="color-box" style="background:#e57373;"></span> Batas Bawah Waspada</div>
                <div class="legend-item"><span class="color-box" style="background:#fb6f92;"></span> Titik Perkembangan Anak</div>
            </div>
        </section>
    </div>

    <div style="margin-top: 30px; text-align: center;">
        <a href="../dashboardgrowlife.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
