<?php
/**
 * Modul 7 — Lengkapi Profil Pasien (Skin Context)
 */
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$pageTitle = 'Lengkapi Profil - Dermalyze.AI';
require_once __DIR__ . '/../../layout/header.php';
require_once __DIR__ . '/../../layout/navbar.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body {
        background: linear-gradient(135deg, #FFF5F7 0%, #F0F7FF 100%);
        font-family: 'Quicksand', sans-serif;
    }
    .profile-container {
        max-width: 500px;
        margin: 50px auto;
        background: #ffffff;
        border-radius: 40px; 
        padding: 50px 40px;
        box-shadow: 0 25px 50px -12px rgba(255, 183, 206, 0.25);
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .profile-container::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 8px;
        background: linear-gradient(135deg, #FFB7CE, #FFD1DC);
    }
    .form-title { font-size: 2rem; font-weight: 800; color: #7D6E7D; margin-bottom: 10px; }
    .form-subtitle { color: #A79BA7; font-size: 0.95rem; margin-bottom: 35px; }
    .input-group { margin-bottom: 20px; text-align: left; }
    .input-label { display: block; margin-left: 20px; margin-bottom: 8px; font-size: 0.85rem; font-weight: 700; color: #FFB7CE; text-transform: uppercase; }
    .custom-input, .custom-select { width: 100%; padding: 16px 24px; border-radius: 40px; border: 2px solid #FFF0F5; background: #FFFBFC; font-size: 1rem; color: #7D6E7D; appearance: none; }
    .custom-select { text-align-last: center; cursor: pointer; }
    .btn-next { background: linear-gradient(135deg, #FFB7CE, #FFD1DC); color: white; padding: 18px 24px; border-radius: 40px; font-size: 1.1rem; font-weight: 700; border: none; width: 100%; cursor: pointer; box-shadow: 0 10px 25px rgba(255, 183, 206, 0.3); }
    .terms-text { font-size: 0.8rem; color: #94a3b8; text-align: left; }
</style>

<div class="profile-container">
    <h1 class="form-title">Kenalan Yuk! ✨</h1>
    <p class="form-subtitle">Bantu AI kami mengenal kulitmu lebih dekat.</p>

    <form action="scanner.php" method="POST" class="space-y-4">
        <div class="input-group">
            <input type="text" name="name" class="custom-input" placeholder="Nama Lengkap" required autofocus>
        </div>
        <div class="input-group">
            <input type="number" name="age" class="custom-input" placeholder="Usia Kamu (Tahun)" required>
        </div>
        <div class="input-group">
            <label class="input-label">Tipe Kulit</label>
            <select name="skin_type" class="custom-select" required>
                <option value="" disabled selected>Pilih Tipe Kulit</option>
                <option value="Berminyak">Berminyak (Oily)</option>
                <option value="Kering">Kering (Dry)</option>
                <option value="Kombinasi">Kombinasi (Combination)</option>
                <option value="Normal">Normal</option>
                <option value="Sensitif">Sensitif</option>
            </select>
        </div>
        <div class="input-group">
            <label class="input-label">Masalah Utama</label>
            <select name="concern" class="custom-select" required>
                <option value="" disabled selected>Apa keluhan utamamu?</option>
                <option value="Jerawat Aktif">Jerawat Aktif</option>
                <option value="Komedo">Komedo / Blackheads</option>
                <option value="Kemerahan">Kemerahan / Iritasi</option>
                <option value="Bekas Jerawat">Bekas Jerawat</option>
                <option value="Kulit Kusam">Kulit Kusam</option>
            </select>
        </div>
        <div class="flex items-start gap-3 mt-4">
            <input type="checkbox" id="terms" name="terms" required class="mt-1">
            <label for="terms" class="terms-text">Saya setuju memberikan data diri untuk keperluan analisis kulit di Dermalyze.AI.</label>
        </div>
        <div class="pt-4">
            <button type="submit" class="btn-next">SIAP, SCAN WAJAHKU!</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>