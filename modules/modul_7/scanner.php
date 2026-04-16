<?php
/**
 * Modul 7 — Scanner Page
 */
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
require_once __DIR__ . '/config/database.php';

requireLogin();
startSession();

// Tangkap nama dari login
$userName = $_GET['name'] ?? getCurrentUser()['name'] ?? 'Pasien';

$pageTitle = 'Scanner Dermalyze.AI';
require_once __DIR__ . '/../../layout/header.php';
require_once __DIR__ . '/../../layout/navbar.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body {
        background: linear-gradient(135deg, #FFF5F7 0%, #F0F7FF 100%);
        font-family: 'Quicksand', 'Inter', sans-serif;
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
        color: #7D6E7D; 
        font-size: 1.8rem;
        font-weight: 600;
        margin-bottom: 30px;
        text-align: center;
    }

    /* Polish: Gradient Text for Name */
    .welcome-text span {
        background: linear-gradient(135deg, #FFB7CE, #FFD1DC);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 800;
    }

    .history-link-wrapper {
       margin-bottom: 40px;
    }

    .history-link {
       display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #FFB7CE; /* Warna teks pink */
        font-weight: 600;
        font-size: 0.95rem;
        background: rgba(255, 255, 255, 0.7); /* Putih transparan glassmorphism */
        backdrop-filter: blur(8px);
        padding: 10px 24px;
        border-radius: 9999px;
        border: 1px solid #FFF0F5;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .history-link:hover {
        color: #FF8AAB;
        box-shadow: 0 8px 15px rgba(255, 183, 206, 0.2);
        border-color: #FFB7CE;
        transform: translateY(-2px);
    }

    .history-link svg {
        stroke: #FFB7CE;
    }
</style>

<div class="scanner-wrapper">
    <div class="welcome-text">
        Halo, <span><?= htmlspecialchars($userName) ?></span>. Siap untuk menganalisis wajah Anda?
    </div>

    <div class="history-link-wrapper">
        <a href="history.php" class="history-link">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            Lihat riwayat scan sebelumnya
        </a>
    </div>

    <div style="width: 100%; max-width: 1000px;">
        <?php require_once __DIR__ . '/views/patient_view.php'; ?>
    </div>
</div>

<script>
   
    function resetApp(newId = null) {
        if (newId) {
            // Jika ada ID baru, langsung ke halaman hasil detail
            window.location.href = 'results.php?id=' + newId;
        } else {
            // Jika tidak ada ID (misal user klik cancel), balik ke dashboard
            window.location.href = 'index.php';
        }
    }


</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>