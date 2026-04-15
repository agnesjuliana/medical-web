<?php
/**
 * Modul 7 — Login / Profil Pasien
 */
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$pageTitle = 'Lengkapi Profil - Dermalyze.AI';
require_once __DIR__ . '/../../layout/header.php';
// Tidak perlu navbar jika ingin fokus seperti landing page, namun kita konsisten dengan sistem.
require_once __DIR__ . '/../../layout/navbar.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        font-family: 'Inter', sans-serif;
    }

    .profile-container {
        max-width: 500px;
        margin: 50px auto;
        background: #ffffff;
        border-radius: 32px;
        padding: 50px 40px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .profile-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 8px;
        background: linear-gradient(135deg, #00d2ff, #3a7bd5);
    }

    .form-title {
        font-size: 2.2rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 10px;
        line-height: 1.2;
    }

    .form-subtitle {
        color: #64748b;
        font-size: 1rem;
        margin-bottom: 40px;
    }

    .input-group {
        margin-bottom: 25px;
        text-align: left;
    }

    .custom-input {
        width: 100%;
        padding: 18px 24px;
        border-radius: 40px;
        border: 2px solid #e2e8f0;
        background: #f8fafc;
        font-size: 1rem;
        font-family: 'Inter', sans-serif;
        color: #334155;
        transition: all 0.3s;
        text-align: center;
        /* Mengikuti referensi gambar */
    }

    .custom-input:focus {
        outline: none;
        border-color: #3a7bd5;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(58, 123, 213, 0.1);
    }

    .custom-input::placeholder {
        color: #94a3b8;
    }

    .btn-group {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-top: 40px;
    }

    .btn-next {
        background: linear-gradient(135deg, #00d2ff, #3a7bd5);
        color: white;
        padding: 18px 24px;
        border-radius: 40px;
        font-size: 1.1rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 10px 25px rgba(58, 123, 213, 0.3);
        width: 100%;
    }

    .btn-next:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(58, 123, 213, 0.4);
    }

    .btn-back {
        background: #f1f5f9;
        color: #64748b;
        padding: 18px 24px;
        border-radius: 40px;
        font-size: 1.1rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: background 0.3s, color 0.3s;
        width: 100%;
    }

    .btn-back:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .terms-box {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        text-align: left;
        margin-top: 25px;
        margin-bottom: 10px;
    }

    .terms-text {
        font-size: 0.85rem;
        color: #64748b;
        line-height: 1.5;
    }

    .terms-text a {
        color: #3a7bd5;
        font-weight: 600;
        text-decoration: none;
    }

    .custom-checkbox {
        width: 20px;
        height: 20px;
        border-radius: 6px;
        border: 2px solid #cbd5e1;
        background: #f8fafc;
        cursor: pointer;
        appearance: none;
        position: relative;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .custom-checkbox:checked {
        background: #3a7bd5;
        border-color: #3a7bd5;
    }

    .custom-checkbox:checked::after {
        content: '✓';
        position: absolute;
        color: white;
        font-size: 14px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
</style>

<div class="profile-container">
    <h1 class="form-title">Complete<br>your profile</h1>
    <p class="form-subtitle">Lengkapi identitas Anda sebelum analisis.</p>

    <form action="scanner.php" method="GET">
        <div class="input-group">
            <input type="text" name="name" class="custom-input" placeholder="Name" required autofocus>
        </div>

        <div class="input-group">
            <input type="email" name="email" class="custom-input" placeholder="Email" required>
        </div>

        <div class="terms-box">
            <input type="checkbox" id="terms" name="terms" class="custom-checkbox" required>
            <label for="terms" class="terms-text">
                I have read and agree to Dermalyze.AI's Terms of Use and consent to the processing of my personal data
                in accordance with <a href="#">[Privacy Policy]</a>.
            </label>
        </div>

        <div class="btn-group">
            <button type="button" class="btn-back" onclick="window.location.href='index.php'">BACK</button>
            <button type="submit" class="btn-next">NEXT</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>