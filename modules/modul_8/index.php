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
$isDev = isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modul 8</title>
    <!-- Pass user data to React -->
    <script>
        window.__USER__ = <?= json_encode($user) ?>;
        window.__BASE_URL__ = "<?= BASE_URL ?>";
    </script>
    <?php if ($isDev): ?>
        <!-- Vite dev server (http://localhost:5173) -->
        <script type="module" src="http://localhost:5173/@vite/client"></script>
        <script type="module" src="http://localhost:5173/src/main.tsx"></script>
    <?php else: ?>
        <!-- Production build -->
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
</head>
<body>
    <div id="root"></div>
</body>
</html>
