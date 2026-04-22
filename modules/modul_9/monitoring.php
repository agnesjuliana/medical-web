<?php
/**
 * SIMRS-TB — Monitoring Kepatuhan
 * 
 * Read data kepatuhan dari database (tb_pmo_logs, tb_patients)
 * Heatmap, risiko drop-out, statistik
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
require_once __DIR__ . '/../../config/database.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'SIMRS-TB — Monitoring Kepatuhan';
$activePage = 'monitoring';

$db = getDBConnection();

// Get patients with compliance data
$stmt = $db->query("SELECT 
    p.id, p.nama, p.no_rm, p.fase_pengobatan,
    COUNT(l.id) as total_hari,
    SUM(CASE WHEN l.status_minum = 'Diminum' THEN 1 ELSE 0 END) as hari_patuh,
    ROUND(COALESCE(SUM(CASE WHEN l.status_minum = 'Diminum' THEN 1 ELSE 0 END) / NULLIF(COUNT(l.id),0) * 100, 0)) as kepatuhan
    FROM tb_patients p
    LEFT JOIN tb_pmo_logs l ON p.id = l.id_pasien AND l.tanggal >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    WHERE p.status = 'Aktif'
    GROUP BY p.id, p.nama, p.no_rm, p.fase_pengobatan
    ORDER BY kepatuhan ASC");
$patients = $stmt->fetchAll();

// Get heatmap for each patient
foreach ($patients as &$pat) {
    $hmStmt = $db->prepare("SELECT tanggal, status_minum FROM tb_pmo_logs 
        WHERE id_pasien = :id AND tanggal >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ORDER BY tanggal ASC");
    $hmStmt->execute(['id' => $pat['id']]);
    $logs = [];
    foreach ($hmStmt->fetchAll() as $row) {
        $logs[$row['tanggal']] = $row['status_minum'];
    }
    
    $heatmap = [];
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $heatmap[] = (isset($logs[$date]) && $logs[$date] === 'Diminum') ? 1 : 0;
    }
    $pat['heatmap'] = $heatmap;
    $pat['kepatuhan'] = (int)$pat['kepatuhan'];
    $pat['total_hari'] = (int)$pat['total_hari'];
    $pat['hari_patuh'] = (int)$pat['hari_patuh'];

    // Calculate streak
    $streak = 0;
    for ($i = count($heatmap) - 1; $i >= 0; $i--) {
        if ($heatmap[$i]) $streak++;
        else break;
    }
    $pat['streak'] = $streak;

    // Risk level
    $k = $pat['kepatuhan'];
    $pat['risiko'] = $k >= 80 ? 'Rendah' : ($k >= 65 ? 'Sedang' : ($k >= 50 ? 'Tinggi' : 'Kritis'));
}
unset($pat);

// Summary stats
$totalPatients = count($patients);
$avgKepatuhan = $totalPatients > 0 ? round(array_sum(array_column($patients, 'kepatuhan')) / $totalPatients, 1) : 0;
$patuh80 = count(array_filter($patients, fn($p) => $p['kepatuhan'] >= 80));
$risikoSedang = count(array_filter($patients, fn($p) => $p['risiko'] === 'Sedang'));
$risikoDropout = count(array_filter($patients, fn($p) => in_array($p['risiko'], ['Tinggi', 'Kritis'])));

$summaryStats = [
    ['label' => 'Rata-rata Kepatuhan', 'value' => $avgKepatuhan . '%', 'color' => 'from-teal-500 to-emerald-500'],
    ['label' => 'Pasien Patuh (>80%)',  'value' => $patuh80,           'color' => 'from-emerald-500 to-green-500'],
    ['label' => 'Risiko Sedang',        'value' => $risikoSedang,      'color' => 'from-amber-500 to-orange-500'],
    ['label' => 'Risiko Drop-out',      'value' => $risikoDropout,     'color' => 'from-rose-500 to-red-500'],
];
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
        <span class="text-gray-700 font-medium">Monitoring Kepatuhan</span>
    </nav>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2"><svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Monitoring Kepatuhan</h1>
        <p class="text-gray-500 text-sm mt-1">Pantau kepatuhan minum obat dan identifikasi pasien risiko drop-out</p>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <?php foreach ($summaryStats as $s): ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
            <div class="w-10 h-10 bg-gradient-to-br <?= $s['color'] ?> rounded-xl flex items-center justify-center mb-3 shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-2xl font-bold text-gray-800"><?= $s['value'] ?></p>
            <p class="text-sm text-gray-500 mt-0.5"><?= $s['label'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Patient Compliance Cards -->
    <div class="space-y-4">
        <?php if (empty($patients)): ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
            <p class="text-sm">Belum ada data monitoring. Catat log PMO terlebih dahulu di halaman Farmasi & PMO.</p>
        </div>
        <?php else: ?>
        <?php foreach ($patients as $p): 
            $riskColor = match($p['risiko']) {
                'Kritis' => ['bg' => 'bg-red-50 border-red-200', 'text' => 'text-red-700'],
                'Tinggi' => ['bg' => 'bg-amber-50 border-amber-200', 'text' => 'text-amber-700'],
                'Sedang' => ['bg' => 'bg-blue-50 border-blue-200', 'text' => 'text-blue-700'],
                default  => ['bg' => 'bg-emerald-50 border-emerald-200', 'text' => 'text-emerald-700'],
            };
        ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-all duration-300">
            <div class="p-5">
                <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                    <div class="flex items-center gap-3 lg:w-56 shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-teal-100 to-emerald-100 rounded-lg flex items-center justify-center">
                            <span class="text-teal-700 text-xs font-bold"><?= strtoupper(substr($p['nama'], 0, 2)) ?></span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800"><?= $p['nama'] ?></p>
                            <p class="text-xs text-gray-400"><?= $p['no_rm'] ?> • <?= $p['fase_pengobatan'] ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 lg:w-48 shrink-0">
                        <div class="relative w-14 h-14">
                            <svg class="w-14 h-14 transform -rotate-90" viewBox="0 0 56 56">
                                <circle cx="28" cy="28" r="24" fill="none" stroke="#e5e7eb" stroke-width="4"/>
                                <circle cx="28" cy="28" r="24" fill="none" 
                                        stroke="<?= $p['kepatuhan'] >= 80 ? '#34d399' : ($p['kepatuhan'] >= 60 ? '#fbbf24' : '#f87171') ?>" 
                                        stroke-width="4" stroke-linecap="round"
                                        stroke-dasharray="<?= 2 * 3.14159 * 24 ?>" 
                                        stroke-dashoffset="<?= 2 * 3.14159 * 24 * (1 - $p['kepatuhan'] / 100) ?>"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-xs font-bold text-gray-800"><?= $p['kepatuhan'] ?>%</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Kepatuhan</p>
                            <p class="text-sm font-semibold text-gray-800"><?= $p['hari_patuh'] ?>/<?= $p['total_hari'] ?> hari</p>
                            <p class="text-xs text-gray-400">Streak: <span class="font-medium <?= $p['streak'] > 0 ? 'text-emerald-600' : 'text-red-500' ?>"><?= $p['streak'] ?> hari</span></p>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] text-gray-400 mb-1.5 font-medium">RIWAYAT 30 HARI TERAKHIR</p>
                        <div class="flex flex-wrap gap-[3px]">
                            <?php foreach ($p['heatmap'] as $day => $val): ?>
                            <div class="w-[14px] h-[14px] rounded-sm <?= $val ? 'bg-emerald-400 hover:bg-emerald-500' : 'bg-red-200 hover:bg-red-300' ?> transition-colors cursor-pointer" 
                                 title="Hari ke-<?= $day + 1 ?>: <?= $val ? 'Patuh' : 'Tidak Patuh' ?>"></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="flex items-center gap-3 mt-1.5 text-[10px] text-gray-400">
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-emerald-400 rounded-sm"></span>Patuh</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-red-200 rounded-sm"></span>Tidak Patuh</span>
                        </div>
                    </div>
                    <div class="shrink-0">
                        <div class="px-3 py-2 rounded-xl border <?= $riskColor['bg'] ?> text-center">
                            <p class="text-[10px] text-gray-500 mb-0.5">RISIKO</p>
                            <p class="text-sm font-bold <?= $riskColor['text'] ?>"><?= $p['risiko'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Legend -->
    <div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">📌 Kriteria Risiko Drop-out</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="flex items-center gap-2 text-sm"><div class="w-3 h-3 rounded-full bg-emerald-400"></div><span class="text-gray-600"><strong class="text-gray-800">Rendah</strong> — Kepatuhan ≥ 80%</span></div>
            <div class="flex items-center gap-2 text-sm"><div class="w-3 h-3 rounded-full bg-blue-400"></div><span class="text-gray-600"><strong class="text-gray-800">Sedang</strong> — Kepatuhan 65-79%</span></div>
            <div class="flex items-center gap-2 text-sm"><div class="w-3 h-3 rounded-full bg-amber-400"></div><span class="text-gray-600"><strong class="text-gray-800">Tinggi</strong> — Kepatuhan 50-64%</span></div>
            <div class="flex items-center gap-2 text-sm"><div class="w-3 h-3 rounded-full bg-red-400"></div><span class="text-gray-600"><strong class="text-gray-800">Kritis</strong> — Kepatuhan &lt; 50%</span></div>
        </div>
    </div>

</div>
</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
