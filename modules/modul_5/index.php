<?php
/**
 * Modul 5 — Landing Page
 * 
 * Initial page for Modul 5.
 * Each module uses the shared auth system (SSO)
 * and can define its own database schema.
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
require_once __DIR__ . '/../../config/database.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'Modul 5';
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>
<?php require_once __DIR__ . '/../../layout/navbar.php'; ?>

<?php

try {
    $pdo = getAppDBConnection();

    $stmt = $pdo->query("
        SELECT id, problem, title, methodology, skills, result, impact, documentation
        FROM projects
        ORDER BY id DESC
    ");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Konversi methodology dan skills menjadi array
    foreach ($projects as &$project) {
        $project['methodology'] = !empty($project['methodology'])
            ? array_map('trim', preg_split('/[\n,→]+/', $project['methodology']))
            : [];

        $project['skills'] = !empty($project['skills'])
            ? array_map('trim', preg_split('/[\n,•]+/', $project['skills']))
            : [];

        // Nilai default jika kolom tidak tersedia
        $project['icon'] = "🔬";
        $project['title'] = $project['title'] ?? 'Untitled Project';
        $project['question'] = $project['question'] ?? $project['problem'];
    }
    unset($project);
} catch (Exception $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">



</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>