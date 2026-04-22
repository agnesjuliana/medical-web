<?php
/**
 * SIMRS-TB — Analitik & SITB
 * 
 * Read analytics dari database: statistik, chart, SITB sync logs
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
require_once __DIR__ . '/../../config/database.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'SIMRS-TB — Analitik & SITB';
$activePage = 'analitik';

$db = getDBConnection();

// SITB Sync Logs from DB
$syncLogs = $db->query("SELECT * FROM tb_sitb_sync_logs ORDER BY tanggal_sync DESC LIMIT 10")->fetchAll();

// Statistik kasus
$kasusBaru = $db->query("SELECT COUNT(*) FROM tb_patients WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn() ?: 0;
$kasusAktif = $db->query("SELECT COUNT(*) FROM tb_patients WHERE status = 'Aktif'")->fetchColumn() ?: 0;
$sembuh = $db->query("SELECT COUNT(*) FROM tb_patients WHERE status = 'Sembuh'")->fetchColumn() ?: 0;
$meninggal = $db->query("SELECT COUNT(*) FROM tb_patients WHERE status = 'Meninggal'")->fetchColumn() ?: 0;

// Distribusi per kecamatan (from alamat)
$wilayah = $db->query("SELECT 
    CASE 
        WHEN alamat LIKE '%Kec. A%' THEN 'Kec. A'
        WHEN alamat LIKE '%Kec. B%' THEN 'Kec. B'
        WHEN alamat LIKE '%Kec. C%' THEN 'Kec. C'
        ELSE 'Lainnya'
    END as wilayah,
    COUNT(*) as total
    FROM tb_patients WHERE status = 'Aktif'
    GROUP BY wilayah")->fetchAll();

$wilayahLabels = array_map(fn($r) => $r['wilayah'], $wilayah);
$wilayahValues = array_map(fn($r) => (int)$r['total'], $wilayah);

if (empty($wilayahLabels)) {
    $wilayahLabels = ['Belum ada data'];
    $wilayahValues = [0];
}

// Treatment outcome from DB
$outcomes = [
    ['label' => 'Sembuh', 'value' => $sembuh, 'color' => '#34d399'],
    ['label' => 'Aktif', 'value' => $kasusAktif, 'color' => '#60a5fa'],
    ['label' => 'Meninggal', 'value' => $meninggal, 'color' => '#f87171'],
];
$outcomeLabels = array_map(fn($o) => $o['label'], $outcomes);
$outcomeValues = array_map(fn($o) => $o['value'], $outcomes);
$outcomeColors = array_map(fn($o) => $o['color'], $outcomes);

// Monthly trend (6 months)
$trendData = $db->query("SELECT 
    DATE_FORMAT(created_at, '%b %Y') as bulan,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Sembuh' THEN 1 ELSE 0 END) as sembuh,
    SUM(CASE WHEN status = 'Aktif' THEN 1 ELSE 0 END) as aktif
    FROM tb_patients 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
    ORDER BY MIN(created_at)")->fetchAll();

$trendMonths = array_map(fn($r) => $r['bulan'], $trendData);
$trendTotal = array_map(fn($r) => (int)$r['total'], $trendData);
$trendSembuh = array_map(fn($r) => (int)$r['sembuh'], $trendData);

if (empty($trendMonths)) {
    $trendMonths = [date('M Y')];
    $trendTotal = [0];
    $trendSembuh = [0];
}

// Last sync info
$lastSync = !empty($syncLogs) ? $syncLogs[0] : null;
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>
<?php require_once __DIR__ . '/../../layout/navbar.php'; ?>
<?php require_once __DIR__ . '/_sidebar.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<main class="lg:ml-64 min-h-[calc(100vh-4rem)] bg-gray-50">
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-5">
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-teal-600 transition-colors">Module Hub</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="index.php" class="hover:text-teal-600 transition-colors">SIMRS-TB</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-700 font-medium">Analitik & SITB</span>
    </nav>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2"><svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>Analitik & SITB</h1>
        <p class="text-gray-500 text-sm mt-1">Statistik TB, tren kasus, dan sinkronisasi SITB Nasional</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"><p class="text-sm text-gray-500">Kasus Baru (Bulan Ini)</p><p class="text-2xl font-bold text-gray-800 mt-1"><?= $kasusBaru ?></p></div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"><p class="text-sm text-gray-500">Kasus Aktif</p><p class="text-2xl font-bold text-blue-600 mt-1"><?= $kasusAktif ?></p></div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"><p class="text-sm text-gray-500">Sembuh</p><p class="text-2xl font-bold text-emerald-600 mt-1"><?= $sembuh ?></p></div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"><p class="text-sm text-gray-500">Meninggal</p><p class="text-2xl font-bold text-red-500 mt-1"><?= $meninggal ?></p></div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-base font-semibold text-gray-800 mb-1">Tren Kasus (6 Bulan)</h3>
            <p class="text-xs text-gray-400 mb-4">Kasus baru vs sembuh</p>
            <div style="position:relative;height:250px;"><canvas id="trendChart"></canvas></div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-base font-semibold text-gray-800 mb-1">Treatment Outcome</h3>
            <p class="text-xs text-gray-400 mb-4">Distribusi status akhir</p>
            <div style="position:relative;height:250px;"><canvas id="outcomeChart"></canvas></div>
        </div>
    </div>

    <!-- SITB Sync -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-800 flex items-center gap-2"><svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Sinkronisasi SITB</h3>
                <p class="text-xs text-gray-400 mt-0.5">Riwayat sinkronisasi data ke Sistem Informasi TB Nasional</p>
            </div>
            <div class="flex items-center gap-3">
                <?php if ($lastSync): ?>
                <span class="text-xs text-gray-400">Terakhir: <?= date('d M Y H:i', strtotime($lastSync['tanggal_sync'])) ?></span>
                <?php endif; ?>
                <button onclick="simulateSync()" id="syncBtn" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl text-xs font-medium transition-colors">
                    <svg class="w-4 h-4" id="syncIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Sinkronkan
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Records</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Response</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($syncLogs)): ?>
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">Belum ada riwayat sinkronisasi</td></tr>
                    <?php else: ?>
                    <?php foreach ($syncLogs as $sl):
                        $sColor = match($sl['status']) { 'Berhasil' => 'success', 'Gagal' => 'error', default => 'warning' };
                    ?>
                    <tr class="hover:bg-teal-50/30 transition-colors">
                        <td class="px-5 py-3 text-gray-500"><?= date('d M Y H:i', strtotime($sl['tanggal_sync'])) ?></td>
                        <td class="px-5 py-3 text-gray-700"><?= $sl['jenis_data'] ?></td>
                        <td class="px-5 py-3 font-semibold text-gray-800"><?= $sl['jumlah_record'] ?></td>
                        <td class="px-5 py-3"><?= component_badge($sl['status'], $sColor) ?></td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-400"><?= $sl['response_code'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</main>

<script>
// Trend Chart
new Chart(document.getElementById('trendChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($trendMonths) ?>,
        datasets: [{
            label: 'Kasus Baru', data: <?= json_encode($trendTotal) ?>,
            backgroundColor: 'rgba(20,184,166,0.6)', borderRadius: 8, barPercentage: 0.5
        },{
            label: 'Sembuh', data: <?= json_encode($trendSembuh) ?>,
            backgroundColor: 'rgba(52,211,153,0.6)', borderRadius: 8, barPercentage: 0.5
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'bottom', labels: { font: { size: 11, family: 'Inter' } } } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
});

// Outcome Chart
new Chart(document.getElementById('outcomeChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($outcomeLabels) ?>,
        datasets: [{ data: <?= json_encode($outcomeValues) ?>, backgroundColor: <?= json_encode($outcomeColors) ?>, borderWidth: 0, spacing: 3, borderRadius: 6 }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { font: { size: 11, family: 'Inter' } } } } }
});

function simulateSync() {
    const btn = document.getElementById('syncBtn');
    const icon = document.getElementById('syncIcon');
    btn.disabled = true;
    icon.classList.add('animate-spin');
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Sinkronisasi...';
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Berhasil!';
        setTimeout(() => location.reload(), 2000);
    }, 3000);
}
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
