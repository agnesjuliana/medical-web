<?php
/**
 * Modul 3 — Landing Page
 * 
 * Initial page for Modul 3.
 * Each module uses the shared auth system (SSO)
 * and can define its own database schema.
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'Modul 3';
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
        <span class="text-gray-700 font-medium">Modul 3</span>
    </nav>

    <!-- Module Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Modul 3</h1>
        <p class="text-gray-500 mt-1">This module is ready for development.</p>
    </div>

    <!-- Empty State -->
    <?= component_empty_state(
        'No content yet',
        'This module is a blank canvas. Start building your features here.',
        component_button('Back to Module Hub', [
            'variant' => 'outline',
            'href' => BASE_URL . '/index.php',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'
        ])
    ) ?>

</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
