<?php
/**
 * Modul 12 — Landing Page
 * 
 * Initial page for Modul 12.
 * Each module uses the shared auth system (SSO)
 * and can define its own database schema.
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'Modul 12';
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
        <span class="text-gray-700 font-medium">Modul 12</span>
    </nav>

    <!-- Module Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Modul 12</h1>
        <p class="text-gray-500 mt-1">HealthEdu — Platform Kesehatan & Nutrisi</p>
    </div>

    <!-- HealthEdu App Frame -->
    <div class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm" style="height: 85vh;">
        <iframe
            src="<?= BASE_URL ?>/modules/modul_12/app/index.html"
            title="HealthEdu App"
            width="100%"
            height="100%"
            style="border: none; display: block;"
            allow="fullscreen"
        ></iframe>
    </div>

</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>