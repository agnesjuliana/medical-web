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
    body, body * {
        font-family: 'Poppins', sans-serif !important;
    }
</style>

<!-- Top Header -->
<div class="w-full bg-white pt-10 pb-6 relative z-40">
    <header class="max-w-6xl mx-auto px-8 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <img src="assets/images/logo.png" alt="RuangPulih Logo" class="h-12 opacity-80">
            <div class="flex flex-col justify-center translate-y-0.5">
                <span class="font-extrabold text-3xl tracking-tight leading-none"><span class="text-[#A3ACA0]">Ruang</span><span class="text-[#B8C9DD]">Pulih</span></span>
                <span class="text-[10px] text-[#CDCDCD] font-bold tracking-widest mt-1.5 uppercase">Pasca-Operasi & Rehabilitasi Mandiri</span>
            </div>
        </div>
        <div>
            <a href="#" class="px-8 py-3 rounded-full border border-[#B8C9DD] text-[#7F7F7F] hover:bg-[#F8FCFF] transition-all font-bold text-xs tracking-wider uppercase shadow-sm">Dashboard</a>
        </div>
    </header>
</div>

<!-- Global Floating Nav -->
<div class="w-full flex justify-center sticky top-4 z-50 pointer-events-none -mb-12 pt-8">
    <nav id="floating-nav" class="bg-white rounded-2xl shadow-[0_10px_40px_rgb(0,0,0,0.06)] px-12 py-5 flex items-center justify-center gap-10 font-medium text-sm border border-white pointer-events-auto">
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
<section id="home" class="bg-[#F8FCFF] relative w-full min-h-[calc(100vh-140px)] flex flex-col justify-center px-8 pb-20 pt-28 z-10 overflow-hidden">
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center relative gap-8">
        <div class="w-full md:w-1/2 space-y-8 text-center md:text-left">
            <h1 class="text-4xl md:text-5xl font-extrabold text-[#728BA9] leading-tight drop-shadow-sm">
                Post-Op Recovery.<br>Safe And Monitored<br>From Home.
            </h1>
            <p class="text-[#7F7F7F] max-w-md mx-auto md:mx-0 text-lg font-medium leading-relaxed">
                Panduan rehabilitasi pasca-operasi mandiri langkah demi langkah untuk mencegah komplikasi dan mempercepat kesembuhan keluarga tercinta Anda.
            </p>
            <a href="#about" class="inline-block px-8 py-3.5 rounded-full bg-[#B8C9DD] text-white font-bold text-lg hover:bg-[#728BA9] transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                Pelajari Lebih Lanjut
            </a>
        </div>
        <div class="w-full md:w-1/2 flex justify-center mt-12 md:mt-0">
            <img src="assets/images/logo.png" alt="Hero Illustration" class="max-w-lg w-full drop-shadow-xl hover:scale-105 transition-transform duration-500">
        </div>
    </div>
</section>

<!-- About Us Section -->
<section id="about" class="bg-[#ECF2E6] w-full min-h-screen flex flex-col justify-center py-24 px-8 relative">
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center gap-16">
        <div class="w-full md:w-1/3 flex flex-col items-center md:items-start text-center md:text-left space-y-6">
            <h2 class="text-4xl font-extrabold text-[#A3ACA0]">Why Do We <br><span class="text-[#728BA9]">Exist?</span></h2>
            <img src="assets/images/logo.png" alt="Heart Icon" class="w-48 mt-4 hover:scale-110 transition-transform duration-500">
        </div>
        <div class="w-full md:w-2/3 border-l-4 border-[#B8C9DD] pl-8 py-6 relative">
            <div class="absolute -top-4 left-4 text-[#BAC7B6] opacity-30 text-7xl font-serif">"</div>
            <p class="text-[#7F7F7F] text-xl leading-relaxed italic relative z-10 font-medium">
                Untuk memberikan ruang aman dan langkah pemulihan yang mudah diakses bagi pasien dan keluarga. Karena setelah pulang dari RS, pasien sering merasa panik, kebingungan, dan khawatir saat menghadapi masa pemulihan tanpa pendampingan intensif dari tenaga medis. Kami hadir untuk menenangkan kecemasan akibat keterbatasan tenaga medis dengan pemantauan recovery yang lebih terstruktur. Kami membawa perawat di rumah, memandu dan membantu pemulihan agar pasien merasa lebih aman di masa transisi yang sulit.
            </p>
        </div>
    </div>
