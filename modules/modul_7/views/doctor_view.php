<?php
/**
 * Doctor View - Queue and Review
 */
require_once __DIR__ . '/../../../config/database.php';

$db = getDBConnection();

// Fetch pending reviews
$stmt = $db->query("
    SELECT s.*, u.name as patient_name, u.email as patient_email 
    FROM modul7_screenings s
    JOIN users u ON s.patient_id = u.id
    WHERE s.status = 'pending_doctor_review'
    ORDER BY s.created_at ASC
");
$pendingCases = $stmt->fetchAll();

// Fetch recently reviewed (for context)
$stmt = $db->prepare("
    SELECT s.*, u.name as patient_name 
    FROM modul7_screenings s
    JOIN users u ON s.patient_id = u.id
    WHERE s.status = 'reviewed_by_doctor' AND s.doctor_id = :did
    ORDER BY s.created_at DESC LIMIT 5
");
$stmt->execute(['did' => $user['id']]);
$reviewedCases = $stmt->fetchAll();
?>

<!-- Interface for Doctor -->
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800">Panel Tinjauan Spesialis Kulit</h2>
    <p class="text-sm text-gray-500 mt-1">Daftar antrean pasien dengan indikasi AI tingkat keparahan parah.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Pending Queue -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Antrean Pemeriksaan (Pending)</h3>
                <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded-full"><?= count($pendingCases) ?> Kasus</span>
            </div>

            <?php if (count($pendingCases) === 0): ?>
            <div class="p-10 text-center text-gray-500 text-sm">
                Tidak ada pasien dalam antrean saat ini. Kerja bagus, Dok!
            </div>
            <?php else: ?>
            <ul class="divide-y divide-gray-100">
                <?php foreach ($pendingCases as $case): ?>
                <li class="p-6 hover:bg-gray-50 transition-colors cursor-pointer group" onclick="openReviewModal(<?= htmlspecialchars(json_encode($case)) ?>)">
                    <div class="flex items-start justify-between">
                        <div class="flex gap-4">
                            <!-- Avatar Placeholder -->
                            <div class="w-12 h-12 bg-cyan-100 text-cyan-700 rounded-full flex items-center justify-center font-bold text-lg shrink-0">
                                <?= substr($case['patient_name'], 0, 1) ?>
                            </div>
                            <div>
                                <h4 class="text-gray-900 font-bold group-hover:text-cyan-600 transition-colors"><?= htmlspecialchars($case['patient_name']) ?></h4>
                                <p class="text-xs text-gray-500 mb-2"><?= htmlspecialchars($case['patient_email']) ?> &middot; Diunggah <?= date('d M Y H:i', strtotime($case['created_at'])) ?></p>
                                
                                <div class="flex gap-2">
                                    <span class="inline-flex items-center gap-1 bg-pink-50 text-pink-700 font-medium text-xs px-2 py-0.5 rounded border border-pink-100">
                                        Papula: <?= $case['ml_papule_count'] ?>
                                    </span>
                                    <span class="inline-flex items-center gap-1 bg-yellow-50 text-yellow-700 font-medium text-xs px-2 py-0.5 rounded border border-yellow-100">
                                        Pustula: <?= $case['ml_pustule_count'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <button class="bg-white border border-gray-200 text-gray-800 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm shadow-sm transition-colors opacity-0 group-hover:opacity-100 font-medium">Tinjau Data</button>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- History / Side Panel -->
    <div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">Baru Saja Ditinjau</h3>
            <?php if (count($reviewedCases) === 0): ?>
            <p class="text-xs text-gray-400">Belum ada aktivitas hari ini.</p>
            <?php else: ?>
            <ul class="space-y-4">
                <?php foreach ($reviewedCases as $rc): ?>
                <li class="border-l-2 border-green-500 pl-3">
                    <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($rc['patient_name']) ?></p>
                    <p class="text-xs text-gray-500">Selesai pada <?= date('d M H:i', strtotime($rc['created_at'])) ?></p>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Dialog -->
<div id="reviewModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeReviewModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
            <form action="<?= BASE_URL ?>/modules/modul_7/api/submit_review.php" method="POST">
                <input type="hidden" name="screening_id" id="modal_id">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-bold leading-6 text-gray-900 mb-4" id="modal_patient_name">Detail Pasien</h3>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-100 rounded-lg aspect-square flex items-center justify-center border border-gray-200">
                                    <span class="text-gray-400 text-sm">Foto Pasien (Dummy AI)</span>
                                </div>
                                <div class="flex flex-col justify-center">
                                    <h4 class="text-sm font-bold text-gray-700 mb-2">Simpulan Deep Learning (Severe)</h4>
                                    <ul class="text-sm text-gray-600 space-y-2 font-mono">
                                        <li>Papula (Merah): <span id="modal_pap">0</span></li>
                                        <li>Pustula (Nanah): <span id="modal_pus">0</span></li>
                                        <li>Blackhead: <span id="modal_bla">0</span></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mt-6">
                                <label for="doctor_notes" class="block text-sm font-medium leading-6 text-gray-900">Catatan Medis & Resep Obat</label>
                                <div class="mt-2">
                                    <textarea id="doctor_notes" name="doctor_notes" rows="4" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-cyan-600 sm:text-sm sm:leading-6 px-3" required></textarea>
                                </div>
                                <p class="mt-2 text-xs leading-6 text-gray-500">Tuliskan diagosis berdasarkan foto, setujui/tolak klaim AI, serta obat yang diresepkan.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="submit" class="inline-flex w-full justify-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-500 sm:ml-3 sm:w-auto">Simpan & Selesaikan</button>
                    <button type="button" onclick="closeReviewModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openReviewModal(caseData) {
    document.getElementById('reviewModal').classList.remove('hidden');
    document.getElementById('modal_id').value = caseData.id;
    document.getElementById('modal_patient_name').textContent = "Pemeriksaan atas " + caseData.patient_name;
    document.getElementById('modal_pap').textContent = caseData.ml_papule_count;
    document.getElementById('modal_pus').textContent = caseData.ml_pustule_count;
    document.getElementById('modal_bla').textContent = caseData.ml_blackhead_count;
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.add('hidden');
    document.getElementById('doctor_notes').value = ''; 
}
</script>
