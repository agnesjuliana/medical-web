<?php
/**
 * SIMRS-TB — Farmasi & PMO
 * 
 * CRUD: Stok obat (Read/Update), Distribusi (Create/Read), Log PMO (Create/Read)
 * All data from database via api.php
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
require_once __DIR__ . '/../../config/database.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'SIMRS-TB — Farmasi & PMO';
$activePage = 'farmasi';

$db = getDBConnection();

// Stok Obat from DB
$stokObat = $db->query("SELECT *, (stok_tersedia <= stok_minimum) as alert FROM tb_drug_inventory ORDER BY nama_obat")->fetchAll();

// Distribusi from DB
$distribusi = $db->query("SELECT pr.*, p.nama as pasien, d.nama_obat as obat 
    FROM tb_prescriptions pr 
    JOIN tb_patients p ON pr.id_pasien = p.id 
    JOIN tb_drug_inventory d ON pr.id_obat = d.id 
    ORDER BY pr.tanggal_distribusi DESC LIMIT 50")->fetchAll();

// PMO Logs from DB
$pmoLogs = $db->query("SELECT l.*, p.nama as pasien 
    FROM tb_pmo_logs l 
    JOIN tb_patients p ON l.id_pasien = p.id 
    ORDER BY l.tanggal DESC, l.waktu_minum DESC LIMIT 50")->fetchAll();

// Dropdown data
$pasienList = $db->query("SELECT id, nama, no_rm FROM tb_patients WHERE status = 'Aktif' ORDER BY nama")->fetchAll();
$obatList = $db->query("SELECT id, kode_obat, nama_obat, stok_tersedia FROM tb_drug_inventory WHERE stok_tersedia > 0 ORDER BY nama_obat")->fetchAll();
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
        <span class="text-gray-700 font-medium">Farmasi & PMO</span>
    </nav>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2"><svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>Farmasi & PMO</h1>
        <p class="text-gray-500 text-sm mt-1">Manajemen distribusi obat dan pencatatan Pengawas Menelan Obat</p>
    </div>

    <!-- Flash Message -->
    <div id="flashMessage" class="hidden mb-4"></div>

    <!-- Tabs -->
    <div class="flex items-center gap-1 bg-white rounded-xl border border-gray-200 p-1 mb-6 w-fit">
        <button onclick="switchTab('stok')" id="tab-stok" class="px-4 py-2 text-sm font-medium rounded-lg bg-teal-50 text-teal-700 transition-colors">Stok Obat</button>
        <button onclick="switchTab('distribusi')" id="tab-distribusi" class="px-4 py-2 text-sm font-medium rounded-lg text-gray-500 hover:bg-gray-50 transition-colors">Distribusi</button>
        <button onclick="switchTab('pmo')" id="tab-pmo" class="px-4 py-2 text-sm font-medium rounded-lg text-gray-500 hover:bg-gray-50 transition-colors">Log PMO</button>
    </div>

    <!-- TAB: Stok Obat -->
    <div id="panel-stok">
        <?php $alertItems = array_filter($stokObat, fn($o) => $o['alert']); ?>
        <?php if (count($alertItems) > 0): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <p class="text-sm font-semibold text-amber-800">Stok Rendah</p>
                <p class="text-xs text-amber-700 mt-0.5"><?= count($alertItems) ?> obat di bawah batas minimum stok. Segera lakukan pengadaan.</p>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kode</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Obat</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stok</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Min</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kadaluarsa</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($stokObat)): ?>
                        <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">Belum ada data obat. Import schema-modul9.sql terlebih dahulu.</td></tr>
                        <?php else: ?>
                        <?php foreach ($stokObat as $obat): ?>
                        <tr class="hover:bg-teal-50/30 transition-colors <?= $obat['alert'] ? 'bg-amber-50/30' : '' ?>">
                            <td class="px-5 py-3 font-mono text-xs text-gray-500"><?= $obat['kode_obat'] ?></td>
                            <td class="px-5 py-3 font-medium text-gray-800"><?= $obat['nama_obat'] ?></td>
                            <td class="px-5 py-3"><?= component_badge($obat['kategori'], 'default') ?></td>
                            <td class="px-5 py-3"><span class="font-bold <?= $obat['alert'] ? 'text-red-500' : 'text-gray-800' ?>"><?= number_format($obat['stok_tersedia']) ?></span></td>
                            <td class="px-5 py-3 text-gray-500"><?= $obat['stok_minimum'] ?></td>
                            <td class="px-5 py-3 text-gray-500"><?= date('d M Y', strtotime($obat['tanggal_kadaluarsa'])) ?></td>
                            <td class="px-5 py-3">
                                <?php if ($obat['alert']): ?>
                                    <?= component_badge('Stok Rendah', 'error') ?>
                                <?php else: ?>
                                    <?= component_badge('Tersedia', 'success') ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: Distribusi -->
    <div id="panel-distribusi" class="hidden">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-800">Distribusi Obat Pasien</h3>
                <button onclick="openModal('addDistribusiModal')" class="inline-flex items-center justify-center gap-2 font-medium rounded-xl text-xs px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white shadow-sm transition-all duration-200">+ Distribusi</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pasien</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Obat</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dosis</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($distribusi)): ?>
                        <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">Belum ada data distribusi</td></tr>
                        <?php else: ?>
                        <?php foreach ($distribusi as $d): 
                            $sColor = match($d['status_ambil']) { 'Sudah Diambil' => 'success', 'Terlambat' => 'error', default => 'warning' };
                        ?>
                        <tr class="hover:bg-teal-50/30 transition-colors">
                            <td class="px-5 py-3 font-medium text-gray-800"><?= $d['pasien'] ?></td>
                            <td class="px-5 py-3 text-gray-600"><?= $d['obat'] ?></td>
                            <td class="px-5 py-3 text-gray-500"><?= $d['dosis'] ?></td>
                            <td class="px-5 py-3 text-gray-800 font-semibold"><?= $d['jumlah_diberikan'] ?></td>
                            <td class="px-5 py-3 text-gray-500"><?= date('d M Y', strtotime($d['tanggal_distribusi'])) ?></td>
                            <td class="px-5 py-3"><?= component_badge($d['status_ambil'], $sColor) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: PMO Logs -->
    <div id="panel-pmo" class="hidden">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-800">Log Pengawas Menelan Obat (PMO)</h3>
                <button onclick="openModal('addPMOModal')" class="inline-flex items-center justify-center gap-2 font-medium rounded-xl text-xs px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white shadow-sm transition-all duration-200">+ Catat PMO</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pasien</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Metode</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($pmoLogs)): ?>
                        <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">Belum ada data log PMO</td></tr>
                        <?php else: ?>
                        <?php foreach ($pmoLogs as $l): 
                            $lColor = match($l['status_minum']) { 'Diminum' => 'success', 'Efek Samping' => 'warning', default => 'error' };
                        ?>
                        <tr class="hover:bg-teal-50/30 transition-colors">
                            <td class="px-5 py-3 font-medium text-gray-800"><?= $l['pasien'] ?></td>
                            <td class="px-5 py-3 text-gray-500"><?= date('d M Y', strtotime($l['tanggal'])) ?></td>
                            <td class="px-5 py-3 text-gray-500"><?= $l['waktu_minum'] ?: '—' ?></td>
                            <td class="px-5 py-3"><?= component_badge($l['status_minum'], $lColor) ?></td>
                            <td class="px-5 py-3"><?= component_badge($l['metode_verifikasi'], 'default') ?></td>
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

<!-- Distribusi Modal -->
<div id="addDistribusiModal" class="fixed inset-0 z-[100] hidden">
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('addDistribusiModal')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-lg max-h-[85vh] flex flex-col transform scale-95 opacity-0 transition-all duration-200" id="addDistribusiModal-panel">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800">Distribusi Obat</h3>
                <button onclick="closeModal('addDistribusiModal')" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form id="distribusiForm" class="px-6 py-5 overflow-y-auto flex-1 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Pasien *</label>
                    <select name="id_pasien" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        <option value="">Pilih pasien...</option>
                        <?php foreach ($pasienList as $pl): ?><option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['nama']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Obat *</label>
                    <select name="id_obat" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        <option value="">Pilih obat...</option>
                        <?php foreach ($obatList as $o): ?><option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nama_obat']) ?> (Stok: <?= $o['stok_tersedia'] ?>)</option><?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Dosis *</label><input type="text" name="dosis" required placeholder="mis: 3 tab/hari" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah *</label><input type="number" name="jumlah_diberikan" required min="1" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"></div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal *</label><input type="date" name="tanggal_distribusi" required value="<?= date('Y-m-d') ?>" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"></div>
            </form>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-end gap-3">
                <button onclick="closeModal('addDistribusiModal')" class="inline-flex items-center font-medium rounded-xl text-sm px-4 py-2.5 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700">Batal</button>
                <button onclick="submitForm('distribusiForm','create_prescription')" class="inline-flex items-center font-medium rounded-xl text-sm px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white shadow-sm">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- PMO Modal -->
<div id="addPMOModal" class="fixed inset-0 z-[100] hidden">
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('addPMOModal')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-lg max-h-[85vh] flex flex-col transform scale-95 opacity-0 transition-all duration-200" id="addPMOModal-panel">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800">Catat Log PMO</h3>
                <button onclick="closeModal('addPMOModal')" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form id="pmoForm" class="px-6 py-5 overflow-y-auto flex-1 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Pasien *</label>
                    <select name="id_pasien" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        <option value="">Pilih pasien...</option>
                        <?php foreach ($pasienList as $pl): ?><option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['nama']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal *</label><input type="date" name="tanggal" required value="<?= date('Y-m-d') ?>" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Waktu</label><input type="time" name="waktu_minum" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Status Minum *</label>
                    <select name="status_minum" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        <option value="Diminum">Diminum</option>
                        <option value="Tidak Diminum">Tidak Diminum</option>
                        <option value="Dimuntahkan">Dimuntahkan</option>
                        <option value="Efek Samping">Efek Samping</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Metode Verifikasi</label>
                    <select name="metode_verifikasi" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        <option>Langsung</option><option>Video Call</option><option>Foto</option><option>Laporan Keluarga</option>
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Catatan</label><textarea name="catatan" rows="2" placeholder="Catatan..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 resize-none"></textarea></div>
            </form>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-end gap-3">
                <button onclick="closeModal('addPMOModal')" class="inline-flex items-center font-medium rounded-xl text-sm px-4 py-2.5 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700">Batal</button>
                <button onclick="submitForm('pmoForm','create_pmo_log')" class="inline-flex items-center font-medium rounded-xl text-sm px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white shadow-sm">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    document.querySelectorAll('[id^="panel-"]').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('[id^="tab-"]').forEach(t => { t.classList.remove('bg-teal-50', 'text-teal-700'); t.classList.add('text-gray-500'); });
    document.getElementById('panel-' + tabName).classList.remove('hidden');
    const tab = document.getElementById('tab-' + tabName);
    tab.classList.add('bg-teal-50', 'text-teal-700');
    tab.classList.remove('text-gray-500');
}

function submitForm(formId, action) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    formData.append('action', action);

    fetch('api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Close all modals
                document.querySelectorAll('[id$="Modal"]').forEach(m => { if (m.id !== 'flashMessage') closeModal(m.id); });
                showFlash(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                alert(data.message || 'Gagal menyimpan');
            }
        });
}

function showFlash(message, type) {
    const el = document.getElementById('flashMessage');
    const colors = { success: 'bg-green-50 border-green-200 text-green-800', error: 'bg-red-50 border-red-200 text-red-800' };
    el.className = `flex items-center gap-3 px-4 py-3.5 rounded-xl border ${colors[type] || colors.success} mb-4`;
    el.innerHTML = `<span class="text-sm">${message}</span>`;
    el.classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
