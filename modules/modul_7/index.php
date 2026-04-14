<?php
/**
 * Modul 7 — Dermalyze.ai Landing Page
 * * Halaman utama untuk modul deteksi jerawat.
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
// 1. Ubah Judul Halaman
$pageTitle = 'Dermalyze.ai — Acne Detection';
?>

<?php require_once __DIR__ . '/../../layout/header.php'; ?>
<?php require_once __DIR__ . '/../../layout/navbar.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-6">
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-cyan-600 transition-colors">Module Hub</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium">Dermalyze.ai</span>
    </nav>

    <div class="mb-8 border-b border-gray-100 pb-6">
        <h1 class="text-3xl font-extrabold text-cyan-700">Dermalyze.ai</h1>
        <p class="text-gray-500 mt-2 text-lg">Solusi cerdas teknologi medis untuk kesehatan kulitmu, Dayana.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center mb-4 text-cyan-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">Scan Jerawat</h2>
            <p class="text-gray-600 mb-6">Gunakan kamera atau upload foto wajah untuk mendeteksi jenis acne secara instan menggunakan AI.</p>
            <a href="scan.php" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-cyan-600 hover:bg-cyan-700 transition-colors w-full">
                Mulai Deteksi
            </a>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4 text-teal-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">Cek Kandungan</h2>
            <p class="text-gray-600 mb-6">Pelajari bahan aktif (ingredients) yang paling cocok untuk membantu pemulihan jenis kulitmu.</p>
            <a href="ingredients.php" class="inline-flex items-center justify-center px-5 py-3 border border-cyan-600 text-base font-medium rounded-md text-cyan-600 bg-transparent hover:bg-cyan-50 transition-colors w-full">
                Lihat Katalog
            </a>
        </div>

    </div>

</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>