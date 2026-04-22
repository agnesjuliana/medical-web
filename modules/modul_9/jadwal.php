<?php
/**
 * SIMRS-TB — Jadwal Kontrol
 * 
 * CRUD jadwal, kalender, dropdown pasien dari database
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
require_once __DIR__ . '/../../config/database.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'SIMRS-TB — Jadwal Kontrol';
$activePage = 'jadwal';

$db = getDBConnection();

// Kalender bulan ini
$currentMonth = (int)($_GET['month'] ?? date('n'));
$currentYear = (int)($_GET['year'] ?? date('Y'));
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
$firstDay = date('N', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
$today = date('j');
$isCurrentMonth = ($currentMonth == date('n') && $currentYear == date('Y'));

// Jadwal count per day from DB
$calStmt = $db->prepare("SELECT DAY(tanggal_jadwal) as hari, COUNT(*) as total
    FROM tb_appointments 
    WHERE MONTH(tanggal_jadwal) = :m AND YEAR(tanggal_jadwal) = :y
    GROUP BY DAY(tanggal_jadwal)");
$calStmt->execute(['m' => $currentMonth, 'y' => $currentYear]);
$jadwalPerTanggal = [];
foreach ($calStmt->fetchAll() as $row) {
    $jadwalPerTanggal[(int)$row['hari']] = (int)$row['total'];
}

// Jadwal hari ini
$jadwalStmt = $db->prepare("SELECT a.*, p.nama as pasien, p.no_rm
    FROM tb_appointments a 
    JOIN tb_patients p ON a.id_pasien = p.id 
    WHERE DATE(a.tanggal_jadwal) = CURDATE() 
    ORDER BY a.tanggal_jadwal ASC");
$jadwalStmt->execute();
$jadwalHariIni = $jadwalStmt->fetchAll();

// Jadwal mendatang
$upcomingStmt = $db->prepare("SELECT a.*, p.nama as pasien, p.no_rm
    FROM tb_appointments a 
    JOIN tb_patients p ON a.id_pasien = p.id 
    WHERE DATE(a.tanggal_jadwal) > CURDATE() AND a.status = 'Terjadwal'
    ORDER BY a.tanggal_jadwal ASC LIMIT 10");
$upcomingStmt->execute();
$jadwalMendatang = $upcomingStmt->fetchAll();

// Pasien list for dropdown
$pasienList = $db->query("SELECT id, nama, no_rm FROM tb_patients WHERE status = 'Aktif' ORDER BY nama")->fetchAll();
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
        <button onclick="openModal('addScheduleModal')" class="inline-flex items-center justify-center gap-2 font-medium rounded-xl text-sm px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white shadow-sm shadow-teal-500/20 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            + Tambah Jadwal
        </button>
    </div>

    <!-- Flash Message -->
    <div id="flashMessage" class="hidden mb-4"></div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Kalender -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-800"><?= date('F Y', mktime(0,0,0,$currentMonth,1,$currentYear)) ?></h3>
                <div class="flex items-center gap-2">
                    <?php 
                    $prevMonth = $currentMonth - 1; $prevYear = $currentYear;
                    if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
                    $nextMonth = $currentMonth + 1; $nextYear = $currentYear;
                    if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
                    ?>
                    <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <a href="jadwal.php" class="px-3 py-1 rounded-lg bg-teal-50 text-teal-700 text-xs font-medium">Hari Ini</a>
                    <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-7 gap-1 mb-2">
                    <?php foreach (['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $day): ?>
                    <div class="text-center text-xs font-semibold text-gray-400 py-2"><?= $day ?></div>
                    <?php endforeach; ?>
                </div>
                <div class="grid grid-cols-7 gap-1">
                    <?php for ($i = 1; $i < $firstDay; $i++): ?>
                    <div class="h-16 rounded-lg"></div>
                    <?php endfor; ?>
                    <?php for ($d = 1; $d <= $daysInMonth; $d++): 
                        $isToday = ($isCurrentMonth && $d == $today);
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
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-800">Hari Ini</h3>
                    <p class="text-xs text-gray-400 mt-0.5"><?= date('l, d F Y') ?></p>
                </div>
                <div class="divide-y divide-gray-50 max-h-96 overflow-y-auto">
                    <?php if (empty($jadwalHariIni)): ?>
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">Tidak ada jadwal hari ini</div>
                    <?php else: ?>
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
                                <p class="text-sm font-bold text-teal-600"><?= date('H:i', strtotime($j['tanggal_jadwal'])) ?></p>
                            </div>
                            <div class="w-px h-10 bg-teal-200"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800"><?= $j['pasien'] ?></p>
                                <p class="text-xs text-gray-400"><?= $j['jenis_kontrol'] ?></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-medium px-2 py-0.5 rounded-full border <?= $sClass ?>"><?= $j['status'] ?></span>
                                <?php if ($j['status'] === 'Terjadwal'): ?>
                                <button onclick="updateAppointmentStatus(<?= $j['id'] ?>, 'Selesai')" class="text-emerald-500 hover:text-emerald-700 p-1" title="Tandai Selesai">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-800">Mendatang</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    <?php if (empty($jadwalMendatang)): ?>
                    <div class="px-5 py-6 text-center text-gray-400 text-sm">Tidak ada jadwal mendatang</div>
                    <?php else: ?>
                    <?php foreach ($jadwalMendatang as $jm): ?>
                    <div class="px-5 py-3 hover:bg-gray-50/50 transition-colors">
                        <p class="text-xs text-gray-400 mb-0.5"><?= date('D, d M', strtotime($jm['tanggal_jadwal'])) ?> • <?= date('H:i', strtotime($jm['tanggal_jadwal'])) ?></p>
                        <p class="text-sm font-medium text-gray-800"><?= $jm['pasien'] ?></p>
                        <p class="text-xs text-gray-400"><?= $jm['jenis_kontrol'] ?></p>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>
</main>

<!-- Add Schedule Modal -->
<div id="addScheduleModal" class="fixed inset-0 z-[100] hidden">
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('addScheduleModal')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-lg max-h-[85vh] flex flex-col transform scale-95 opacity-0 transition-all duration-200" id="addScheduleModal-panel">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800">Tambah Jadwal Kontrol</h3>
                <button onclick="closeModal('addScheduleModal')" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="scheduleForm" class="px-6 py-5 overflow-y-auto flex-1">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Pasien <span class="text-red-400">*</span></label>
                        <select id="sched_pasien" name="id_pasien" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                            <option value="">Pilih pasien...</option>
                            <?php foreach ($pasienList as $pl): ?>
                            <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['nama']) ?> (<?= $pl['no_rm'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal <span class="text-red-400">*</span></label>
                            <input type="date" id="sched_tanggal" name="tanggal_jadwal" required
                                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Waktu <span class="text-red-400">*</span></label>
                            <input type="time" id="sched_waktu" name="waktu_jadwal" required
                                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Kontrol</label>
                        <select name="jenis_kontrol" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                            <option>Kontrol Rutin</option>
                            <option>Pemeriksaan Lab</option>
                            <option>Rontgen</option>
                            <option>Evaluasi Fase</option>
                            <option>Konsultasi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Catatan</label>
                        <textarea name="catatan" rows="3" placeholder="Catatan tambahan..."
                                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white transition-all resize-none"></textarea>
                    </div>
                </div>
            </form>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-end gap-3">
                <button onclick="closeModal('addScheduleModal')" class="inline-flex items-center justify-center gap-2 font-medium rounded-xl text-sm px-4 py-2.5 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 transition-all duration-200">Batal</button>
                <button onclick="submitSchedule()" class="inline-flex items-center justify-center gap-2 font-medium rounded-xl text-sm px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white shadow-sm shadow-teal-500/20 transition-all duration-200">Simpan Jadwal</button>
            </div>
        </div>
    </div>
</div>

<script>
function submitSchedule() {
    const form = document.getElementById('scheduleForm');
    const formData = new FormData(form);
    formData.append('action', 'create_appointment');

    fetch('api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeModal('addScheduleModal');
                showFlash(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                alert(data.message);
            }
        });
}

function updateAppointmentStatus(id, status) {
    const formData = new FormData();
    formData.append('action', 'update_appointment_status');
    formData.append('id', id);
    formData.append('status', status);

    fetch('api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showFlash('Status jadwal diperbarui', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        });
}

function showFlash(message, type) {
    const el = document.getElementById('flashMessage');
    const colors = { success: 'bg-green-50 border-green-200 text-green-800', error: 'bg-red-50 border-red-200 text-red-800' };
    el.className = `flex items-center gap-3 px-4 py-3.5 rounded-xl border ${colors[type] || colors.success} mb-4`;
    el.innerHTML = `<span class="text-sm">${message}</span>`;
    el.classList.remove('hidden');
    setTimeout(() => el.classList.add('hidden'), 4000);
}
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
