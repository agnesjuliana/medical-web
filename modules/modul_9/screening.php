<?php
/**
 * SIMRS-TB — Skrining AI Batuk
 * 
 * Create/Read: Riwayat skrining dari tb_screenings + simpan hasil
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
require_once __DIR__ . '/../../config/database.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'SIMRS-TB — Skrining AI';
$activePage = 'screening';

$db = getDBConnection();

// Riwayat skrining dari DB
$riwayat = $db->query("SELECT s.*, p.nama as pasien_nama 
    FROM tb_screenings s 
    LEFT JOIN tb_patients p ON s.id_pasien = p.id 
    ORDER BY s.created_at DESC LIMIT 10")->fetchAll();

// Pasien list
$pasienList = $db->query("SELECT id, nama, no_rm FROM tb_patients WHERE status = 'Aktif' ORDER BY nama")->fetchAll();

// Stats
$totalSkrining = $db->query("SELECT COUNT(*) FROM tb_screenings")->fetchColumn() ?: 0;
$terdeteksi = $db->query("SELECT COUNT(*) FROM tb_screenings WHERE hasil = 'Positif Indikasi'")->fetchColumn() ?: 0;
$skriningHariIni = $db->query("SELECT COUNT(*) FROM tb_screenings WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0;
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>
<?php require_once __DIR__ . '/../../layout/navbar.php'; ?>
<?php require_once __DIR__ . '/_sidebar.php'; ?>

<main class="lg:ml-64 min-h-[calc(100vh-4rem)] bg-gray-50">
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-5">
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-teal-600 transition-colors">Module Hub</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="index.php" class="hover:text-teal-600 transition-colors">SIMRS-TB</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-700 font-medium">Skrining AI Batuk</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2"><svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>Skrining AI Batuk</h1>
            <p class="text-gray-500 text-sm mt-1">Deteksi dini TB melalui analisis suara batuk berbasis AI</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500">Total Skrining</p>
            <p class="text-2xl font-bold text-gray-800 mt-1"><?= $totalSkrining ?></p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500">Terdeteksi TB</p>
            <p class="text-2xl font-bold text-amber-600 mt-1"><?= $terdeteksi ?></p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500">Skrining Hari Ini</p>
            <p class="text-2xl font-bold text-teal-600 mt-1"><?= $skriningHariIni ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
        <!-- Skrining Baru -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-800">Skrining Baru</h3>
                <p class="text-xs text-gray-400 mt-0.5">Simulasi analisis AI</p>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Pasien (opsional)</label>
                    <select id="scr_pasien" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        <option value="">Non-pasien / Walk-in</option>
                        <?php foreach ($pasienList as $pl): ?><option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['nama']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <!-- Audio Simulation -->
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-teal-400 hover:bg-teal-50/30 transition-all cursor-pointer" id="recordArea" onclick="startRecording()">
                    <div class="w-14 h-14 bg-gradient-to-br from-teal-100 to-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg id="micIcon" class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                    </div>
                    <p class="text-sm font-medium text-gray-700" id="recordText">Klik untuk mulai merekam</p>
                    <p class="text-xs text-gray-400 mt-1">Atau simulasi rekaman untuk demo</p>
                </div>
                <!-- Waveform -->
                <div id="waveSection" class="hidden">
                    <div class="flex items-center gap-1 h-12 justify-center">
                        <?php for ($i = 0; $i < 30; $i++): ?>
                        <div class="wf-bar w-1 bg-teal-400 rounded-full transition-all duration-300" style="height: 8px;"></div>
                        <?php endfor; ?>
                    </div>
                </div>
                <button onclick="analyzeRecording()" id="analyzeBtn" class="w-full py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-sm font-medium shadow-sm shadow-teal-500/20 transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    Analisis Suara Batuk
                </button>
                <!-- Result -->
                <div id="resultSection" class="hidden">
                    <div class="bg-gradient-to-r from-teal-50 to-emerald-50 rounded-xl p-5 border border-teal-100">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-800">Hasil Analisis</h4>
                            <span id="resultBadge" class="text-xs font-medium px-2.5 py-1 rounded-full bg-green-50 text-green-700 border border-green-100">—</span>
                        </div>
                        <div id="resultDetails" class="space-y-2 text-sm text-gray-600"></div>
                        <div class="flex gap-2 mt-4">
                            <button onclick="saveScreening()" class="flex-1 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-sm font-medium shadow-sm transition-all">Simpan Hasil</button>
                            <button onclick="saveScreening(true)" class="flex-1 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-medium shadow-sm transition-all">Rujuk ke Dokter</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat -->
        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-800">Riwayat Skrining</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pasien</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Confidence</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hasil</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rujuk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($riwayat)): ?>
                        <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">Belum ada riwayat skrining</td></tr>
                        <?php else: ?>
                        <?php foreach ($riwayat as $r): 
                            $hColor = match($r['hasil']) {
                                'Positif Indikasi' => 'error',
                                'Tidak Dapat Ditentukan' => 'warning',
                                default => 'success'
                            };
                        ?>
                        <tr class="hover:bg-teal-50/30 transition-colors">
                            <td class="px-5 py-3 text-gray-500"><?= date('d M Y H:i', strtotime($r['created_at'])) ?></td>
                            <td class="px-5 py-3 font-medium text-gray-800"><?= $r['pasien_nama'] ?: 'Walk-in' ?></td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden"><div class="h-full bg-teal-400 rounded-full" style="width:<?= $r['confidence_score'] ?>%"></div></div>
                                    <span class="text-xs text-gray-500"><?= $r['confidence_score'] ?>%</span>
                                </div>
                            </td>
                            <td class="px-5 py-3"><?= component_badge($r['hasil'], $hColor) ?></td>
                            <td class="px-5 py-3"><?= $r['dirujuk'] ? component_badge('Ya', 'warning') : component_badge('Tidak', 'default') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</main>

<script>
let isRecording = false;
let analyzeResult = null;
let waveInterval = null;

function startRecording() {
    if (isRecording) return;
    isRecording = true;
    document.getElementById('recordText').textContent = 'Merekam... (simulasi 3 detik)';
    document.getElementById('micIcon').classList.add('text-red-500');
    document.getElementById('micIcon').classList.remove('text-teal-600');
    document.getElementById('waveSection').classList.remove('hidden');
    
    // Animate waveform
    waveInterval = setInterval(() => {
        document.querySelectorAll('.wf-bar').forEach(bar => {
            bar.style.height = (Math.random() * 32 + 8) + 'px';
        });
    }, 100);

    setTimeout(() => {
        clearInterval(waveInterval);
        isRecording = false;
        document.getElementById('recordText').textContent = 'Rekaman selesai ✓';
        document.getElementById('micIcon').classList.remove('text-red-500');
        document.getElementById('micIcon').classList.add('text-emerald-600');
        document.getElementById('analyzeBtn').disabled = false;
        document.querySelectorAll('.wf-bar').forEach(bar => bar.style.height = '8px');
    }, 3000);
}

function analyzeRecording() {
    const btn = document.getElementById('analyzeBtn');
    btn.textContent = 'Menganalisis...';
    btn.disabled = true;

    // Simulate AI analysis
    setTimeout(() => {
        const confidence = Math.floor(Math.random() * 40 + 55); // 55-95
        const hasil = confidence > 80 ? 'Positif Indikasi' : (confidence > 65 ? 'Tidak Dapat Ditentukan' : 'Negatif Indikasi');
        const badgeColor = confidence > 80 ? 'bg-red-50 text-red-700 border-red-100' : (confidence > 65 ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-green-50 text-green-700 border-green-100');

        analyzeResult = { confidence, hasil };

        document.getElementById('resultBadge').className = 'text-xs font-medium px-2.5 py-1 rounded-full border ' + badgeColor;
        document.getElementById('resultBadge').textContent = hasil;
        document.getElementById('resultDetails').innerHTML = `
            <p><span class="text-gray-400">Confidence Score:</span> <strong>${confidence}%</strong></p>
            <p><span class="text-gray-400">Durasi Audio:</span> <strong>3.2 detik</strong></p>
            <p><span class="text-gray-400">Frekuensi Batuk:</span> <strong>${Math.floor(Math.random()*5+2)} kali</strong></p>
        `;
        document.getElementById('resultSection').classList.remove('hidden');
        btn.textContent = 'Analisis Suara Batuk';
    }, 2000);
}

function saveScreening(rujuk = false) {
    if (!analyzeResult) return;
    const formData = new FormData();
    formData.append('action', 'create_screening');
    formData.append('id_pasien', document.getElementById('scr_pasien').value);
    formData.append('confidence_score', analyzeResult.confidence);
    formData.append('hasil', analyzeResult.hasil);
    formData.append('durasi_detik', 3.2);
    formData.append('dirujuk', rujuk ? 1 : 0);
    formData.append('catatan', rujuk ? 'Dirujuk ke dokter untuk pemeriksaan lanjutan' : '');

    fetch('api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        });
}
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
