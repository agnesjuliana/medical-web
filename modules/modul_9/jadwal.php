<?php
/**
 * SIMRS-TB — Jadwal Kontrol
 * 
 * Penjadwalan kontrol, kalender, dan alarm peringatan
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'SIMRS-TB — Jadwal Kontrol';
$activePage = 'jadwal';

// ── Dummy: Jadwal ──
$jadwalHariIni = [
    ['waktu' => '08:00', 'pasien' => 'Rina Wijaya',       'jenis' => 'Kontrol Rutin',   'dokter' => 'dr. Rina Susanti',  'status' => 'Selesai'],
    ['waktu' => '09:30', 'pasien' => 'Ahmad Fauzi',       'jenis' => 'Evaluasi Fase',   'dokter' => 'dr. Rina Susanti',  'status' => 'Selesai'],
    ['waktu' => '10:00', 'pasien' => 'Dewi Lestari',      'jenis' => 'Pemeriksaan Lab',  'dokter' => 'dr. Aditya Putra', 'status' => 'Terjadwal'],
    ['waktu' => '11:00', 'pasien' => 'Hendra Gunawan',    'jenis' => 'Kontrol Rutin',   'dokter' => 'dr. Hendra Wijaya', 'status' => 'Terjadwal'],
    ['waktu' => '13:30', 'pasien' => 'Maya Sari',         'jenis' => 'Konsultasi',      'dokter' => 'dr. Rina Susanti',  'status' => 'Terjadwal'],
    ['waktu' => '14:00', 'pasien' => 'Budi Santoso',      'jenis' => 'Rontgen',         'dokter' => 'dr. Aditya Putra',  'status' => 'Terjadwal'],
    ['waktu' => '15:30', 'pasien' => 'Siti Aminah',       'jenis' => 'Kontrol Rutin',   'dokter' => 'dr. Rina Susanti',  'status' => 'Terjadwal'],
];

$jadwalMendatang = [
    ['tanggal' => '2026-04-14', 'pasien' => 'Riko Pratama',   'jenis' => 'Kontrol Rutin', 'dokter' => 'dr. Rina Susanti'],
    ['tanggal' => '2026-04-14', 'pasien' => 'Lina Marlina',   'jenis' => 'Pemeriksaan Lab','dokter' => 'dr. Hendra Wijaya'],
    ['tanggal' => '2026-04-15', 'pasien' => 'Agus Supriyadi', 'jenis' => 'Evaluasi Fase', 'dokter' => 'dr. Aditya Putra'],
    ['tanggal' => '2026-04-16', 'pasien' => 'Fitriani',       'jenis' => 'Rontgen',       'dokter' => 'dr. Hendra Wijaya'],
];

// ── Kalender bulan ini ──
$currentMonth = date('n');
$currentYear = date('Y');
$daysInMonth = date('t');
$firstDay = date('N', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
$today = date('j');

// Jadwal per tanggal (dummy)
$jadwalPerTanggal = [3 => 2, 5 => 1, 8 => 3, 10 => 1, 13 => 7, 14 => 2, 15 => 1, 16 => 1, 18 => 2, 22 => 4, 25 => 3, 28 => 1];
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
        <span class="text-gray-700 font-medium">Jadwal Kontrol</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2"><svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Jadwal Kontrol</h1>
            <p class="text-gray-500 text-sm mt-1">Penjadwalan kontrol dan alarm peringatan pasien</p>
        </div>
        <?= component_button('+ Tambah Jadwal', [
            'variant' => 'primary',
            'class' => '!bg-teal-600 hover:!bg-teal-700 !shadow-teal-500/20',
            'onclick' => "openModal('addScheduleModal')"
        ]) ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Kalender -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-800"><?= date('F Y') ?></h3>
                <div class="flex items-center gap-2">
                    <button class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button class="px-3 py-1 rounded-lg bg-teal-50 text-teal-700 text-xs font-medium">Hari Ini</button>
                    <button class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
            <div class="p-4">
                <!-- Day Headers -->
                <div class="grid grid-cols-7 gap-1 mb-2">
                    <?php foreach (['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $day): ?>
                    <div class="text-center text-xs font-semibold text-gray-400 py-2"><?= $day ?></div>
                    <?php endforeach; ?>
                </div>
                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 gap-1">
                    <?php 
                    // Empty cells before first day
                    for ($i = 1; $i < $firstDay; $i++):
                    ?>
                    <div class="h-16 rounded-lg"></div>
                    <?php endfor; ?>
                    
                    <?php for ($d = 1; $d <= $daysInMonth; $d++): 
                        $isToday = ($d == $today);
                        $hasSchedule = isset($jadwalPerTanggal[$d]);
                        $scheduleCount = $jadwalPerTanggal[$d] ?? 0;
                    ?>
                    <div class="h-16 rounded-lg p-1.5 text-sm cursor-pointer hover:bg-teal-50 transition-colors relative <?= $isToday ? 'bg-teal-50 ring-2 ring-teal-400' : 'hover:bg-gray-50' ?>">
                        <span class="text-xs font-medium <?= $isToday ? 'text-teal-700 font-bold' : 'text-gray-600' ?>"><?= $d ?></span>
                        <?php if ($hasSchedule): ?>
                        <div class="mt-0.5">
                            <div class="text-[10px] font-medium px-1 py-0.5 rounded bg-teal-100 text-teal-700 truncate"><?= $scheduleCount ?> jadwal</div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Today's Schedule -->
        <div class="space-y-5">
            <!-- Hari ini -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-800">Hari Ini</h3>
                    <p class="text-xs text-gray-400 mt-0.5"><?= date('l, d F Y') ?></p>
                </div>
                <div class="divide-y divide-gray-50 max-h-96 overflow-y-auto">
                    <?php foreach ($jadwalHariIni as $j): 
                        $sClass = match($j['status']) {
                            'Selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'Tidak Hadir' => 'bg-red-50 text-red-700 border-red-200',
                            default => 'bg-blue-50 text-blue-700 border-blue-200'
                        };
                    ?>
                    <div class="px-5 py-3 hover:bg-gray-50/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="text-center shrink-0 w-12">
                                <p class="text-sm font-bold text-teal-600"><?= $j['waktu'] ?></p>
                            </div>
                            <div class="w-px h-10 bg-teal-200"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800"><?= $j['pasien'] ?></p>
                                <p class="text-xs text-gray-400"><?= $j['jenis'] ?> • <?= $j['dokter'] ?></p>
                            </div>
                            <span class="text-[10px] font-medium px-2 py-0.5 rounded-full border <?= $sClass ?>"><?= $j['status'] ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Mendatang -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-800">Mendatang</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    <?php foreach ($jadwalMendatang as $jm): ?>
                    <div class="px-5 py-3 hover:bg-gray-50/50 transition-colors">
                        <p class="text-xs text-gray-400 mb-0.5"><?= date('D, d M', strtotime($jm['tanggal'])) ?></p>
                        <p class="text-sm font-medium text-gray-800"><?= $jm['pasien'] ?></p>
                        <p class="text-xs text-gray-400"><?= $jm['jenis'] ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

</div>
</main>

<!-- Add Schedule Modal -->
<?= component_modal('addScheduleModal', [
    'title' => 'Tambah Jadwal Kontrol',
    'content' => '
    <div class="space-y-4">
        ' . component_input('jadwal_pasien', ['label' => 'Pasien', 'placeholder' => 'Cari nama pasien...', 'required' => true]) . '
        <div class="grid grid-cols-2 gap-4">
            ' . component_input('jadwal_tanggal', ['label' => 'Tanggal', 'type' => 'date', 'required' => true]) . '
            ' . component_input('jadwal_waktu', ['label' => 'Waktu', 'type' => 'time', 'required' => true]) . '
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Kontrol</label>
            <select class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                <option>Kontrol Rutin</option>
                <option>Pemeriksaan Lab</option>
                <option>Rontgen</option>
                <option>Evaluasi Fase</option>
                <option>Konsultasi</option>
            </select>
        </div>
        ' . component_input('jadwal_catatan', ['label' => 'Catatan', 'type' => 'textarea', 'placeholder' => 'Catatan tambahan...']) . '
    </div>',
    'footer' => component_button('Batal', ['variant' => 'outline', 'onclick' => "closeModal('addScheduleModal')"])
        . ' ' . component_button('Simpan Jadwal', ['variant' => 'primary', 'class' => '!bg-teal-600 hover:!bg-teal-700', 'onclick' => "closeModal('addScheduleModal')"])
]) ?>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