</section>

<!-- Risky Section -->
<section class="bg-white w-full min-h-screen flex flex-col justify-center py-24 px-8 text-center" id="problem">
    <h2 class="text-4xl font-extrabold text-[#7F7F7F] mb-3">Why Is <span class="text-[#728BA9]">Home Recovery</span> Risky?</h2>
    <p class="text-[#CDCDCD] font-bold mb-16 uppercase tracking-widest text-sm">Menurut Beberapa Studi Empiris</p>
    
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-10">
        <!-- Card 1 -->
        <div class="bg-white shadow-sm rounded-2xl p-10 border border-[#F8FCFF] hover:-translate-y-2 transition-transform duration-300">
            <div class="text-[#728BA9] mb-6 flex justify-center">
                <div class="w-20 h-20 bg-[#F8FCFF] rounded-full flex items-center justify-center shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <h3 class="font-extrabold text-xl text-[#728BA9] mb-3">Tidak Tahu Harus Apa/Bagaimana</h3>
            <p class="text-[#7F7F7F] leading-relaxed text-sm font-medium">Pasien dan pendamping sering bingung dan panik terkait langkah spesifik apa yang harus dilakukan jika terjadi kondisi tertentu.</p>
        </div>
        <!-- Card 2 -->
        <div class="bg-white shadow-sm rounded-2xl p-10 border border-[#F8FCFF] hover:-translate-y-2 transition-transform duration-300">
            <div class="text-[#728BA9] mb-6 flex justify-center">
                <div class="w-20 h-20 bg-[#F8FCFF] rounded-full flex items-center justify-center shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
            <h3 class="font-extrabold text-xl text-[#728BA9] mb-3">Sulit Membedakan Gejala Normal Vs Bahaya</h3>
            <p class="text-[#7F7F7F] leading-relaxed text-sm font-medium">Gejala kritis kadang terlihat seperti tidak berbahaya, dan sebaliknya. Perbedaan ini sering luput dari pengawasan orang awam.</p>
        </div>
        <!-- Card 3 -->
        <div class="bg-white shadow-sm rounded-2xl p-10 border border-[#F8FCFF] hover:-translate-y-2 transition-transform duration-300">
            <div class="text-[#728BA9] mb-6 flex justify-center">
                <div class="w-20 h-20 bg-[#F8FCFF] rounded-full flex items-center justify-center shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
            </div>
            <h3 class="font-extrabold text-xl text-[#728BA9] mb-3">Tidak Ada Panduan Harian Yang Jelas</h3>
            <p class="text-[#7F7F7F] leading-relaxed text-sm font-medium">Kurangnya petunjuk langkah demi langkah harian dari profesional medis untuk pemulihan optimal di rumah.</p>
        </div>
    </div>
</section>

<!-- Features Support Section -->
<section id="services" class="w-full min-h-screen flex flex-col justify-center relative px-8 py-20">
    <div class="bg-[#ECF2E6] mx-auto rounded-[2.5rem] max-w-6xl text-center py-20 px-8 relative z-0 shadow-sm border border-[#D1D9CA]">
        <h2 class="text-4xl font-extrabold text-[#7F7F7F] mb-3">How Will <span class="text-[#728BA9]">Ruang<span class="text-[#A3ACA0]">Pulih</span></span> Support You?</h2>
        <p class="text-[#A3ACA0] font-bold tracking-wider text-sm uppercase">Layanan Utama RuangPulih</p>
    </div>
    
    <div class="max-w-6xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 -mt-10 relative z-10 px-8">
        <div class="bg-white shadow-sm rounded-2xl p-8 border border-gray-100/50 text-center hover:scale-105 transition-transform duration-300">
            <div class="text-[#728BA9] mb-5 flex justify-center">
                <div class="bg-[#F8FCFF] p-3 rounded-xl shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
            <h4 class="font-extrabold text-lg text-[#728BA9] mb-2 leading-tight">Daily Recovery Roadmap</h4>
            <p class="text-sm text-[#7F7F7F] font-medium leading-relaxed">Panduan pemulihan harian sesuai prosedur operasi.</p>
        </div>
        <div class="bg-white shadow-sm rounded-2xl p-8 border border-gray-100/50 text-center hover:scale-105 transition-transform duration-300">
            <div class="text-[#728BA9] mb-5 flex justify-center">
                <div class="bg-[#F8FCFF] p-3 rounded-xl shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
            <h4 class="font-extrabold text-lg text-[#728BA9] mb-2 leading-tight">Emergency Red Flag</h4>
            <p class="text-sm text-[#7F7F7F] font-medium leading-relaxed">Deteksi dini gejala kritis pasca bedah.</p>
        </div>
        <div class="bg-white shadow-sm rounded-2xl p-8 border border-gray-100/50 text-center hover:scale-105 transition-transform duration-300">
            <div class="text-[#728BA9] mb-5 flex justify-center">
                <div class="bg-[#F8FCFF] p-3 rounded-xl shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
            <h4 class="font-extrabold text-lg text-[#728BA9] mb-2 leading-tight">Wound<br>Log</h4>
            <p class="text-sm text-[#7F7F7F] font-medium leading-relaxed">Pantau kondisi luka dan perkembangan jahitan.</p>
        </div>
        <div class="bg-white shadow-sm rounded-2xl p-8 border border-gray-100/50 text-center hover:scale-105 transition-transform duration-300">
            <div class="text-[#728BA9] mb-5 flex justify-center">
                <div class="bg-[#F8FCFF] p-3 rounded-xl shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                </div>
            </div>
            <h4 class="font-extrabold text-lg text-[#728BA9] mb-2 leading-tight">Reliability Content Guide</h4>
            <p class="text-sm text-[#7F7F7F] font-medium leading-relaxed">Artikel edukasi valid dari tenaga profesional.</p>
        </div>
    </div>
</section>

<!-- How it works steps -->
<section id="how-it-works" class="w-full min-h-screen flex flex-col justify-center py-20 px-8 relative">
    <div class="max-w-6xl mx-auto text-center mb-16 w-full">
        <h2 class="text-4xl font-extrabold text-[#7F7F7F]">How Does <span class="text-[#728BA9]">Ruang<span class="text-[#A3ACA0]">Pulih</span></span> Work?</h2>
        <p class="text-lg text-[#CDCDCD] mt-4 font-bold">Langkah mudah menggunakan RuangPulih.</p>
    </div>
    
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center gap-16 md:gap-20 w-full">
        <div class="w-full md:w-1/2 space-y-8 text-left md:pr-12">
            <div class="flex gap-6 group">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-[#B8C9DD] text-white flex items-center justify-center text-xl font-bold group-hover:bg-[#728BA9] transition-colors shadow-md">1</div>
                <div>
                    <h4 class="font-extrabold text-xl text-[#728BA9] mb-2">Create Your Profile</h4>
                    <p class="text-[#7F7F7F] leading-relaxed font-medium">Lengkapi data rekam medis sederhana mengenai riwayat penyakit, alergi, dan riwayat operasi sebelumnya untuk mendapatkan roadmap pemulihan yang dipersonalisasi.</p>
                </div>
            </div>
            <div class="flex gap-6 group">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-[#B8C9DD] text-white flex items-center justify-center text-xl font-bold group-hover:bg-[#728BA9] transition-colors shadow-md">2</div>
                <div>
                    <h4 class="font-extrabold text-xl text-[#728BA9] mb-2">Get Roadmap</h4>
                    <p class="text-[#7F7F7F] leading-relaxed font-medium">Dapatkan panduan tahapan pemulihan setiap harinya, mulai dari pantangan makanan, tingkat aktivitas fisik, hingga tips merawat luka secara mandiri.</p>
                </div>
            </div>
            <div class="flex gap-6 group">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-[#B8C9DD] text-white flex items-center justify-center text-xl font-bold group-hover:bg-[#728BA9] transition-colors shadow-md">3</div>
                <div>
                    <h4 class="font-extrabold text-xl text-[#728BA9] mb-2">Monitor & Recover</h4>
                    <p class="text-[#7F7F7F] leading-relaxed font-medium">Ikuti instruksi harian dengan disiplin, laporkan keluhan melalui fitur log, dan manfaatkan fitur konsultasi jika ada kondisi di luar kendali.</p>
                </div>
            </div>
        </div>
    <div class="w-full md:w-1/2 flex justify-center relative mt-10 md:mt-0">
        <div class="absolute inset-0 bg-[#F8FCFF] rounded-full blur-[80px] opacity-70 z-0 scale-90"></div>
        <img src="assets/images/logo.png" alt="Heart graphic" class="w-80 relative z-10 drop-shadow-xl hover:scale-105 transition-transform duration-500">
    </div>
    </div>
</section>

<!-- Team Section -->
<section id="team" class="bg-[#F8FCFF] w-full min-h-screen flex flex-col justify-center py-28 px-8 mt-12">
    <div class="max-w-6xl mx-auto text-center">
        <h2 class="text-4xl font-extrabold text-[#7F7F7F] mb-16">Get To Know <span class="text-[#728BA9]">Our Team</span></h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <!-- Team 1 -->
            <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 text-center md:text-left border border-white hover:-translate-y-2 transition-transform duration-300">
                <div class="w-full aspect-square bg-[#DAE3EC] rounded-2xl mb-6 flex items-center justify-center overflow-hidden">
                    <img src="assets/images/team-1.png" alt="Malfa Syakira Nauraliefia" class="w-full h-full object-cover">
                </div>
                <div class="inline-block px-4 py-1.5 rounded-full bg-[#ECF2E6] text-[#728BA9] text-[10px] tracking-widest font-bold mb-4 border border-[#D1D9CA]">Lead Developer</div>
                <h4 class="font-extrabold text-xl text-[#728BA9] mb-1">Malfa Syakira Nauraliefia</h4>
                <p class="text-[#CDCDCD] font-bold text-sm">5049231049</p>
            </div>
            <!-- Team 2 -->
            <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 text-center md:text-left border border-white hover:-translate-y-2 transition-transform duration-300">
                <div class="w-full aspect-square bg-[#DAE3EC] rounded-2xl mb-6 flex items-center justify-center overflow-hidden">
                    <img src="assets/images/team-2.png" alt="Intan Fitri Hardyanti" class="w-full h-full object-cover">
                </div>
                <div class="inline-block px-4 py-1.5 rounded-full bg-[#ECF2E6] text-[#728BA9] text-[10px] tracking-widest font-bold mb-4 border border-[#D1D9CA]">Lead Researcher</div>
                <h4 class="font-extrabold text-xl text-[#728BA9] mb-1">Intan Fitri Hardyanti</h4>
                <p class="text-[#CDCDCD] font-bold text-sm">5049231051</p>
            </div>
            <!-- Team 3 -->
            <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 text-center md:text-left border border-white hover:-translate-y-2 transition-transform duration-300">
                <div class="w-full aspect-square bg-[#DAE3EC] rounded-2xl mb-6 flex items-center justify-center overflow-hidden">
                    <img src="assets/images/team-3.png" alt="Alya Puti Larasati" class="w-full h-full object-cover">
                </div>
                <div class="inline-block px-4 py-1.5 rounded-full bg-[#ECF2E6] text-[#728BA9] text-[10px] tracking-widest font-bold mb-4 border border-[#D1D9CA]">Lead Analyst</div>
                <h4 class="font-extrabold text-xl text-[#728BA9] mb-1">Alya Puti Larasati</h4>
                <p class="text-[#CDCDCD] font-bold text-sm">5049231107</p>
            </div>
        </div>
    </div>
</section>

<!-- Standalone Disclaimer Section -->
<section class="w-full min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-white to-[#F8FCFF] relative overflow-hidden px-8">
    <!-- Abstract blurred shapes for an interesting background -->
    <div class="absolute -left-20 -bottom-20 w-[400px] h-[400px] bg-[#D1D9CA] rounded-full opacity-30 blur-[80px] pointer-events-none"></div>
    <div class="absolute top-10 right-10 w-[300px] h-[300px] bg-[#B8C9DD] rounded-full opacity-20 blur-[80px] pointer-events-none"></div>

    <div class="max-w-4xl mx-auto flex flex-col justify-center items-center text-center relative z-10">
        <div class="w-20 h-20 bg-white rounded-full shadow-md flex items-center justify-center mb-8 text-[#A3ACA0]">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <h3 class="text-4xl md:text-5xl font-extrabold text-[#7F7F7F] mb-8 leading-tight">Disclaimer For <span class="text-[#A3ACA0]">Ruang</span><span class="text-[#728BA9]">Pulih</span> Users</h3>
        <p class="text-[#A3ACA0] text-xl leading-relaxed font-semibold max-w-3xl">RuangPulih adalah platform panduan dan pendamping mandiri, dan tidak bisa mengganti diagnosis serta penanganan medis secara profesional.</p>
    </div>
</section>

<!-- Combined CTA and Footer Section (#contact) -->
<section id="contact" class="w-full flex flex-col items-center border-t border-white">
    <!-- Top Half: CTA -->
    <div class="w-full bg-[#ECF2E6] py-32 px-8 md:px-16 flex flex-col items-center relative overflow-hidden">
        <div class="absolute -left-32 -bottom-32 w-[600px] h-[600px] border-[40px] border-[#D1D9CA] rounded-full opacity-10"></div>
        <div class="absolute -right-32 -top-32 w-[500px] h-[500px] bg-[#BAC7B6] rounded-full opacity-20 blur-[80px]"></div>
        
        <div class="max-w-6xl w-full flex flex-col md:flex-row items-center justify-between gap-12 relative z-10">
            <div class="text-center md:text-left">
                <h2 class="text-5xl md:text-6xl font-extrabold text-[#7F7F7F] leading-tight">Ready To Start<br><span class="text-[#B8C9DD]">Your Recovery?</span></h2>
            </div>
            <div>
                <a href="#" class="inline-flex items-center gap-3 px-10 py-5 rounded-full bg-[#B8C9DD] text-white font-extrabold text-xl hover:bg-[#728BA9] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    Open Dashboard
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Bottom Half: Footer -->
    <div class="w-full bg-[#F8FCFF] py-20 px-8 md:px-16 flex flex-col items-center">
        <div class="max-w-6xl w-full flex flex-col md:flex-row justify-center md:items-start gap-12 lg:gap-24 text-[#7F7F7F] mb-16">
            <!-- Brand Column -->
            <div class="space-y-6 w-full md:w-1/3 flex-shrink-0">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <img src="assets/images/logo.png" alt="RuangPulih Logo" class="h-10 opacity-70 grayscale border border-[#BAC7B6] rounded-lg p-1">
                    <div class="flex flex-col">
                        <span class="font-extrabold text-[#B8C9DD] text-2xl tracking-tight leading-none">Ruang<span class="text-[#A3ACA0]">Pulih</span></span>
                        <span class="text-[9px] text-[#A3ACA0] font-bold mt-1 uppercase tracking-wider">Pasca-Operasi & Rehabilitasi Mandiri</span>
                    </div>
                </div>
                <p class="text-xs font-semibold text-[#A3ACA0] leading-relaxed">
                    Panduan rehabilitasi mandiri langkah demi langkah untuk mencegah komplikasi dan mempercepat kesembuhan keluarga tercinta Anda.
                </p>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h4 class="font-extrabold text-[#7F7F7F] mb-6 text-lg whitespace-nowrap">Quick Links</h4>
                <ul class="space-y-4 font-semibold text-sm text-[#A3ACA0]">
                    <li><a href="#home" class="hover:text-[#728BA9] transition-colors">Home</a></li>
                    <li><a href="#about" class="hover:text-[#728BA9] transition-colors">About Us</a></li>
                    <li><a href="#services" class="hover:text-[#728BA9] transition-colors">Features</a></li>
                    <li><a href="#team" class="hover:text-[#728BA9] transition-colors">Our Team</a></li>
                </ul>
            </div>
            
            <!-- Hubungi Kami -->
            <div>
                <h4 class="font-extrabold text-[#7F7F7F] mb-6 text-lg whitespace-nowrap">Hubungi Kami</h4>
                <ul class="space-y-4 font-semibold text-sm text-[#A3ACA0]">
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-[#BAC7B6] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>Institut Teknologi Sepuluh Nopember, Surabaya</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-[#BAC7B6] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span>ruangpulih@gmail.com</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-[#BAC7B6] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        <span>(+62) 1252830763</span>
                    </li>
                </ul>
            </div>
            
            <!-- Ikuti Kami -->
            <div>
                <h4 class="font-extrabold text-[#7F7F7F] mb-6 text-lg whitespace-nowrap">Ikuti Kami</h4>
                <div class="flex gap-4">
                    <a href="#" class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-[#BAC7B6] hover:bg-[#BAC7B6] hover:text-white transition-all">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.20 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-[#BAC7B6] hover:bg-[#BAC7B6] hover:text-white transition-all">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="text-[#A3ACA0] text-xs font-semibold border-t border-[#DAE3EC] pt-8 w-full text-center max-w-6xl">
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
});
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
