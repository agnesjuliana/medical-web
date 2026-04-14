<?php
/**
 * Modul 7 — Skrining Jerawat (Dashboard)
 */
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
require_once __DIR__ . '/database.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'Dermalyze.AI Dashboard';

// Ambil riwayat skrining dari database dermalyzeai
$histories = getPatientScreeningsHistory($user['id']);

require_once __DIR__ . '/../../layout/header.php';
require_once __DIR__ . '/../../layout/navbar.php';
?>

<!-- Custom font integration for premium look -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
.modal-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(8px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    margin-top: 40px;
}
@media(max-width: 1024px) {
    .dashboard-grid { grid-template-columns: 1fr; gap: 40px;}
}
</style>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" style="font-family: 'Inter', sans-serif;">
    <!-- Top Breadcrumb/Info -->
    <nav class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-500 font-medium">
            <a href="<?= BASE_URL ?>/index.php" class="hover:text-cyan-600 transition-colors flex items-center gap-1">Beranda</a>
            <svg class="w-4 h-4" stroke="currentColor" fill="none"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round"/></svg>
            <span>Analisis</span>
            <svg class="w-4 h-4" stroke="currentColor" fill="none"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round"/></svg>
            <span class="text-cyan-600 font-bold">Dermalyze.AI Dashboard</span>
        </div>
        <button class="bg-cyan-50 text-cyan-700 px-6 py-2 rounded-full font-semibold border border-cyan-100 hover:bg-cyan-100 transition shadow-sm">Konsultasi Ahli</button>
    </nav>

    <!-- Two-Column Layout -->
    <div class="dashboard-grid">
        <!-- LEFT COLUMN: Typography & Action -->
        <div style="display: flex; flex-direction: column; justify-content: center;">
            <h1 style="font-size: 4rem; font-weight: 800; color: #0f172a; line-height: 1.1; margin-bottom: 24px; letter-spacing: -0.02em;">
                Wajah Bersih,<br>
                <span style="background: linear-gradient(135deg, #00d2ff, #3a7bd5); -webkit-background-clip: text; color: transparent;">Solusi Saintifik.</span>
            </h1>
            <p style="font-size: 1.25rem; color: #64748b; margin-bottom: 40px; line-height: 1.6;">
                Pahami kebutuhan kulit Anda dengan pemindaian tingkat klinis berbasis Kecerdasan Buatan (AI) secara real-time. Temukan rahasia di balik setiap pori-pori.
            </p>
            <div>
                <button onclick="window.location.href='login.php'" style="background: linear-gradient(135deg, #00d2ff, #3a7bd5); color: white; padding: 20px 48px; border-radius: 40px; font-size: 1.2rem; font-weight: 700; box-shadow: 0 15px 30px rgba(0, 210, 255, 0.3); border: none; cursor: pointer; transition: transform 0.2s;">
                    Mulai Analisis
                </button>
            </div>
            
            <div style="margin-top: 80px; background: #ffffff; padding: 24px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); display:flex; align-items:center; gap:20px; border: 1px solid #f1f5f9;">
                 <div style="background: rgba(0, 210, 255, 0.1); padding: 16px; border-radius: 16px;">
                     <svg width="32" height="32" fill="none" stroke="#3a7bd5" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                 </div>
                 <div>
                     <div style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 4px;">Tingkat Akurasi 94.8%</div>
                     <div style="font-size:0.95rem; color:#64748b; font-weight: 500;">Model CNN Arsitektur Khusus</div>
                 </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Beauty Portrait Image -->
        <div style="display: flex; align-items: center; justify-content: center; height: 100%; min-height: 500px;">
            <div style="width: 100%; height: 100%; border-radius: 32px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15); position: relative;">
                <img src="assets/dermalyze_beauty.png" alt="Flawless Skin Dermatology" style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 30%; background: linear-gradient(to top, rgba(15,23,42,0.8), transparent);"></div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
