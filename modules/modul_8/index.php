<?php
/**
 * Modul 8 — React + Vite Application
 *
 * PHP serves authentication and passes user data to React.
 * React app runs via Vite (dev) or built dist/ (production).
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../config/database.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'Modul 8: Health Analytics';

// Use dev mode if manifest.json doesn't exist (production build not ready)
$isDev = !file_exists(__DIR__ . '/app/dist/manifest.json');
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
        <span class="text-gray-700 font-medium">Modul 8</span>
    </nav>

    <!-- Module Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Modul 8: Health Analytics</h1>
        <p class="text-gray-500 mt-1">Advanced health data visualization and patient outcomes tracking.</p>
    </div>

    <!-- Dashboard Preview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-700">Patient Trends</h3>
            <p class="text-sm text-gray-500 mt-1">Real-time health monitoring.</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-700">Analytics Report</h3>
            <p class="text-sm text-gray-500 mt-1">Monthly health performance.</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-700">Data Export</h3>
            <p class="text-sm text-gray-500 mt-1">Download diagnostic logs.</p>
        </div>
    </div>
    
    <div id="root" class="mt-8"></div>
    
    <script>
        window.__USER__ = <?= json_encode($user) ?>;
        window.__BASE_URL__ = "<?= BASE_URL ?>";
    </script>
    
    <?php if ($isDev): ?>
        <script type="module">
            import RefreshRuntime from 'http://localhost:5173/@react-refresh'
            RefreshRuntime.injectIntoGlobalHook(window)
            window.$RefreshReg$ = () => {}
            window.$RefreshSig$ = () => (type) => type
            window.__vite_plugin_react_preamble_installed__ = true
        </script>
        <script type="module" src="http://localhost:5173/@vite/client"></script>
        <script type="module" src="http://localhost:5173/src/main.tsx"></script>
    <?php else: ?>
        <?php
        $manifest = json_decode(file_get_contents(__DIR__ . '/app/dist/manifest.json'), true);
        foreach ($manifest as $file => $entry):
            if (isset($entry['file'])):
                if (str_ends_with($file, '.css')):
                    echo '<link rel="stylesheet" href="' . BASE_URL . '/modules/modul_8/app/dist/' . $entry['file'] . '">';
                endif;
            endif;
        endforeach;
        ?>
        <script type="module">
            import('./app/dist/<?= $manifest['src/main.tsx']['file'] ?>').catch(console.error);
        </script>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
