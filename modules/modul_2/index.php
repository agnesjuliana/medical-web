<?php
/**
 * Modul 2 - Landing Page / Redirector
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

// JURUS RAHASIA: Langsung lempar ke dashboard GrowLife
header("Location: dashboardgrowlife.php");
exit;

// Kode di bawah ini tidak akan dieksekusi lagi karena sudah di-redirect di atas
$user = getCurrentUser();
$pageTitle = 'Modul 2';
?>

<?php require_once __DIR__ . '/../../layout/header.php'; ?>
<?php require_once __DIR__ . '/../../layout/navbar.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-6">
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-cyan-600 transition-colors">Module Hub</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium">Modul 2</span>
    </nav>

    <!-- Module Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Modul 2</h1>
        <p class="text-gray-500 mt-1">This module is ready for development.</p>
    </div>

    <!-- Empty State -->
    <?= component_empty_state(
        'GrowLife App',
        'Website monitoring Stunting interaktif dan Ibu Hamil (Tugas Kelompok 2).',
        component_button('Akses Aplikasi GrowLife', [
            'variant' => 'primary',
            'href' => 'dashboardgrowlife.php'
        ])
    ) ?>

</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
