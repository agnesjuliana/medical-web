<?php
/**
 * SIMRS-TB — Shared Sidebar Navigation
 * 
 * Include this file in all modul_9 pages.
 * Set $activePage before including to highlight the active menu item.
 * 
 * Usage: $activePage = 'dashboard'; require_once __DIR__ . '/_sidebar.php';
 */

$sidebarItems = [
    'dashboard'  => ['label' => 'Dashboard',           'href' => 'dashboard.php',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10-1a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1h-4a1 1 0 01-1-1v-5z"/>'],
    'screening'  => ['label' => 'Skrining AI Batuk',   'href' => 'screening.php',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>'],
    'rekam-medis'=> ['label' => 'Rekam Medis',         'href' => 'rekam-medis.php',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>'],
    'farmasi'    => ['label' => 'Farmasi & PMO',       'href' => 'farmasi.php',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>'],
    'jadwal'     => ['label' => 'Jadwal Kontrol',      'href' => 'jadwal.php',       'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'],
    'monitoring' => ['label' => 'Monitoring Kepatuhan', 'href' => 'monitoring.php',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
    'analitik'   => ['label' => 'Analitik & SITB',     'href' => 'analitik.php',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
];

$activePage = $activePage ?? 'dashboard';
?>

<!-- SIMRS-TB Sidebar -->
<aside id="tb-sidebar" class="fixed left-0 top-16 w-64 h-[calc(100vh-4rem)] bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 border-r border-slate-700/50 z-40 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 flex flex-col">
    
    <!-- Brand -->
    <div class="px-5 py-5 border-b border-slate-700/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-teal-400 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-teal-500/20">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-white font-bold text-sm tracking-wide">SIMRS-TB</h2>
                <p class="text-slate-400 text-xs">Manajemen Tuberkulosis</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto scrollbar-thin">
        <p class="px-3 mb-2 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Menu Utama</p>
        <?php foreach ($sidebarItems as $key => $item): 
            $isActive = ($activePage === $key);
            $activeClass = $isActive 
                ? 'bg-teal-500/15 text-teal-400 border-l-2 border-teal-400' 
                : 'text-slate-400 hover:bg-slate-700/50 hover:text-slate-200 border-l-2 border-transparent';
        ?>
        <a href="<?= $item['href'] ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-r-lg text-sm font-medium transition-all duration-200 group <?= $activeClass ?>">
            <svg class="w-5 h-5 shrink-0 <?= $isActive ? 'text-teal-400' : 'text-slate-500 group-hover:text-slate-300' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <?= $item['icon'] ?>
            </svg>
            <span><?= $item['label'] ?></span>
        </a>
        <?php endforeach; ?>
    </nav>

    <!-- Dark Mode Toggle + Bottom Info -->
    <div class="px-4 py-4 border-t border-slate-700/50 space-y-3">
        <!-- Dark Mode Toggle -->
        <button onclick="toggleDarkMode()" id="darkModeBtn"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700/50 hover:bg-slate-700/60 transition-all duration-300 group cursor-pointer">
            <div class="flex items-center gap-2.5">
                <!-- Sun icon (shown in dark mode → click to go light) -->
                <svg id="sunIcon" class="w-4.5 h-4.5 text-amber-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <!-- Moon icon (shown in light mode → click to go dark) -->
                <svg id="moonIcon" class="w-4.5 h-4.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <span id="darkModeLabel" class="text-xs font-medium text-slate-400">Mode Gelap</span>
            </div>
            <!-- Toggle switch -->
            <div id="toggleTrack" class="w-9 h-5 bg-slate-700 rounded-full relative transition-colors duration-500">
                <div id="toggleThumb" class="absolute top-0.5 left-0.5 w-4 h-4 bg-slate-400 rounded-full shadow-sm transition-all duration-500"></div>
            </div>
        </button>

        <div class="bg-slate-800/80 rounded-xl p-3 border border-slate-700/50">
            <div class="flex items-center gap-2 mb-1.5">
                <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                <span class="text-xs font-medium text-emerald-400">Sistem Aktif</span>
            </div>
            <p class="text-[10px] text-slate-500">Modul 9 • SIMRS-TB v1.0</p>
        </div>
    </div>
</aside>

<!-- Sidebar Overlay (mobile) -->
<div id="tb-sidebar-overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-30 hidden lg:hidden" onclick="toggleTbSidebar()"></div>

<!-- Mobile Sidebar Toggle -->
<button onclick="toggleTbSidebar()" 
        class="fixed bottom-5 left-5 z-50 lg:hidden w-12 h-12 bg-gradient-to-br from-teal-500 to-emerald-600 text-white rounded-full shadow-lg shadow-teal-500/30 flex items-center justify-center hover:scale-110 active:scale-95 transition-transform">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
</button>

<script>
function toggleTbSidebar() {
    const sidebar = document.getElementById('tb-sidebar');
    const overlay = document.getElementById('tb-sidebar-overlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

// ═══════════════════════════════════════
// DARK MODE SYSTEM
// ═══════════════════════════════════════
function initDarkMode() {
    const saved = localStorage.getItem('simrstb-darkmode');
    if (saved === 'true') {
        document.body.classList.add('dark-mode');
        updateToggleUI(true);
    }
}

function toggleDarkMode() {
    const isDark = document.body.classList.toggle('dark-mode');
    localStorage.setItem('simrstb-darkmode', isDark);
    updateToggleUI(isDark);
}

function updateToggleUI(isDark) {
    const sun = document.getElementById('sunIcon');
    const moon = document.getElementById('moonIcon');
    const track = document.getElementById('toggleTrack');
    const thumb = document.getElementById('toggleThumb');
    const label = document.getElementById('darkModeLabel');

    if (isDark) {
        sun.classList.remove('hidden');
        moon.classList.add('hidden');
        track.classList.remove('bg-slate-700');
        track.classList.add('bg-teal-600');
        thumb.style.transform = 'translateX(16px)';
        thumb.classList.remove('bg-slate-400');
        thumb.classList.add('bg-white');
        label.textContent = 'Mode Terang';
    } else {
        sun.classList.add('hidden');
        moon.classList.remove('hidden');
        track.classList.add('bg-slate-700');
        track.classList.remove('bg-teal-600');
        thumb.style.transform = 'translateX(0)';
        thumb.classList.add('bg-slate-400');
        thumb.classList.remove('bg-white');
        label.textContent = 'Mode Gelap';
    }
}

// Initialize on load
initDarkMode();
</script>

<style>
/* ═══════════════════════════════════════
   SIDEBAR SCROLLBAR
   ═══════════════════════════════════════ */
.scrollbar-thin::-webkit-scrollbar { width: 3px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }

/* ═══════════════════════════════════════
   SMOOTH TRANSITIONS FOR DARK MODE
   All color-related properties animate
   ═══════════════════════════════════════ */
*, *::before, *::after {
    transition: background-color 0.45s cubic-bezier(0.4, 0, 0.2, 1),
                border-color 0.45s cubic-bezier(0.4, 0, 0.2, 1),
                color 0.45s cubic-bezier(0.4, 0, 0.2, 1),
                box-shadow 0.45s cubic-bezier(0.4, 0, 0.2, 1),
                fill 0.45s cubic-bezier(0.4, 0, 0.2, 1),
                stroke 0.45s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ═══════════════════════════════════════
   DARK MODE OVERRIDES
   Applied when body has .dark-mode class
   ═══════════════════════════════════════ */

/* ─── Main Background ─── */
body.dark-mode main,
body.dark-mode .bg-gray-50 { 
    background-color: #0f172a !important; /* slate-900 */
}

/* ─── Cards / White Backgrounds ─── */
body.dark-mode .bg-white {
    background-color: #1e293b !important; /* slate-800 */
    border-color: #334155 !important;     /* slate-700 */
}

/* ─── Text Colors ─── */
body.dark-mode .text-gray-800,
body.dark-mode .text-gray-900 {
    color: #f1f5f9 !important; /* slate-100 */
}
body.dark-mode .text-gray-700 {
    color: #e2e8f0 !important; /* slate-200 */
}
body.dark-mode .text-gray-600 {
    color: #cbd5e1 !important; /* slate-300 */
}
body.dark-mode .text-gray-500 {
    color: #94a3b8 !important; /* slate-400 */
}
body.dark-mode .text-gray-400 {
    color: #64748b !important; /* slate-500 */
}

/* ─── Borders ─── */
body.dark-mode .border-gray-100,
body.dark-mode .border-gray-200 {
    border-color: #334155 !important; /* slate-700 */
}
body.dark-mode .divide-gray-50 > :not([hidden]) ~ :not([hidden]),
body.dark-mode .divide-gray-100 > :not([hidden]) ~ :not([hidden]) {
    border-color: #1e293b !important;
}

/* ─── Inputs & Selects ─── */
body.dark-mode input,
body.dark-mode select,
body.dark-mode textarea,
body.dark-mode .bg-gray-50 input,
body.dark-mode .bg-gray-50 select {
    background-color: #0f172a !important;
    border-color: #334155 !important;
    color: #e2e8f0 !important;
}
body.dark-mode input::placeholder,
body.dark-mode textarea::placeholder {
    color: #475569 !important;
}

/* ─── Table Rows ─── */
body.dark-mode .bg-gray-50\/50,
body.dark-mode tr.bg-gray-50\/50,
body.dark-mode thead tr {
    background-color: #162032 !important;
}
body.dark-mode tr:hover,
body.dark-mode .hover\:bg-teal-50\/30:hover,
body.dark-mode .hover\:bg-gray-50\/50:hover,
body.dark-mode .hover\:bg-gray-50:hover {
    background-color: rgba(20, 184, 166, 0.08) !important;
}

/* ─── Breadcrumb ─── */
body.dark-mode nav .hover\:text-teal-600:hover {
    color: #2dd4bf !important;
}

/* ─── Stat Cards Hover ─── */
body.dark-mode .hover\:shadow-md:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
}

/* ─── Dropdown / Select backgrounds ─── */
body.dark-mode select option {
    background-color: #1e293b !important;
    color: #e2e8f0 !important;
}

/* ─── Alert Overrides ─── */
body.dark-mode .bg-amber-50 {
    background-color: rgba(245, 158, 11, 0.1) !important;
    border-color: rgba(245, 158, 11, 0.25) !important;
}
body.dark-mode .bg-red-50 {
    background-color: rgba(239, 68, 68, 0.1) !important;
    border-color: rgba(239, 68, 68, 0.25) !important;
}
body.dark-mode .bg-blue-50 {
    background-color: rgba(59, 130, 246, 0.1) !important;
    border-color: rgba(59, 130, 246, 0.25) !important;
}
body.dark-mode .bg-emerald-50,
body.dark-mode .bg-green-50 {
    background-color: rgba(16, 185, 129, 0.1) !important;
    border-color: rgba(16, 185, 129, 0.25) !important;
}

/* ─── Teal accent backgrounds ─── */
body.dark-mode .bg-teal-50 {
    background-color: rgba(20, 184, 166, 0.1) !important;
}
body.dark-mode .bg-teal-50\/30 {
    background-color: rgba(20, 184, 166, 0.06) !important;
}

/* ─── Gradient overlays for cards ─── */
body.dark-mode .bg-gradient-to-r.from-teal-50.to-emerald-50 {
    background: linear-gradient(to right, rgba(20,184,166,0.08), rgba(16,185,129,0.08)) !important;
    border-color: rgba(20, 184, 166, 0.2) !important;
}

/* ─── Monitoring: teal-100 & emerald-100 initials ─── */
body.dark-mode .from-teal-100 {
    --tw-gradient-from: rgba(20,184,166,0.15) !important;
}
body.dark-mode .to-emerald-100 {
    --tw-gradient-to: rgba(16,185,129,0.15) !important;
}

/* ─── Progress bars keep their colors ─── */
body.dark-mode .bg-gray-200 {
    background-color: #334155 !important;
}

/* ─── Pagination ─── */
body.dark-mode .hover\:bg-gray-100:hover {
    background-color: #334155 !important;
}

/* ─── Monitoring Risk Badges ─── */
body.dark-mode .bg-emerald-50.border-emerald-200 { background-color: rgba(16,185,129,0.1) !important; border-color: rgba(16,185,129,0.25) !important; }
body.dark-mode .bg-blue-50.border-blue-200 { background-color: rgba(59,130,246,0.1) !important; border-color: rgba(59,130,246,0.25) !important; }
body.dark-mode .bg-amber-50.border-amber-200 { background-color: rgba(245,158,11,0.1) !important; border-color: rgba(245,158,11,0.25) !important; }
body.dark-mode .bg-red-50.border-red-200 { background-color: rgba(239,68,68,0.1) !important; border-color: rgba(239,68,68,0.25) !important; }

/* ─── Jadwal Calendar ─── */
body.dark-mode .hover\:bg-teal-50:hover {
    background-color: rgba(20, 184, 166, 0.1) !important;
}
body.dark-mode .ring-teal-400 {
    --tw-ring-color: rgba(45, 212, 191, 0.5) !important;
}

/* ─── Farmasi tab buttons ─── */
body.dark-mode .hover\:bg-gray-50:hover {
    background-color: rgba(255,255,255,0.05) !important;
}

/* ─── Screening waveform ─── */
body.dark-mode .border-dashed {
    border-color: #475569 !important;
}
body.dark-mode .hover\:border-teal-400:hover {
    border-color: #2dd4bf !important;
}
body.dark-mode .hover\:bg-teal-50\/30:hover {
    background-color: rgba(20,184,166,0.06) !important;
}

/* ─── Analitik SITB sync ─── */
body.dark-mode .bg-blue-50\/50 {
    background-color: rgba(59, 130, 246, 0.08) !important;
}

/* ─── Modal Override ─── */
body.dark-mode .modal-content,
body.dark-mode [class*="bg-white"][class*="rounded"] {
    background-color: #1e293b !important;
}

/* ─── Navbar Override ─── */
body.dark-mode .bg-white.shadow-sm,
body.dark-mode nav.bg-white {
    background-color: #1e293b !important;
    border-color: #334155 !important;
}
</style>
