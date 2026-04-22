<?php
/**
 * SIMRS-TB — Rekam Medis Digital
 * 
 * Full CRUD: Daftar pasien, tambah, edit, hapus, detail rekam medis
 * Data diambil dari database via AJAX ke api.php
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'SIMRS-TB — Rekam Medis';
$activePage = 'rekam-medis';
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
            'onclick' => "openAddModal()"
        ]) ?>
    </div>

    <!-- Flash Message -->
    <div id="flashMessage" class="hidden mb-4"></div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[200px]">
            <input type="text" id="searchInput" placeholder="Cari nama / No. RM..."
                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white transition-all">
        </div>
        <select id="filterFase" class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
            <option value="">Semua Fase</option>
            <option value="Intensif">Intensif</option>
            <option value="Lanjutan">Lanjutan</option>
            <option value="Selesai">Selesai</option>
            <option value="Belum Mulai">Belum Mulai</option>
        </select>
        <select id="filterStatus" class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
            <option value="">Semua Status</option>
            <option value="Aktif">Aktif</option>
            <option value="Sembuh">Sembuh</option>
            <option value="Putus Obat">Putus Obat</option>
            <option value="Meninggal">Meninggal</option>
        </select>
    </div>

    <!-- Patient Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="patientTable">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pasien</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. RM</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fase</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Progress</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody id="patientTableBody" class="divide-y divide-gray-50">
                    <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Memuat data...
                    </td></tr>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500" id="paginationArea">
            <span id="paginationInfo">Memuat...</span>
            <div class="flex items-center gap-1" id="paginationButtons"></div>
        </div>
    </div>

</div>
</main>

<!-- ═══ ADD/EDIT PATIENT MODAL ═══ -->
<div id="patientModal" class="fixed inset-0 z-[100] hidden">
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('patientModal')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-2xl max-h-[85vh] flex flex-col transform scale-95 opacity-0 transition-all duration-200" id="patientModal-panel">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800" id="modalTitle">Tambah Pasien Baru</h3>
                <button onclick="closeModal('patientModal')" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="patientForm" class="px-6 py-5 overflow-y-auto flex-1" onsubmit="return false;">
                <input type="hidden" id="f_id" name="id">
                <input type="hidden" id="f_action" name="action" value="create_patient">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                        <input type="text" id="f_nama" name="nama" required placeholder="Masukkan nama..."
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">NIK</label>
                        <input type="text" id="f_nik" name="nik" placeholder="16 digit NIK..." maxlength="16"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Lahir <span class="text-red-400">*</span></label>
                        <input type="date" id="f_tanggal_lahir" name="tanggal_lahir" required
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Kelamin <span class="text-red-400">*</span></label>
                        <select id="f_jenis_kelamin" name="jenis_kelamin" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                            <option value="">Pilih...</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">No. Telepon</label>
                        <input type="text" id="f_no_telepon" name="no_telepon" placeholder="08xxxxxxxxxx"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Pekerjaan</label>
                        <input type="text" id="f_pekerjaan" name="pekerjaan" placeholder="Pekerjaan..."
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white transition-all">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Alamat</label>
                        <textarea id="f_alamat" name="alamat" rows="2" placeholder="Alamat lengkap..."
                                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white transition-all resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Kategori TB</label>
                        <select id="f_kategori_tb" name="kategori_tb"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                            <option value="Paru">Paru</option>
                            <option value="Ekstra Paru">Ekstra Paru</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipe Pasien</label>
                        <select id="f_tipe_pasien" name="tipe_pasien"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                            <option value="Baru">Baru</option>
                            <option value="Kambuh">Kambuh</option>
                            <option value="Gagal">Gagal</option>
                            <option value="Putus Obat">Putus Obat</option>
                            <option value="Pindahan">Pindahan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Fase Pengobatan</label>
                        <select id="f_fase_pengobatan" name="fase_pengobatan"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                            <option value="Belum Mulai">Belum Mulai</option>
                            <option value="Intensif">Intensif</option>
                            <option value="Lanjutan">Lanjutan</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                        <select id="f_status" name="status"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                            <option value="Aktif">Aktif</option>
                            <option value="Sembuh">Sembuh</option>
                            <option value="Pengobatan Lengkap">Pengobatan Lengkap</option>
                            <option value="Gagal">Gagal</option>
                            <option value="Meninggal">Meninggal</option>
                            <option value="Putus Obat">Putus Obat</option>
                            <option value="Pindah">Pindah</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tgl Mulai Pengobatan</label>
                        <input type="date" id="f_tanggal_mulai_pengobatan" name="tanggal_mulai_pengobatan"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tgl Target Selesai</label>
                        <input type="date" id="f_tanggal_target_selesai" name="tanggal_target_selesai"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white transition-all">
                    </div>
                </div>
                <div id="formErrors" class="hidden mt-4 text-sm text-red-600 bg-red-50 border border-red-100 rounded-xl p-3"></div>
            </form>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-end gap-3">
                <button onclick="closeModal('patientModal')" class="inline-flex items-center justify-center gap-2 font-medium rounded-xl text-sm px-4 py-2.5 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 transition-all duration-200">Batal</button>
                <button onclick="submitPatientForm()" id="submitBtn" class="inline-flex items-center justify-center gap-2 font-medium rounded-xl text-sm px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white shadow-sm shadow-teal-500/20 transition-all duration-200">
                    <svg class="w-4 h-4 animate-spin hidden" id="submitSpinner" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span id="submitLabel">Simpan Pasien</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ═══ DETAIL PATIENT MODAL ═══ -->
<div id="detailModal" class="fixed inset-0 z-[100] hidden">
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('detailModal')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-2xl max-h-[85vh] flex flex-col transform scale-95 opacity-0 transition-all duration-200" id="detailModal-panel">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800" id="detailTitle">Detail Pasien</h3>
                <button onclick="closeModal('detailModal')" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-6 py-5 overflow-y-auto flex-1" id="detailContent">
                <div class="text-center py-12 text-gray-400">
                    <svg class="w-8 h-8 mx-auto mb-2 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Memuat...
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-end gap-3">
                <button onclick="closeModal('detailModal')" class="inline-flex items-center justify-center gap-2 font-medium rounded-xl text-sm px-4 py-2.5 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 transition-all duration-200">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══ DELETE CONFIRMATION MODAL ═══ -->
<div id="deleteModal" class="fixed inset-0 z-[100] hidden">
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('deleteModal')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-sm transform scale-95 opacity-0 transition-all duration-200" id="deleteModal-panel">
            <div class="px-6 py-5 text-center">
                <div class="w-14 h-14 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Hapus Pasien?</h3>
                <p class="text-sm text-gray-500" id="deleteMessage">Data pasien dan semua rekam medis terkait akan dihapus permanen.</p>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-end gap-3">
                <button onclick="closeModal('deleteModal')" class="inline-flex items-center justify-center gap-2 font-medium rounded-xl text-sm px-4 py-2.5 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 transition-all duration-200">Batal</button>
                <button onclick="confirmDelete()" class="inline-flex items-center justify-center gap-2 font-medium rounded-xl text-sm px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white shadow-sm shadow-red-500/20 transition-all duration-200">Hapus</button>
            </div>
        </div>
    </div>
</div>

<script>
// ═══════════════════════════════════════
// STATE
// ═══════════════════════════════════════
let currentPage = 1;
let deletePatientId = null;
let debounceTimer = null;

// ═══════════════════════════════════════
// LOAD PATIENTS (READ)
// ═══════════════════════════════════════
function loadPatients(page = 1) {
    currentPage = page;
    const search = document.getElementById('searchInput').value;
    const fase = document.getElementById('filterFase').value;
    const status = document.getElementById('filterStatus').value;

    const params = new URLSearchParams({
        action: 'get_patients',
        page: page,
        search: search,
        fase: fase,
        status: status
    });

    fetch('api.php?' + params)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                showFlash(data.message, 'error');
                return;
            }
            renderTable(data.patients);
            renderPagination(data);
        })
        .catch(err => {
            document.getElementById('patientTableBody').innerHTML = 
                '<tr><td colspan="7" class="px-5 py-12 text-center text-red-400">Gagal memuat data. Pastikan database sudah diimport.</td></tr>';
        });
}

function renderTable(patients) {
    const tbody = document.getElementById('patientTableBody');
    if (!patients.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">' +
            '<svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>' +
            'Belum ada data pasien</td></tr>';
        return;
    }

    tbody.innerHTML = patients.map(p => {
        const initials = p.nama.substring(0, 2).toUpperCase();
        const umur = p.umur || '—';
        const jk = p.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
        const progress = Math.max(0, Math.min(100, p.progress || 0));
        const progColor = progress >= 70 ? 'bg-emerald-400' : (progress >= 30 ? 'bg-teal-400' : 'bg-amber-400');
        
        const faseColor = {'Intensif':'bg-amber-50 text-amber-700 border border-amber-100','Lanjutan':'bg-blue-50 text-blue-700 border border-blue-100','Selesai':'bg-green-50 text-green-700 border border-green-100'}[p.fase_pengobatan] || 'bg-gray-100 text-gray-600';
        const statusColor = {'Aktif':'bg-blue-50 text-blue-700 border border-blue-100','Sembuh':'bg-green-50 text-green-700 border border-green-100','Putus Obat':'bg-red-50 text-red-700 border border-red-100','Meninggal':'bg-gray-100 text-gray-600'}[p.status] || 'bg-gray-100 text-gray-600';

        return `<tr class="hover:bg-teal-50/30 transition-colors group">
            <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-gradient-to-br from-teal-100 to-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                        <span class="text-teal-700 text-xs font-bold">${initials}</span>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">${esc(p.nama)}</p>
                        <p class="text-xs text-gray-400">${umur} thn • ${jk}</p>
                    </div>
                </div>
            </td>
            <td class="px-5 py-3.5 text-gray-500 font-mono text-xs">${esc(p.no_rm)}</td>
            <td class="px-5 py-3.5 text-gray-600">${esc(p.kategori_tb)}</td>
            <td class="px-5 py-3.5"><span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full ${faseColor}">${esc(p.fase_pengobatan)}</span></td>
            <td class="px-5 py-3.5">
                <div class="flex items-center gap-2 w-32">
                    <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden"><div class="h-full rounded-full ${progColor} transition-all duration-500" style="width:${progress}%"></div></div>
                    <span class="text-xs font-medium text-gray-500 w-9 text-right">${progress}%</span>
                </div>
            </td>
            <td class="px-5 py-3.5"><span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full ${statusColor}">${esc(p.status)}</span></td>
            <td class="px-5 py-3.5">
                <div class="flex items-center gap-1">
                    <button onclick="viewPatient(${p.id})" class="text-teal-600 hover:text-teal-700 text-xs font-medium hover:underline p-1" title="Detail">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                    <button onclick="editPatient(${p.id})" class="text-blue-600 hover:text-blue-700 text-xs font-medium p-1" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button onclick="deletePatient(${p.id}, '${esc(p.nama)}')" class="text-red-400 hover:text-red-600 text-xs font-medium p-1" title="Hapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function renderPagination(data) {
    document.getElementById('paginationInfo').textContent = 
        `Menampilkan ${(data.page-1)*data.limit+1}-${Math.min(data.page*data.limit, data.total)} dari ${data.total} pasien`;
    
    const btns = document.getElementById('paginationButtons');
    let html = '';
    if (data.page > 1) html += `<button onclick="loadPatients(${data.page-1})" class="px-3 py-1 rounded-lg hover:bg-gray-100 transition-colors">←</button>`;
    for (let i = 1; i <= data.pages; i++) {
        html += `<button onclick="loadPatients(${i})" class="px-3 py-1 rounded-lg ${i===data.page?'bg-teal-50 text-teal-700 font-medium':'hover:bg-gray-100'} transition-colors">${i}</button>`;
    }
    if (data.page < data.pages) html += `<button onclick="loadPatients(${data.page+1})" class="px-3 py-1 rounded-lg hover:bg-gray-100 transition-colors">→</button>`;
    btns.innerHTML = html;
}

// ═══════════════════════════════════════
// CREATE / UPDATE
// ═══════════════════════════════════════
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Pasien Baru';
    document.getElementById('submitLabel').textContent = 'Simpan Pasien';
    document.getElementById('f_action').value = 'create_patient';
    document.getElementById('patientForm').reset();
    document.getElementById('f_id').value = '';
    document.getElementById('formErrors').classList.add('hidden');
    openModal('patientModal');
}

function editPatient(id) {
    fetch('api.php?action=get_patient&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { showFlash(data.message, 'error'); return; }
            const p = data.patient;
            document.getElementById('modalTitle').textContent = 'Edit Pasien — ' + p.nama;
            document.getElementById('submitLabel').textContent = 'Update Pasien';
            document.getElementById('f_action').value = 'update_patient';
            document.getElementById('f_id').value = p.id;
            document.getElementById('f_nama').value = p.nama;
            document.getElementById('f_nik').value = p.nik || '';
            document.getElementById('f_tanggal_lahir').value = p.tanggal_lahir;
            document.getElementById('f_jenis_kelamin').value = p.jenis_kelamin;
            document.getElementById('f_no_telepon').value = p.no_telepon || '';
            document.getElementById('f_pekerjaan').value = p.pekerjaan || '';
            document.getElementById('f_alamat').value = p.alamat || '';
            document.getElementById('f_kategori_tb').value = p.kategori_tb;
            document.getElementById('f_tipe_pasien').value = p.tipe_pasien;
            document.getElementById('f_fase_pengobatan').value = p.fase_pengobatan;
            document.getElementById('f_status').value = p.status;
            document.getElementById('f_tanggal_mulai_pengobatan').value = p.tanggal_mulai_pengobatan || '';
            document.getElementById('f_tanggal_target_selesai').value = p.tanggal_target_selesai || '';
            document.getElementById('formErrors').classList.add('hidden');
            openModal('patientModal');
        });
}

function submitPatientForm() {
    const btn = document.getElementById('submitBtn');
    const spinner = document.getElementById('submitSpinner');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    const form = document.getElementById('patientForm');
    const formData = new FormData(form);

    fetch('api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            spinner.classList.add('hidden');
            if (!data.success) {
                const errDiv = document.getElementById('formErrors');
                if (data.errors) {
                    errDiv.innerHTML = Object.values(data.errors).map(e => '• ' + e).join('<br>');
                } else {
                    errDiv.textContent = data.message;
                }
                errDiv.classList.remove('hidden');
                return;
            }
            closeModal('patientModal');
            showFlash(data.message, 'success');
            loadPatients(currentPage);
        })
        .catch(() => {
            btn.disabled = false;
            spinner.classList.add('hidden');
            showFlash('Gagal menyimpan data', 'error');
        });
}

// ═══════════════════════════════════════
// DELETE
// ═══════════════════════════════════════
function deletePatient(id, nama) {
    deletePatientId = id;
    document.getElementById('deleteMessage').textContent = `Data pasien "${nama}" dan semua rekam medis terkait akan dihapus permanen.`;
    openModal('deleteModal');
}

function confirmDelete() {
    if (!deletePatientId) return;
    const formData = new FormData();
    formData.append('action', 'delete_patient');
    formData.append('id', deletePatientId);

    fetch('api.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            closeModal('deleteModal');
            if (data.success) {
                showFlash(data.message, 'success');
                loadPatients(currentPage);
            } else {
                showFlash(data.message, 'error');
            }
        });
}

// ═══════════════════════════════════════
// VIEW DETAIL
// ═══════════════════════════════════════
function viewPatient(id) {
    document.getElementById('detailContent').innerHTML = '<div class="text-center py-12 text-gray-400"><svg class="w-8 h-8 mx-auto mb-2 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Memuat...</div>';
    openModal('detailModal');

    fetch('api.php?action=get_patient&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { document.getElementById('detailContent').innerHTML = '<p class="text-red-500">'+data.message+'</p>'; return; }
            const p = data.patient;
            const jk = p.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
            const progress = Math.max(0, Math.min(100, p.progress || 0));
            document.getElementById('detailTitle').textContent = 'Detail — ' + p.nama;
            
            let labHtml = '';
            if (data.lab_results && data.lab_results.length) {
                labHtml = data.lab_results.map(l => {
                    const color = l.hasil && l.hasil.toLowerCase().includes('negatif') ? 'emerald' : 'amber';
                    return `<div class="relative">
                        <div class="absolute -left-[27px] w-3 h-3 bg-${color}-400 rounded-full border-2 border-white"></div>
                        <p class="text-xs text-gray-400">${formatDate(l.tanggal_pemeriksaan)}</p>
                        <p class="text-sm font-medium text-gray-800">${esc(l.jenis_pemeriksaan)} — <span class="text-${color}-600">${esc(l.hasil)}</span></p>
                    </div>`;
                }).join('');
            } else {
                labHtml = '<p class="text-xs text-gray-400">Belum ada hasil lab</p>';
            }

            document.getElementById('detailContent').innerHTML = `
                <div class="space-y-5">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div><p class="text-xs text-gray-400 mb-0.5">No. RM</p><p class="text-sm font-semibold text-gray-800">${esc(p.no_rm)}</p></div>
                        <div><p class="text-xs text-gray-400 mb-0.5">NIK</p><p class="text-sm font-semibold text-gray-800">${p.nik || '—'}</p></div>
                        <div><p class="text-xs text-gray-400 mb-0.5">Tgl Lahir</p><p class="text-sm font-semibold text-gray-800">${formatDate(p.tanggal_lahir)}</p></div>
                        <div><p class="text-xs text-gray-400 mb-0.5">Jenis Kelamin</p><p class="text-sm font-semibold text-gray-800">${jk}</p></div>
                        <div><p class="text-xs text-gray-400 mb-0.5">Telepon</p><p class="text-sm font-semibold text-gray-800">${p.no_telepon || '—'}</p></div>
                        <div><p class="text-xs text-gray-400 mb-0.5">Pekerjaan</p><p class="text-sm font-semibold text-gray-800">${p.pekerjaan || '—'}</p></div>
                        <div class="sm:col-span-2"><p class="text-xs text-gray-400 mb-0.5">Alamat</p><p class="text-sm font-semibold text-gray-800">${p.alamat || '—'}</p></div>
                    </div>
                    <div class="bg-gradient-to-r from-teal-50 to-emerald-50 rounded-xl p-4 border border-teal-100">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-semibold text-teal-800">Fase ${esc(p.fase_pengobatan)} — ${esc(p.tipe_pasien)}</p>
                            <span class="text-xs font-bold text-teal-600">${progress}%</span>
                        </div>
                        <div class="h-2 bg-white rounded-full overflow-hidden"><div class="h-full bg-gradient-to-r from-teal-500 to-emerald-400 rounded-full" style="width:${progress}%"></div></div>
                        <p class="text-xs text-teal-600 mt-2">Mulai: ${p.tanggal_mulai_pengobatan ? formatDate(p.tanggal_mulai_pengobatan) : '—'} • Target: ${p.tanggal_target_selesai ? formatDate(p.tanggal_target_selesai) : '—'}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-800 mb-3">Hasil Laboratorium</h4>
                        <div class="relative border-l-2 border-teal-200 pl-5 space-y-4 ml-2">${labHtml}</div>
                    </div>
                </div>`;
        });
}

// ═══════════════════════════════════════
// UTILITIES
// ═══════════════════════════════════════
function esc(str) {
    if (!str) return '';
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'});
}

function showFlash(message, type) {
    const el = document.getElementById('flashMessage');
    const colors = {
        success: 'bg-green-50 border-green-200 text-green-800',
        error: 'bg-red-50 border-red-200 text-red-800',
        info: 'bg-blue-50 border-blue-200 text-blue-800'
    };
    el.className = `flex items-center gap-3 px-4 py-3.5 rounded-xl border ${colors[type] || colors.info} mb-4`;
    el.innerHTML = `<span class="text-sm">${message}</span>
        <button onclick="this.parentElement.classList.add('hidden')" class="ml-auto shrink-0 p-1 rounded hover:bg-black/5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>`;
    el.classList.remove('hidden');
    setTimeout(() => el.classList.add('hidden'), 5000);
}

// ═══════════════════════════════════════
// EVENT LISTENERS
// ═══════════════════════════════════════
document.getElementById('searchInput').addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => loadPatients(1), 400);
});
document.getElementById('filterFase').addEventListener('change', () => loadPatients(1));
document.getElementById('filterStatus').addEventListener('change', () => loadPatients(1));

// Initial load
loadPatients();
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
