<?php
/**
 * Modul 7 — Skrining Jerawat (Dashboard)
 */
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
require_once __DIR__ . '/config/database.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'Dermalyze.AI Dashboard';

// Ambil riwayat skrining dari database dermalyzeai
$histories = getPatientScreeningsHistory($user['id']);

require_once __DIR__ . '/../../layout/header.php';
require_once __DIR__ . '/../../layout/navbar.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        margin-top: 40px;
    }

    @media(max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 40px;
        }
    }
</style>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" style="font-family: 'Inter', sans-serif;">
    <nav class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-500 font-medium">
            <a href="<?= BASE_URL ?>/index.php"
                class="hover:text-cyan-600 transition-colors flex items-center gap-1">Beranda</a>
            <svg class="w-4 h-4" stroke="currentColor" fill="none">
                <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" />
            </svg>
            <span>Analisis</span>
            <svg class="w-4 h-4" stroke="currentColor" fill="none">
                <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" />
            </svg>
            <span class="text-cyan-600 font-bold">Dermalyze.AI Dashboard</span>
        </div>

        <a href="history.php"
            class="bg-cyan-50 text-cyan-700 px-6 py-2 rounded-full font-semibold border border-cyan-100 hover:bg-cyan-100 transition shadow-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Riwayat Analisis
        </a>
    </nav>

    <div class="dashboard-grid">
        <div style="display: flex; flex-direction: column; justify-content: center;">
            <h1
                style="font-size: 4rem; font-weight: 800; color: #0f172a; line-height: 1.1; margin-bottom: 24px; letter-spacing: -0.02em;">
                Wajah Bersih,<br>
                <span
                    style="background: linear-gradient(135deg, #00d2ff, #3a7bd5); -webkit-background-clip: text; color: transparent;">Solusi
                    Saintifik.</span>
            </h1>
            <p style="font-size: 1.25rem; color: #64748b; margin-bottom: 40px; line-height: 1.6;">
                Pahami kebutuhan kulit Anda dengan pemindaian tingkat klinis berbasis Kecerdasan Buatan (AI) secara
                real-time. Temukan rahasia di balik setiap pori-pori.
            </p>
            <div>
                <button onclick="window.location.href='login.php'"
                    style="background: linear-gradient(135deg, #00d2ff, #3a7bd5); color: white; padding: 20px 48px; border-radius: 40px; font-size: 1.2rem; font-weight: 700; box-shadow: 0 15px 30px rgba(0, 210, 255, 0.3); border: none; cursor: pointer; transition: transform 0.2s;">
                    Mulai Analisis
                </button>
            </div>
        </div>

        <div style="display: flex; align-items: center; justify-content: center; height: 100%; min-height: 500px;">
            <div
                style="width: 100%; height: 100%; border-radius: 32px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15); position: relative;">
                <img src="assets/dermalyze_beauty.png" alt="Flawless Skin Dermatology"
                    style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                <div
                    style="position: absolute; bottom: 0; left: 0; right: 0; height: 30%; background: linear-gradient(to top, rgba(15,23,42,0.8), transparent);">
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>