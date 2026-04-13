<?php
/**
 * SIMRS-TB — Rekam Medis Digital
 * 
 * Daftar pasien, detail rekam medis, hasil lab terintegrasi
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'SIMRS-TB — Rekam Medis';
$activePage = 'rekam-medis';

// ── Dummy Data ──
$patients = [
    ['no_rm' => 'RM-2024-0142', 'nama' => 'Ahmad Fauzi',      'umur' => 45, 'jk' => 'L', 'kategori' => 'Paru',        'fase' => 'Intensif', 'tipe' => 'Baru',       'status' => 'Aktif',    'mulai' => '2026-02-15', 'dokter' => 'dr. Rina Susanti', 'progress' => 35],
    ['no_rm' => 'RM-2024-0198', 'nama' => 'Siti Aminah',       'umur' => 32, 'jk' => 'P', 'kategori' => 'Paru',        'fase' => 'Intensif', 'tipe' => 'Baru',       'status' => 'Aktif',    'mulai' => '2026-03-01', 'dokter' => 'dr. Rina Susanti', 'progress' => 25],
    ['no_rm' => 'RM-2024-0076', 'nama' => 'Budi Santoso',      'umur' => 58, 'jk' => 'L', 'kategori' => 'Paru',        'fase' => 'Lanjutan', 'tipe' => 'Kambuh',     'status' => 'Aktif',    'mulai' => '2025-11-20', 'dokter' => 'dr. Hendra Wijaya', 'progress' => 72],
    ['no_rm' => 'RM-2024-0213', 'nama' => 'Dewi Lestari',      'umur' => 28, 'jk' => 'P', 'kategori' => 'Ekstra Paru', 'fase' => 'Intensif', 'tipe' => 'Baru',       'status' => 'Aktif',    'mulai' => '2026-03-10', 'dokter' => 'dr. Aditya Putra', 'progress' => 18],
    ['no_rm' => 'RM-2024-0167', 'nama' => 'Riko Pratama',      'umur' => 40, 'jk' => 'L', 'kategori' => 'Paru',        'fase' => 'Lanjutan', 'tipe' => 'Baru',       'status' => 'Aktif',    'mulai' => '2025-12-05', 'dokter' => 'dr. Rina Susanti', 'progress' => 65],
    ['no_rm' => 'RM-2024-0089', 'nama' => 'Rina Wijaya',       'umur' => 35, 'jk' => 'P', 'kategori' => 'Paru',        'fase' => 'Lanjutan', 'tipe' => 'Baru',       'status' => 'Aktif',    'mulai' => '2025-10-15', 'dokter' => 'dr. Hendra Wijaya', 'progress' => 82],
    ['no_rm' => 'RM-2024-0234', 'nama' => 'Hendra Gunawan',    'umur' => 52, 'jk' => 'L', 'kategori' => 'Paru',        'fase' => 'Selesai',  'tipe' => 'Baru',       'status' => 'Sembuh',   'mulai' => '2025-06-01', 'dokter' => 'dr. Aditya Putra', 'progress' => 100],
    ['no_rm' => 'RM-2024-0301', 'nama' => 'Maya Sari',         'umur' => 26, 'jk' => 'P', 'kategori' => 'Paru',        'fase' => 'Belum Mulai','tipe' => 'Baru',     'status' => 'Aktif',    'mulai' => '—',         'dokter' => 'dr. Rina Susanti', 'progress' => 0],
];

$labTimeline = [
    ['tanggal' => '2026-04-10', 'jenis' => 'BTA',       'hasil' => 'BTA Negatif',           'status' => 'Baik'],
    ['tanggal' => '2026-03-12', 'jenis' => 'Rontgen',   'hasil' => 'Perbaikan infiltrat',   'status' => 'Baik'],
    ['tanggal' => '2026-02-15', 'jenis' => 'GeneXpert', 'hasil' => 'MTB Detected, Rif Sens','status' => 'Perhatian'],
    ['tanggal' => '2026-02-15', 'jenis' => 'BTA',       'hasil' => 'BTA +1',                'status' => 'Perhatian'],
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
        <span class="text-gray-700 font-medium">Rekam Medis</span>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2"><svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>Rekam Medis Digital</h1>
            <p class="text-gray-500 text-sm mt-1">Kelola data pasien TB dan riwayat medis terintegrasi</p>
        </div>
        <?= component_button('+ Tambah Pasien', [
            'variant' => 'primary',
            'class' => '!bg-teal-600 hover:!bg-teal-700 !shadow-teal-500/20',
            'onclick' => "openModal('addPatientModal')"
        ]) ?>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 flex flex-wrap items-center gap-3">
        <?= component_input('search', ['placeholder' => 'Cari nama / No. RM...', 'class' => 'flex-1 min-w-[200px]']) ?>
        <select class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
            <option>Semua Fase</option>
            <option>Intensif</option>
            <option>Lanjutan</option>
            <option>Selesai</option>
            <option>Belum Mulai</option>
        </select>
        <select class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
            <option>Semua Status</option>
            <option>Aktif</option>
            <option>Sembuh</option>
            <option>Putus Obat</option>
        </select>
    </div>

    <!-- Patient Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pasien</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. RM</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fase</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Progress</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dokter</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($patients as $p): 
                        $faseColor = match($p['fase']) {
                            'Intensif' => 'warning',
                            'Lanjutan' => 'primary',
                            'Selesai' => 'success',
                            default => 'default'
                        };
                        $statusColor = match($p['status']) {
                            'Aktif' => 'info',
                            'Sembuh' => 'success',
                            'Putus Obat' => 'error',
                            default => 'default'
                        };
                        $progColor = $p['progress'] >= 70 ? 'bg-emerald-400' : ($p['progress'] >= 30 ? 'bg-teal-400' : 'bg-amber-400');
                    ?>
                    <tr class="hover:bg-teal-50/30 transition-colors group">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-gradient-to-br from-teal-100 to-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                                    <span class="text-teal-700 text-xs font-bold"><?= strtoupper(substr($p['nama'], 0, 2)) ?></span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800"><?= $p['nama'] ?></p>
                                    <p class="text-xs text-gray-400"><?= $p['umur'] ?> thn • <?= $p['jk'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-500 font-mono text-xs"><?= $p['no_rm'] ?></td>
                        <td class="px-5 py-3.5 text-gray-600"><?= $p['kategori'] ?></td>
                        <td class="px-5 py-3.5"><?= component_badge($p['fase'], $faseColor) ?></td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2 w-32">
                                <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full <?= $progColor ?> transition-all duration-500" style="width: <?= $p['progress'] ?>%"></div>
                                </div>
                                <span class="text-xs font-medium text-gray-500 w-9 text-right"><?= $p['progress'] ?>%</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-500 text-xs"><?= $p['dokter'] ?></td>
                        <td class="px-5 py-3.5"><?= component_badge($p['status'], $statusColor) ?></td>
                        <td class="px-5 py-3.5">
                            <button onclick="openModal('detailModal')" class="text-teal-600 hover:text-teal-700 text-xs font-medium hover:underline">Detail</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
            <span>Menampilkan 1-<?= count($patients) ?> dari <?= count($patients) ?> pasien</span>
            <div class="flex items-center gap-1">
                <button class="px-3 py-1 rounded-lg hover:bg-gray-100 transition-colors">←</button>
                <button class="px-3 py-1 rounded-lg bg-teal-50 text-teal-700 font-medium">1</button>
                <button class="px-3 py-1 rounded-lg hover:bg-gray-100 transition-colors">2</button>
                <button class="px-3 py-1 rounded-lg hover:bg-gray-100 transition-colors">→</button>
            </div>
        </div>
    </div>

</div>
</main>

<!-- Detail Patient Modal -->
<?= component_modal('detailModal', [
    'title' => 'Detail Rekam Medis — Ahmad Fauzi',
    'size' => 'lg',
    'content' => '
    <div class="space-y-5">
        <!-- Patient Info -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-400 mb-0.5">No. RM</p>
                <p class="text-sm font-semibold text-gray-800">RM-2024-0142</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">NIK</p>
                <p class="text-sm font-semibold text-gray-800">3201XXXXXXXXXX</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Tgl Lahir</p>
                <p class="text-sm font-semibold text-gray-800">15 Mar 1981</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Tipe Pasien</p>
                <p class="text-sm font-semibold text-gray-800">Baru</p>
            </div>
        </div>

        <!-- Fase Progress -->
        <div class="bg-gradient-to-r from-teal-50 to-emerald-50 rounded-xl p-4 border border-teal-100">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-semibold text-teal-800">Fase Intensif — Bulan ke-2</p>
                <span class="text-xs font-bold text-teal-600">35%</span>
            </div>
            <div class="h-2 bg-white rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-teal-500 to-emerald-400 rounded-full" style="width:35%"></div>
            </div>
            <p class="text-xs text-teal-600 mt-2">Mulai: 15 Feb 2026 • Target selesai: 15 Agu 2026</p>
        </div>

        <!-- Timeline Hasil Lab -->
        <div>
            <h4 class="text-sm font-semibold text-gray-800 mb-3">Hasil Laboratorium</h4>
            <div class="relative border-l-2 border-teal-200 pl-5 space-y-4 ml-2">
                <div class="relative">
                    <div class="absolute -left-[27px] w-3 h-3 bg-emerald-400 rounded-full border-2 border-white"></div>
                    <p class="text-xs text-gray-400">10 Apr 2026</p>
                    <p class="text-sm font-medium text-gray-800">BTA — <span class="text-emerald-600">Negatif</span></p>
                </div>
                <div class="relative">
                    <div class="absolute -left-[27px] w-3 h-3 bg-emerald-400 rounded-full border-2 border-white"></div>
                    <p class="text-xs text-gray-400">12 Mar 2026</p>
                    <p class="text-sm font-medium text-gray-800">Rontgen — <span class="text-emerald-600">Perbaikan infiltrat paru</span></p>
                </div>
                <div class="relative">
                    <div class="absolute -left-[27px] w-3 h-3 bg-amber-400 rounded-full border-2 border-white"></div>
                    <p class="text-xs text-gray-400">15 Feb 2026</p>
                    <p class="text-sm font-medium text-gray-800">GeneXpert — <span class="text-amber-600">MTB Detected, Rif Sensitive</span></p>
                </div>
                <div class="relative">
                    <div class="absolute -left-[27px] w-3 h-3 bg-red-400 rounded-full border-2 border-white"></div>
                    <p class="text-xs text-gray-400">15 Feb 2026</p>
                    <p class="text-sm font-medium text-gray-800">BTA — <span class="text-red-500">Positif +1</span></p>
                </div>
            </div>
        </div>
    </div>',
    'footer' => component_button('Tutup', ['variant' => 'outline', 'onclick' => "closeModal('detailModal')"])
        . ' ' . component_button('Edit Rekam Medis', ['variant' => 'primary', 'class' => '!bg-teal-600 hover:!bg-teal-700'])
]) ?>

<!-- Add Patient Modal -->
<?= component_modal('addPatientModal', [
    'title' => 'Tambah Pasien Baru',
    'size' => 'lg',
    'content' => '
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        ' . component_input('nama_pasien', ['label' => 'Nama Lengkap', 'placeholder' => 'Masukkan nama...', 'required' => true]) . '
        ' . component_input('nik', ['label' => 'NIK', 'placeholder' => '16 digit NIK...']) . '
        ' . component_input('tgl_lahir', ['label' => 'Tanggal Lahir', 'type' => 'date', 'required' => true]) . '
        ' . component_input('no_telepon', ['label' => 'No. Telepon', 'placeholder' => '08xxxxxxxxxx']) . '
        ' . component_input('alamat', ['label' => 'Alamat', 'type' => 'textarea', 'placeholder' => 'Alamat lengkap...', 'class' => 'sm:col-span-2']) . '
    </div>',
    'footer' => component_button('Batal', ['variant' => 'outline', 'onclick' => "closeModal('addPatientModal')"])
        . ' ' . component_button('Simpan Pasien', ['variant' => 'primary', 'class' => '!bg-teal-600 hover:!bg-teal-700', 'onclick' => "closeModal('addPatientModal')"])
]) ?>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
