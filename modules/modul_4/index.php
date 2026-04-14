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
    themeToggleContainer.className = 'fixed bottom-6 right-6 z-50 flex flex-col gap-2';
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

        let recommendation = "";
        if (currentLang === 'en') {
            if (goalValue === 'lose') {
                recommendation = `This creates a solid caloric deficit to help your weight loss journey! Pair this with a protein-rich meal to maintain muscle.`;
            } else if (goalValue === 'bulk') {
                recommendation = `Great cardio session! Since you are bulking, make sure to eat an extra <b>${burnedCalories + 300} kcal</b> today to stay in a caloric surplus for muscle growth.`;
            } else {
                recommendation = `Excellent work keeping your body active! Consistent activity like this is perfect for maintaining your healthy lifestyle.`;
            }

            document.getElementById('narrativeResult').innerHTML = `
          You just burned approximately <span class="text-4xl font-bold block my-3 text-green-200">${burnedCalories} kcal</span> 
          from <b>${duration} minutes</b> of ${activityName.toLowerCase()}. <br><br>
          ${recommendation}
        `;
            document.getElementById('hydrationTip').innerText = `Recovery Tip: Drink ~${waterNeeded}ml of water to replenish lost fluids.`;
        } else {
            if (goalValue === 'lose') {
                recommendation = `Aktivitas ini menciptakan defisit kalori yang bagus untuk program dietmu! Imbangi dengan makanan tinggi protein untuk menjaga massa otot.`;
            } else if (goalValue === 'bulk') {
                recommendation = `Kardio yang mantap! Karena kamu sedang bulking, pastikan kamu makan ekstra <b>${burnedCalories + 300} kkal</b> hari ini agar tetap surplus kalori untuk ototmu.`;
            } else {
                recommendation = `Kerja bagus! Aktivitas fisik yang konsisten seperti ini sangat sempurna untuk menjaga pola hidup sehat dan berat badan idealmu.`;
            }

            document.getElementById('narrativeResult').innerHTML = `
          Kamu berhasil membakar sekitar <span class="text-4xl font-bold block my-3 text-green-200">${burnedCalories} kkal</span> 
          dari <b>${duration} menit</b> melakukan ${activityName.toLowerCase()}. <br><br>
          ${recommendation}
        `;
            document.getElementById('hydrationTip').innerText = `Tips Pemulihan: Minum sekitar ${waterNeeded}ml air putih untuk mengganti cairan tubuh.`;
        }

        resultBox.classList.remove('hidden');
        resultBox.scrollIntoView({ behavior: 'smooth', block: 'center' });

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
    });

    // Setel bahasa awal
    updateLanguage();
});
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>