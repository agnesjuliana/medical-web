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
    <title>GrowLife - Integrated Locator</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <header class="page-header">
        <div class="logo"><i class="fas fa-leaf"></i> GrowLife Locator</div>
        <p>Cari Fasilitas Kesehatan Terdekat (GPS Real-time Aktif)</p>
    </header>

    <div class="filter-bar">
        <button class="filter-btn active" onclick="filterFaskes('Semua')">Semua</button>
        <button class="filter-btn" onclick="filterFaskes('RSIA')">Rumah Sakit Ibu & Anak</button>
        <button class="filter-btn" onclick="filterFaskes('Puskesmas')">Puskesmas</button>
        <button class="filter-btn" onclick="filterFaskes('Bidan')">Bidan Praktik</button>
        
        <!-- Indikator GPS -->
        <span id="gpsStatus" style="margin-left: auto; font-size: 0.85rem; color: #ffafcc; font-weight: 600;">
            <i class="fas fa-spinner fa-spin"></i> Mencari Lokasi Anda...
        </span>
    </div>

    <div class="locator-layout">
        <!-- List Sebelah Kiri -->
        <aside class="list-container">
            <h3 style="margin-top:0; color: var(--text); border-bottom: 2px solid #eee; padding-bottom: 15px;">
                Rekomendasi Terdekat
            </h3>
            <div id="faskesList">
                <p style="text-align:center; color:#aaa; font-size: 0.9rem; margin-top: 30px;">Memuat data jarak lokasi...</p>
            </div>
        </aside>

        <!-- Area Map Sebelah Kanan -->
        <main class="map-container">
            <div id="map"></div>
        </main>
    </div>

    <div style="margin-top: 30px; text-align: center;">
        <a href="../dashboardgrowlife.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="script.js"></script>
</body>
</html>
