<?php
/**
 * Modul 1 — Landing Page
 * 
 * Initial page for Modul 1.
 * Each module uses the shared auth system (SSO)
 * and can define its own database schema.
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'Modul 1';
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

    body,
    body * {
        font-family: 'Poppins', sans-serif !important;
    }

    @keyframes floating {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-15px);
        }
    }

    .animate-floating {
        animation: floating 4s ease-in-out infinite;
    }

    @keyframes gradientBG {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    @keyframes blobOrbit {
        0% {
            transform: translate(0, 0) scale(1) rotate(0deg);
        }

        33% {
            transform: translate(3vw, -3vh) scale(1.1) rotate(120deg);
        }

        66% {
            transform: translate(-3vw, 3vh) scale(0.9) rotate(240deg);
        }

        100% {
            transform: translate(0, 0) scale(1) rotate(360deg);
        }
    }

    @keyframes sparkle {

        0%,
        100% {
            opacity: 0;
            transform: scale(0.5) translateY(0);
        }

        50% {
            opacity: 0.8;
            transform: scale(1.2) translateY(-15px);
        }
    }

    .ios-glass {
        background: rgba(255, 255, 255, 0.45);
        backdrop-filter: blur(28px);
        -webkit-backdrop-filter: blur(28px);
        border: 1px solid rgba(255, 255, 255, 0.7);
        box-shadow: 0 8px 32px 0 rgba(114, 139, 169, 0.08);
        /* slight blue tint shadow */
        border-radius: 1.5rem;
        /* 24px */
    }

    .ios-glass-heavy {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(40px);
        -webkit-backdrop-filter: blur(40px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 12px 40px 0 rgba(114, 139, 169, 0.12);
        border-radius: 2rem;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    /* Abstract Shapes & Waves */
    .shape-blob {
        border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
        animation: blobMorph 10s ease-in-out infinite alternate;
    }

    @keyframes blobMorph {
        0% {
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            transform: rotate(0deg) scale(1);
        }

        100% {
            border-radius: 60% 40% 30% 70% / 50% 60% 40% 50%;
            transform: rotate(45deg) scale(1.05);
        }
    }

    .shape-cross {
        position: relative;
    }

    .shape-cross::before,
    .shape-cross::after {
        content: '';
        position: absolute;
        background-color: currentColor;
        border-radius: 50px;
    }

    .shape-cross::before {
        top: 0;
        bottom: 0;
        left: 35%;
        right: 35%;
    }

    .shape-cross::after {
        left: 0;
        right: 0;
        top: 35%;
        bottom: 35%;
    }

    .wave-divider {
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100%;
        overflow: hidden;
        line-height: 0;
    }

    .wave-divider svg {
        position: relative;
        display: block;
        width: 100%;
    }

    /* Home Mesh Background */
    .home-mesh-bg {
        position: absolute;
        inset: 0;
        min-height: 100%;
        overflow: hidden;
        z-index: 0;
        background-color: #F8FCFF;
    }

    .home-mesh-bg div {
        position: absolute;
        filter: blur(80px);
        border-radius: 50%;
        opacity: 0.7;
        animation: blobOrbit 20s ease-in-out infinite alternate;
    }

    .home-mesh-bg .blob1 {
        width: 35vw;
        height: 35vw;
        background: #DAE3EC;
        top: 5%;
        left: 5%;
    }

    .home-mesh-bg .blob2 {
        width: 30vw;
        height: 30vw;
        background: #728BA9;
        bottom: 10%;
        right: 5%;
        animation-delay: -5s;
        opacity: 0.5;
    }

    .home-mesh-bg .blob3 {
        width: 25vw;
        height: 25vw;
        background: #BAC7B6;
        top: 40%;
        left: 45%;
        animation-delay: -10s;
        opacity: 0.4;
    }
</style>

<!-- Top Header -->
<div class="w-full bg-[#728BA9] pt-10 pb-12 md:pb-20 relative z-40">
    <header class="max-w-6xl mx-auto px-8 flex justify-between items-center relative z-10">
        <div class="flex items-center gap-4">
            <img src="assets/images/logo.png" alt="RuangPulih Logo" class="h-12 opacity-90 brightness-0 invert">
            <div class="flex flex-col justify-center translate-y-0.5 mt-1">
                <span class="font-extrabold text-3xl tracking-tight leading-none text-white">Ruang<span
                        class="text-white/80">Pulih</span></span>
                <span class="text-[10px] text-white/70 font-bold tracking-widest mt-1.5 uppercase">Pasca-Operasi &
                    Rehabilitasi Mandiri</span>
            </div>
        </div>
        <div>
            <a href="onboarding.php"
                class="px-8 py-3 rounded-full border border-white/50 text-white hover:bg-white/10 transition-all font-bold text-xs tracking-wider uppercase shadow-sm">Dashboard</a>
        </div>
    </header>

    <!-- Wave Bridging into Hero Mesh (#F8FCFF) -->
    <div class="wave-divider">
        <svg class="h-[40px] md:h-[60px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,120 L0,40 C300,120 400,0 600,60 C800,120 900,0 1200,40 L1200,120 Z" fill="#F8FCFF"></path>
        </svg>
    </div>
</div>

<!-- Global Floating Nav -->
<div class="w-full flex justify-center sticky top-4 z-50 pointer-events-none -mb-12 pt-8">
    <nav id="floating-nav"
        class="bg-white rounded-2xl shadow-[0_10px_40px_rgb(0,0,0,0.06)] px-12 py-5 flex items-center justify-center gap-10 font-medium text-sm border border-white pointer-events-auto">
        <a href="#home" class="text-[#728BA9] font-bold hover:text-[#A3ACA0] transition-colors">Home</a>
        <span class="text-[#DAE3EC] font-light">|</span>
        <a href="#about" class="text-[#7F7F7F] hover:text-[#728BA9] transition-colors">About Us</a>
        <span class="text-[#DAE3EC] font-light">|</span>
        <a href="#services" class="text-[#7F7F7F] hover:text-[#728BA9] transition-colors">Features</a>
        <span class="text-[#DAE3EC] font-light">|</span>
        <a href="#team" class="text-[#7F7F7F] hover:text-[#728BA9] transition-colors">Our Team</a>
        <span class="text-[#DAE3EC] font-light">|</span>
        <a href="#contact" class="text-[#7F7F7F] hover:text-[#728BA9] transition-colors">Contact</a>
    </nav>
</div>

<!-- Hero Section -->
<section id="home"
    class="relative w-full min-h-[calc(100vh-140px)] flex flex-col justify-center px-8 pb-40 pt-28 z-10 overflow-hidden">
    <!-- Restored Mesh Background -->
    <div class="home-mesh-bg pointer-events-none">
        <div class="blob1"></div>
        <div class="blob2"></div>
        <div class="blob3"></div>
    </div>

    <!-- Abstract shapes replaced with surgical doodles -->
    <div class="absolute top-[10%] left-[5%] w-24 h-24 text-[#728BA9]/20 animate-floating pointer-events-none"
        style="animation-duration: 6s; transform: rotate(15deg);">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 2.5a1.5 1.5 0 00-3 0v4a5.5 5.5 0 0011 0v-4a1.5 1.5 0 00-3 0" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12.5 12v3a5.5 5.5 0 01-11 0v-2" />
            <circle cx="10" cy="18" r="4" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </div>
    <div class="absolute top-[40%] right-[25%] w-20 h-20 text-[#A3ACA0]/20 animate-floating pointer-events-none"
        style="animation-duration: 5s; transform: rotate(-10deg);">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 2l4 4M17 7l3-3M19 9L8.7 19.3c-1 1-2.5 1-3.4 0l-.6-.6c-1-1-1-2.5 0-3.4L15 5M9 11l4 4M5 19l-3 3M14 4l6 6" />
        </svg>        
    </div>
    <div class="absolute bottom-[20%] left-[30%] w-16 h-16 text-[#728BA9]/15 animate-floating pointer-events-none"
        style="animation-duration: 7s; transform: rotate(20deg);">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12A9 9 0 013 12a9 9 0 0118 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h2l1-3 2 6 1-3h2" />
        </svg>
    </div>

    <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center relative gap-8 text-center md:text-left z-10">
        <div
            class="w-full md:w-1/2 space-y-8 reveal-left opacity-0 -translate-x-16 transition-all duration-1000 ease-out">
            <h1 class="text-4xl md:text-5xl font-extrabold text-[#728BA9] leading-tight drop-shadow-sm">
                Post-Op Recovery.<br>Safe And Monitored<br>From Home.
            </h1>
            <p class="text-[#7F7F7F] max-w-md mx-auto md:mx-0 text-lg font-medium leading-relaxed">
                Panduan rehabilitasi pasca-operasi mandiri langkah demi langkah untuk mencegah komplikasi dan
                mempercepat kesembuhan keluarga tercinta Anda.
            </p>
            <a href="#about"
                class="inline-block px-8 py-3.5 rounded-full bg-[#B8C9DD] text-white font-bold text-lg hover:bg-[#728BA9] transition-all shadow-md hover:shadow-lg transform hover:-translate-y-1">
                Pelajari Lebih Lanjut
            </a>
        </div>
        <div
            class="w-full md:w-1/2 flex justify-center mt-12 md:mt-0 reveal-right opacity-0 translate-x-16 transition-all duration-1000 ease-out delay-200">
            <img src="assets/images/logo.png" alt="Hero Illustration"
                class="max-w-lg w-full drop-shadow-xl animate-floating transition-transform duration-500 hover:scale-105 opacity-80">
        </div>
    </div>

    <!-- Wave Bridging to White (#about) -->
    <div class="wave-divider">
        <svg class="h-[60px] md:h-[120px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,120 L0,60 C250,120 350,0 600,40 C850,80 950,0 1200,60 L1200,120 Z" fill="#FFFFFF"></path>
        </svg>
    </div>
</section>

<!-- About Us Section -->
<section id="about"
    class="w-full min-h-screen flex flex-col justify-center py-32 px-8 relative z-10 bg-[#FFFFFF] overflow-hidden">
    <!-- Abstract Shapes replaced with surgical doodles -->
    <div class="absolute top-[15%] left-[5%] w-32 md:w-48 text-[#728BA9]/10 animate-floating pointer-events-none" style="transform: rotate(-15deg);">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14h6M9 10h6m-6 8h6" />
        </svg>
    </div>
    <div class="absolute bottom-[10%] right-[10%] w-24 h-24 text-[#7F7F7F]/10 animate-floating pointer-events-none"
        style="animation-duration: 7s; transform: rotate(10deg);">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <circle cx="6" cy="6" r="3" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="6" cy="18" r="3" stroke-linecap="round" stroke-linejoin="round" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 4L8.12 15.88M14.47 14.48L20 20M8.12 8.12L12 12" />
        </svg>
    </div>

    <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center gap-16 relative z-10">
        <div
            class="w-full md:w-1/3 flex flex-col items-center md:items-start text-center md:text-left space-y-6 reveal-up opacity-0 translate-y-16 transition-all duration-1000">
            <h2 class="text-4xl font-extrabold text-[#A3ACA0]">Why Do We <br><span class="text-[#728BA9]">Exist?</span>
            </h2>
            <img src="assets/images/logo.png" alt="Heart Icon" class="w-48 mt-4 animate-floating">
        </div>
        <div
            class="w-full md:w-2/3 ios-glass p-10 relative reveal-up opacity-0 translate-y-16 transition-all duration-1000 delay-300">
            <div class="absolute -top-4 left-6 text-[#728BA9] opacity-20 text-8xl font-serif">"</div>
            <p class="text-[#7F7F7F] text-xl leading-relaxed italic relative z-10 font-medium">
                Untuk memberikan ruang aman dan langkah pemulihan yang mudah diakses bagi pasien dan keluarga. Karena
                setelah pulang dari RS, pasien sering merasa panik, kebingungan, dan khawatir saat menghadapi masa
                pemulihan tanpa pendampingan intensif dari tenaga medis. Kami hadir untuk menenangkan kecemasan akibat
                keterbatasan tenaga medis dengan pemantauan recovery yang lebih terstruktur. Kami membawa perawat di
                rumah, memandu dan membantu pemulihan agar pasien merasa lebih aman di masa transisi yang sulit.
            </p>
        </div>
    </div>

    <!-- Wave Bridging to #ECF2E6 (#problem) -->
    <div class="wave-divider">
        <svg class="h-[50px] md:h-[100px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,120 L0,40 C300,0 400,100 600,60 C800,20 900,120 1200,40 L1200,120 Z" fill="#ECF2E6"></path>
        </svg>
    </div>
</section>

<!-- Risky Section -->
<section
    class="w-full min-h-screen flex flex-col justify-center py-32 px-8 text-center relative z-10 bg-[#ECF2E6] overflow-hidden"
    id="problem">
    <!-- Abstract Shapes replaced with surgical doodles -->
    <div class="absolute top-[5%] right-[5%] w-32 md:w-48 text-[#728BA9]/10 animate-floating pointer-events-none" style="transform: rotate(12deg);">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 20.5l10-10a4.95 4.95 0 10-7-7l-10 10a4.95 4.95 0 107 7z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 8.5l7 7" />
        </svg>
    </div>
    <div class="absolute bottom-[10%] left-[10%] w-20 h-20 text-[#728BA9]/10 animate-floating pointer-events-none" style="transform: rotate(-20deg);">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12A9 9 0 013 12a9 9 0 0118 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h2l1-3 2 6 1-3h2" />
        </svg>
    </div>

    <div class="reveal-up opacity-0 translate-y-16 transition-all duration-1000 relative z-10">
        <h2 class="text-4xl font-extrabold text-[#7F7F7F] mb-3">Why Is <span class="text-[#728BA9]">Home Recovery</span>
            Risky?</h2>
        <p class="text-[#BAC7B6] font-bold mb-16 uppercase tracking-widest text-sm">Menurut Beberapa Studi Empiris</p>
    </div>

    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-10 reveal-stagger-parent relative z-10 w-full">
        <!-- Card 1 -->
        <div
            class="reveal-stagger-child opacity-0 translate-y-16 transition-all duration-700 bg-white/60 backdrop-blur-3xl border border-white p-10 rounded-3xl hover:-translate-y-4 group shadow-[0_10px_40px_rgb(0,0,0,0.03)] focus:outline-none">
            <div
                class="text-[#BAC7B6] mb-6 flex justify-center transform transition-transform duration-500 group-hover:scale-110">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <h3
                class="font-extrabold text-xl text-[#7F7F7F] mb-3 group-hover:text-[#A3ACA0] transition-colors duration-300">
                Tidak Tahu Harus Apa/Bagaimana</h3>
            <p class="text-[#A3ACA0] leading-relaxed text-sm font-medium">Pasien dan pendamping sering bingung dan panik
                terkait langkah spesifik apa yang harus dilakukan jika terjadi kondisi tertentu.</p>
        </div>
        <!-- Card 2 -->
        <div
            class="reveal-stagger-child opacity-0 translate-y-16 transition-all duration-700 bg-white/60 backdrop-blur-3xl border border-white p-10 rounded-3xl hover:-translate-y-4 delay-100 group shadow-[0_10px_40px_rgb(0,0,0,0.03)] focus:outline-none">
            <div
                class="text-[#BAC7B6] mb-6 flex justify-center transform transition-transform duration-500 group-hover:scale-110">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
            </div>
            <h3
                class="font-extrabold text-xl text-[#7F7F7F] mb-3 group-hover:text-[#A3ACA0] transition-colors duration-300">
                Sulit Membedakan Gejala Normal Vs Bahaya</h3>
            <p class="text-[#A3ACA0] leading-relaxed text-sm font-medium">Gejala kritis kadang terlihat seperti tidak
                berbahaya, dan sebaliknya. Perbedaan ini sering luput dari pengawasan orang awam.</p>
        </div>
        <!-- Card 3 -->
        <div
            class="reveal-stagger-child opacity-0 translate-y-16 transition-all duration-700 bg-white/60 backdrop-blur-3xl border border-white p-10 rounded-3xl hover:-translate-y-4 delay-200 group shadow-[0_10px_40px_rgb(0,0,0,0.03)] focus:outline-none">
            <div
                class="text-[#BAC7B6] mb-6 flex justify-center transform transition-transform duration-500 group-hover:scale-110">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                </div>
            </div>
            <h3
                class="font-extrabold text-xl text-[#7F7F7F] mb-3 group-hover:text-[#A3ACA0] transition-colors duration-300">
                Tidak Ada Panduan Harian Yang Jelas</h3>
            <p class="text-[#A3ACA0] leading-relaxed text-sm font-medium">Kurangnya petunjuk langkah demi langkah harian
                dari profesional medis untuk pemulihan optimal di rumah.</p>
        </div>
    </div>

    <!-- Wave Bridging to #FFFFFF (#services) -->
    <div class="wave-divider">
        <svg class="h-[60px] md:h-[120px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,120 L0,70 C200,120 400,0 600,50 C800,100 1000,0 1200,70 L1200,120 Z" fill="#FFFFFF"></path>
        </svg>
    </div>
</section>

<!-- Features Support Section -->
<section id="services"
    class="w-full min-h-screen flex flex-col justify-center relative px-8 py-32 z-10 bg-[#FFFFFF] overflow-hidden">
    <!-- Abstract Shapes replaced with surgical doodles -->
    <div class="absolute top-[10%] left-[10%] w-32 md:w-40 text-[#728BA9]/10 animate-floating pointer-events-none" style="transform: rotate(-20deg);">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 10l-4 4M10 10l4 4" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 14.5l6-6a5 5 0 017 7l-6 6a5 5 0 01-7-7z" />
        </svg>
    </div>
    <div class="absolute bottom-[20%] right-[15%] w-24 h-24 text-[#728BA9]/10 animate-floating pointer-events-none" style="animation-duration: 6s; transform: rotate(15deg);">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
        </svg>
    </div>

    <div
        class="bg-white/80 backdrop-blur-2xl border border-white mx-auto p-12 md:p-20 max-w-6xl text-center relative z-10 reveal-scale opacity-0 scale-95 transition-all duration-1000 mb-10 w-full rounded-[3rem] shadow-[0_20px_60px_rgba(0,0,0,0.04)]">
        <h2 class="text-4xl font-extrabold text-[#7F7F7F] mb-3">How Will <span class="text-[#728BA9]">Ruang<span
                    class="text-[#A3ACA0]">Pulih</span></span> Support You?</h2>
        <p class="text-[#A3ACA0] font-bold tracking-wider text-sm uppercase">Layanan Utama RuangPulih</p>
    </div>

    <div
        class="max-w-6xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 -mt-16 sm:-mt-24 relative z-10 px-8 reveal-stagger-parent">
        <div
            class="reveal-stagger-child opacity-0 translate-y-16 transition-all duration-700 ios-glass p-8 text-center hover:scale-105 transition-transform duration-300 shadow-[0_10px_40px_rgba(114,139,169,0.06)]">
            <div
                class="text-[#728BA9] mb-5 flex justify-center transform transition-transform duration-500 hover:-translate-y-2">
                <div class="bg-white/60 p-3 rounded-xl shadow-sm border border-white">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
            </div>
            <h4 class="font-extrabold text-lg text-[#728BA9] mb-2 leading-tight">Daily Recovery Roadmap</h4>
            <p class="text-sm text-[#7F7F7F] font-medium leading-relaxed">Panduan pemulihan harian sesuai prosedur
                operasi.</p>
        </div>
        <div
            class="reveal-stagger-child opacity-0 translate-y-16 transition-all duration-700 ios-glass p-8 text-center hover:scale-105 transition-transform duration-300 delay-100 shadow-[0_10px_40px_rgba(114,139,169,0.06)]">
            <div
                class="text-[#728BA9] mb-5 flex justify-center transform transition-transform duration-500 hover:-translate-y-2">
                <div class="bg-white/60 p-3 rounded-xl shadow-sm border border-white">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
            </div>
            <h4 class="font-extrabold text-lg text-[#728BA9] mb-2 leading-tight">Emergency Red Flag</h4>
            <p class="text-sm text-[#7F7F7F] font-medium leading-relaxed">Deteksi dini gejala kritis pasca bedah.</p>
        </div>
        <div
            class="reveal-stagger-child opacity-0 translate-y-16 transition-all duration-700 ios-glass p-8 text-center hover:scale-105 transition-transform duration-300 delay-200 shadow-[0_10px_40px_rgba(114,139,169,0.06)]">
            <div
                class="text-[#728BA9] mb-5 flex justify-center transform transition-transform duration-500 hover:-translate-y-2">
                <div class="bg-white/60 p-3 rounded-xl shadow-sm border border-white">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
            </div>
            <h4 class="font-extrabold text-lg text-[#728BA9] mb-2 leading-tight">Wound<br>Log</h4>
            <p class="text-sm text-[#7F7F7F] font-medium leading-relaxed">Pantau kondisi luka dan perkembangan jahitan.
            </p>
        </div>
        <div
            class="reveal-stagger-child opacity-0 translate-y-16 transition-all duration-700 ios-glass p-8 text-center hover:scale-105 transition-transform duration-300 delay-300 shadow-[0_10px_40px_rgba(114,139,169,0.06)]">
            <div
                class="text-[#728BA9] mb-5 flex justify-center transform transition-transform duration-500 hover:-translate-y-2">
                <div class="bg-white/60 p-3 rounded-xl shadow-sm border border-white">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">
                        </path>
                    </svg>
                </div>
            </div>
            <h4 class="font-extrabold text-lg text-[#728BA9] mb-2 leading-tight">Reliability Content Guide</h4>
            <p class="text-sm text-[#7F7F7F] font-medium leading-relaxed">Artikel edukasi valid dari tenaga profesional.
            </p>
        </div>
    </div>

    <!-- Wave Bridging to #F8FCFF (#how-it-works) -->
    <div class="wave-divider">
        <svg class="h-[50px] md:h-[100px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,120 L0,60 C250,120 350,0 600,40 C850,80 950,0 1200,60 L1200,120 Z" fill="#F8FCFF"></path>
        </svg>
    </div>
</section>

<!-- How it works steps -->
<section id="how-it-works"
    class="w-full min-h-screen flex flex-col justify-center py-32 px-8 relative z-10 bg-[#F8FCFF] overflow-hidden">
    <!-- Abstract Shapes replaced with surgical doodles -->
    <div class="absolute top-[5%] right-[10%] w-32 md:w-48 text-[#728BA9]/10 animate-floating pointer-events-none" style="transform: rotate(10deg);">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 2.5a1.5 1.5 0 00-3 0v4a5.5 5.5 0 0011 0v-4a1.5 1.5 0 00-3 0" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12.5 12v3a5.5 5.5 0 01-11 0v-2" />
            <circle cx="10" cy="18" r="4" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </div>
    <div class="absolute bottom-[10%] left-[10%] w-24 h-24 text-[#728BA9]/10 animate-floating pointer-events-none" style="animation-duration: 7s; transform: rotate(-15deg);">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14h6M9 10h6m-6 8h6" />
        </svg>
    </div>

    <div
        class="max-w-6xl mx-auto text-center mb-16 w-full reveal-up opacity-0 translate-y-16 transition-all duration-1000 relative z-10">
        <h2 class="text-4xl font-extrabold text-[#728BA9]">How Does <span class="text-[#A3ACA0]">RuangPulih</span> Work?
        </h2>
        <p class="text-lg text-[#7F7F7F] mt-4 font-bold">Langkah mudah menggunakan RuangPulih.</p>
    </div>

    <div
        class="max-w-6xl mx-auto flex flex-col md:flex-row items-center gap-16 md:gap-20 w-full reveal-stagger-parent relative z-10">
        <div class="w-full md:w-1/2 space-y-6 text-left md:pr-12">
            <div
                class="flex gap-6 group reveal-stagger-child opacity-0 -translate-x-16 transition-all duration-700 bg-white/80 backdrop-blur-md border border-white shadow-sm p-6 rounded-3xl hover:-translate-y-2">
                <div
                    class="flex-shrink-0 w-12 h-12 rounded-full bg-[#B8C9DD] text-white flex items-center justify-center text-xl font-bold group-hover:bg-[#728BA9] transition-all shadow-md group-hover:scale-110">
                    1</div>
                <div>
                    <h4 class="font-extrabold text-xl text-[#728BA9] mb-2 group-hover:text-[#A3ACA0] transition-colors">
                        Create Your Profile</h4>
                    <p class="text-[#7F7F7F] leading-relaxed font-medium">Lengkapi data rekam medis sederhana mengenai
                        riwayat penyakit, alergi, dan riwayat operasi sebelumnya untuk mendapatkan roadmap pemulihan
                        yang dipersonalisasi.</p>
                </div>
            </div>
            <div
                class="flex gap-6 group reveal-stagger-child opacity-0 -translate-x-16 transition-all duration-700 delay-100 bg-white/80 backdrop-blur-md border border-white shadow-sm p-6 rounded-3xl hover:-translate-y-2">
                <div
                    class="flex-shrink-0 w-12 h-12 rounded-full bg-[#B8C9DD] text-white flex items-center justify-center text-xl font-bold group-hover:bg-[#728BA9] transition-all shadow-md group-hover:scale-110">
                    2</div>
                <div>
                    <h4 class="font-extrabold text-xl text-[#728BA9] mb-2 group-hover:text-[#A3ACA0] transition-colors">
                        Get Roadmap</h4>
                    <p class="text-[#7F7F7F] leading-relaxed font-medium">Dapatkan panduan tahapan pemulihan setiap
                        harinya, mulai dari pantangan makanan, tingkat aktivitas fisik, hingga tips merawat luka secara
                        mandiri.</p>
                </div>
            </div>
            <div
                class="flex gap-6 group reveal-stagger-child opacity-0 -translate-x-16 transition-all duration-700 delay-200 bg-white/80 backdrop-blur-md border border-white shadow-sm p-6 rounded-3xl hover:-translate-y-2">
                <div
                    class="flex-shrink-0 w-12 h-12 rounded-full bg-[#B8C9DD] text-white flex items-center justify-center text-xl font-bold group-hover:bg-[#728BA9] transition-all shadow-md group-hover:scale-110">
                    3</div>
                <div>
                    <h4 class="font-extrabold text-xl text-[#728BA9] mb-2 group-hover:text-[#A3ACA0] transition-colors">
                        Monitor & Recover</h4>
                    <p class="text-[#7F7F7F] leading-relaxed font-medium">Ikuti instruksi harian dengan disiplin,
                        laporkan keluhan melalui fitur log, dan manfaatkan fitur konsultasi jika ada kondisi di luar
                        kendali.</p>
                </div>
            </div>
        </div>
        <div
            class="w-full md:w-1/2 flex justify-center relative mt-10 md:mt-0 reveal-right opacity-0 translate-x-16 transition-all duration-1000 delay-300">
            <div class="absolute inset-0 bg-[#FFFFFF] rounded-full blur-[80px] opacity-70 z-0 scale-90"></div>
            <img src="assets/images/logo.png" alt="Heart graphic"
                class="w-80 relative z-10 drop-shadow-xl hover:scale-105 transition-transform duration-500 animate-floating">
        </div>
    </div>

    <!-- Wave Bridging to #DAE3EC (#team) -->
    <div class="wave-divider">
        <svg class="h-[60px] md:h-[120px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,120 L0,40 C200,0 400,120 600,70 C800,20 1000,100 1200,40 L1200,120 Z" fill="#DAE3EC"></path>
        </svg>
    </div>
</section>

<!-- Team Section -->
<section id="team"
    class="w-full min-h-screen flex flex-col justify-center py-32 px-8 relative z-10 bg-[#DAE3EC] overflow-hidden">
    <!-- Abstract Shapes replaced with surgical doodles -->
    <div class="absolute top-[5%] right-[5%] w-32 md:w-48 text-[#FFFFFF]/25 animate-floating pointer-events-none" style="transform: rotate(20deg);">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 2l4 4M17 7l3-3M19 9L8.7 19.3c-1 1-2.5 1-3.4 0l-.6-.6c-1-1-1-2.5 0-3.4L15 5M9 11l4 4M5 19l-3 3M14 4l6 6" />
        </svg>
    </div>
    <div class="absolute bottom-[5%] left-[10%] w-24 h-24 text-[#FFFFFF]/30 animate-floating pointer-events-none" style="transform: rotate(-10deg);">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <circle cx="6" cy="6" r="3" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="6" cy="18" r="3" stroke-linecap="round" stroke-linejoin="round" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 4L8.12 15.88M14.47 14.48L20 20M8.12 8.12L12 12" />
        </svg>
    </div>

    <div class="max-w-6xl mx-auto text-center relative z-10 w-full">
        <h2 class="text-4xl font-extrabold text-[#728BA9] mb-16">Get To Know <span class="text-[#FFFFFF]">Our
                Team</span></h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 perspective-1000">
            <!-- Team 1 -->
            <div class="team-card group bg-white/70 backdrop-blur-2xl border border-white shadow-xl p-8 rounded-[2rem] text-center md:text-left transition-all duration-300 relative overflow-hidden z-10"
                style="transform-style: preserve-3d;">
                <!-- Subtle glare effect on hover -->
                <div
                    class="absolute inset-0 bg-gradient-to-tr from-white/0 via-white/80 to-white/0 opacity-0 group-hover:opacity-100 transition-all duration-700 pointer-events-none transform -translate-x-full group-hover:translate-x-full ease-in-out">
                </div>
                <div class="w-full aspect-square bg-[#F8FCFF]/50 rounded-2xl mb-6 flex items-center justify-center overflow-hidden transform-gpu backdrop-blur-md"
                    style="transform: translateZ(30px);">
                    <img src="assets/images/team-1.png" alt="Malfa Syakira Nauraliefia"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 mix-blend-multiply">
                </div>
                <div class="inline-block px-4 py-1.5 rounded-full bg-white text-[#728BA9] text-[10px] tracking-widest font-bold mb-4 shadow"
                    style="transform: translateZ(20px);">Lead Developer</div>
                <h4 class="font-extrabold text-xl text-[#728BA9] mb-1" style="transform: translateZ(25px);">Malfa
                    Syakira Nauraliefia</h4>
                <p class="text-[#7F7F7F] font-bold text-sm" style="transform: translateZ(15px);">5049231049</p>
            </div>
            <!-- Team 2 -->
            <div class="team-card group bg-white/70 backdrop-blur-2xl border border-white shadow-xl p-8 rounded-[2rem] text-center md:text-left transition-all duration-300 relative overflow-hidden z-10"
                style="transform-style: preserve-3d;">
                <!-- Subtle glare effect on hover -->
                <div
                    class="absolute inset-0 bg-gradient-to-tr from-white/0 via-white/80 to-white/0 opacity-0 group-hover:opacity-100 transition-all duration-700 pointer-events-none transform -translate-x-full group-hover:translate-x-full ease-in-out">
                </div>
                <div class="w-full aspect-square bg-[#F8FCFF]/50 rounded-2xl mb-6 flex items-center justify-center overflow-hidden transform-gpu backdrop-blur-md"
                    style="transform: translateZ(30px);">
                    <img src="assets/images/team-2.png" alt="Intan Fitri Hardyanti"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 mix-blend-multiply">
                </div>
                <div class="inline-block px-4 py-1.5 rounded-full bg-white text-[#728BA9] text-[10px] tracking-widest font-bold mb-4 shadow"
                    style="transform: translateZ(20px);">Lead Researcher</div>
                <h4 class="font-extrabold text-xl text-[#728BA9] mb-1" style="transform: translateZ(25px);">Intan Fitri
                    Hardyanti</h4>
                <p class="text-[#7F7F7F] font-bold text-sm" style="transform: translateZ(15px);">5049231051</p>
            </div>
            <!-- Team 3 -->
            <div class="team-card group bg-white/70 backdrop-blur-2xl border border-white shadow-xl p-8 rounded-[2rem] text-center md:text-left transition-all duration-300 relative overflow-hidden z-10"
                style="transform-style: preserve-3d;">
                <!-- Subtle glare effect on hover -->
                <div
                    class="absolute inset-0 bg-gradient-to-tr from-white/0 via-white/80 to-white/0 opacity-0 group-hover:opacity-100 transition-all duration-700 pointer-events-none transform -translate-x-full group-hover:translate-x-full ease-in-out">
                </div>
                <div class="w-full aspect-square bg-[#F8FCFF]/50 rounded-2xl mb-6 flex items-center justify-center overflow-hidden transform-gpu backdrop-blur-md"
                    style="transform: translateZ(30px);">
                    <img src="assets/images/team-3.png" alt="Alya Puti Larasati"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 mix-blend-multiply">
                </div>
                <div class="inline-block px-4 py-1.5 rounded-full bg-white text-[#728BA9] text-[10px] tracking-widest font-bold mb-4 shadow"
                    style="transform: translateZ(20px);">Lead Analyst</div>
                <h4 class="font-extrabold text-xl text-[#728BA9] mb-1" style="transform: translateZ(25px);">Alya Puti
                    Larasati</h4>
                <p class="text-[#7F7F7F] font-bold text-sm" style="transform: translateZ(15px);">5049231107</p>
            </div>
        </div>
    </div>

    <!-- Wave Bridging to Disclaimer Gradient (using reverse up wave pointing to team) -->
    <div class="wave-divider">
        <svg class="h-[50px] md:h-[100px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,120 L0,90 C300,120 400,20 600,50 C800,80 900,0 1200,90 L1200,120 Z" fill="#728BA9"></path>
        </svg>
    </div>
</section>

<!-- Standalone Disclaimer Section -->
<section
    class="w-full min-h-[50vh] flex flex-col items-center justify-center relative z-10 px-8 py-20 bg-gradient-to-b from-[#728BA9] to-[#BAC7B6] overflow-hidden">
    <!-- Abstract Shapes replaced with surgical doodles -->
    <div class="absolute top-[15%] left-[5%] w-32 md:w-40 text-white/10 animate-floating pointer-events-none" style="transform: rotate(-15deg);">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 2.5a1.5 1.5 0 00-3 0v4a5.5 5.5 0 0011 0v-4a1.5 1.5 0 00-3 0" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12.5 12v3a5.5 5.5 0 01-11 0v-2" />
            <circle cx="10" cy="18" r="4" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </div>
    <div class="absolute bottom-[10%] right-[10%] w-32 md:w-40 text-white/10 animate-floating pointer-events-none" style="transform: rotate(15deg);">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
        </svg>
    </div>

    <div class="max-w-4xl mx-auto flex flex-col justify-center items-center text-center relative z-10">
        <div
            class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full shadow-lg flex items-center justify-center mb-8 text-white border border-white/30 hover:scale-110 transition-transform animate-floating">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h3 class="text-4xl md:text-5xl font-extrabold text-white mb-8 leading-tight">Disclaimer For <span
                class="text-white/70">RuangPulih</span> Users</h3>
        <p class="text-white/90 text-xl leading-relaxed font-medium max-w-3xl">RuangPulih adalah platform panduan dan
            pendamping mandiri, dan tidak bisa mengganti diagnosis serta penanganan medis secara profesional.</p>
    </div>

    <!-- Wave Bridging to White CTA/Footer -->
    <div class="wave-divider">
        <svg class="h-[60px] md:h-[120px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,120 L0,30 C200,120 400,10 600,50 C800,90 1000,-10 1200,30 L1200,120 Z" fill="#F8FCFF"></path>
        </svg>
    </div>
</section>

<!-- Combined CTA and Footer Section (#contact) -->
<section id="contact" class="w-full flex flex-col items-center relative z-10 bg-[#FFFFFF] overflow-hidden">
    <!-- Top Half: CTA -->
    <div class="w-full py-32 px-8 md:px-16 flex flex-col items-center relative z-10">
        <div class="absolute inset-0 bg-gradient-to-t from-white to-[#F8FCFF] pointer-events-none -z-10"></div>

        <div
            class="max-w-6xl w-full flex flex-col md:flex-row items-center justify-between gap-12 relative z-10 bg-white/90 backdrop-blur-xl border border-white shadow-[0_20px_60px_rgba(0,0,0,0.06)] rounded-[3rem] p-12 md:p-16">
            <div class="text-center md:text-left">
                <h2 class="text-5xl md:text-6xl font-extrabold text-[#728BA9] leading-tight">Ready To Start<br><span
                        class="text-[#B8C9DD]">Your Recovery?</span></h2>
            </div>
            <div>
                <a href="onboarding.php"
                    class="inline-flex items-center gap-3 px-10 py-5 rounded-full bg-[#728BA9] text-white font-extrabold text-xl hover:bg-[#B8C9DD] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    Mulai Pemulihan
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Bottom Half: Footer -->
    <div class="w-full bg-white py-20 px-8 md:px-16 flex flex-col items-center border-t border-[#F8FCFF]">
        <div
            class="max-w-6xl w-full flex flex-col md:flex-row justify-center md:items-start gap-12 lg:gap-24 text-[#7F7F7F] mb-16">
            <!-- Brand Column -->
            <div class="space-y-6 w-full md:w-1/3 flex-shrink-0">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <img src="assets/images/logo.png" alt="RuangPulih Logo"
                        class="h-10 opacity-70 grayscale border border-[#BAC7B6] rounded-xl p-1">
                    <div class="flex flex-col">
                        <span class="font-extrabold text-[#B8C9DD] text-2xl tracking-tight leading-none">Ruang<span
                                class="text-[#A3ACA0]">Pulih</span></span>
                        <span class="text-[9px] text-[#A3ACA0] font-bold mt-1 uppercase tracking-wider">Pasca-Operasi &
                            Rehabilitasi</span>
                    </div>
                </div>
                <p class="text-xs font-medium text-[#7F7F7F] leading-relaxed">
                    Panduan rehabilitasi mandiri langkah demi langkah untuk mencegah komplikasi dan mempercepat
                    kesembuhan keluarga tercinta Anda.
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="font-extrabold text-[#728BA9] mb-6 text-lg whitespace-nowrap">Quick Links</h4>
                <ul class="space-y-4 font-semibold text-sm text-[#7F7F7F]">
                    <li><a href="#home" class="hover:text-[#A3ACA0] transition-colors">Home</a></li>
                    <li><a href="#about" class="hover:text-[#A3ACA0] transition-colors">About Us</a></li>
                    <li><a href="#services" class="hover:text-[#A3ACA0] transition-colors">Features</a></li>
                    <li><a href="#team" class="hover:text-[#A3ACA0] transition-colors">Our Team</a></li>
                </ul>
            </div>

            <!-- Hubungi Kami -->
            <div>
                <h4 class="font-extrabold text-[#728BA9] mb-6 text-lg whitespace-nowrap">Hubungi Kami</h4>
                <ul class="space-y-4 font-semibold text-sm text-[#7F7F7F]">
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-[#B8C9DD] flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>Institut Teknologi Sepuluh Nopember, Surabaya</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-[#B8C9DD] flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                            </path>
                        </svg>
                        <span>ruangpulih@gmail.com</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-[#B8C9DD] flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                            </path>
                        </svg>
                        <span>(+62) 1252830763</span>
                    </li>
                </ul>
            </div>

            <!-- Ikuti Kami -->
            <div>
                <h4 class="font-extrabold text-[#728BA9] mb-6 text-lg whitespace-nowrap">Ikuti Kami</h4>
                <div class="flex gap-4">
                    <a href="#"
                        class="w-10 h-10 rounded-xl bg-[#F8FCFF] shadow-sm flex items-center justify-center text-[#B8C9DD] hover:bg-[#728BA9] hover:text-white transition-all">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.20 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" />
                        </svg>
                    </a>
                    <a href="#"
                        class="w-10 h-10 rounded-xl bg-[#F8FCFF] shadow-sm flex items-center justify-center text-[#B8C9DD] hover:bg-[#728BA9] hover:text-white transition-all">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="text-[#A3ACA0] text-xs font-medium border-t border-[#F8FCFF] pt-8 w-full text-center max-w-6xl">
            <p>&copy; 2026 RuangPulih - All rights reserved</p>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const navLinks = document.querySelectorAll('#floating-nav a');

        // Create mapping of link href to section element
        const sections = Array.from(navLinks).map(link => {
            const id = link.getAttribute('href');
            if (id && id.startsWith('#')) return document.querySelector(id);
            return null;
        }).filter(Boolean);

        const updateNav = () => {
            let currentId = '';

            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                // Add a 200px offset to trigger color change slightly before hitting the exact top
                if (window.scrollY >= (sectionTop - 200)) {
                    currentId = '#' + section.getAttribute('id');
                }
            });

            // Fallback constraint: if we've scrolled fully to the bottom, force activate the absolute last section
            if ((window.innerHeight + Math.round(window.scrollY)) >= document.body.offsetHeight - 50) {
                currentId = '#' + sections[sections.length - 1].getAttribute('id');
            }

            // Apply styles based on active section
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentId) {
                    link.classList.add('text-[#728BA9]', 'font-bold');
                    link.classList.remove('text-[#7F7F7F]', 'font-medium');
                } else {
                    link.classList.add('text-[#7F7F7F]', 'font-medium');
                    link.classList.remove('text-[#728BA9]', 'font-bold');
                }
            });
        };

        // Listen for scroll events and also trigger once on load
        window.addEventListener('scroll', updateNav);
        updateNav();

        // =============== GENERAL SCROLL REVEAL (FOR ALL SECTIONS) ===============
        const scrollObserverOptions = { threshold: 0.1, rootMargin: '50px' };
        const scrollObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if (entry.target.classList.contains('reveal-stagger-parent')) {
                        const children = entry.target.querySelectorAll('.reveal-stagger-child');
                        children.forEach((child) => {
                            child.classList.remove('opacity-0', 'translate-y-16', '-translate-x-16', 'translate-x-16', 'scale-95');
                        });
                    } else {
                        entry.target.classList.remove('opacity-0', 'translate-y-16', '-translate-x-16', 'translate-x-16', 'scale-95');
                    }
                    observer.unobserve(entry.target);
                }
            });
        }, scrollObserverOptions);

        const revealElements = document.querySelectorAll('.reveal-up, .reveal-left, .reveal-right, .reveal-scale, .reveal-stagger-parent');
        revealElements.forEach(el => scrollObserver.observe(el));

        // =============== NEW FEATURE: INTERACTIVE TEAM CARDS ===============
        const teamCards = document.querySelectorAll('.team-card');

        // 1. Scroll Reveal Animation for Team Cards
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '50px'
        };

        const teamObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    // Determine order index to stagger the animation
                    // (Using index property from entries array might not work if they appear sequentially over separate events, 
                    // but since they are side by side, they usually intersect grouped together)
                    setTimeout(() => {
                        entry.target.classList.add('opacity-100', 'translate-y-0');
                        entry.target.classList.remove('opacity-0', 'translate-y-16');
                    }, index * 150);
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        teamCards.forEach((card, index) => {
            // Initial state for fade-up, using longer transition
            card.classList.add('opacity-0', 'translate-y-16', 'duration-700');
            teamObserver.observe(card);

            // 2. 3D Hover Tilt Effect
            let bounds;

            const rotateToMouse = (e) => {
                const mouseX = e.clientX;
                const mouseY = e.clientY;
                const leftX = mouseX - bounds.x;
                const topY = mouseY - bounds.y;
                const center = {
                    x: leftX - bounds.width / 2,
                    y: topY - bounds.height / 2
                };

                // Limit the maximum rotation degrees
                const maxTilt = 8;
                const tiltX = (center.y / (bounds.height / 2)) * -maxTilt;
                const tiltY = (center.x / (bounds.width / 2)) * maxTilt;

                card.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) scale3d(1.02, 1.02, 1.02)`;
            };

            card.addEventListener('mouseenter', () => {
                bounds = card.getBoundingClientRect();
                // remove duration tailwind class during hover to avoid laggy feeling while tracking mouse
                card.classList.remove('duration-300', 'duration-700');
                card.style.transition = 'none';
            });

            card.addEventListener('mousemove', rotateToMouse);

            card.addEventListener('mouseleave', () => {
                card.style.transition = 'transform 0.5s ease-out';
                card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale3d(1, 1, 1)';

                // restore standard duration class for other transitions
                setTimeout(() => {
                    card.classList.add('duration-300');
                    card.style.transition = ''; // clear inline style to let CSS Classes take over next time
                }, 500);
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>