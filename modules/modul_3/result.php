<?php
/**
 * Modul 3 — Result Page
 */
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
requireLogin();
startSession();

// Cek apakah ada data hasil analisis di session
if (!isset($_SESSION['modul3_result'])) {
    header('Location: ' . BASE_URL . '/modules/modul_3/index.php');
    exit;
}

$result = $_SESSION['modul3_result'];
$user = getCurrentUser();
$pageTitle = 'Hasil Deteksi';
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>
<?php require_once __DIR__ . '/../../layout/navbar.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-6">
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-cyan-600 transition-colors">Module Hub</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="<?= BASE_URL ?>/modules/modul_3/index.php" class="hover:text-cyan-600 transition-colors">Modul 3</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-700 font-medium">Hasil Analisis AI</span>
    </nav>

    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Laporan Deteksi</h1>
            <p class="text-gray-500 mt-1">Hasil ekstraksi fitur klasifikasi menggunakan Model Machine Learning.</p>
        </div>
        <?= component_button('Pindai Citra Lain', [
            'href' => BASE_URL . '/modules/modul_3/index.php',
            'variant' => 'primary',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>'
        ]) ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Kolom Kiri: Citra -->
        <div class="lg:col-span-1">
            <div class="bg-slate-900 rounded-3xl overflow-hidden shadow-2xl border border-slate-700">
                <div class="px-5 py-3 border-b border-white/10 flex justify-between items-center text-slate-300">
                    <span class="text-xs uppercase tracking-wider font-semibold">Tinjauan Gambar</span>
                    <span class="text-xs truncate max-w-[120px]" title="<?= htmlspecialchars($result['filename']) ?>"><?= htmlspecialchars($result['filename']) ?></span>
                </div>
                <div class="relative w-full aspect-[4/5] bg-black flex items-center justify-center p-2">
                    <img src="<?= BASE_URL ?>/modules/modul_3/uploads/<?= htmlspecialchars($result['filename']) ?>" class="max-w-full max-h-full object-contain rounded-xl" alt="Sinar-X yang Diunggah">
                    <!-- Efek Garis Scan Vertikal untuk Kosmetik -->
                    <div class="absolute inset-x-0 h-[200%] w-full top-[-50%] bg-gradient-to-b from-transparent via-cyan-500/10 to-transparent opacity-40 mix-blend-screen animate-[pulse_4s_ease-in-out_infinite]" style="background-size: 100% 6px; box-shadow: 0 0 40px rgba(6, 182, 212, 0.4)"></div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Hasil & Detail -->
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white rounded-3xl border <?= $result['border'] ?> shadow-lg overflow-hidden text-center relative p-12 hover:-translate-y-1 transition-transform duration-300">
                <div class="absolute top-0 inset-x-0 h-3 <?= $result['bg'] ?> border-b <?= $result['border'] ?>"></div>
                <div class="absolute inset-0 <?= $result['alertBg'] ?> opacity-20 pointer-events-none"></div>
                
                <h3 class="relative text-sm font-semibold text-gray-400 uppercase tracking-[0.2em] mb-3">Tingkat Keyakinan (Confidence)</h3>
                
                <div class="relative text-7xl md:text-8xl font-black text-gray-900 my-4 tracking-tighter flex items-center justify-center gap-1">
                    <?= $result['score'] ?>
                    <span class="text-4xl text-gray-300">%</span>
                </div>

                <div class="relative inline-flex items-center justify-center px-6 py-2 rounded-full <?= $result['alertBg'] ?> border <?= $result['border'] ?> my-4">
                    <h2 class="text-xl font-bold <?= $result['color'] ?> tracking-wide"><?= $result['status'] ?></h2>
                </div>
                
                <p class="relative mt-4 text-sm text-gray-500 max-w-md mx-auto leading-relaxed">
                    Probabilitas citra sinar-X dada tersebut memiliki fitur-fitur opasitas atau infiltrat yang mengindikasikan infeksi.
                </p>
                
                <!-- Progress bar indikator -->
                <div class="relative mt-10 w-full h-5 bg-gray-100 rounded-full overflow-hidden shadow-inner flex">
                    <div class="h-full <?= $result['bg'] ?> transition-all duration-[2000ms] ease-out flex items-center justify-end pr-2 shadow-[0_0_15px_rgba(0,0,0,0.2)]" style="width: 0%;" id="confidence-bar">
                        <div class="w-1.5 h-1.5 bg-white rounded-full opacity-60"></div>
                    </div>
                </div>
                <div class="relative flex justify-between mt-3 text-xs text-gray-400 font-bold px-1 uppercase tracking-wide">
                    <span>Aman (0%)</span>
                    <span>Kritis (100%)</span>
                </div>

                <script>
                    // Animasi Progress Bar
                    setTimeout(() => {
                        document.getElementById('confidence-bar').style.width = '<?= $result['score'] ?>%';
                    }, 300);
                </script>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Info Tambahan 1 -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h4 class="text-gray-800 font-bold mb-4 flex items-center gap-2">
                        <div class="p-1.5 bg-gray-100 rounded-lg text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                        Tindak Lanjut Medis
                    </h4>
                    <ul class="text-sm text-gray-600 space-y-3">
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-cyan-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span><?= ($result['score'] > 50) ? 'Bawa hasil rontgen asli dan diskusikan dengan <b>Dokter Spesialis Paru</b> secepatnya.' : 'Jaga kesehatan paru dengan tidak merokok dan jauhi asap rokok.' ?></span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-cyan-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span><?= ($result['score'] > 50) ? 'Disarankan mengambil tes dahak TCM / BTA di fasilitas kesehatan (Puskesmas/Klinik).' : 'Gunakan masker bila berada di dekat orang yang sedang sakit batuk-batuk kronis.' ?></span>
                        </li>
                    </ul>
                </div>
                
                <!-- Info Tambahan 2 -->
                <div class="bg-gray-50 rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col justify-center">
                    <h4 class="text-gray-800 font-bold mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Simulasi Model
                    </h4>
                    <p class="text-sm text-gray-500 leading-relaxed mb-4">
                        Analisis saat ini berjalan dalam mode <b>Simulasi (Mock Up)</b>. Nilai dikirim secara dummy untuk mendemonstrasikan antarmuka alur kerja (UI Workflow) pengguna sebelum digabungkan dengan skrip Python asli kelompok Anda.
                    </p>
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-cyan-100 text-cyan-700 text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span>
                            UI Ready
                        </span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
