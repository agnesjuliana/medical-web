<?php
/**
 * SIMRS-TB — Skrining AI Batuk
 * 
 * Upload/rekam suara batuk → analisis deep learning → hasil skrining
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'SIMRS-TB — Skrining AI Batuk';
$activePage = 'screening';

// ── Dummy Data: Riwayat Skrining ──
$riwayat = [
    ['id' => 'SCR-0045', 'nama' => 'Rina Wijaya',    'tanggal' => '2026-04-13 08:15', 'confidence' => 87.2, 'hasil' => 'Positif Indikasi',        'dirujuk' => true],
    ['id' => 'SCR-0044', 'nama' => 'Dedi Kurniawan', 'tanggal' => '2026-04-13 07:40', 'confidence' => 23.5, 'hasil' => 'Negatif Indikasi',        'dirujuk' => false],
    ['id' => 'SCR-0043', 'nama' => 'Fitriani',       'tanggal' => '2026-04-12 14:20', 'confidence' => 92.8, 'hasil' => 'Positif Indikasi',        'dirujuk' => true],
    ['id' => 'SCR-0042', 'nama' => 'Agus Supriyadi', 'tanggal' => '2026-04-12 11:05', 'confidence' => 45.1, 'hasil' => 'Tidak Dapat Ditentukan',  'dirujuk' => false],
    ['id' => 'SCR-0041', 'nama' => 'Lina Marlina',   'tanggal' => '2026-04-12 09:30', 'confidence' => 78.6, 'hasil' => 'Positif Indikasi',        'dirujuk' => true],
    ['id' => 'SCR-0040', 'nama' => 'Bambang Setiawan','tanggal' => '2026-04-11 15:45', 'confidence' => 12.3, 'hasil' => 'Negatif Indikasi',       'dirujuk' => false],
];
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>
<?php require_once __DIR__ . '/../../layout/navbar.php'; ?>
<?php require_once __DIR__ . '/_sidebar.php'; ?>

<main class="lg:ml-64 min-h-[calc(100vh-4rem)] bg-gray-50">
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-5">
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-teal-600 transition-colors">Module Hub</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="index.php" class="hover:text-teal-600 transition-colors">SIMRS-TB</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-700 font-medium">Skrining AI Batuk</span>
    </nav>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2"><svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>Skrining AI Batuk</h1>
        <p class="text-gray-500 text-sm mt-1">Analisis akustik suara batuk menggunakan Deep Learning untuk deteksi dini TB</p>
    </div>

    <!-- Screening Area -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
        <!-- Upload / Record -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-800">Input Suara Batuk</h3>
                <p class="text-xs text-gray-400 mt-0.5">Rekam atau upload file audio batuk pasien</p>
            </div>
            <div class="p-5 space-y-5">
                <!-- Record Button -->
                <div class="text-center">
                    <button id="btnRecord" onclick="toggleRecording()" 
                            class="w-28 h-28 rounded-full bg-gradient-to-br from-teal-500 to-emerald-600 text-white shadow-lg shadow-teal-500/30 hover:shadow-xl hover:shadow-teal-500/40 hover:scale-105 active:scale-95 transition-all duration-300 flex items-center justify-center mx-auto relative">
                        <svg id="micIcon" class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                        </svg>
                        <svg id="stopIcon" class="w-10 h-10 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                        </svg>
                        <div id="pulseRing" class="absolute inset-0 rounded-full border-4 border-teal-400 opacity-0"></div>
                    </button>
                    <p id="recordLabel" class="text-sm text-gray-500 mt-3">Klik untuk mulai merekam</p>
                    <p id="recordTimer" class="text-2xl font-mono font-bold text-teal-600 mt-1 hidden">00:00</p>
                </div>

                <!-- Waveform Visualization -->
                <div id="waveformContainer" class="hidden">
                    <div class="bg-slate-900 rounded-xl p-4 h-20 flex items-center justify-center gap-[3px] overflow-hidden" id="waveform">
                        <!-- Bars generated by JS -->
                    </div>
                </div>

                <!-- Divider -->
                <div class="flex items-center gap-3">
                    <div class="h-px flex-1 bg-gray-200"></div>
                    <span class="text-xs text-gray-400 font-medium">ATAU</span>
                    <div class="h-px flex-1 bg-gray-200"></div>
                </div>

                <!-- Upload -->
                <div id="dropZone" class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-teal-400 hover:bg-teal-50/30 transition-all cursor-pointer"
                     onclick="document.getElementById('audioFile').click()">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-sm text-gray-500">Drag & drop file audio atau <span class="text-teal-600 font-medium">browse</span></p>
                    <p class="text-xs text-gray-400 mt-1">WAV, MP3, OGG — Maks 10MB</p>
                    <input type="file" id="audioFile" accept="audio/*" class="hidden">
                </div>

                <!-- Analyze Button -->
                <div class="text-center">
                    <?= component_button('Analisis Suara Batuk', [
                        'variant' => 'primary',
                        'size' => 'lg',
                        'fullWidth' => true,
                        'class' => '!bg-gradient-to-r !from-teal-600 !to-emerald-600 hover:!from-teal-700 hover:!to-emerald-700 !shadow-teal-500/20',
                        'onclick' => 'simulateAnalysis()'
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Result Panel -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-800">Hasil Analisis AI</h3>
                <p class="text-xs text-gray-400 mt-0.5">Confidence score & rekomendasi</p>
            </div>
            
            <!-- Before Analysis -->
            <div id="resultEmpty" class="p-5 flex flex-col items-center justify-center min-h-[400px] text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-9 h-9 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-400">Menunggu input suara batuk...</p>
                <p class="text-xs text-gray-300 mt-1">Rekam atau upload file untuk memulai analisis</p>
            </div>

            <!-- Analysis Progress (hidden by default) -->
            <div id="resultProgress" class="p-5 hidden flex flex-col items-center justify-center min-h-[400px]">
                <div class="relative w-32 h-32 mb-6">
                    <svg class="w-32 h-32 transform -rotate-90 animate-spin-slow" viewBox="0 0 120 120">
                        <circle class="text-gray-200" stroke="currentColor" stroke-width="8" fill="none" r="52" cx="60" cy="60"/>
                        <circle class="text-teal-500" stroke="currentColor" stroke-width="8" fill="none" r="52" cx="60" cy="60" 
                                stroke-dasharray="326.7" stroke-dashoffset="326.7" stroke-linecap="round" id="progressCircle"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-xl font-bold text-gray-800" id="progressPercent">0%</span>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-600" id="progressLabel">Memproses audio...</p>
                <div class="flex items-center gap-2 mt-3 text-xs text-gray-400">
                    <div class="w-1.5 h-1.5 bg-teal-500 rounded-full animate-pulse"></div>
                    <span>Deep Learning Model aktif</span>
                </div>
            </div>

            <!-- Analysis Result (hidden by default) -->
            <div id="resultDone" class="p-5 hidden">
                <div class="text-center mb-6">
                    <!-- Gauge -->
                    <div class="relative w-40 h-20 mx-auto overflow-hidden mb-2">
                        <div class="absolute bottom-0 left-0 right-0">
                            <svg viewBox="0 0 200 100" class="w-full">
                                <path d="M20 90 A70 70 0 0 1 180 90" fill="none" stroke="#e5e7eb" stroke-width="14" stroke-linecap="round"/>
                                <path d="M20 90 A70 70 0 0 1 180 90" fill="none" stroke="url(#gaugeGrad)" stroke-width="14" stroke-linecap="round"
                                      stroke-dasharray="220" stroke-dashoffset="220" id="gaugeArc"/>
                                <defs>
                                    <linearGradient id="gaugeGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" stop-color="#34d399"/>
                                        <stop offset="50%" stop-color="#fbbf24"/>
                                        <stop offset="100%" stop-color="#ef4444"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-800" id="resultScore">87.2%</p>
                    <p class="text-xs text-gray-400 mt-0.5">Confidence Score</p>
                </div>

                <div class="bg-red-50 border border-red-100 rounded-xl p-4 mb-4" id="resultBadge">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-semibold text-red-700" id="resultLabel">Positif Indikasi TB</span>
                    </div>
                    <p class="text-xs text-red-600" id="resultDesc">Model mendeteksi pola akustik yang konsisten dengan batuk TB. Disarankan untuk pemeriksaan lebih lanjut.</p>
                </div>

                <div class="space-y-3 mb-5">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Durasi Audio</span>
                        <span class="font-medium text-gray-800">4.2 detik</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Frekuensi Dominan</span>
                        <span class="font-medium text-gray-800">380 Hz</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Pola Terdeteksi</span>
                        <span class="font-medium text-gray-800">Batuk produktif</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Model</span>
                        <span class="font-medium text-gray-800">CoughNet v3.2</span>
                    </div>
                </div>

                <div class="flex gap-2">
                    <?= component_button('Rujuk ke Dokter', [
                        'variant' => 'primary',
                        'fullWidth' => true,
                        'class' => '!bg-teal-600 hover:!bg-teal-700',
                        'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>'
                    ]) ?>
                    <?= component_button('Simpan Hasil', [
                        'variant' => 'outline',
                        'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Skrining -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-800 flex items-center gap-2"><svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>Riwayat Skrining</h3>
                <p class="text-xs text-gray-400 mt-0.5">Data skrining batuk terbaru</p>
            </div>
            <div class="flex items-center gap-2">
                <?= component_input('search_screening', ['placeholder' => 'Cari pasien...', 'class' => 'w-48']) ?>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pasien</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Confidence</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hasil</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rujukan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($riwayat as $r): 
                        $hasilColor = match($r['hasil']) {
                            'Positif Indikasi' => 'error',
                            'Negatif Indikasi' => 'success',
                            default => 'warning'
                        };
                        $confColor = $r['confidence'] > 70 ? 'text-red-500' : ($r['confidence'] > 40 ? 'text-amber-500' : 'text-green-500');
                    ?>
                    <tr class="hover:bg-teal-50/30 transition-colors">
                        <td class="px-5 py-3 text-gray-400 font-mono text-xs"><?= $r['id'] ?></td>
                        <td class="px-5 py-3">
                            <span class="font-medium text-gray-800"><?= $r['nama'] ?></span>
                        </td>
                        <td class="px-5 py-3 text-gray-500"><?= date('d M Y H:i', strtotime($r['tanggal'])) ?></td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full <?= $r['confidence'] > 70 ? 'bg-red-400' : ($r['confidence'] > 40 ? 'bg-amber-400' : 'bg-green-400') ?>" style="width: <?= $r['confidence'] ?>%"></div>
                                </div>
                                <span class="text-xs font-semibold <?= $confColor ?>"><?= $r['confidence'] ?>%</span>
                            </div>
                        </td>
                        <td class="px-5 py-3"><?= component_badge($r['hasil'], $hasilColor) ?></td>
                        <td class="px-5 py-3">
                            <?php if ($r['dirujuk']): ?>
                                <?= component_badge('Dirujuk', 'primary', ['icon' => '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>']) ?>
                            <?php else: ?>
                                <span class="text-xs text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</main>

<style>
@keyframes spin-slow { from { transform: rotate(-90deg); } to { transform: rotate(270deg); } }
.animate-spin-slow { animation: spin-slow 2s linear infinite; }
@keyframes pulse-ring { 0% { transform: scale(1); opacity: 0.6; } 100% { transform: scale(1.4); opacity: 0; } }
.recording .animate-pulse-ring { animation: pulse-ring 1.2s ease-out infinite; }
</style>

<script>
let isRecording = false;
let timerInterval = null;
let seconds = 0;

function toggleRecording() {
    isRecording = !isRecording;
    const btn = document.getElementById('btnRecord');
    const mic = document.getElementById('micIcon');
    const stop = document.getElementById('stopIcon');
    const label = document.getElementById('recordLabel');
    const timer = document.getElementById('recordTimer');
    const wave = document.getElementById('waveformContainer');
    const pulse = document.getElementById('pulseRing');

    if (isRecording) {
        mic.classList.add('hidden');
        stop.classList.remove('hidden');
        btn.classList.add('!from-red-500', '!to-red-600', '!shadow-red-500/30');
        btn.classList.remove('from-teal-500', 'to-emerald-600', 'shadow-teal-500/30');
        label.textContent = 'Merekam... Klik untuk berhenti';
        label.classList.add('text-red-500');
        timer.classList.remove('hidden');
        wave.classList.remove('hidden');
        pulse.style.animation = 'pulse-ring 1.2s ease-out infinite';
        
        seconds = 0;
        timerInterval = setInterval(() => {
            seconds++;
            const m = String(Math.floor(seconds / 60)).padStart(2, '0');
            const s = String(seconds % 60).padStart(2, '0');
            timer.textContent = m + ':' + s;
        }, 1000);
        
        generateWaveform();
    } else {
        mic.classList.remove('hidden');
        stop.classList.add('hidden');
        btn.classList.remove('!from-red-500', '!to-red-600', '!shadow-red-500/30');
        btn.classList.add('from-teal-500', 'to-emerald-600', 'shadow-teal-500/30');
        label.textContent = 'Rekaman selesai (' + timer.textContent + ')';
        label.classList.remove('text-red-500');
        label.classList.add('text-teal-600');
        pulse.style.animation = 'none';
        
        clearInterval(timerInterval);
        clearInterval(window.waveInterval);
    }
}

function generateWaveform() {
    const wf = document.getElementById('waveform');
    wf.innerHTML = '';
    for (let i = 0; i < 60; i++) {
        const bar = document.createElement('div');
        bar.className = 'wf-bar';
        bar.style.cssText = 'width:3px;border-radius:2px;background:linear-gradient(to top,#14b8a6,#34d399);transition:height 0.1s;';
        bar.style.height = '4px';
        wf.appendChild(bar);
    }
    window.waveInterval = setInterval(() => {
        document.querySelectorAll('.wf-bar').forEach(bar => {
            bar.style.height = (4 + Math.random() * 55) + 'px';
        });
    }, 100);
}

function simulateAnalysis() {
    document.getElementById('resultEmpty').classList.add('hidden');
    document.getElementById('resultDone').classList.add('hidden');
    document.getElementById('resultProgress').classList.remove('hidden');
    
    const circle = document.getElementById('progressCircle');
    const percentEl = document.getElementById('progressPercent');
    const labelEl = document.getElementById('progressLabel');
    const total = 326.7;
    const steps = ['Memproses audio...', 'Ekstraksi fitur MFCC...', 'Analisis spektrum...', 'Inferensi model CNN...', 'Menghitung confidence...'];
    let progress = 0;
    
    const interval = setInterval(() => {
        progress += Math.random() * 8 + 2;
        if (progress > 100) progress = 100;
        circle.style.strokeDashoffset = total - (total * progress / 100);
        percentEl.textContent = Math.round(progress) + '%';
        labelEl.textContent = steps[Math.min(Math.floor(progress / 25), steps.length - 1)];
        
        if (progress >= 100) {
            clearInterval(interval);
            setTimeout(() => {
                document.getElementById('resultProgress').classList.add('hidden');
                document.getElementById('resultDone').classList.remove('hidden');
                // Animate gauge
                const gauge = document.getElementById('gaugeArc');
                const score = 87.2;
                setTimeout(() => {
                    gauge.style.transition = 'stroke-dashoffset 1.5s ease-out';
                    gauge.style.strokeDashoffset = 220 - (220 * score / 100);
                }, 100);
            }, 500);
        }
    }, 200);
}
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
