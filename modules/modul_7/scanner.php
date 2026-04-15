<?php
/**
 * Modul 7 — Scanner Page
 */
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
require_once __DIR__ . '/config/database.php';

requireLogin();
startSession();

// Tangkap nama dari login (dummy/contoh)
$userName = $_GET['name'] ?? getCurrentUser()['name'] ?? 'Pasien';

$pageTitle = 'Scanner Dermalyze.AI';
require_once __DIR__ . '/../../layout/header.php';
require_once __DIR__ . '/../../layout/navbar.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        font-family: 'Inter', sans-serif;
    }

    .scanner-wrapper {
        min-height: 80vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .welcome-text {
        color: #1e293b;
        font-size: 1.8rem;
        font-weight: 600;
        margin-bottom: 30px;
        text-align: center;
    }

    .welcome-text span {
        color: #3a7bd5;
        font-weight: 800;
    }
</style>

<div class="scanner-wrapper">
    <div class="welcome-text">
        Halo, <span><?= htmlspecialchars($userName) ?></span>. Siap untuk menganalisis wajah Anda?
    </div>
    <div style="width: 100%; max-width: 1000px;">
        <?php require_once __DIR__ . '/views/patient_view.php'; ?>
    </div>
</div>

<script>
    // Override the resetApp function so it redirects back to dashboard when done
    function resetApp() {
        window.location.href = 'index.php';
    }
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>