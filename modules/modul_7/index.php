<?php
/**
 * Modul 7 — Skrining Jerawat
 * Router based on User Role (Patient, Doctor, Admin)
 */
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$role = $user['role'] ?? 'patient';
$pageTitle = 'Modul 7 - Skrining Jerawat';

// --- DEBUG ROLE SWITCHER (For Testing Only) ---
if (isset($_GET['switch_role'])) {
    $_SESSION['user_role'] = $_GET['switch_role'];
    header("Location: " . BASE_URL . "/modules/modul_7/index.php");
    exit;
}
// ----------------------------------------------

require_once __DIR__ . '/../../layout/header.php';
require_once __DIR__ . '/../../layout/navbar.php';
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-6">
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-cyan-600 transition-colors">Module Hub</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium">Skrining Jerawat</span>
    </nav>
    
    <!-- DEBUG ROLE SWITCHER UI -->
    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl flex items-center justify-between">
        <div class="text-sm text-yellow-800">
            <strong>Mode Uji Coba:</strong> Peran simulasi Anda saat ini adalah <span class="uppercase font-bold"><?= htmlspecialchars($role) ?></span>.
        </div>
        <div class="flex gap-2">
            <a href="?switch_role=patient" class="text-xs px-3 py-1.5 <?= $role=='patient' ? 'bg-cyan-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' ?> rounded-lg text-decoration-none shadow-sm transition">Pasien</a>
            <a href="?switch_role=doctor" class="text-xs px-3 py-1.5 <?= $role=='doctor' ? 'bg-cyan-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' ?> rounded-lg text-decoration-none shadow-sm transition">Dokter</a>
            <a href="?switch_role=admin" class="text-xs px-3 py-1.5 <?= $role=='admin' ? 'bg-cyan-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' ?> rounded-lg text-decoration-none shadow-sm transition">Admin</a>
        </div>
    </div>

    <?php
    // Router Logic
    if ($role === 'doctor') {
        require_once __DIR__ . '/views/doctor_view.php';
    } elseif ($role === 'admin') {
        require_once __DIR__ . '/views/admin_view.php';
    } else {
        require_once __DIR__ . '/views/patient_view.php';
    }
    ?>
</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
