<?php
/**
 * Module Hub — Index Page
 * 
 * Protected page (requires login).
 * Shows a grid of module cards for navigation.
 */

require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'Module Hub';

// Module definitions
$modules = [
    1  => ['name' => 'Modul 1',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>', 'color' => 'from-cyan-500 to-cyan-600'],
    2  => ['name' => 'Modul 2',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>', 'color' => 'from-blue-500 to-blue-600'],
    3  => ['name' => 'Modul 3',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>', 'color' => 'from-violet-500 to-violet-600'],
    4  => ['name' => 'Calorie Care','icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>', 'color' => 'from-emerald-500 to-emerald-600', 'bg_image' => 'https://images.unsplash.com/photo-1550684848-fac1c5b4e853?q=80&w=800&auto=format&fit=crop'],
    5  => ['name' => 'Modul 5',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>', 'color' => 'from-rose-500 to-rose-600'],
    6  => ['name' => 'Modul 6',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'color' => 'from-amber-500 to-amber-600'],
    7  => ['name' => 'Modul 7',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>', 'color' => 'from-indigo-500 to-indigo-600'],
    8  => ['name' => 'Modul 8',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>', 'color' => 'from-teal-500 to-teal-600'],
    9  => ['name' => 'SIMRS-TB',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>', 'color' => 'from-teal-500 to-emerald-600', 'logo' => 'logo.png'],
    10 => ['name' => 'Modul 10', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>', 'color' => 'from-orange-500 to-orange-600'],
    11 => ['name' => 'Cephalo AI', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>', 'color' => 'from-slate-500 to-slate-600', 'logo' => 'logo.svg'],
    12 => ['name' => 'Modul 12', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>', 'color' => 'from-sky-500 to-sky-600'],
    13 => ['name' => 'Modul 13', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>', 'color' => 'from-fuchsia-500 to-fuchsia-600'],
];
?>
<?php require_once __DIR__ . '/layout/header.php'; ?>
<?php require_once __DIR__ . '/layout/navbar.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Welcome back, <?= htmlspecialchars($user['name'] ?? 'User') ?> 👋</h1>
        <p class="text-gray-500 mt-1">Select a module to get started.</p>
    </div>

    <!-- Module Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        <?php foreach ($modules as $id => $module): ?>

        <?php if (!empty($module['logo'])): ?>
        <!-- Logo Card with Vanta background -->
        <a href="<?= BASE_URL ?>/modules/modul_<?= $id ?>/index.php"
           class="group relative rounded-2xl border border-gray-700/50 shadow-sm p-6 hover:shadow-xl hover:shadow-cyan-500/10 hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden">
            <div id="vanta-bg-<?= $id ?>" class="absolute inset-0 z-0 rounded-2xl overflow-hidden"></div>
            <div class="relative z-10 flex flex-col flex-1">
                <div class="w-12 h-12 rounded-xl overflow-hidden shadow-lg shadow-cyan-500/20 mb-4 group-hover:scale-110 transition-transform duration-300 border border-white/20">
                    <img src="<?= BASE_URL ?>/modules/modul_<?= $id ?>/<?= $module['logo'] ?>" alt="<?= htmlspecialchars($module['name']) ?>" class="w-full h-full object-cover">
                </div>
                <h3 class="text-base font-semibold text-white group-hover:text-cyan-300 transition-colors"><?= htmlspecialchars($module['name']) ?></h3>
                <p class="text-sm text-white/50 mt-1">Click to open module</p>
                <div class="mt-auto pt-4 flex items-center text-sm text-white/40 group-hover:text-cyan-400 transition-colors">
                    <span class="font-medium">Open</span>
                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>
        </a>

        <?php else: ?>
        <!-- Standard Module Card -->
        <a href="<?= BASE_URL ?>/modules/modul_<?= $id ?>/index.php"
           class="group bg-white rounded-2xl border border-gray-200 shadow-sm p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col">
            <div class="w-12 h-12 bg-gradient-to-br <?= $module['color'] ?> rounded-xl flex items-center justify-center shadow-sm mb-4 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $module['icon'] ?></svg>
            </div>
            <h3 class="text-base font-semibold text-gray-800 group-hover:text-cyan-600 transition-colors"><?= htmlspecialchars($module['name']) ?></h3>
            <p class="text-sm text-gray-400 mt-1">Click to open module</p>
            <div class="mt-auto pt-4 flex items-center text-sm text-gray-400 group-hover:text-cyan-500 transition-colors">
                <span class="font-medium">Open</span>
                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
        </a>
        <?php endif; ?>

        <?php endforeach; ?>
    </div>

    <!-- Component Preview Link -->
    <div class="mt-8 text-center">
        <a href="<?= BASE_URL ?>/components/preview/index.php"
           class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-cyan-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            View Component Kit Preview
        </a>
    </div>

</main>

<!-- Vanta.js NET for SIMRS-TB card and WAVES for Modul 11 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.waves.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var el9 = document.getElementById('vanta-bg-9');
    if (el9) {
        VANTA.NET({
            el: el9,
            mouseControls: true,
            touchControls: true,
            gyroControls: false,
            minHeight: 200.00,
            minWidth: 200.00,
            scale: 1.00,
            scaleMobile: 1.00,
            color: 0xffff,
            backgroundColor: 0x0,
            points: 9.00,
            spacing: 17.00
        });
    }

    var el11 = document.getElementById('vanta-bg-11');
    if (el11) {
        VANTA.WAVES({
            el: el11,
            mouseControls: true,
            touchControls: true,
            gyroControls: false,
            minHeight: 200.00,
            minWidth: 200.00,
            scale: 1.00,
            scaleMobile: 1.00,
            color: 0x011329,
            shininess: 45.00,
            waveHeight: 15.00,
            waveSpeed: 0.85,
            zoom: 0.85
        });
    }
});
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

