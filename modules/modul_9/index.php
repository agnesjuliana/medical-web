<?php
/**
 * SIMRS-TB — Landing Page
 * 
 * Halaman informasi sebelum masuk ke dashboard utama
 * Background: Vanta.js RINGS animation
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'SIMRS-TB — Sistem Informasi Manajemen RS Tuberkulosis';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <meta name="description" content="Platform SIMRS-TB terintegrasi dengan AI Deep Learning untuk skrining batuk, manajemen pengobatan, dan sinkronisasi SITB Kemenkes.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; overflow-x: hidden; }

        /* Vanta container */
        #vanta-bg {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 0;
        }

        /* Glass card */
        .glass {
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .glass-strong {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.12);
        }

        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes slideLeft {
            from { opacity: 0; transform: translateX(60px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes slideRight {
            from { opacity: 0; transform: translateX(-60px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(20,184,166,0.3); }
            50% { box-shadow: 0 0 40px rgba(20,184,166,0.6); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        @keyframes count-up {
            from { opacity: 0; transform: scale(0.5); }
            to   { opacity: 1; transform: scale(1); }
        }
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .animate-fade-up   { animation: fadeUp 0.8s ease-out forwards; }
        .animate-fade-in   { animation: fadeIn 0.6s ease-out forwards; }
        .animate-slide-left  { animation: slideLeft 0.8s ease-out forwards; }
        .animate-slide-right { animation: slideRight 0.8s ease-out forwards; }
        .animate-float      { animation: float 3s ease-in-out infinite; }
        .animate-pulse-glow { animation: pulse-glow 2s ease-in-out infinite; }
        .animate-count      { animation: count-up 0.6s ease-out forwards; }

        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        .delay-500 { animation-delay: 0.5s; }
        .delay-600 { animation-delay: 0.6s; }
        .delay-700 { animation-delay: 0.7s; }
        .delay-800 { animation-delay: 0.8s; }

        .start-hidden { opacity: 0; }

        /* Scroll indicator */
        .scroll-indicator {
            animation: float 2s ease-in-out infinite;
        }

        /* Feature icon hover */
        .feature-card:hover .feature-icon {
            transform: scale(1.15) rotate(5deg);
            box-shadow: 0 0 30px rgba(20,184,166,0.4);
        }
        .feature-card:hover {
            transform: translateY(-4px);
            border-color: rgba(20,184,166,0.3);
        }

        /* Workflow step connector */
        .workflow-line {
            background: linear-gradient(to bottom, rgba(20,184,166,0.5), rgba(52,211,153,0.2));
        }

        /* Stats shimmer */
        .stat-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05), transparent);
            background-size: 200% 100%;
            animation: shimmer 3s infinite;
        }

        /* Smooth scroll */
        html { scroll-behavior: smooth; }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #14b8a6; border-radius: 3px; }
    </style>
</head>
<body class="bg-slate-950 text-white">

<!-- Vanta.js Background -->
<div id="vanta-bg"></div>

<!-- Content Overlay -->
<div class="relative z-10">

    <!-- ═══════════════════════════════════════════ -->
    <!-- HERO SECTION -->
    <!-- ═══════════════════════════════════════════ -->
    <section class="min-h-screen flex flex-col justify-center items-center px-6 relative">
        <!-- Top Bar -->
        <div class="absolute top-0 left-0 right-0 px-6 py-5 flex items-center justify-between animate-fade-in">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-teal-400 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-teal-500/20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <span class="text-white font-bold text-lg tracking-wide">SIMRS-TB</span>
            </div>
            <a href="<?= BASE_URL ?>/index.php" class="text-sm text-slate-400 hover:text-teal-400 transition-colors flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Module Hub
            </a>
        </div>

        <!-- Hero Content -->
        <div class="text-center max-w-4xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass text-xs font-medium text-teal-300 mb-6 start-hidden animate-fade-up">
                <div class="w-2 h-2 bg-teal-400 rounded-full animate-pulse"></div>
                Powered by Deep Learning & AI
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-7xl font-extrabold leading-tight mb-6 start-hidden animate-fade-up delay-100">
                <span class="text-white">Sistem Informasi</span><br>
                <span class="bg-gradient-to-r from-teal-400 via-emerald-400 to-cyan-400 bg-clip-text text-transparent">Manajemen RS</span><br>
                <span class="text-white">Tuberkulosis</span>
            </h1>

            <p class="text-base sm:text-lg text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed start-hidden animate-fade-up delay-200">
                Platform web terintegrasi yang memanfaatkan teknologi <strong class="text-teal-300">Deep Learning</strong> untuk analisis akustik suara batuk sebagai metode skrining awal yang <strong class="text-teal-300">instan dan non-invasif</strong>, serta mengotomasi seluruh alur pengobatan pasien TB.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 start-hidden animate-fade-up delay-300">
                <a href="dashboard.php" class="group inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-teal-500 to-emerald-500 rounded-2xl text-white font-semibold text-base shadow-xl shadow-teal-500/25 hover:shadow-2xl hover:shadow-teal-500/40 hover:scale-105 active:scale-95 transition-all duration-300 animate-pulse-glow">
                    Masuk ke Dashboard
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="#fitur" class="inline-flex items-center gap-2 px-8 py-4 glass rounded-2xl text-slate-300 font-medium text-base hover:bg-white/10 hover:text-white transition-all duration-300">
                    Pelajari Fitur
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                </a>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-10 left-1/2 -translate-x-1/2 scroll-indicator">
            <div class="w-6 h-10 border-2 border-slate-500 rounded-full flex items-start justify-center p-1.5">
                <div class="w-1.5 h-3 bg-teal-400 rounded-full animate-bounce"></div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════ -->
    <!-- STATISTIK SECTION -->
    <!-- ═══════════════════════════════════════════ -->
    <section class="py-16 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <?php
                $heroStats = [
                    ['value' => '10.000+', 'label' => 'Pasien Terdata',  'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['value' => '85.2%',   'label' => 'Tingkat Kesembuhan','icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['value' => '< 3 dtk', 'label' => 'Waktu Skrining AI', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                    ['value' => '24/7',    'label' => 'Monitoring Aktif',  'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ];
                foreach ($heroStats as $i => $s): ?>
                <div class="glass-strong rounded-2xl p-6 text-center group hover:bg-white/10 transition-all duration-500 start-hidden stat-shimmer" data-animate="count" data-delay="<?= $i * 100 ?>">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-teal-500/15 flex items-center justify-center group-hover:bg-teal-500/25 transition-colors">
                        <svg class="w-6 h-6 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="<?= $s['icon'] ?>"/>
                        </svg>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-white mb-1"><?= $s['value'] ?></p>
                    <p class="text-xs sm:text-sm text-slate-400"><?= $s['label'] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════ -->
    <!-- FITUR UTAMA SECTION -->
    <!-- ═══════════════════════════════════════════ -->
    <section id="fitur" class="py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <span class="inline-block px-3 py-1 rounded-full glass text-xs font-medium text-teal-300 mb-4">FITUR UTAMA</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Solusi Lengkap Manajemen TB</h2>
                <p class="text-slate-400 max-w-xl mx-auto">Dari skrining AI hingga sinkronisasi nasional — satu platform untuk seluruh alur pengobatan tuberkulosis</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php
                $features = [
                    ['icon' => '🎙️', 'title' => 'Skrining AI Batuk', 'desc' => 'Analisis akustik suara batuk menggunakan model Deep Learning CNN untuk deteksi dini TB secara instan dan non-invasif.', 'color' => 'from-violet-500/20 to-purple-500/20', 'border' => 'hover:border-violet-400/30'],
                    ['icon' => '📋', 'title' => 'Rekam Medis Digital', 'desc' => 'Catatan medis terintegrasi antara dokter dan laboratorium dengan timeline hasil pemeriksaan BTA, GeneXpert, dan Rontgen.', 'color' => 'from-blue-500/20 to-cyan-500/20', 'border' => 'hover:border-blue-400/30'],
                    ['icon' => '💊', 'title' => 'Farmasi & PMO', 'desc' => 'Manajemen distribusi obat TB beserta pencatatan Pengawas Menelan Obat (PMO) untuk memastikan kepatuhan pasien.', 'color' => 'from-emerald-500/20 to-green-500/20', 'border' => 'hover:border-emerald-400/30'],
                    ['icon' => '📅', 'title' => 'Jadwal Kontrol', 'desc' => 'Kalender interaktif dengan alarm peringatan terpusat untuk memantau jadwal kunjungan dan kontrol pasien.', 'color' => 'from-amber-500/20 to-orange-500/20', 'border' => 'hover:border-amber-400/30'],
                    ['icon' => '✅', 'title' => 'Monitoring Kepatuhan', 'desc' => 'Heatmap kepatuhan 30 hari, klasifikasi risiko drop-out, dan alert otomatis untuk pasien dengan kepatuhan rendah.', 'color' => 'from-teal-500/20 to-emerald-500/20', 'border' => 'hover:border-teal-400/30'],
                    ['icon' => '📊', 'title' => 'Analitik & SITB', 'desc' => 'Dashboard analitik komprehensif dengan visualisasi data dan sinkronisasi langsung ke SITB Kementerian Kesehatan.', 'color' => 'from-rose-500/20 to-pink-500/20', 'border' => 'hover:border-rose-400/30'],
                ];
                foreach ($features as $i => $f): ?>
                <div class="feature-card glass rounded-2xl p-6 border border-transparent <?= $f['border'] ?> transition-all duration-500 cursor-default group" data-animate="slide" data-delay="<?= $i * 100 ?>">
                    <div class="feature-icon w-14 h-14 rounded-2xl bg-gradient-to-br <?= $f['color'] ?> flex items-center justify-center text-2xl mb-4 transition-all duration-500">
                        <?= $f['icon'] ?>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2"><?= $f['title'] ?></h3>
                    <p class="text-sm text-slate-400 leading-relaxed"><?= $f['desc'] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════ -->
    <!-- ALUR PENGOBATAN (WORKFLOW) SECTION -->
    <!-- ═══════════════════════════════════════════ -->
    <section class="py-20 px-6">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-16">
                <span class="inline-block px-3 py-1 rounded-full glass text-xs font-medium text-emerald-300 mb-4">ALUR PENGOBATAN</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">User Journey Pasien TB</h2>
                <p class="text-slate-400 max-w-xl mx-auto">Dari skrining awal hingga sembuh — setiap tahap terotomasi dan terpantau</p>
            </div>

            <div class="space-y-0 relative">
                <!-- Vertical line -->
                <div class="absolute left-6 sm:left-8 top-6 bottom-6 w-0.5 workflow-line hidden sm:block"></div>

                <?php
                $steps = [
                    ['step' => '01', 'title' => 'Skrining Awal', 'desc' => 'Pasien merekam suara batuk → AI menganalisis pola akustik → Confidence score & rekomendasi rujukan', 'icon' => '🎤', 'color' => 'from-violet-500 to-purple-500'],
                    ['step' => '02', 'title' => 'Diagnosis & Rekam Medis', 'desc' => 'Dokter melakukan pemeriksaan → Lab (BTA/GeneXpert) → Diagnosis pasti → Rekam medis digital', 'icon' => '🔬', 'color' => 'from-blue-500 to-cyan-500'],
                    ['step' => '03', 'title' => 'Pengobatan & Farmasi', 'desc' => 'Resep OAT sesuai kategori → Distribusi obat dari farmasi → PMO mencatat kepatuhan harian', 'icon' => '💊', 'color' => 'from-teal-500 to-emerald-500'],
                    ['step' => '04', 'title' => 'Monitoring Berkelanjutan', 'desc' => 'Kontrol rutin terjadwal → Evaluasi hasil lab → Heatmap kepatuhan → Alert drop-out otomatis', 'icon' => '📊', 'color' => 'from-amber-500 to-orange-500'],
                    ['step' => '05', 'title' => 'Laporan & SITB', 'desc' => 'Data diolah menjadi dasbor analitik → Export laporan → Sinkronisasi otomatis ke SITB Kemenkes', 'icon' => '☁️', 'color' => 'from-rose-500 to-pink-500'],
                ];
                foreach ($steps as $i => $step): ?>
                <div class="flex gap-4 sm:gap-6 mb-8 group" data-animate="workflow" data-delay="<?= $i * 150 ?>">
                    <div class="shrink-0 relative z-10">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br <?= $step['color'] ?> flex items-center justify-center text-xl sm:text-2xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <?= $step['icon'] ?>
                        </div>
                    </div>
                    <div class="glass rounded-2xl p-5 flex-1 group-hover:bg-white/10 transition-all duration-300">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-xs font-bold text-teal-400 bg-teal-500/10 px-2 py-0.5 rounded-full">STEP <?= $step['step'] ?></span>
                            <h3 class="text-base sm:text-lg font-semibold text-white"><?= $step['title'] ?></h3>
                        </div>
                        <p class="text-sm text-slate-400 leading-relaxed"><?= $step['desc'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════ -->
    <!-- TECH STACK SECTION -->
    <!-- ═══════════════════════════════════════════ -->
    <section class="py-16 px-6">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-10">
                <span class="inline-block px-3 py-1 rounded-full glass text-xs font-medium text-cyan-300 mb-4">TECH STACK</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-white">Dibangun dengan Teknologi Modern</h2>
            </div>
            <div class="flex flex-wrap justify-center gap-3">
                <?php
                $techs = [
                    ['name' => 'PHP 8.x',       'color' => 'text-indigo-300 border-indigo-500/30 bg-indigo-500/10'],
                    ['name' => 'MySQL',          'color' => 'text-blue-300 border-blue-500/30 bg-blue-500/10'],
                    ['name' => 'TailwindCSS',    'color' => 'text-cyan-300 border-cyan-500/30 bg-cyan-500/10'],
                    ['name' => 'Chart.js',       'color' => 'text-pink-300 border-pink-500/30 bg-pink-500/10'],
                    ['name' => 'Three.js',       'color' => 'text-green-300 border-green-500/30 bg-green-500/10'],
                    ['name' => 'Deep Learning',  'color' => 'text-amber-300 border-amber-500/30 bg-amber-500/10'],
                    ['name' => 'CNN Model',      'color' => 'text-violet-300 border-violet-500/30 bg-violet-500/10'],
                    ['name' => 'SITB API',       'color' => 'text-teal-300 border-teal-500/30 bg-teal-500/10'],
                ];
                foreach ($techs as $t): ?>
                <span class="px-4 py-2 rounded-xl border text-sm font-medium <?= $t['color'] ?> hover:scale-105 transition-transform cursor-default"><?= $t['name'] ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════ -->
    <!-- CTA FINAL SECTION -->
    <!-- ═══════════════════════════════════════════ -->
    <section class="py-24 px-6">
        <div class="max-w-3xl mx-auto text-center">
            <div class="glass-strong rounded-3xl p-10 sm:p-14 relative overflow-hidden">
                <!-- Glow effects -->
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-teal-500/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 w-60 h-60 bg-emerald-500/10 rounded-full blur-3xl"></div>

                <div class="relative z-10">
                    <div class="w-16 h-16 mx-auto mb-6 bg-gradient-to-br from-teal-400 to-emerald-500 rounded-2xl flex items-center justify-center shadow-lg shadow-teal-500/30 animate-float">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">Siap Mengelola Data TB?</h2>
                    <p class="text-slate-400 mb-8 max-w-lg mx-auto">Akses dashboard untuk mulai mengelola pasien, memantau pengobatan, dan mengoptimalkan alur kerja SIMRS-TB.</p>
                    <a href="dashboard.php" class="inline-flex items-center gap-3 px-10 py-4 bg-gradient-to-r from-teal-500 to-emerald-500 rounded-2xl text-white font-semibold text-lg shadow-xl shadow-teal-500/25 hover:shadow-2xl hover:shadow-teal-500/40 hover:scale-105 active:scale-95 transition-all duration-300">
                        Buka Dashboard
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 px-6 border-t border-white/5">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-slate-500">
            <p>© <?= date('Y') ?> SIMRS-TB — Modul 9 Medical Web</p>
            <p>Built with PHP • TailwindCSS • Chart.js • Three.js</p>
        </div>
    </footer>

</div>

<!-- ═══ Vanta.js Scripts ═══ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.waves.min.js"></script>
<script>
VANTA.WAVES({
    el: "#vanta-bg",
    mouseControls: true,
    touchControls: true,
    gyroControls: false,
    minHeight: 200.00,
    minWidth: 200.00,
    scale: 1.00,
    scaleMobile: 1.00,
    color: 0x1115
});

// ═══ Intersection Observer for Scroll Animations ═══
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const el = entry.target;
            const delay = el.dataset.delay || 0;
            const type = el.dataset.animate;

            setTimeout(() => {
                el.style.opacity = '1';
                el.style.transform = 'none';

                if (type === 'count') {
                    el.classList.add('animate-count');
                } else if (type === 'slide') {
                    el.classList.add('animate-fade-up');
                } else if (type === 'workflow') {
                    el.classList.add('animate-slide-right');
                }
            }, delay);

            observer.unobserve(el);
        }
    });
}, { threshold: 0.15, rootMargin: '0px 0px -50px 0px' });

document.querySelectorAll('[data-animate]').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    observer.observe(el);
});
</script>

</body>
</html>
