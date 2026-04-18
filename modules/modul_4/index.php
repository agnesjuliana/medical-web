<?php
/**
 * Modul 4 — CalorieCare: Health & Calorie Calculator
 * * Calorie calculator with dark mode, bilingual (EN/ID),
 * and personalized health recommendations.
 * Combined from: proyek web-4.html, proyek web-4.css, proyek web-4.js
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'CalorieCare - Modul 4';
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script>
    // Extend Tailwind config for dark mode support
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                colors: {
                    primary: {
                        DEFAULT: '#0891b2',
                        hover: '#0e7490',
                        50: '#ecfeff',
                        100: '#cffafe',
                        200: '#a5f3fc',
                        300: '#67e8f9',
                        400: '#22d3ee',
                        500: '#06b6d4',
                        600: '#0891b2',
                        700: '#0e7490',
                        800: '#155e75',
                        900: '#164e63',
                    }
                },
                fontFamily: {
                    sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                }
            }
        }
    }
</script>

<style>
    /* CalorieCare Styles */
    .cc-section {
        font-family: 'Poppins', 'Inter', sans-serif;
    }

    /* Scroll Animations */
    .fade-in {
        animation: fadeIn 0.8s ease-out forwards;
        opacity: 0;
    }

    .slide-up {
        animation: slideUp 0.8s ease-out forwards;
        opacity: 0;
        transform: translateY(30px);
    }

    .scale-in {
        animation: scaleIn 0.8s ease-out forwards;
        opacity: 0;
        transform: scale(0.9);
    }

    @keyframes fadeIn {
        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes scaleIn {
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* Micro-interactions */
    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-lift:focus-within,
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }

    .interactive-pop {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .interactive-pop:hover {
        transform: scale(1.03) translateY(-2px);
    }

    .interactive-pop:active {
        transform: scale(0.97);
    }

    /* Floating Animation */
    .interactive-float {
        animation: floating 6s ease-in-out infinite;
    }

    @keyframes floating {
        0% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-15px);
        }

        100% {
            transform: translateY(0px);
        }
    }

    .backdrop-blur-custom {
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    /* ========== SMART HEALTH ASSISTANT STYLES ========== */

    /* --- Food Equivalent Cards --- */
    .food-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; }
    .food-card {
        text-align: center;
        padding: 16px 10px;
        border-radius: 16px;
        background: white;
        border: 1px solid #e5e7eb;
        transition: all 0.25s ease;
    }
    .dark .food-card { background: #1f2937; border-color: #374151; }
    .food-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
    .food-card .food-emoji { font-size: 32px; margin-bottom: 6px; }
    .food-card .food-name { font-size: 13px; font-weight: 600; color: #374151; }
    .dark .food-card .food-name { color: #d1d5db; }
    .food-card .food-cal { font-size: 11px; color: #6b7280; margin-top: 2px; }
    .dark .food-card .food-cal { color: #9ca3af; }
    .food-card .food-count {
        font-size: 20px;
        font-weight: 700;
        color: #16a34a;
        margin-top: 6px;
    }

    /* --- Weekly Progress Bar --- */
    .progress-track {
        width: 100%;
        height: 16px;
        background: #e5e7eb;
        border-radius: 99px;
        overflow: hidden;
        position: relative;
    }
    .dark .progress-track { background: #374151; }
    .progress-fill {
        height: 100%;
        border-radius: 99px;
        background: linear-gradient(90deg, #22c55e, #16a34a);
        transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        min-width: 0;
    }
    .progress-fill.over { background: linear-gradient(90deg, #f59e0b, #ef4444); }

    /* --- Activity History --- */
    .history-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        transition: all 0.2s;
    }
    .dark .history-item { background: #1f2937; border-color: #374151; }
    .history-item:hover { border-color: #16a34a; }
    .history-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .history-meta { flex: 1; min-width: 0; }
    .history-meta .h-title {
        font-size: 13.5px;
        font-weight: 600;
        color: #1f2937;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .dark .history-meta .h-title { color: #e5e7eb; }
    .history-meta .h-sub { font-size: 11.5px; color: #6b7280; }
    .dark .history-meta .h-sub { color: #9ca3af; }
    .history-cal {
        font-size: 15px; font-weight: 700;
        color: #16a34a;
        white-space: nowrap;
    }

    /* --- Quick Chat Floating Widget --- */
    #qchat-toggle {
        position: fixed;
        bottom: 90px;
        right: 24px;
        z-index: 1000;
        width: 56px; height: 56px;
        border-radius: 50%;
        border: none;
        background: linear-gradient(135deg, #16a34a, #059669);
        color: white;
        cursor: pointer;
        box-shadow: 0 6px 24px rgba(22,163,74,0.4);
        display: flex; align-items: center; justify-content: center;
        transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    #qchat-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 30px rgba(22,163,74,0.5);
    }
    #qchat-toggle .qc-badge {
        position: absolute; top: -2px; right: -2px;
        width: 14px; height: 14px;
        background: #ef4444; border-radius: 50%;
        border: 2px solid white;
        animation: qcPulse 2s infinite;
    }
    @keyframes qcPulse {
        0%,100% { opacity:1; }
        50% { opacity:0.5; }
    }

    #qchat-box {
        position: fixed;
        bottom: 160px; right: 24px;
        width: 360px;
        max-height: 500px;
        z-index: 1000;
        border-radius: 20px;
        overflow: hidden;
        display: none;
        flex-direction: column;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.05);
        animation: qcSlideUp 0.35s cubic-bezier(0.16,1,0.3,1) forwards;
        font-family: 'Poppins','Inter',sans-serif;
    }
    #qchat-box.open { display: flex; }
    @keyframes qcSlideUp {
        from { opacity:0; transform: translateY(20px) scale(0.95); }
        to   { opacity:1; transform: translateY(0) scale(1); }
    }

    .qc-header {
        background: linear-gradient(135deg, #16a34a, #059669);
        color: white;
        padding: 14px 18px;
        display: flex; align-items: center; gap: 10px;
    }
    .qc-header .qc-avatar {
        width: 36px; height: 36px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 17px; flex-shrink: 0;
    }
    .qc-header .qc-info h4 { margin:0; font-size:14px; font-weight:600; }
    .qc-header .qc-info span { font-size:11px; opacity:0.85; }
    .qc-header .qc-close {
        margin-left: auto;
        background: rgba(255,255,255,0.15);
        border: none; color: white;
        width: 28px; height: 28px;
        border-radius: 50%; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.2s;
    }
    .qc-header .qc-close:hover { background: rgba(255,255,255,0.3); }

    .qc-messages {
        flex: 1;
        overflow-y: auto;
        padding: 14px;
        background: #f9fafb;
        display: flex; flex-direction: column;
        gap: 10px;
        min-height: 180px;
        max-height: 280px;
    }
    .dark .qc-messages { background: #1f2937; }

    .qc-bubble {
        max-width: 88%;
        padding: 10px 14px;
        border-radius: 16px;
        font-size: 13px;
        line-height: 1.5;
        word-wrap: break-word;
        animation: qcBubblePop 0.25s ease-out;
    }
    @keyframes qcBubblePop {
        from { opacity:0; transform:scale(0.9); }
        to   { opacity:1; transform:scale(1); }
    }
    .qc-bubble.bot {
        align-self: flex-start;
        background: white;
        color: #1f2937;
        border: 1px solid #e5e7eb;
        border-bottom-left-radius: 4px;
    }
    .dark .qc-bubble.bot {
        background: #374151;
        color: #e5e7eb;
        border-color: #4b5563;
    }
    .qc-bubble.user {
        align-self: flex-end;
        background: linear-gradient(135deg, #16a34a, #059669);
        color: white;
        border-bottom-right-radius: 4px;
    }

    .qc-options {
        padding: 10px 14px;
        background: #f9fafb;
        display: flex; flex-direction: column; gap: 6px;
        border-top: 1px solid #e5e7eb;
    }
    .dark .qc-options { background: #111827; border-top-color: #374151; }
    .qc-opt-btn {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 14px;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        font-size: 12.5px;
        color: #374151;
        cursor: pointer;
        transition: all 0.2s;
        text-align: left;
        font-family: 'Poppins',sans-serif;
    }
    .dark .qc-opt-btn { background: #1f2937; border-color: #4b5563; color: #d1d5db; }
    .qc-opt-btn:hover {
        border-color: #16a34a;
        background: #f0fdf4;
        color: #15803d;
    }
    .dark .qc-opt-btn:hover {
        border-color: #16a34a;
        background: rgba(22,163,74,0.1);
        color: #86efac;
    }

    @media (max-width: 480px) {
        #qchat-box {
            right: 8px; left: 8px;
            bottom: 80px;
            width: auto;
            max-height: 70vh;
        }
    }
    /* ========== END SMART HEALTH ASSISTANT STYLES ========== */

    /* ========== ARTICLE SECTION STYLES ========== */
    .article-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        cursor: pointer;
        display: flex;
        flex-direction: column;
    }
    .dark .article-card { background: #1f2937; border-color: #374151; }
    .article-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 40px rgba(0,0,0,0.1);
        border-color: #16a34a;
    }
    .dark .article-card:hover { box-shadow: 0 16px 40px rgba(0,0,0,0.3); }
    .article-card .art-banner {
        height: 140px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        position: relative;
    }
    .article-card .art-body {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .article-card .art-source {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 600;
        color: #16a34a;
        background: #f0fdf4;
        padding: 3px 10px;
        border-radius: 99px;
        margin-bottom: 10px;
        width: fit-content;
    }
    .dark .article-card .art-source { background: rgba(22,163,74,0.15); color: #86efac; }
    .article-card .art-title {
        font-size: 15px;
        font-weight: 700;
        color: #1f2937;
        line-height: 1.4;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .dark .article-card .art-title { color: #f3f4f6; }
    .article-card .art-excerpt {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.6;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .dark .article-card .art-excerpt { color: #9ca3af; }
    .article-card .art-read {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 12.5px;
        font-weight: 600;
        color: #16a34a;
        margin-top: 14px;
        transition: gap 0.2s;
    }
    .article-card:hover .art-read { gap: 8px; }

    /* Article Detail Overlay */
    #articleDetail {
        position: fixed;
        inset: 0;
        z-index: 999;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
        display: none;
        align-items: flex-start;
        justify-content: center;
        padding: 40px 16px;
        overflow-y: auto;
        animation: artFadeIn 0.3s ease;
    }
    #articleDetail.open { display: flex; }
    @keyframes artFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .art-detail-box {
        background: white;
        border-radius: 20px;
        max-width: 720px;
        width: 100%;
        padding: 0;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(0,0,0,0.2);
        animation: artSlideUp 0.35s cubic-bezier(0.16,1,0.3,1);
    }
    .dark .art-detail-box { background: #111827; }
    @keyframes artSlideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .art-detail-header {
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 56px;
        position: relative;
    }
    .art-detail-content {
        padding: 32px;
    }
    .art-detail-content h2 {
        font-size: 22px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 6px;
        line-height: 1.3;
    }
    .dark .art-detail-content h2 { color: #f9fafb; }
    .art-detail-content .art-d-source {
        font-size: 12px;
        color: #16a34a;
        font-weight: 600;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .art-detail-content .art-d-body {
        font-size: 14.5px;
        color: #374151;
        line-height: 1.85;
    }
    .dark .art-detail-content .art-d-body { color: #d1d5db; }
    .art-detail-content .art-d-body h3 {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        margin: 20px 0 8px 0;
    }
    .dark .art-detail-content .art-d-body h3 { color: #f3f4f6; }
    .art-detail-content .art-d-body ul {
        padding-left: 20px;
        margin: 8px 0;
    }
    .art-detail-content .art-d-body ul li {
        margin-bottom: 6px;
        position: relative;
        padding-left: 6px;
    }
    .art-detail-content .art-d-body ul li::marker {
        color: #16a34a;
    }
    .art-detail-footer {
        padding: 16px 32px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .art-btn-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        border-radius: 12px;
        border: 1px solid #d1d5db;
        background: white;
        color: #374151;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        font-family: 'Poppins',sans-serif;
    }
    .dark .art-btn-back { background: #1f2937; border-color: #4b5563; color: #d1d5db; }
    .art-btn-back:hover { border-color: #16a34a; color: #16a34a; }
    .art-ref-link {
        font-size: 12px;
        color: #6b7280;
        text-decoration: none;
        transition: color 0.2s;
    }
    .art-ref-link:hover { color: #16a34a; }
    .dark .art-ref-link { color: #9ca3af; }

    @media (max-width: 640px) {
        #articleDetail { padding: 16px 8px; }
        .art-detail-content { padding: 20px; }
        .art-detail-footer { padding: 12px 20px 20px; flex-direction: column; }
    }
    /* ========== END ARTICLE STYLES ========== */
</style>

<?php require_once __DIR__ . '/../../layout/navbar.php'; ?>

<main class="cc-section">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        <nav class="flex items-center gap-2 text-sm text-gray-400 dark:text-gray-500 mb-6">
            <a href="<?= BASE_URL ?>/index.php" class="hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">Module Hub</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-700 dark:text-gray-300 font-medium">CalorieCare</span>
        </nav>
    </div>

    <section id="home" class="pt-8 pb-20 px-6 fade-in bg-white dark:bg-gray-900 transition-colors duration-300">
        <div class="max-w-7xl mx-auto grid md:grid-cols-2 gap-12 items-center">
            <div class="slide-up">
                <h1 class="text-5xl md:text-6xl font-bold text-gray-900 dark:text-white leading-tight mb-6 translatable"
                    data-en="Track Your Calories,<br/><span class='text-green-600'>Improve Your Health</span>"
                    data-id="Pantau Kalori Anda,<br/><span class='text-green-600'>Tingkatkan Kesehatan</span>">
                    Track Your Calories,<br />
                    <span class="text-green-600">Improve Your Health</span>
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-300 mb-8 leading-relaxed translatable"
                    data-en="Simple and accurate calorie calculator with personalized health insights."
                    data-id="Kalkulator kalori yang akurat dan mudah digunakan dengan rekomendasi kesehatan personal.">
                    Simple and accurate calorie calculator with personalized health insights.
                </p>
                <a href="#calculator"
                    class="inline-block bg-green-600 text-white px-8 py-4 rounded-lg hover:bg-green-700 interactive-pop shadow-lg hover:shadow-xl translatable"
                    data-en="Start Now" data-id="Mulai Sekarang">
                    Start Now
                </a>
            </div>
            <div class="scale-in relative interactive-float">
                <div class="rounded-2xl overflow-hidden shadow-2xl border-4 border-white dark:border-gray-800">
                    <img src="https://images.unsplash.com/photo-1773681823208-7f3657c0688f?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxmaXRuZXNzJTIwZXhlcmNpc2UlMjBoZWFsdGglMjBydW5uaW5nfGVufDF8fHx8MTc3NjA5MDYyNXww&ixlib=rb-4.1.0&q=80&w=1080"
                        alt="Fitness" class="w-full h-full object-cover" />
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 px-6 bg-gray-50 dark:bg-gray-800 transition-colors duration-300">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 fade-in">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4 translatable"
                    data-en="Why Choose CalorieCare?"
                    data-id="Mengapa Pilih CalorieCare?">Why Choose CalorieCare?</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 translatable"
                    data-en="Everything you need to track and improve your health"
                    data-id="Segala yang Anda butuhkan untuk melacak dan meningkatkan kesehatan Anda">Everything you
                    need to track
                    and improve your health</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div
                    class="bg-white dark:bg-gray-900 rounded-2xl p-8 shadow-md hover:shadow-xl transition-all hover-lift fade-in border border-transparent dark:border-gray-700">
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3 translatable"
                        data-en="Calorie Calculation" data-id="Perhitungan Kalori">Calorie Calculation</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed translatable"
                        data-en="Accurate calorie tracking based on your activity type, duration, and body metrics"
                        data-id="Pelacakan kalori yang akurat berdasarkan jenis aktivitas, durasi, dan metrik tubuh Anda">
                        Accurate
                        calorie tracking based on your activity type, duration, and body metrics</p>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-2xl p-8 shadow-md hover:shadow-xl transition-all hover-lift fade-in border border-transparent dark:border-gray-700"
                    style="animation-delay: 0.1s;">
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3 translatable"
                        data-en="Simple Health Advice" data-id="Saran Kesehatan Mudah">Simple Health Advice</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed translatable"
                        data-en="Easy-to-understand recommendations from medical insights, tailored to your goals"
                        data-id="Rekomendasi mudah dimengerti dari wawasan medis, disesuaikan dengan tujuan Anda">
                        Easy-to-understand
                        recommendations from medical insights, tailored to your goals</p>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-2xl p-8 shadow-md hover:shadow-xl transition-all hover-lift fade-in border border-transparent dark:border-gray-700"
                    style="animation-delay: 0.2s;">
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3 translatable"
                        data-en="Easy to Use" data-id="Mudah Digunakan">Easy to Use</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed translatable"
                        data-en="Clean, intuitive interface that makes tracking your health effortless"
                        data-id="Antarmuka yang bersih dan intuitif sehingga pelacakan kesehatan Anda tidak merepotkan">
                        Clean,
                        intuitive interface that makes tracking your health effortless</p>
                </div>
            </div>
        </div>
    </section>

    <section id="calculator" class="py-20 px-6 bg-white dark:bg-gray-900 transition-colors duration-300">
        <div class="max-w-5xl mx-auto">
            <div class="text-center mb-12 fade-in">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4 translatable"
                    data-en="Calculate Your Calories" data-id="Hitung Kalori Anda">Calculate Your Calories</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 translatable"
                    data-en="Fill in your details below to get personalized insights."
                    data-id="Lengkapi data di bawah ini untuk mendapatkan rekomendasi.">Fill in your details below to
                    get
                    personalized insights.</p>
            </div>

            <div
                class="bg-gray-50 dark:bg-gray-800 rounded-3xl shadow-xl p-8 md:p-12 border border-green-100 dark:border-gray-700 scale-in transition-colors duration-300">

                <form id="calcForm">
                    <div class="grid md:grid-cols-2 gap-6 mb-8">
                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 translatable"
                                data-en="What is your primary goal?"
                                data-id="Apa tujuan utama Anda?">What is your
                                primary goal?</label>
                            <select id="goal"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-900 dark:text-white outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/50 transition-all cursor-pointer hover-lift"
                                required>
                                <option value="lose" class="translatable" data-en="Weight Loss (Diet)"
                                    data-id="Menurunkan Berat Badan (Diet)">Weight Loss (Diet)</option>
                                <option value="maintain" class="translatable"
                                    data-en="Maintain Weight (Healthy Lifestyle)"
                                    data-id="Menjaga Berat Badan (Hidup Sehat)" selected>Maintain Weight (Healthy
                                    Lifestyle)</option>
                                <option value="bulk" class="translatable" data-en="Muscle Gain (Bulking)"
                                    data-id="Menambah Massa Otot (Bulking)">Muscle Gain (Bulking)</option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 translatable"
                                data-en="Activity Type" data-id="Jenis Aktivitas">Activity Type</label>
                            <select id="activity"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-900 dark:text-white outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/50 transition-all cursor-pointer hover-lift"
                                required>
                                <option value="3.3" class="translatable" data-en="Walking (light pace)"
                                    data-id="Jalan Santai">Walking
                                    (light pace)</option>
                                <option value="4.3" class="translatable" data-en="Walking (brisk pace)"
                                    data-id="Jalan Cepat">Walking
                                    (brisk pace)</option>
                                <option value="8.0" class="translatable" data-en="Running (moderate pace)"
                                    data-id="Lari (Sedang)" selected>Running (moderate pace)</option>
                                <option value="10.0" class="translatable" data-en="Running (fast pace)"
                                    data-id="Lari (Cepat)">Running
                                    (fast pace)</option>
                                <option value="6.0" class="translatable" data-en="Cycling (moderate)"
                                    data-id="Bersepeda (Sedang)">
                                    Cycling (moderate)</option>
                                <option value="3.0" class="translatable" data-en="Yoga / Stretching"
                                    data-id="Yoga / Peregangan">Yoga /
                                    Stretching</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 translatable"
                                data-en="Duration (minutes)" data-id="Durasi (Menit)">Duration (minutes)</label>
                            <input type="number" id="duration" value="30" min="1" required
                                class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-900 dark:text-white outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/50 transition-all hover-lift">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 translatable"
                                data-en="Weight (kg)" data-id="Berat Badan (kg)">Weight (kg)</label>
                            <input type="number" id="weight" value="70" min="20" required
                                class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-900 dark:text-white outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/50 transition-all hover-lift">
                        </div>

                        <div class="flex items-end gap-4">
                            <button type="button" id="btnReset"
                                class="w-1/3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-white px-4 py-3 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-semibold interactive-pop translatable"
                                data-en="Reset" data-id="Ulang">
                                Reset
                            </button>
                            <button type="submit"
                                class="w-2/3 bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-xl hover:from-green-600 hover:to-green-700 transition-colors font-semibold shadow-lg interactive-pop translatable"
                                data-en="Calculate" data-id="Hitung">
                                Calculate
                            </button>
                        </div>
                    </div>
                </form>

                <div id="resultBox"
                    class="bg-gradient-to-br from-green-600 to-green-800 rounded-2xl p-8 text-white hidden mt-8">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-semibold translatable" data-en="Your Results & Recommendation"
                            data-id="Hasil & Rekomendasi">Your Results & Recommendation</h3>
                    </div>
                    <div id="narrativeResult" class="text-white/95 leading-relaxed mb-6 text-lg">
                    </div>
                    <div
                        class="flex items-center gap-3 bg-white/10 p-4 rounded-xl text-white/90 text-sm border border-white/20">
                        <svg class="w-6 h-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z">
                            </path>
                        </svg>
                        <span id="hydrationTip"></span>
                    </div>

                    <!-- Food Equivalent Section (shown after calculation) -->
                    <div id="foodEquivalent" class="mt-6 hidden">
                        <h4 class="text-lg font-semibold mb-3 flex items-center gap-2 translatable"
                            data-en="🍽️ Food Equivalent"
                            data-id="🍽️ Setara dengan Makanan">
                            🍽️ Food Equivalent
                        </h4>
                        <p class="text-white/80 text-sm mb-3 translatable"
                            data-en="Your burned calories are equivalent to:"
                            data-id="Kalori yang kamu bakar setara dengan:">
                            Your burned calories are equivalent to:
                        </p>
                        <div id="foodGrid" class="food-grid"></div>
                    </div>

                    <!-- Weekly Progress Section (shown after calculation) -->
                    <div id="weeklyProgress" class="mt-6 hidden">
                        <h4 class="text-lg font-semibold mb-3 flex items-center gap-2 translatable"
                            data-en="📊 Weekly Activity Progress"
                            data-id="📊 Progres Aktivitas Mingguan">
                            📊 Weekly Activity Progress
                        </h4>
                        <div class="bg-white/10 rounded-xl p-4 border border-white/20">
                            <div class="flex justify-between text-sm mb-2">
                                <span id="weeklyLabel" class="text-white/80"></span>
                                <span id="weeklyPercent" class="font-bold text-green-200"></span>
                            </div>
                            <div class="progress-track">
                                <div id="weeklyBar" class="progress-fill" style="width:0%"></div>
                            </div>
                            <p id="weeklyTip" class="text-white/70 text-xs mt-2"></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="py-20 px-6 bg-gray-50 dark:bg-gray-800 transition-colors duration-300">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 fade-in">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4 translatable"
                    data-en="More Than Just Numbers" data-id="Lebih dari Sekadar Angka">More Than Just Numbers</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 translatable"
                    data-en="Comprehensive insights to support your wellness journey"
                    data-id="Wawasan komprehensif untuk mendukung perjalanan kebugaran Anda">Comprehensive insights to
                    support
                    your wellness journey</p>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <div
                    class="bg-white dark:bg-gray-900 rounded-2xl p-8 shadow-md slide-up border border-transparent dark:border-gray-700">
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-semibold text-gray-900 dark:text-white translatable"
                            data-en="Daily Calorie Targets" data-id="Target Kalori Harian">Daily Calorie Targets</h3>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6 translatable"
                        data-en="Get personalized daily calorie goals based on your activity level, age, and fitness objectives. Whether you're maintaining, losing, or gaining weight, we provide clear targets."
                        data-id="Dapatkan target kalori harian yang dipersonalisasi berdasarkan level aktivitas, usia, dan tujuan kebugaran Anda. Baik Anda mempertahankan, menurunkan, atau meningkatkan berat badan, kami menyediakan target yang jelas.">
                        Get personalized daily calorie goals based on your activity level, age, and fitness objectives.
                        Whether
                        you're maintaining, losing, or gaining weight, we provide clear targets.
                    </p>
                    <div
                        class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4 border border-green-100 dark:border-green-800">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 translatable"
                            data-en="Your recommended daily intake:" data-id="Asupan harian direkomendasikan:">Your
                            recommended daily
                            intake:</p>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400">2,200 kcal</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-2xl p-8 shadow-md slide-up border border-transparent dark:border-gray-700"
                    style="animation-delay: 0.2s;">
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-semibold text-gray-900 dark:text-white translatable"
                            data-en="Activity Comparison" data-id="Perbandingan Aktivitas">Activity Comparison</h3>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6 translatable"
                        data-en="Compare different activities to find what works best for your goals. See how light walking stacks up against intense cycling."
                        data-id="Bandingkan berbagai aktivitas untuk menemukan mana yang paling cocok untuk tujuan Anda. Lihat bagaimana jalan ringan dibandingkan dengan bersepeda intens.">
                        Compare different activities to find what works best for your goals. See how light walking
                        stacks up against
                        intense cycling.
                    </p>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <span class="text-gray-700 dark:text-gray-300 translatable" data-en="Walking (light)"
                                data-id="Jalan (ringan)">Walking (light)</span>
                            <span class="font-semibold text-gray-900 dark:text-white">150 kcal/30min</span>
                        </div>
                        <div
                            class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                            <span class="text-gray-700 dark:text-gray-300 translatable" data-en="Running (moderate)"
                                data-id="Lari (sedang)">Running (moderate)</span>
                            <span class="font-semibold text-green-600 dark:text-green-400">320 kcal/30min</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <span class="text-gray-700 dark:text-gray-300 translatable" data-en="Cycling (intense)"
                                data-id="Bersepeda (intens)">Cycling (intense)</span>
                            <span class="font-semibold text-gray-900 dark:text-white">450 kcal/30min</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== ACTIVITY HISTORY SECTION ========== -->
    <section id="historySection" class="py-20 px-6 bg-white dark:bg-gray-900 transition-colors duration-300">
        <div class="max-w-5xl mx-auto">
            <div class="text-center mb-12 fade-in">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4 translatable"
                    data-en="📋 Activity History"
                    data-id="📋 Riwayat Aktivitas">📋 Activity History</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 translatable"
                    data-en="Your last 5 calculation results"
                    data-id="5 hasil kalkulasi terakhirmu">Your last 5 calculation results</p>
            </div>
            <div id="historyList" class="space-y-3">
                <!-- Filled by JS -->
            </div>
            <div id="historyEmpty" class="text-center py-12">
                <div class="text-5xl mb-4">🏃</div>
                <p class="text-gray-500 dark:text-gray-400 translatable"
                    data-en="No activity recorded yet. Start calculating above!"
                    data-id="Belum ada aktivitas tercatat. Mulai hitung di atas!">No activity recorded yet. Start calculating above!</p>
            </div>
            <div class="text-center mt-6">
                <button id="btnClearHistory"
                    class="text-sm text-gray-400 hover:text-red-500 transition-colors hidden translatable"
                    data-en="🗑️ Clear History"
                    data-id="🗑️ Hapus Riwayat">🗑️ Clear History</button>
            </div>
        </div>
    </section>
    <!-- ========== END ACTIVITY HISTORY ========== -->

    <!-- ========== ARTIKEL KESEHATAN SECTION ========== -->
    <section id="artikelSection" class="py-20 px-6 bg-gray-50 dark:bg-gray-800 transition-colors duration-300">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12 fade-in">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4 translatable"
                    data-en="📰 Health Articles"
                    data-id="📰 Artikel Kesehatan">📰 Health Articles</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 translatable"
                    data-en="Health insights from trusted Indonesian sources"
                    data-id="Wawasan kesehatan dari sumber terpercaya Indonesia">Health insights from trusted Indonesian sources</p>
            </div>

            <!-- Article Cards Grid (shows first 3) -->
            <div id="articleGrid" class="grid md:grid-cols-3 gap-6"></div>

            <!-- Show All / Show Less Button -->
            <div class="text-center mt-8">
                <button id="btnToggleArticles"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-300 hover:border-green-500 hover:text-green-600 dark:hover:text-green-400 transition-all shadow-sm hover:shadow-md">
                    <span class="translatable" data-en="Show All Articles" data-id="Lihat Semua Artikel">Show All Articles</span>
                    <svg class="w-4 h-4 transition-transform" id="toggleArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <!-- Article Detail Overlay -->
    <div id="articleDetail">
        <div class="art-detail-box">
            <div class="art-detail-header" id="artDetailBanner"></div>
            <div class="art-detail-content">
                <h2 id="artDetailTitle"></h2>
                <div class="art-d-source" id="artDetailSource"></div>
                <div class="art-d-body" id="artDetailBody"></div>
            </div>
            <div class="art-detail-footer">
                <button class="art-btn-back" id="btnCloseArticle">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="translatable" data-en="Back" data-id="Kembali">Back</span>
                </button>
                <a class="art-ref-link" id="artDetailRef" href="#" target="_blank" rel="noopener">
                    <span class="translatable" data-en="View original source ↗" data-id="Lihat sumber asli ↗">View original source ↗</span>
                </a>
            </div>
        </div>
    </div>
    <!-- ========== END ARTIKEL KESEHATAN ========== -->

    <!-- ========== QUICK CHAT FLOATING WIDGET ========== -->
    <button id="qchat-toggle" title="Quick Health Chat">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
        <div class="qc-badge"></div>
    </button>

    <div id="qchat-box">
        <div class="qc-header">
            <div class="qc-avatar">💬</div>
            <div class="qc-info">
                <h4>Quick Health Chat</h4>
                <span>Asisten Kesehatanmu</span>
            </div>
            <button class="qc-close" id="qchat-close" title="Tutup">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="qc-messages" id="qchat-messages">
            <div class="qc-bubble bot">Halo! 👋 Saya asisten kesehatan CalorieCare. Pilih pertanyaan di bawah, dan saya akan memberikan saran berdasarkan data kalkulasimu! 😊</div>
        </div>
        <div class="qc-options" id="qchat-options">
            <!-- Filled by JS -->
        </div>
    </div>
    <!-- ========== END QUICK CHAT ========== -->

    <section class="bg-gray-900 text-white py-12 px-6">
        <div class="max-w-7xl mx-auto text-center">
            <div class="flex items-center justify-center gap-2 mb-4">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                </div>
                <span class="font-semibold text-xl">CalorieCare</span>
            </div>
            <p class="text-gray-400 text-lg translatable" data-en="Simple Health Tools for Everyone"
                data-id="Alat Kesehatan Sederhana untuk Semua Orang">Simple Health Tools for Everyone</p>
            <p class="text-gray-500 text-sm mt-4 translatable"
                data-en="© 2026 CalorieCare. Supporting your wellness journey."
                data-id="© 2026 CalorieCare. Mendukung perjalanan kebugaran Anda.">© 2026 CalorieCare. Supporting your
                wellness
                journey.</p>
        </div>
    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // --- 1. FITUR MODE GELAP / TERANG ---
    // Create a theme toggle button dynamically (since Navbar doesn't have one)
    const themeToggleContainer = document.createElement('div');
    themeToggleContainer.className = 'fixed bottom-6 left-6 z-50 flex flex-col gap-2';
    themeToggleContainer.innerHTML = `
        <button id="themeToggle" class="w-12 h-12 bg-white dark:bg-gray-800 shadow-lg rounded-full flex items-center justify-center hover:shadow-xl transition-all border border-gray-200 dark:border-gray-700" title="Toggle Dark Mode">
            <svg id="themeIcon" class="w-5 h-5 text-gray-700 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z">
                </path>
            </svg>
        </button>
        <button id="langToggle" class="w-12 h-12 bg-white dark:bg-gray-800 shadow-lg rounded-full flex items-center justify-center hover:shadow-xl transition-all border border-gray-200 dark:border-gray-700 text-sm font-bold text-gray-700 dark:text-white" title="Toggle Language">
            ID
        </button>
    `;
    document.body.appendChild(themeToggleContainer);

    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');

    // Cek preferensi sebelumnya
    if (localStorage.getItem('cc-theme') === 'dark' || (!('cc-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        themeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>';
    } else {
        document.documentElement.classList.remove('dark');
    }

    themeToggle.addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
        if (document.documentElement.classList.contains('dark')) {
            localStorage.setItem('cc-theme', 'dark');
            themeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>';
        } else {
            localStorage.setItem('cc-theme', 'light');
            themeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>';
        }
    });

    // --- 2. FITUR UBAH BAHASA (EN / ID) ---
    let currentLang = 'en';
    const langToggle = document.getElementById('langToggle');
    const translatableElements = document.querySelectorAll('.translatable');

    function updateLanguage() {
        translatableElements.forEach(el => {
            if (currentLang === 'en') {
                el.innerHTML = el.getAttribute('data-en');
            } else {
                el.innerHTML = el.getAttribute('data-id');
            }
        });
        langToggle.innerText = currentLang === 'en' ? 'ID' : 'EN';
    }

    langToggle.addEventListener('click', () => {
        currentLang = currentLang === 'en' ? 'id' : 'en';
        updateLanguage();
        document.getElementById('resultBox').classList.add('hidden');
    });

    // --- 3. ANIMASI SCROLL (UI) ---
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = entry.target.classList.contains('scale-in') ? 'scale(1)' : 'translateY(0)';
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.slide-up, .scale-in, .fade-in').forEach(el => observer.observe(el));

    // --- 4. LOGIKA KALKULATOR KALORI ---
    const form = document.getElementById('calcForm');
    const resultBox = document.getElementById('resultBox');
    const btnReset = document.getElementById('btnReset');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const weight = parseFloat(document.getElementById('weight').value);
        const duration = parseFloat(document.getElementById('duration').value);
        const activitySelect = document.getElementById('activity');
        const metValue = parseFloat(activitySelect.value);

        const activityOption = activitySelect.options[activitySelect.selectedIndex];
        const activityName = currentLang === 'en' ? activityOption.getAttribute('data-en') : activityOption.getAttribute('data-id');

        const goalValue = document.getElementById('goal').value;

        const burnedCalories = Math.round(((metValue * 3.5 * weight) / 200) * duration);
        const waterNeeded = Math.round((duration / 30) * 250);

        // === SMART RECOMMENDATION ENGINE (Rule-Based) ===
        const smartRec = generateSmartRecommendation(burnedCalories, duration, goalValue, activityName, weight, currentLang);

        document.getElementById('narrativeResult').innerHTML = smartRec.narrative;
        document.getElementById('hydrationTip').innerText = smartRec.hydration;

        resultBox.classList.remove('hidden');
        resultBox.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // === FOOD EQUIVALENT ===
        renderFoodEquivalent(burnedCalories, currentLang);

        // === SAVE TO HISTORY (LocalStorage) — harus sebelum weekly progress ===
        saveToHistory({ aktivitas: activityName, durasi: duration, kalori: burnedCalories, tujuan: goalValue, berat: weight });
        renderHistory();

        // === WEEKLY PROGRESS (dihitung dari history 7 hari terakhir) ===
        updateWeeklyProgress(currentLang);

        // === UPDATE QUICK CHAT CONTEXT ===
        lastCalcData = { kalori: burnedCalories, durasi: duration, aktivitas: activityName, tujuan: goalValue, berat: weight };
        renderQuickChatOptions();

        // =====> KODE FETCH DITARUH DI SINI <=====
        fetch('simpan_riwayat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                tujuan: goalValue,
                aktivitas: activityName,
                durasi: duration,
                kalori: burnedCalories
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                console.log("Mantap! Data berhasil masuk database.");
            } else {
                console.error("Gagal simpan:", data.pesan);
            }
        })
        .catch(error => console.error('Error pengiriman:', error));
        // =====> AKHIR KODE FETCH <=====

    });

    // Fitur Tombol Reset
    btnReset.addEventListener('click', () => {
        form.reset();
        resultBox.classList.add('hidden');
        document.getElementById('foodEquivalent').classList.add('hidden');
        document.getElementById('weeklyProgress').classList.add('hidden');
    });

    // ================================================================
    // 5. SMART RECOMMENDATION ENGINE (Rule-Based, No API)
    // ================================================================
    function generateSmartRecommendation(cal, dur, goal, activity, weight, lang) {
        // Klasifikasi intensitas kalori
        let intensity; // ringan / sedang / tinggi
        if (cal < 150) intensity = 'ringan';
        else if (cal < 350) intensity = 'sedang';
        else intensity = 'tinggi';

        // Klasifikasi durasi
        let durLevel;
        if (dur < 20) durLevel = 'singkat';
        else if (dur <= 45) durLevel = 'sedang';
        else durLevel = 'panjang';

        const waterNeeded = Math.round((dur / 30) * 250);

        if (lang === 'id') {
            let narr = `Kamu berhasil membakar sekitar <span class="text-4xl font-bold block my-3 text-green-200">${cal} kkal</span> dari <b>${dur} menit</b> melakukan ${activity.toLowerCase()}. <br><br>`;

            // Narasi berdasarkan kombinasi intensitas × tujuan
            if (goal === 'lose') {
                if (intensity === 'tinggi') {
                    narr += `🔥 Luar biasa! Pembakaran kalori yang sangat tinggi — sangat ideal untuk program diet kamu. Dengan intensitas ini, pastikan kamu tetap makan cukup protein (minimal 1.6g/kg berat badan) agar massa otot tetap terjaga.`;
                } else if (intensity === 'sedang') {
                    narr += `💪 Bagus! Ini menciptakan defisit kalori yang sehat. Untuk hasil optimal, kombinasikan dengan mengurangi asupan karbohidrat sederhana dan perbanyak sayuran hijau.`;
                } else {
                    narr += `🚶 Aktivitas ringan ini tetap berkontribusi pada defisit kalorimu. Coba tingkatkan durasi ke 30-45 menit atau pilih aktivitas yang lebih intens seperti lari atau bersepeda untuk mempercepat hasil.`;
                }
            } else if (goal === 'bulk') {
                if (intensity === 'tinggi') {
                    narr += `⚡ Sesi kardio yang intens! Karena kamu sedang bulking, pastikan makan ekstra <b>${cal + 300} kkal</b> hari ini. Fokus pada protein dan karbohidrat kompleks dalam 30 menit setelah olahraga.`;
                } else if (intensity === 'sedang') {
                    narr += `🏋️ Kardio moderat yang bagus untuk kesehatan jantung tanpa mengorbankan bulking-mu. Pastikan asupan kalori harianmu tetap surplus — tambahkan minimal <b>${cal + 200} kkal</b> hari ini.`;
                } else {
                    narr += `🧘 Aktivitas ringan ini bagus untuk recovery aktif. Tetap pertahankan surplus kalori harianmu dan fokus pada latihan beban untuk memaksimalkan pertumbuhan otot.`;
                }
            } else {
                if (intensity === 'tinggi') {
                    narr += `🌟 Performa hebat! Kamu sudah membakar cukup banyak kalori. Untuk menjaga berat badan, pastikan asupan makananmu seimbang — jangan sampai terlalu defisit.`;
                } else if (intensity === 'sedang') {
                    narr += `✅ Aktivitas yang sempurna untuk menjaga gaya hidup sehat! Konsistensi seperti ini adalah kunci — tubuhmu berterima kasih.`;
                } else {
                    narr += `🌿 Setiap gerakan berarti! Aktivitas ringan ini membantu metabolisme dan kesehatan mental. Coba variasikan dengan aktivitas yang berbeda setiap harinya.`;
                }
            }

            // Tambahan saran durasi
            if (durLevel === 'singkat') {
                narr += `<br><br>⏱️ <em>Saran: Durasi ${dur} menit masih relatif singkat. WHO merekomendasikan 150-300 menit aktivitas per minggu. Coba tingkatkan secara bertahap!</em>`;
            } else if (durLevel === 'panjang') {
                narr += `<br><br>⏱️ <em>Saran: Durasi ${dur} menit sudah sangat bagus! Pastikan kamu melakukan pemanasan dan pendinginan yang cukup untuk mencegah cedera.</em>`;
            }

            return {
                narrative: narr,
                hydration: `Tips Pemulihan: Minum sekitar ${waterNeeded}ml air putih untuk mengganti cairan tubuh. ${dur > 45 ? 'Untuk sesi panjang, pertimbangkan minuman elektrolit.' : ''}`
            };
        } else {
            let narr = `You just burned approximately <span class="text-4xl font-bold block my-3 text-green-200">${cal} kcal</span> from <b>${dur} minutes</b> of ${activity.toLowerCase()}. <br><br>`;

            if (goal === 'lose') {
                if (intensity === 'tinggi') {
                    narr += `🔥 Incredible! This high-calorie burn is perfect for your weight loss journey. With this intensity, ensure you're eating enough protein (at least 1.6g/kg body weight) to preserve muscle mass.`;
                } else if (intensity === 'sedang') {
                    narr += `💪 Great work! This creates a healthy caloric deficit. For optimal results, combine this with reducing simple carbs and increasing green vegetables in your diet.`;
                } else {
                    narr += `🚶 This light activity still contributes to your caloric deficit. Try increasing duration to 30-45 minutes or choose a more intense activity like running or cycling for faster results.`;
                }
            } else if (goal === 'bulk') {
                if (intensity === 'tinggi') {
                    narr += `⚡ Intense cardio session! Since you're bulking, make sure to eat an extra <b>${cal + 300} kcal</b> today. Focus on protein and complex carbs within 30 minutes post-workout.`;
                } else if (intensity === 'sedang') {
                    narr += `🏋️ Good moderate cardio for heart health without sacrificing your bulk. Keep your daily calorie intake in surplus — add at least <b>${cal + 200} kcal</b> today.`;
                } else {
                    narr += `🧘 This light activity is great for active recovery. Maintain your daily calorie surplus and focus on weight training to maximize muscle growth.`;
                }
            } else {
                if (intensity === 'tinggi') {
                    narr += `🌟 Amazing performance! You've burned quite a lot of calories. To maintain your weight, make sure your food intake is balanced — don't go too deep into a deficit.`;
                } else if (intensity === 'sedang') {
                    narr += `✅ Perfect activity for maintaining a healthy lifestyle! Consistency like this is key — your body thanks you.`;
                } else {
                    narr += `🌿 Every movement counts! This light activity helps your metabolism and mental health. Try mixing in different activities each day for variety.`;
                }
            }

            if (durLevel === 'singkat') {
                narr += `<br><br>⏱️ <em>Tip: ${dur} minutes is relatively short. WHO recommends 150-300 minutes of activity per week. Try increasing gradually!</em>`;
            } else if (durLevel === 'panjang') {
                narr += `<br><br>⏱️ <em>Tip: ${dur} minutes is excellent! Make sure to warm up and cool down properly to prevent injuries.</em>`;
            }

            return {
                narrative: narr,
                hydration: `Recovery Tip: Drink ~${waterNeeded}ml of water to replenish lost fluids. ${dur > 45 ? 'For longer sessions, consider an electrolyte drink.' : ''}`
            };
        }
    }

    // ================================================================
    // 6. FOOD EQUIVALENT
    // ================================================================
    const foodDatabase = [
        { emoji: '🍌', name_en: 'Banana', name_id: 'Pisang', cal: 90 },
        { emoji: '🍎', name_en: 'Apple', name_id: 'Apel', cal: 95 },
        { emoji: '🍞', name_en: 'Bread Slice', name_id: 'Roti Tawar', cal: 80 },
        { emoji: '🍚', name_en: 'Rice Bowl', name_id: 'Nasi Putih', cal: 200 },
        { emoji: '🍗', name_en: 'Fried Chicken', name_id: 'Ayam Goreng', cal: 250 },
        { emoji: '🍕', name_en: 'Pizza Slice', name_id: 'Potongan Pizza', cal: 280 },
        { emoji: '🍩', name_en: 'Donut', name_id: 'Donat', cal: 250 },
        { emoji: '🥤', name_en: 'Soft Drink', name_id: 'Minuman Manis', cal: 140 },
        { emoji: '🍫', name_en: 'Chocolate Bar', name_id: 'Cokelat Batang', cal: 230 },
        { emoji: '🥚', name_en: 'Boiled Egg', name_id: 'Telur Rebus', cal: 70 },
    ];

    function renderFoodEquivalent(cal, lang) {
        const container = document.getElementById('foodGrid');
        const section = document.getElementById('foodEquivalent');
        container.innerHTML = '';

        // Pick 5 foods that make sense for this calorie range
        const relevant = foodDatabase.filter(f => cal / f.cal >= 0.3).slice(0, 5);

        relevant.forEach(food => {
            const count = (cal / food.cal).toFixed(1);
            const name = lang === 'id' ? food.name_id : food.name_en;
            container.innerHTML += `
                <div class="food-card">
                    <div class="food-emoji">${food.emoji}</div>
                    <div class="food-name">${name}</div>
                    <div class="food-cal">${food.cal} kkal</div>
                    <div class="food-count">≈ ${count}×</div>
                </div>`;
        });

        section.classList.remove('hidden');
    }

    // ================================================================
    // 7. WEEKLY PROGRESS (Dihitung dari cc-history, rolling 7 hari)
    // ================================================================

    /**
     * Hitung total menit dari riwayat aktivitas dalam 7 hari terakhir.
     * Tidak menggunakan localStorage terpisah — langsung baca cc-history.
     * Ini memastikan weekly progress selalu sinkron dengan riwayat.
     */
    function getWeeklyMinutesFromHistory() {
        const history = getHistory();
        const now = new Date();
        const sevenDaysAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);

        let totalMinutes = 0;
        history.forEach(item => {
            const itemDate = new Date(item.timestamp);
            if (itemDate >= sevenDaysAgo && itemDate <= now) {
                totalMinutes += Number(item.durasi) || 0;
            }
        });

        return totalMinutes;
    }

    /**
     * Render weekly progress bar.
     * Tidak menerima parameter duration — menghitung langsung dari history.
     */
    function updateWeeklyProgress(lang) {
        const totalMinutes = getWeeklyMinutesFromHistory();
        const target = 150; // WHO minimum
        const targetMax = 300;
        const pct = Math.min(Math.round((totalMinutes / target) * 100), 200);
        const section = document.getElementById('weeklyProgress');

        const label = lang === 'id'
            ? `${totalMinutes} dari ${target}–${targetMax} menit/minggu`
            : `${totalMinutes} of ${target}–${targetMax} min/week`;

        document.getElementById('weeklyLabel').textContent = label;
        document.getElementById('weeklyPercent').textContent = `${pct}%`;

        const bar = document.getElementById('weeklyBar');
        bar.style.width = Math.min(pct, 100) + '%';
        bar.className = 'progress-fill' + (pct > 100 ? ' over' : '');

        let tip;
        if (lang === 'id') {
            if (totalMinutes === 0) tip = '📭 Belum ada aktivitas minggu ini. Ayo mulai bergerak!';
            else if (pct < 50) tip = '🏁 Kamu baru memulai minggu ini. Terus semangat!';
            else if (pct < 100) tip = '🔥 Hebat! Kamu hampir mencapai target mingguan.';
            else if (pct <= 150) tip = '🎉 Target tercapai! Kamu sudah di zona sehat WHO.';
            else tip = '⚡ Luar biasa! Kamu melampaui rekomendasi. Jangan lupa istirahat!';
        } else {
            if (totalMinutes === 0) tip = '📭 No activity this week yet. Let\'s get moving!';
            else if (pct < 50) tip = '🏁 You\'re just starting this week. Keep it up!';
            else if (pct < 100) tip = '🔥 Great! You\'re almost at your weekly target.';
            else if (pct <= 150) tip = '🎉 Target reached! You\'re in the WHO healthy zone.';
            else tip = '⚡ Amazing! You\'ve exceeded the recommendation. Don\'t forget to rest!';
        }
        document.getElementById('weeklyTip').textContent = tip;
        section.classList.remove('hidden');
    }

    // ================================================================
    // 8. ACTIVITY HISTORY (LocalStorage)
    // ================================================================
    function getHistory() {
        return JSON.parse(localStorage.getItem('cc-history') || '[]');
    }

    function saveToHistory(entry) {
        const history = getHistory();
        entry.timestamp = new Date().toISOString();
        history.unshift(entry);
        if (history.length > 5) history.pop();
        localStorage.setItem('cc-history', JSON.stringify(history));
    }

    function renderHistory() {
        const history = getHistory();
        const list = document.getElementById('historyList');
        const empty = document.getElementById('historyEmpty');
        const clearBtn = document.getElementById('btnClearHistory');

        list.innerHTML = '';

        if (history.length === 0) {
            empty.classList.remove('hidden');
            clearBtn.classList.add('hidden');
            return;
        }

        empty.classList.add('hidden');
        clearBtn.classList.remove('hidden');

        const actEmoji = { 'lose': '🏃', 'maintain': '🧘', 'bulk': '🏋️' };
        const actColor = { 'lose': 'bg-red-100 dark:bg-red-900/30', 'maintain': 'bg-green-100 dark:bg-green-900/30', 'bulk': 'bg-blue-100 dark:bg-blue-900/30' };

        history.forEach((item, i) => {
            const date = new Date(item.timestamp);
            const timeStr = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
            list.innerHTML += `
                <div class="history-item" style="animation: qcBubblePop 0.3s ease-out ${i * 0.08}s both">
                    <div class="history-icon ${actColor[item.tujuan] || 'bg-gray-100 dark:bg-gray-800'}">
                        ${actEmoji[item.tujuan] || '🏃'}
                    </div>
                    <div class="history-meta">
                        <div class="h-title">${item.aktivitas}</div>
                        <div class="h-sub">${timeStr} · ${item.durasi} min · ${item.berat} kg</div>
                    </div>
                    <div class="history-cal">${item.kalori} kkal</div>
                </div>`;
        });
    }

    document.getElementById('btnClearHistory').addEventListener('click', () => {
        if (confirm(currentLang === 'id' ? 'Hapus semua riwayat aktivitas?' : 'Clear all activity history?')) {
            localStorage.removeItem('cc-history');
            localStorage.removeItem('cc-weekly'); // bersihkan data lama jika ada
            renderHistory();
            updateWeeklyProgress(currentLang); // refresh weekly — otomatis jadi 0
        }
    });

    // Render history & weekly progress on page load
    renderHistory();
    updateWeeklyProgress(currentLang);

    // ================================================================
    // 9. QUICK CHAT (Rule-Based, No API)
    // ================================================================
    let lastCalcData = null;

    const chatQuestions = [
        {
            id: 'diet_enough',
            label_id: '🍽️ Apakah ini cukup untuk diet?',
            label_en: '🍽️ Is this enough for a diet?',
            answer: (d, lang) => {
                if (!d) return lang === 'id' ? 'Kamu belum menghitung kalori. Gunakan kalkulator di atas dulu ya! ☝️' : 'You haven\'t calculated yet. Use the calculator above first! ☝️';
                if (lang === 'id') {
                    if (d.kalori >= 300) return `✅ Dengan membakar ${d.kalori} kkal, ini sudah cukup bagus untuk program diet! Pastikan defisit kalori harianmu sekitar 300-500 kkal untuk penurunan berat badan yang sehat dan berkelanjutan.`;
                    if (d.kalori >= 150) return `⚠️ Pembakaran ${d.kalori} kkal sudah lumayan, tapi untuk diet yang efektif, coba tingkatkan durasi atau pilih aktivitas yang lebih intens. Target idealnya 300+ kkal per sesi.`;
                    return `📌 ${d.kalori} kkal masih sedikit untuk program diet. Coba lari 30 menit atau bersepeda 40 menit untuk pembakaran yang lebih optimal.`;
                } else {
                    if (d.kalori >= 300) return `✅ Burning ${d.kalori} kcal is great for a diet! Aim for a daily deficit of 300-500 kcal for healthy, sustainable weight loss.`;
                    if (d.kalori >= 150) return `⚠️ ${d.kalori} kcal is decent, but for effective dieting, try increasing duration or choosing a more intense activity. Aim for 300+ kcal per session.`;
                    return `📌 ${d.kalori} kcal is quite low for dieting. Try running 30 min or cycling 40 min for better calorie burn.`;
                }
            }
        },
        {
            id: 'ideal_duration',
            label_id: '⏱️ Berapa durasi ideal?',
            label_en: '⏱️ What\'s the ideal duration?',
            answer: (d, lang) => {
                if (lang === 'id') {
                    let ans = `WHO merekomendasikan 150-300 menit aktivitas aerobik intensitas sedang per minggu, atau 75-150 menit intensitas tinggi.\n\n`;
                    ans += `Untuk sesi harian, 30-60 menit adalah durasi ideal. `;
                    if (d && d.durasi < 30) ans += `Durasi kamu (${d.durasi} menit) masih di bawah rekomendasi. Coba tingkatkan bertahap! 👍`;
                    else if (d && d.durasi <= 60) ans += `Durasi kamu (${d.durasi} menit) sudah pas! Pertahankan konsistensi ini. 💪`;
                    else if (d) ans += `Durasi kamu (${d.durasi} menit) sudah sangat bagus! Pastikan kamu juga memberi waktu pemulihan. 🧘`;
                    return ans;
                } else {
                    let ans = `WHO recommends 150-300 minutes of moderate or 75-150 minutes of vigorous aerobic activity per week.\n\n`;
                    ans += `For daily sessions, 30-60 minutes is ideal. `;
                    if (d && d.durasi < 30) ans += `Your duration (${d.durasi} min) is below recommendation. Try increasing gradually! 👍`;
                    else if (d && d.durasi <= 60) ans += `Your duration (${d.durasi} min) is spot on! Keep this consistency. 💪`;
                    else if (d) ans += `Your duration (${d.durasi} min) is excellent! Make sure you also allow recovery time. 🧘`;
                    return ans;
                }
            }
        },
        {
            id: 'best_activity',
            label_id: '🏃 Apa aktivitas yang disarankan?',
            label_en: '🏃 What activity is recommended?',
            answer: (d, lang) => {
                if (lang === 'id') {
                    let ans = '';
                    if (d && d.tujuan === 'lose') ans = `Untuk menurunkan berat badan, aktivitas terbaik adalah:\n🏃 Lari (8-10 MET) — pembakaran tinggi\n🚴 Bersepeda cepat (6-8 MET)\n🏊 Renang (7 MET)\n\nKombinasikan dengan latihan kekuatan 2-3x seminggu untuk hasil optimal!`;
                    else if (d && d.tujuan === 'bulk') ans = `Untuk bulking, prioritaskan latihan beban. Tapi kardio ringan tetap penting:\n🚶 Jalan 20-30 menit untuk recovery\n🚴 Bersepeda santai\n🧘 Yoga untuk fleksibilitas\n\nJangan terlalu banyak kardio intensitas tinggi agar surplus kalorimu terjaga!`;
                    else ans = `Untuk menjaga kebugaran, variasikan aktivitas:\n🏃 Jogging 3x seminggu\n🧘 Yoga 2x seminggu\n🚴 Bersepeda di akhir pekan\n🚶 Jalan kaki setiap hari minimal 20 menit\n\nVariasi menjaga motivasi dan melatih otot yang berbeda!`;
                    return ans;
                } else {
                    let ans = '';
                    if (d && d.tujuan === 'lose') ans = `For weight loss, the best activities are:\n🏃 Running (8-10 MET) — high calorie burn\n🚴 Fast cycling (6-8 MET)\n🏊 Swimming (7 MET)\n\nCombine with strength training 2-3x/week for optimal results!`;
                    else if (d && d.tujuan === 'bulk') ans = `For bulking, prioritize weight training. But light cardio is still important:\n🚶 Walking 20-30 min for recovery\n🚴 Casual cycling\n🧘 Yoga for flexibility\n\nAvoid too much high-intensity cardio to maintain your calorie surplus!`;
                    else ans = `For maintaining fitness, vary your activities:\n🏃 Jogging 3x/week\n🧘 Yoga 2x/week\n🚴 Cycling on weekends\n🚶 Walking daily for at least 20 min\n\nVariety keeps you motivated and trains different muscles!`;
                    return ans;
                }
            }
        },
        {
            id: 'water_intake',
            label_id: '💧 Berapa air yang harus diminum?',
            label_en: '💧 How much water should I drink?',
            answer: (d, lang) => {
                const baseWater = d ? Math.round(d.berat * 33) : 2300;
                const exerciseWater = d ? Math.round((d.durasi / 30) * 250) : 250;
                if (lang === 'id') {
                    return `💧 Kebutuhan air harianmu sekitar <b>${baseWater}ml</b> (berdasarkan berat badan ${d ? d.berat : '?'}kg).\n\nSetelah olahraga ${d ? d.durasi : '?'} menit, tambahkan sekitar <b>${exerciseWater}ml</b> untuk mengganti cairan yang hilang.\n\nTotal hari ini: sekitar <b>${baseWater + exerciseWater}ml</b> (≈ ${Math.round((baseWater + exerciseWater) / 250)} gelas).`;
                } else {
                    return `💧 Your daily water need is about <b>${baseWater}ml</b> (based on ${d ? d.berat : '?'}kg body weight).\n\nAfter ${d ? d.durasi : '?'} min of exercise, add about <b>${exerciseWater}ml</b> to replace lost fluids.\n\nTotal today: about <b>${baseWater + exerciseWater}ml</b> (≈ ${Math.round((baseWater + exerciseWater) / 250)} glasses).`;
                }
            }
        },
        {
            id: 'weekly_target',
            label_id: '📊 Bagaimana progres mingguan saya?',
            label_en: '📊 How is my weekly progress?',
            answer: (d, lang) => {
                const weeklyMin = getWeeklyMinutesFromHistory();
                const pct = Math.round((weeklyMin / 150) * 100);
                if (lang === 'id') {
                    let ans = `📊 Dalam 7 hari terakhir kamu sudah berlatih <b>${weeklyMin} menit</b> (${pct}% dari target 150 menit).\n\n`;
                    if (weeklyMin === 0) ans += `Belum ada aktivitas tercatat. Ayo mulai bergerak! 🏃`;
                    else if (pct < 50) ans += `Masih ada waktu! Coba tambah 2-3 sesi lagi untuk mencapai target.`;
                    else if (pct < 100) ans += `Hebat, kamu hampir di sana! Sedikit lagi dan target tercapai. 💪`;
                    else ans += `🎉 Target tercapai! Kamu sudah melampaui rekomendasi WHO. Pertahankan!`;
                    return ans;
                } else {
                    let ans = `📊 In the last 7 days you've exercised <b>${weeklyMin} minutes</b> (${pct}% of the 150-min target).\n\n`;
                    if (weeklyMin === 0) ans += `No activity recorded yet. Let's get moving! 🏃`;
                    else if (pct < 50) ans += `There's still time! Try adding 2-3 more sessions to hit your target.`;
                    else if (pct < 100) ans += `Great, you're almost there! Just a bit more and you'll reach your goal. 💪`;
                    else ans += `🎉 Target reached! You've exceeded the WHO recommendation. Keep it up!`;
                    return ans;
                }
            }
        }
    ];

    const qcToggle  = document.getElementById('qchat-toggle');
    const qcBox     = document.getElementById('qchat-box');
    const qcClose   = document.getElementById('qchat-close');
    const qcMsgs    = document.getElementById('qchat-messages');
    const qcOpts    = document.getElementById('qchat-options');

    qcToggle.addEventListener('click', () => {
        qcBox.classList.toggle('open');
        if (qcBox.classList.contains('open')) {
            const badge = qcToggle.querySelector('.qc-badge');
            if (badge) badge.style.display = 'none';
            renderQuickChatOptions();
        }
    });
    qcClose.addEventListener('click', () => qcBox.classList.remove('open'));

    function addQCBubble(text, role) {
        const div = document.createElement('div');
        div.className = `qc-bubble ${role}`;
        div.innerHTML = text.replace(/\n/g, '<br>');
        qcMsgs.appendChild(div);
        qcMsgs.scrollTop = qcMsgs.scrollHeight;
    }

    function renderQuickChatOptions() {
        qcOpts.innerHTML = '';
        chatQuestions.forEach(q => {
            const label = currentLang === 'id' ? q.label_id : q.label_en;
            const btn = document.createElement('button');
            btn.className = 'qc-opt-btn';
            btn.textContent = label;
            btn.addEventListener('click', () => handleQuickChat(q));
            qcOpts.appendChild(btn);
        });
    }

    function handleQuickChat(q) {
        const label = currentLang === 'id' ? q.label_id : q.label_en;
        addQCBubble(label, 'user');

        // Simulate brief "thinking" delay
        setTimeout(() => {
            const answer = q.answer(lastCalcData, currentLang);
            addQCBubble(answer, 'bot');
        }, 400);
    }

    // Initial render
    renderQuickChatOptions();

    // ================================================================
    // 10. ARTIKEL KESEHATAN (Inline, No External Redirect)
    // ================================================================
    const artikelData = [
        {
            id: 1,
            emoji: '🏃',
            banner: 'linear-gradient(135deg, #059669, #10b981)',
            title: 'Berapa Banyak Aktivitas Fisik yang Anda Butuhkan?',
            source: 'Kemenkes RI',
            sourceUrl: 'https://keslan.kemkes.go.id/view_artikel/2459/berapa-banyak-aktivitas-fisik-yang-anda-butuhkan',
            excerpt: 'WHO merekomendasikan 150–300 menit aktivitas aerobik intensitas sedang per minggu untuk orang dewasa.',
            content: `<h3>Rekomendasi WHO untuk Aktivitas Fisik</h3>
<p>Organisasi Kesehatan Dunia (WHO) merekomendasikan agar orang dewasa berusia 18–64 tahun melakukan:</p>
<ul>
<li><strong>150–300 menit</strong> aktivitas fisik aerobik intensitas sedang per minggu, ATAU</li>
<li><strong>75–150 menit</strong> aktivitas fisik aerobik intensitas berat per minggu</li>
<li>Kombinasi keduanya juga direkomendasikan</li>
</ul>

<h3>Aktivitas Penguatan Otot</h3>
<p>Selain aerobik, WHO juga menyarankan latihan penguatan otot untuk semua kelompok otot utama sebanyak <strong>2 hari atau lebih per minggu</strong>. Contohnya:</p>
<ul>
<li>Angkat beban atau latihan resistance</li>
<li>Push-up, sit-up, dan squat</li>
<li>Yoga dan pilates</li>
</ul>

<h3>Untuk Anak dan Remaja (5–17 tahun)</h3>
<ul>
<li>Minimal <strong>60 menit per hari</strong> aktivitas fisik intensitas sedang hingga berat</li>
<li>Aktivitas aerobik intensitas berat sebaiknya dilakukan minimal <strong>3 kali per minggu</strong></li>
</ul>

<h3>Manfaat Utama</h3>
<p>Aktivitas fisik yang teratur terbukti dapat mengurangi risiko penyakit jantung, diabetes tipe 2, beberapa jenis kanker, serta meningkatkan kesehatan mental dan kualitas tidur. Setiap gerakan berarti — bahkan aktivitas ringan lebih baik daripada tidak bergerak sama sekali.</p>

<p><em>Catatan: Konsultasikan dengan tenaga medis sebelum memulai program olahraga baru, terutama jika memiliki kondisi kesehatan tertentu.</em></p>`
        },
        {
            id: 2,
            emoji: '💪',
            banner: 'linear-gradient(135deg, #0ea5e9, #06b6d4)',
            title: 'Manfaat Aktivitas Fisik: Sehat, Bugar, Bahagia',
            source: 'Halodoc',
            sourceUrl: 'https://www.halodoc.com/artikel/manfaat-aktivitas-fisik-sehat-bugar-bahagia',
            excerpt: 'Aktivitas fisik tidak hanya menyehatkan tubuh, tapi juga meningkatkan mood dan kebahagiaan secara keseluruhan.',
            content: `<h3>Manfaat untuk Kesehatan Fisik</h3>
<ul>
<li><strong>Menjaga berat badan ideal</strong> — Aktivitas fisik membantu membakar kalori berlebih dan mencegah obesitas</li>
<li><strong>Memperkuat jantung</strong> — Olahraga teratur menurunkan tekanan darah dan kadar kolesterol jahat (LDL)</li>
<li><strong>Meningkatkan imunitas</strong> — Tubuh yang aktif memiliki sistem kekebalan yang lebih kuat</li>
<li><strong>Mencegah penyakit kronis</strong> — Mengurangi risiko diabetes tipe 2, stroke, dan beberapa jenis kanker</li>
</ul>

<h3>Manfaat untuk Kesehatan Mental</h3>
<ul>
<li><strong>Mengurangi stres dan kecemasan</strong> — Olahraga memicu pelepasan endorfin, hormon yang membuat perasaan lebih baik</li>
<li><strong>Meningkatkan kualitas tidur</strong> — Aktivitas fisik membantu mengatur ritme sirkadian tubuh</li>
<li><strong>Meningkatkan kepercayaan diri</strong> — Pencapaian dalam olahraga memberikan rasa bangga dan motivasi</li>
<li><strong>Melawan depresi</strong> — Penelitian menunjukkan olahraga teratur sama efektifnya dengan antidepresan ringan</li>
</ul>

<h3>Tips Memulai</h3>
<p>Tidak perlu langsung intensitas tinggi. Mulailah dengan <strong>jalan kaki 15-20 menit per hari</strong>, lalu tingkatkan secara bertahap. Yang terpenting adalah konsistensi, bukan intensitas. Pilih aktivitas yang kamu nikmati agar mudah dijadikan kebiasaan.</p>

<p>Ingat: <strong>Bergerak sedikit lebih baik daripada tidak bergerak sama sekali!</strong></p>`
        },
        {
            id: 3,
            emoji: '🏋️',
            banner: 'linear-gradient(135deg, #8b5cf6, #a78bfa)',
            title: 'Pentingnya Olahraga untuk Kesehatan Optimal',
            source: 'Pemkab Sarolangun',
            sourceUrl: 'https://sarolangunkab.go.id/artikel/baca/pentingnya-olahraga-untuk-kesehatan-optimal',
            excerpt: 'Olahraga merupakan bagian integral dari gaya hidup sehat yang memberikan manfaat bagi kesehatan fisik dan mental.',
            content: `<h3>Manfaat Kesehatan Fisik</h3>
<p>Olahraga teratur membantu menjaga berat badan yang sehat, meningkatkan kekuatan otot, serta meningkatkan kesehatan jantung dan sistem kardiovaskular.</p>
<ul>
<li><strong>Meningkatkan kebugaran dan stamina</strong> — tubuh mampu menjalani aktivitas sehari-hari dengan lebih mudah dan berenergi</li>
<li><strong>Memperkuat otot dan tulang</strong> — mengurangi risiko cedera dan osteoporosis</li>
<li><strong>Menjaga kesehatan jantung</strong> — menurunkan tekanan darah, kolesterol, dan risiko penyakit jantung</li>
<li><strong>Menurunkan berat badan</strong> — membakar kalori dan mengurangi risiko obesitas</li>
<li><strong>Meningkatkan kualitas tidur</strong> — tidur lebih nyenyak dan berkualitas</li>
</ul>

<h3>Manfaat Kesehatan Mental</h3>
<p>Berolahraga melepaskan endorfin, neurotransmiter alami yang dikenal sebagai &ldquo;hormon bahagia&rdquo;, yang dapat meningkatkan perasaan positif dan mengurangi rasa sakit.</p>
<ul>
<li>Mengurangi stres dan kecemasan</li>
<li>Meningkatkan fokus dan konsentrasi</li>
<li>Meningkatkan kepercayaan diri</li>
<li>Mengurangi risiko depresi</li>
</ul>

<h3>Tips Memulai Rutinitas Olahraga</h3>
<ul>
<li><strong>Pilih aktivitas yang Anda nikmati</strong> — berjalan, berlari, bersepeda, atau berenang</li>
<li><strong>Tetapkan tujuan realistis</strong> — spesifik dan terukur</li>
<li><strong>Mulai secara perlahan</strong> — tingkatkan intensitas secara bertahap</li>
<li><strong>Jadwalkan waktu</strong> — seperti janji penting lainnya</li>
<li><strong>Temukan dukungan</strong> — berolahraga dengan teman lebih menyenangkan</li>
</ul>

<p><em>Sumber: Ringgana Wandy Wiguna</em></p>`
        },
        {
            id: 4,
            emoji: '🧘',
            banner: 'linear-gradient(135deg, #f59e0b, #fbbf24)',
            title: 'Aktivitas Fisik untuk Kesehatan Tubuh dan Jiwa',
            source: 'Puskesmas Babakan Sari',
            sourceUrl: 'https://pkmbabakansaribdg.com/aktivitas-fisik-untuk-kesehatan-tubuh-dan-jiwa/',
            excerpt: 'Kurangnya aktivitas fisik merupakan salah satu penyebab tingginya Penyakit Tidak Menular (PTM) di Indonesia.',
            content: `<h3>Jenis Aktivitas Fisik</h3>
<p>Menurut Kemenkes, kurangnya aktivitas fisik merupakan salah satu penyebab cukup tingginya PTM (Penyakit Tidak Menular) di Indonesia.</p>

<p><strong>1. Aktivitas Fisik Harian</strong><br>Kegiatan sehari-hari seperti mencuci baju, mengepel, jalan kaki, berkebun, dan bermain dengan anak. Kalori yang terbakar bisa 50–200 kkal per kegiatan.</p>

<p><strong>2. Latihan Fisik</strong><br>Aktivitas terstruktur dan terencana seperti jalan kaki, jogging, push-up, peregangan, senam aerobik, dan bersepeda.</p>

<p><strong>3. Olahraga</strong><br>Aktivitas fisik terstruktur dengan aturan dan tujuan prestasi, seperti sepak bola, bulu tangkis, basket, dan berenang.</p>

<h3>Berdasarkan Intensitas</h3>
<ul>
<li><strong>Ringan</strong> — Mencuci piring, memasak, jalan santai, memancing, peregangan otot</li>
<li><strong>Sedang</strong> — Berjalan cepat, bersepeda, naik tangga, yoga, menari, voli</li>
<li><strong>Berat</strong> — Futsal, lari, berenang, naik gunung, lompat tali, bulutangkis</li>
</ul>

<h3>Manfaat bagi Tubuh</h3>
<ul>
<li>Mengendalikan kadar kolesterol dan stres</li>
<li>Mengurangi kecemasan</li>
<li>Memperbaiki kelenturan sendi dan kekuatan otot</li>
<li>Memperbaiki postur tubuh</li>
<li>Menurunkan risiko osteoporosis pada wanita</li>
</ul>

<h3>Prinsip BBTT</h3>
<p>Untuk hasil maksimal, lakukan aktivitas fisik dengan prinsip:</p>
<ul>
<li><strong>Baik</strong> — sesuai kemampuan</li>
<li><strong>Benar</strong> — bertahap mulai pemanasan hingga pendinginan</li>
<li><strong>Terukur</strong> — diukur intensitas dan waktunya</li>
<li><strong>Teratur</strong> — 3-5 kali seminggu, minimal 150 menit/minggu (30 menit/hari)</li>
</ul>

<p><em>Oleh: Dwi Fera Wittya Sari, S.KM dan Dini Susanti, AMK</em></p>`
        },
        {
            id: 5,
            emoji: '📊',
            banner: 'linear-gradient(135deg, #ef4444, #f87171)',
            title: 'Hubungan Aktivitas Fisik dengan Kesehatan: Tinjauan Ilmiah',
            source: 'Jurnal FKM UMI',
            sourceUrl: 'https://jurnal.fkmumi.ac.id/index.php/woh/article/download/179/261',
            excerpt: 'Penelitian menunjukkan korelasi kuat antara tingkat aktivitas fisik dengan penurunan risiko penyakit kronis.',
            content: `<h3>Latar Belakang Penelitian</h3>
<p>Penelitian dari Fakultas Kesehatan Masyarakat Universitas Muslim Indonesia ini mengkaji hubungan antara tingkat aktivitas fisik dengan berbagai indikator kesehatan. Data menunjukkan bahwa gaya hidup sedenter (kurang gerak) merupakan faktor risiko utama penyakit tidak menular.</p>

<h3>Temuan Utama</h3>
<ul>
<li><strong>Penurunan risiko kardiovaskular</strong> — Individu yang aktif secara fisik memiliki risiko 20–35% lebih rendah terkena penyakit jantung</li>
<li><strong>Kontrol gula darah</strong> — Aktivitas fisik teratur meningkatkan sensitivitas insulin hingga 40%</li>
<li><strong>Kesehatan mental</strong> — Olahraga 30 menit/hari dapat mengurangi gejala depresi sebesar 26%</li>
<li><strong>Mortalitas</strong> — Orang yang aktif secara fisik memiliki harapan hidup 3–7 tahun lebih panjang</li>
</ul>

<h3>Rekomendasi Berdasarkan Penelitian</h3>
<p>Berdasarkan tinjauan literatur, peneliti merekomendasikan:</p>
<ul>
<li>Aktivitas fisik intensitas sedang minimal <strong>150 menit per minggu</strong></li>
<li>Mengurangi waktu duduk berkepanjangan (setiap 30 menit, berdiri dan bergerak)</li>
<li>Kombinasi latihan aerobik dan latihan resistensi untuk hasil optimal</li>
<li>Pendekatan bertahap untuk individu yang baru memulai program olahraga</li>
</ul>

<h3>Kesimpulan</h3>
<p>Aktivitas fisik merupakan "obat" yang paling murah dan efektif untuk pencegahan berbagai penyakit kronis. Peningkatan aktivitas fisik di tingkat populasi dapat menurunkan beban penyakit secara signifikan dan meningkatkan kualitas hidup masyarakat Indonesia.</p>

<p><em>Sumber: Window of Health — Jurnal Kesehatan, Vol. 1, No. 2</em></p>`
        }
    ];

    let showAllArticles = false;

    function renderArticles() {
        const grid = document.getElementById('articleGrid');
        const btn = document.getElementById('btnToggleArticles');
        const arrow = document.getElementById('toggleArrow');
        const items = showAllArticles ? artikelData : artikelData.slice(0, 3);

        grid.innerHTML = '';
        items.forEach((art, i) => {
            grid.innerHTML += `
            <div class="article-card" onclick="openArticle(${art.id})" style="animation: qcBubblePop 0.3s ease-out ${i * 0.1}s both">
                <div class="art-banner" style="background: ${art.banner}">
                    <span>${art.emoji}</span>
                </div>
                <div class="art-body">
                    <span class="art-source">📌 ${art.source}</span>
                    <div class="art-title">${art.title}</div>
                    <div class="art-excerpt">${art.excerpt}</div>
                    <div class="art-read">
                        <span class="translatable" data-en="Read summary" data-id="Baca ringkasan">Read summary</span>
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </div>`;
        });

        // Update button text
        const spanEl = btn.querySelector('.translatable');
        if (showAllArticles) {
            spanEl.setAttribute('data-en', 'Show Less');
            spanEl.setAttribute('data-id', 'Tampilkan Sedikit');
            arrow.style.transform = 'rotate(180deg)';
        } else {
            spanEl.setAttribute('data-en', 'Show All Articles');
            spanEl.setAttribute('data-id', 'Lihat Semua Artikel');
            arrow.style.transform = 'rotate(0deg)';
        }
        updateLanguage();
    }

    // Make openArticle globally accessible
    window.openArticle = function(id) {
        const art = artikelData.find(a => a.id === id);
        if (!art) return;

        document.getElementById('artDetailBanner').style.background = art.banner;
        document.getElementById('artDetailBanner').innerHTML = `<span>${art.emoji}</span>`;
        document.getElementById('artDetailTitle').textContent = art.title;
        document.getElementById('artDetailSource').innerHTML = `📌 ${art.source}`;
        document.getElementById('artDetailBody').innerHTML = art.content;
        document.getElementById('artDetailRef').href = art.sourceUrl;

        const overlay = document.getElementById('articleDetail');
        overlay.classList.add('open');
        overlay.scrollTop = 0;
        document.body.style.overflow = 'hidden';
    };

    function closeArticle() {
        document.getElementById('articleDetail').classList.remove('open');
        document.body.style.overflow = '';
    }

    document.getElementById('btnCloseArticle').addEventListener('click', closeArticle);
    document.getElementById('articleDetail').addEventListener('click', (e) => {
        if (e.target === document.getElementById('articleDetail')) closeArticle();
    });

    document.getElementById('btnToggleArticles').addEventListener('click', () => {
        showAllArticles = !showAllArticles;
        renderArticles();
        if (!showAllArticles) {
            document.getElementById('artikelSection').scrollIntoView({ behavior: 'smooth' });
        }
    });

    // Render articles on load
    renderArticles();

    // Setel bahasa awal
    updateLanguage();
});
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>