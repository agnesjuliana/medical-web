<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'Dashboard RuangPulih';
$page = $_GET['page'] ?? 'home';
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
    body, body *, .font-sans {
        font-family: 'Poppins', sans-serif !important;
    }
    
    /* Simple Custom Checkbox via CSS since we aren't sure if tailwind forms plugin is loaded */
    input[type="checkbox"] {
        accent-color: #98b0c4;
    }
</style>

<div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
        <!-- Logo & Subtitle -->
        <div class="px-6 py-8">
            <a href="index.php" class="flex items-center gap-3">
                <img src="assets/images/logo.png" alt="RuangPulih Logo" class="h-8 opacity-80">
                <div>
                    <h1 class="text-xl font-bold text-[#b1c3ce] leading-none">Ruang<span class="text-[#98b0c4]">Pulih</span></h1>
                    <p class="text-[0.6rem] text-gray-400 mt-1 uppercase tracking-tight">Pasca-Operasi & Rehabilitasi Mandiri</p>
                </div>
            </a>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-4 space-y-1 flex flex-col py-4">
            <a href="dashboard.php?page=home" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $page === 'home' ? 'bg-[#e2e8f0] text-[#5A6C7A] font-bold' : 'text-gray-500 hover:bg-gray-50' ?>">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Home
            </a>

            <a href="dashboard.php?page=roadmap" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $page === 'roadmap' ? 'bg-[#e2e8f0] text-[#5A6C7A] font-bold' : 'text-gray-500 hover:bg-gray-50' ?>">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"></path>
                </svg>
                Recovery Roadmap
            </a>

            <a href="dashboard.php?page=monitoring" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $page === 'monitoring' ? 'bg-[#e2e8f0] text-[#5A6C7A] font-bold' : 'text-gray-500 hover:bg-gray-50' ?>">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"></path>
                </svg>
                Monitoring
            </a>

            <a href="dashboard.php?page=content" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $page === 'content' ? 'bg-[#e2e8f0] text-[#5A6C7A] font-bold' : 'text-gray-500 hover:bg-gray-50' ?>">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"></path>
                </svg>
                Content Library
            </a>

            <!-- Exit Link pushed to bottom -->
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-[#728BA9] hover:bg-gray-50 rounded-lg transition-colors font-bold !mt-auto">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H9m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h6a3 3 0 013 3v1"></path>
                </svg>
                Exit
            </a>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 p-8">
        <?php if ($page === 'home'): ?>
            <!-- HOME VIEW -->
            <header>
                <h2 class="text-2xl font-bold text-gray-800">Selamat datang, Budi</h2>
                <p class="text-gray-500 mt-1">Hari ke-2 pasca operasi</p>
            </header>

            <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Recovery Hari Ini (Spans 2 cols) -->
                <div class="lg:col-span-2 bg-[#ECF2E6] rounded-2xl p-8 relative overflow-hidden flex flex-col justify-start min-h-[340px]">
                    <div class="absolute right-0 top-0 w-64 h-64 bg-[#D1D9CA] rounded-full opacity-40 -translate-y-1/4 translate-x-1/4"></div>
                    <div class="absolute right-20 top-20 w-80 h-80 bg-[#D1D9CA] rounded-full opacity-20"></div>
                    
                    <div class="relative z-10 w-full">
                        <h3 class="text-xl font-bold text-[#5A6C7A] mb-4">Recovery Hari Ini</h3>
                        
                        <div class="flex items-center bg-white rounded-full h-6 w-full max-w-sm mb-6 pr-4">
                            <div class="bg-gradient-to-r from-[#D1D9CA] to-[#B8C9DD] h-full rounded-full w-1/2"></div>
                            <span class="ml-auto text-[#728BA9] font-bold text-sm">50%</span>
                        </div>

                        <ul class="space-y-4">
                            <li class="flex items-center gap-4">
                                <div class="w-6 h-6 rounded bg-[#D1DFEC] text-white flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="text-[#5A6C7A] font-medium">Latihan pernapasan</span>
                            </li>
                            <li class="flex items-center gap-4">
                                <div class="w-6 h-6 rounded bg-[#D1DFEC] text-white flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="text-[#5A6C7A] font-medium">Jalan kaki 5 menit</span>
                            </li>
                            <li class="flex items-center gap-4">
                                <div class="w-6 h-6 rounded bg-white flex items-center justify-center flex-shrink-0"></div>
                                <span class="text-[#5A6C7A] font-medium">Minum obat</span>
                            </li>
                            <li class="flex items-center gap-4">
                                <div class="w-6 h-6 rounded bg-white flex items-center justify-center flex-shrink-0"></div>
                                <span class="text-[#5A6C7A] font-medium">Latihan ...</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Right Column: Status Terkini (Spans 1 col) -->
                <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] p-8 flex flex-col justify-start">
                    <h3 class="text-lg font-bold text-gray-700 mb-6">Status Terkini</h3>
                    
                    <div class="space-y-4">
                        <div class="bg-red-50 text-red-600 rounded-xl p-4 flex items-start gap-3 border border-red-100/50">
                            <span class="text-lg leading-none">⚠️</span>
                            <div class="text-sm font-medium">
                                <span class="font-bold">Luka:</span> Belum diperiksa hari ini
                            </div>
                        </div>
                        
                        <div class="bg-[#F8FCFF] text-[#728BA9] rounded-xl p-4 flex items-start gap-3 border border-[#E2E8F0]">
                            <span class="text-lg leading-none">📺</span>
                            <div class="text-sm font-medium">
                                <span class="font-bold">Fisioterapi:</span> 1 Video belum ditonton
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($page === 'roadmap'): ?>
            <!-- ROADMAP VIEW -->
            <header>
                <h2 class="text-2xl font-bold text-gray-800">Roadmap Pemulihan Anda</h2>
                <p class="text-[#728BA9] mt-1 font-medium">Protokol: Pasca Operasi Jantung (CABG) - Hari 1-3</p>
            </header>

            <div class="mt-8 space-y-6 max-w-4xl">
                
                <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] p-8">
                    <h3 class="text-xl font-bold text-[#5A6C7A] mb-4">Posisi Tidur</h3>
                    <p class="text-gray-800 text-lg font-semibold mb-4">Semi-Fowler 30–45°</p>
                    <div class="inline-flex items-center gap-3 bg-[#F8FCFF] text-[#728BA9] text-sm px-5 py-3 rounded-xl font-medium border border-[#E2E8F0]">
                        <span class="text-lg">ℹ️</span> 
                        <span><strong class="font-bold">Info Penting:</strong> Gunakan bantal untuk menyangga dada saat batuk.</span>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] p-8">
                    <h3 class="text-xl font-bold text-[#5A6C7A] mb-6">Latihan</h3>
                    <ul class="space-y-5">
                        <li class="flex items-start gap-4 p-4 hover:bg-gray-50 rounded-xl transition-colors border border-transparent hover:border-gray-100">
                            <input type="checkbox" id="lat-1" class="w-6 h-6 mt-1 cursor-pointer">
                            <label for="lat-1" class="cursor-pointer flex-1">
                                <span class="text-gray-800 font-bold block mb-1">Latihan Pernapasan (Deep Breathing)</span>
                                <span class="text-gray-500 text-sm block">Tarik napas dalam 3 detik, tahan 2 detik, hembuskan. 5–10 repetisi/jam.</span>
                            </label>
                        </li>
                        <li class="flex items-start gap-4 p-4 hover:bg-gray-50 rounded-xl transition-colors border border-transparent hover:border-gray-100">
                            <input type="checkbox" id="lat-2" class="w-6 h-6 mt-1 cursor-pointer">
                            <label for="lat-2" class="cursor-pointer flex-1">
                                <span class="text-gray-800 font-bold block mb-1">Batuk Efektif</span>
                                <span class="text-gray-500 text-sm block">2–3x tiap sesi (dengan menahan dada).</span>
                            </label>
                        </li>
                    </ul>
                </div>

                <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] p-8">
                    <h3 class="text-xl font-bold text-[#5A6C7A] mb-6">Aktivitas Fisik</h3>
                    <ul class="space-y-5">
                        <li class="flex items-start gap-4 p-4 hover:bg-gray-50 rounded-xl transition-colors border border-transparent hover:border-gray-100">
                            <input type="checkbox" id="akt-1" class="w-6 h-6 mt-1 cursor-pointer">
                            <label for="akt-1" class="cursor-pointer flex-1">
                                <span class="text-gray-800 font-bold block mb-1">Duduk di kursi</span>
                                <span class="text-gray-500 text-sm block">15–30 menit, 2–3x/hari.</span>
                            </label>
                        </li>
                        <li class="flex items-start gap-4 p-4 hover:bg-gray-50 rounded-xl transition-colors border border-transparent hover:border-gray-100">
                            <input type="checkbox" id="akt-2" class="w-6 h-6 mt-1 cursor-pointer">
                            <label for="akt-2" class="cursor-pointer flex-1">
                                <span class="text-gray-800 font-bold block mb-1">Jalan kaki dengan pendamping</span>
                                <span class="text-gray-500 text-sm block">5 menit, 2x/hari.</span>
                            </label>
                        </li>
                    </ul>
                </div>

                <!-- Card 4: Monitoring Tanda Vital -->
                <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] p-8">
                    <h3 class="text-xl font-bold text-[#5A6C7A] mb-6">Monitoring Mandiri</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Skala Nyeri (0-10)</label>
                            <input type="number" min="0" max="10" placeholder="Contoh: 3" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-[#98b0c4] focus:ring focus:ring-[#98b0c4]/20 transition-all outline-none text-gray-700">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Detak Jantung (60-100 bpm)</label>
                            <input type="number" placeholder="Contoh: 80" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-[#98b0c4] focus:ring focus:ring-[#98b0c4]/20 transition-all outline-none text-gray-700">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Saturasi Oksigen / SpO2 (%)</label>
                            <input type="number" placeholder="Contoh: 98" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-[#98b0c4] focus:ring focus:ring-[#98b0c4]/20 transition-all outline-none text-gray-700">
                        </div>
                    </div>

                    <div class="bg-red-50 text-red-700 p-4 rounded-xl font-medium border border-red-100 flex items-start gap-3">
                        <span class="text-xl leading-none">🚨</span>
                        <span><strong class="font-bold">Peringatan:</strong> Jika SpO2 Anda &lt; 92%, segera hentikan aktivitas dan hubungi dokter!</span>
                    </div>
                </div>

                <!-- Card 5: Larangan Keras -->
                <div class="bg-red-50/50 rounded-2xl p-8 border border-red-100">
                    <h3 class="text-xl font-bold text-red-700 mb-6 flex items-center gap-2">
                        <span class="text-2xl">🚫</span> Larangan Hari Ini
                    </h3>
                    <ul class="space-y-4">
                        <li class="flex items-center gap-3 text-red-800 font-medium">
                            <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0 font-bold">X</span>
                            Dilarang angkat beban &gt; 2-3 kg
                        </li>
                        <li class="flex items-center gap-3 text-red-800 font-medium">
                            <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0 font-bold">X</span>
                            Dilarang mengemudi
                        </li>
                        <li class="flex items-center gap-3 text-red-800 font-medium">
                            <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0 font-bold">X</span>
                            Dilarang mendorong/menarik benda berat
                        </li>
                    </ul>
                </div>

                <!-- Submit Action -->
                <div class="pt-4">
                    <button class="w-full bg-[#98b0c4] hover:bg-[#859eb3] text-white text-lg font-bold py-4 rounded-xl transition-colors shadow-sm">
                        Simpan Progres Hari Ini
                    </button>
                </div>
                
            </div>

        <?php elseif ($page === 'monitoring' || $page === 'content'): ?>
            <!-- OTHER PAGES PLACEHOLDER -->
            <header>
                <h2 class="text-2xl font-bold text-gray-800 capitalize"><?= htmlspecialchars($page) ?></h2>
                <p class="text-gray-500 mt-1">Halaman ini sedang dalam pengembangan.</p>
            </header>
            <div class="mt-8 bg-white rounded-2xl shadow-sm p-8 text-center py-20">
                <span class="text-5xl mb-4 block">🚧</span>
                <p class="text-[#728BA9] font-medium text-lg">Under Construction</p>
            </div>
            
        <?php else: ?>
            <header>
                <h2 class="text-2xl font-bold text-gray-800">404 Not Found</h2>
                <p class="text-gray-500 mt-1">Halaman tidak ditemukan.</p>
            </header>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
