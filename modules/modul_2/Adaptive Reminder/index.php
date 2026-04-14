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
    <title>GrowLife - Adaptive Reminder</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header class="page-header">
        <div class="logo"><i class="fas fa-bell"></i> Adaptive Reminder</div>
        <p>Jadwal otomatis Imunisasi Bayi dan Kontrol Kehamilan Bunda untuk: <b id="namaAnakHeader" style="color:var(--pink-dark)">...</b></p>
    </header>

    <!-- BANNER NOTIFIKASI H-1 DAN HARI H -->
    <div id="notificationBanner" style="display:none; background: linear-gradient(135deg, #ffc8dd 0%, #ffafcc 100%); border-radius: 15px; padding: 25px 25px; margin-bottom: 25px; box-shadow: 0 5px 20px rgba(251,111,146, 0.4); border-left: 8px solid #e05e80;">
    </div>

    <div class="monitor-layout">
        <!-- Form Pengaturan Tanggal -->
        <section class="card input-section">
            <h3 class="card-title">Setup Patokan Jadwal</h3>
            <p style="font-size: 0.85rem; color: #888;">Lengkapi data kalender untuk men-generate jadwal puluhan kontrol secara instan.</p>
            
            <div class="input-group">
                <label id="labelTgl">Tanggal Lahir Anak</label>
                <input type="date" id="tglLahir" class="custom-input">
            </div>

            <button class="btn btn-pink" onclick="generateSchedule()">Buat Kalender Otomatis</button>
            <p style="font-size: 0.75rem; color: #aaa; margin-top: 15px; text-align: center;">Diadaptasi dari logika reminder PHP versi 1.0</p>
        </section>

        <!-- Area Hasil / Kalender -->
        <section class="card schedule-section">
            <h3 class="card-title">Daftar Pengingat Mendatang</h3>
            <div id="scheduleTimeline">
                <!-- Injeksi JS -->
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
