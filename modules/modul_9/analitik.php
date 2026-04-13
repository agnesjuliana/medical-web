<?php
/**
 * SIMRS-TB — Analitik & SITB
 * 
 * Dasbor analitik komprehensif, sinkronisasi SITB Kemenkes
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'SIMRS-TB — Analitik & SITB';
$activePage = 'analitik';

// ── Dummy: Sync Logs ──
$syncLogs = [
    ['tanggal' => '2026-04-13 06:00', 'jenis' => 'Laporan Bulanan',   'records' => 248, 'status' => 'Berhasil', 'response' => '200 OK'],
    ['tanggal' => '2026-04-12 18:00', 'jenis' => 'Update Status',     'records' => 12,  'status' => 'Berhasil', 'response' => '200 OK'],
    ['tanggal' => '2026-04-12 06:00', 'jenis' => 'Pasien Baru',       'records' => 3,   'status' => 'Berhasil', 'response' => '200 OK'],
    ['tanggal' => '2026-04-11 06:00', 'jenis' => 'Hasil Lab',         'records' => 8,   'status' => 'Partial',  'response' => '207 Multi-Status'],
    ['tanggal' => '2026-04-10 06:00', 'jenis' => 'Pengobatan Selesai','records' => 5,   'status' => 'Berhasil', 'response' => '200 OK'],
    ['tanggal' => '2026-04-09 06:00', 'jenis' => 'Update Status',     'records' => 15,  'status' => 'Gagal',    'response' => '503 Service Unavailable'],
];
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>
<?php require_once __DIR__ . '/../../layout/navbar.php'; ?>
<?php require_once __DIR__ . '/_sidebar.php'; ?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<main class="lg:ml-64 min-h-[calc(100vh-4rem)] bg-gray-50">
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-5">
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-teal-600 transition-colors">Module Hub</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="index.php" class="hover:text-teal-600 transition-colors">SIMRS-TB</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-700 font-medium">Analitik & SITB</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📊 Analitik & SITB</h1>
            <p class="text-gray-500 text-sm mt-1">Dashboard analitik komprehensif dan sinkronisasi SITB Kemenkes</p>
        </div>
        <div class="flex items-center gap-2">
            <?= component_button('Export PDF', [
                'variant' => 'outline',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
            ]) ?>
            <?= component_button('Export Excel', [
                'variant' => 'outline',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
            ]) ?>
        </div>
    </div>

    <!-- Period Selector -->
    <div class="bg-white rounded-xl border border-gray-200 p-1 mb-6 w-fit flex items-center gap-1">
        <button onclick="setPeriod(this)" class="px-4 py-2 text-sm font-medium rounded-lg bg-teal-50 text-teal-700">Bulanan</button>
        <button onclick="setPeriod(this)" class="px-4 py-2 text-sm font-medium rounded-lg text-gray-500 hover:bg-gray-50">Triwulan</button>
        <button onclick="setPeriod(this)" class="px-4 py-2 text-sm font-medium rounded-lg text-gray-500 hover:bg-gray-50">Tahunan</button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <?php 
        $analitikStats = [
            ['label' => 'Total Kasus', 'value' => '312', 'sub' => 'Tahun ini'],
            ['label' => 'Kesembuhan', 'value' => '78.4%', 'sub' => 'Success rate'],
            ['label' => 'BTA Konversi', 'value' => '85.2%', 'sub' => 'Bulan ke-2'],
            ['label' => 'Drop-out', 'value' => '3.2%', 'sub' => 'Target < 5%'],
            ['label' => 'SITB Sync', 'value' => '✓', 'sub' => 'Terakhir 6 jam lalu'],
        ];
        foreach ($analitikStats as $as): ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
            <p class="text-2xl font-bold text-gray-800"><?= $as['value'] ?></p>
            <p class="text-sm font-medium text-gray-600 mt-0.5"><?= $as['label'] ?></p>
            <p class="text-xs text-gray-400 mt-0.5"><?= $as['sub'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
        <!-- Kasus per Bulan -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-base font-semibold text-gray-800 mb-1">Kasus TB per Bulan</h3>
            <p class="text-xs text-gray-400 mb-4">Perbandingan kasus baru vs sembuh</p>
            <div style="position:relative;height:280px;"><canvas id="casesChart"></canvas></div>
        </div>

        <!-- Outcome Pengobatan -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-base font-semibold text-gray-800 mb-1">Outcome Pengobatan</h3>
            <p class="text-xs text-gray-400 mb-4">Distribusi hasil pengobatan</p>
            <div style="position:relative;height:280px;"><canvas id="outcomeChart"></canvas></div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
        <!-- Tipe Pasien -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-base font-semibold text-gray-800 mb-1">Tipe Pasien</h3>
            <p class="text-xs text-gray-400 mb-4">Klasifikasi riwayat</p>
            <div style="position:relative;height:250px;"><canvas id="typeChart"></canvas></div>
        </div>

        <!-- Usia & Gender -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-base font-semibold text-gray-800 mb-1">Distribusi Usia</h3>
            <p class="text-xs text-gray-400 mb-4">Kelompok umur pasien</p>
            <div style="position:relative;height:250px;"><canvas id="ageChart"></canvas></div>
        </div>

        <!-- Kepatuhan Trend -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-base font-semibold text-gray-800 mb-1">Tren Kepatuhan</h3>
            <p class="text-xs text-gray-400 mb-4">Rata-rata bulanan</p>
            <div style="position:relative;height:250px;"><canvas id="complianceChart"></canvas></div>
        </div>
    </div>

    <!-- SITB Sync Section -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800">Sinkronisasi SITB Kemenkes</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Sistem Informasi Tuberkulosis — Kementerian Kesehatan RI</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1.5 text-xs text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-full border border-emerald-100">
                    <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                    Terhubung
                </div>
                <?= component_button('Sinkronkan Sekarang', [
                    'variant' => 'primary',
                    'size' => 'sm',
                    'class' => '!bg-blue-600 hover:!bg-blue-700 !shadow-blue-500/20',
                    'onclick' => 'simulateSync()',
                    'id' => 'syncBtn',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>'
                ]) ?>
            </div>
        </div>

        <!-- Sync Progress (hidden) -->
        <div id="syncProgress" class="hidden px-5 py-4 bg-blue-50/50 border-b border-blue-100">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-blue-700" id="syncLabel">Menghubungkan ke server SITB...</p>
                    <div class="w-full h-1.5 bg-blue-200 rounded-full mt-2 overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full transition-all duration-500" id="syncBar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sync Logs -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Data</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Records</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Response</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($syncLogs as $sl): 
                        $syncColor = match($sl['status']) { 'Berhasil' => 'success', 'Gagal' => 'error', default => 'warning' };
                    ?>
                    <tr class="hover:bg-teal-50/30 transition-colors">
                        <td class="px-5 py-3 text-gray-500 text-xs"><?= date('d M Y H:i', strtotime($sl['tanggal'])) ?></td>
                        <td class="px-5 py-3 font-medium text-gray-800"><?= $sl['jenis'] ?></td>
                        <td class="px-5 py-3 text-gray-600"><?= $sl['records'] ?> record</td>
                        <td class="px-5 py-3"><?= component_badge($sl['status'], $syncColor) ?></td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-400"><?= $sl['response'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</main>

<script>
// ── Period Selector ──
function setPeriod(btn) {
    btn.parentElement.querySelectorAll('button').forEach(b => {
        b.classList.remove('bg-teal-50', 'text-teal-700');
        b.classList.add('text-gray-500');
    });
    btn.classList.add('bg-teal-50', 'text-teal-700');
    btn.classList.remove('text-gray-500');
}

// ── Simulate SITB Sync ──
function simulateSync() {
    const progress = document.getElementById('syncProgress');
    const bar = document.getElementById('syncBar');
    const label = document.getElementById('syncLabel');
    const btn = document.getElementById('syncBtn');
    
    progress.classList.remove('hidden');
    btn.disabled = true;
    btn.style.opacity = '0.5';
    
    const steps = [
        { pct: 15, text: 'Menghubungkan ke server SITB...' },
        { pct: 35, text: 'Autentikasi API Kemenkes...' },
        { pct: 55, text: 'Mengirim data pasien (248 records)...' },
        { pct: 75, text: 'Mengirim data pengobatan...' },
        { pct: 90, text: 'Memverifikasi response...' },
        { pct: 100, text: 'Sinkronisasi berhasil! ✓' },
    ];
    
    let i = 0;
    const interval = setInterval(() => {
        if (i < steps.length) {
            bar.style.width = steps[i].pct + '%';
            label.textContent = steps[i].text;
            i++;
        } else {
            clearInterval(interval);
            setTimeout(() => {
                progress.classList.add('hidden');
                btn.disabled = false;
                btn.style.opacity = '1';
            }, 2000);
        }
    }, 800);
}

// ── Charts ──
const chartFont = { family: 'Inter', size: 11 };
const gridColor = '#f1f5f9';

// Kasus per Bulan
new Chart(document.getElementById('casesChart'), {
    type: 'bar',
    data: {
        labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
        datasets: [{
            label: 'Kasus Baru',
            data: [19, 22, 27, 24, 30, 28, 32, 26, 20, 25, 22, 18],
            backgroundColor: '#14b8a6',
            borderRadius: 6
        },{
            label: 'Sembuh',
            data: [17, 20, 23, 21, 24, 22, 28, 24, 19, 22, 20, 15],
            backgroundColor: '#34d399',
            borderRadius: 6
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { font: chartFont, padding: 15, usePointStyle: true, pointStyle: 'circle' } } },
        scales: {
            y: { beginAtZero: true, grid: { color: gridColor }, ticks: { font: chartFont, color: '#94a3b8' } },
            x: { grid: { display: false }, ticks: { font: chartFont, color: '#94a3b8' } }
        }
    }
});

// Outcome Pengobatan
new Chart(document.getElementById('outcomeChart'), {
    type: 'doughnut',
    data: {
        labels: ['Sembuh', 'Pengobatan Lengkap', 'Gagal', 'Meninggal', 'Putus Obat', 'Pindah'],
        datasets: [{
            data: [156, 42, 12, 5, 8, 6],
            backgroundColor: ['#14b8a6', '#34d399', '#f87171', '#6b7280', '#fbbf24', '#60a5fa'],
            borderWidth: 0, spacing: 2, borderRadius: 4
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false, cutout: '65%',
        plugins: { legend: { position: 'bottom', labels: { font: chartFont, padding: 12, usePointStyle: true, pointStyle: 'circle' } } }
    }
});

// Tipe Pasien
new Chart(document.getElementById('typeChart'), {
    type: 'pie',
    data: {
        labels: ['Baru', 'Kambuh', 'Gagal', 'Putus Obat', 'Pindahan'],
        datasets: [{
            data: [210, 35, 12, 8, 15],
            backgroundColor: ['#14b8a6', '#fbbf24', '#f87171', '#ef4444', '#60a5fa'],
            borderWidth: 0, spacing: 2
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { font: chartFont, padding: 12, usePointStyle: true, pointStyle: 'circle' } } }
    }
});

// Distribusi Usia
new Chart(document.getElementById('ageChart'), {
    type: 'bar',
    data: {
        labels: ['0-14', '15-24', '25-34', '35-44', '45-54', '55-64', '65+'],
        datasets: [{
            label: 'Laki-laki',
            data: [5, 18, 35, 42, 38, 22, 12],
            backgroundColor: '#14b8a6',
            borderRadius: 4
        },{
            label: 'Perempuan',
            data: [3, 15, 28, 30, 25, 18, 8],
            backgroundColor: '#a78bfa',
            borderRadius: 4
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { font: chartFont, padding: 12, usePointStyle: true, pointStyle: 'circle' } } },
        scales: {
            y: { beginAtZero: true, grid: { color: gridColor }, ticks: { font: chartFont, color: '#94a3b8' } },
            x: { grid: { display: false }, ticks: { font: chartFont, color: '#94a3b8' } }
        }
    }
});

// Tren Kepatuhan
new Chart(document.getElementById('complianceChart'), {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
        datasets: [{
            label: 'Kepatuhan (%)',
            data: [82, 84, 83, 85, 86, 84, 87, 88, 86, 89, 87, 87],
            borderColor: '#14b8a6',
            backgroundColor: 'rgba(20,184,166,0.1)',
            borderWidth: 2.5, fill: true, tension: 0.4,
            pointRadius: 3, pointBackgroundColor: '#14b8a6'
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { min: 70, max: 100, grid: { color: gridColor }, ticks: { font: chartFont, color: '#94a3b8', callback: v => v + '%' } },
            x: { grid: { display: false }, ticks: { font: chartFont, color: '#94a3b8' } }
        }
    }
});
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
