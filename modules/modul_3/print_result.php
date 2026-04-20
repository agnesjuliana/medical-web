<?php
/**
 * Cetak Hasil Scan (PDF/Print)
 */
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/config/database3.php';

requireLogin(BASE_URL . '/modules/modul_3/login.php');

$user = getCurrentUser();
global $db;

$history_id = $_GET['id'] ?? null;
if (!$history_id) {
    die("ID Rekam Rontgen tidak valid.");
}

$stmt = $db->prepare("
    SELECT h.*, p.name as patient_name, p.age as patient_age, p.gender as patient_gender 
    FROM modul3_history h 
    LEFT JOIN modul3_patients p ON h.patient_id = p.id 
    WHERE h.id = ? AND h.user_id = ? 
    LIMIT 1
");
$stmt->execute([$history_id, $user['id']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Data tidak ditemukan atau Anda tidak memiliki akses.");
}

$isTbc = $data['status'] !== 'Normal / AMAN' && $data['confidence_score'] > 50; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Hasil #<?= htmlspecialchars($data['id']) ?></title>
    <!-- Tailwind CSS (CDN for printing directly) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #fff; color: #000; }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
        .batas-biru { border-top: 4px solid #5B88D6; }
    </style>
</head>
<body class="p-8 max-w-4xl mx-auto">

    <!-- Print Action Bar -->
    <div class="no-print bg-gray-50 flex justify-between rounded-lg p-4 mb-8 border border-gray-200">
        <p class="text-gray-600 font-medium">Laman ini dikhususkan untuk pencetakan dokumen Medis (A4).</p>
        <div class="gap-2 flex">
            <button onclick="window.close()" class="px-4 py-2 bg-white border border-gray-300 rounded text-gray-700 hover:bg-gray-50">Tutup</button>
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 rounded text-white font-bold hover:bg-blue-700">Print / Simpan PDF</button>
        </div>
    </div>

    <!-- Document Wrapper -->
    <div class="border border-gray-200 shadow-sm p-10 batas-biru rounded-lg" id="print-area">
        <!-- Header / Letterhead -->
        <div class="flex justify-between items-start border-b-2 border-gray-800 pb-6 mb-8">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-gray-900">PulmoAI</h1>
                <p class="text-sm text-gray-500 font-medium tracking-wide mt-1">SISTEM ANALISIS CITRA MEDIS AI</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-gray-400">HASIL PEMERIKSAAN</p>
                <p class="text-sm font-semibold mt-1">Ref ID: #RGT-<?= htmlspecialchars($data['id']) ?></p>
                <p class="text-sm text-gray-500">Tanggal: <?= date('d F Y', strtotime($data['created_at'])) ?></p>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-2 gap-8 mb-10">
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Informasi Pasien</h3>
                <div class="space-y-2">
                    <p class="font-semibold text-lg"><?= htmlspecialchars($data['patient_name'] ?? 'Anonim') ?></p>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($data['patient_age'] ?? '-') ?> Tahun / <?= htmlspecialchars($data['patient_gender'] ?? '-') ?></p>
                </div>
            </div>
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Informasi Dokter Penanggung Jawab</h3>
                <div class="space-y-2">
                    <p class="font-semibold text-lg">Dr. <?= htmlspecialchars($user['name']) ?></p>
                    <p class="text-sm text-gray-600">ID Dokter: <?= htmlspecialchars($user['id']) ?></p>
                </div>
            </div>
        </div>

        <!-- Analisis AI box -->
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 mb-10 flex gap-6 items-center">
            <div class="w-1/3 bg-black rounded-lg aspect-square flex items-center justify-center overflow-hidden border border-gray-300">
                <img src="uploads/<?= htmlspecialchars($data['filename']) ?>" class="object-cover" style="filter: contrast(1.1) grayscale(1);">
            </div>
            <div class="w-2/3">
                <h2 class="text-xl font-bold mb-4">Hasil Skrining Komputer (AI)</h2>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-500 mb-1">Status Indikasi</p>
                    <div class="inline-block px-4 py-2 font-bold rounded-lg <?= $isTbc ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?> border <?= $isTbc ? 'border-red-200' : 'border-green-200' ?>">
                        <?= htmlspecialchars($data['status']) ?>
                    </div>
                </div>

                <div>
                    <p class="text-sm text-gray-500 mb-1">Tingkat Keyakinan AI (Confidence Score)</p>
                    <div class="flex items-center gap-3">
                        <div class="flex-1 h-3 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full <?= $isTbc ? 'bg-red-500' : 'bg-green-500' ?>" style="width: <?= htmlspecialchars($data['confidence_score']) ?>%"></div>
                        </div>
                        <span class="font-bold text-lg"><?= htmlspecialchars($data['confidence_score']) ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disclaimer & Signature -->
        <div class="mt-8 pt-8 border-t border-gray-200 flex justify-between items-end">
            <div class="w-2/3 pr-8">
                <h4 class="font-bold text-sm mb-2 text-gray-800">Catatan Klinis</h4>
                <p class="text-xs text-gray-500 italic leading-relaxed text-justify">
                    Laporan ini dibuat otomatis menggunakan algoritma kecerdasan buatan PulmoAI. Hasil ini merupakan alat bantu skrining awal dan <strong>tidak menggantikan diagnosis klinis definitif</strong>. Harap lakukan pemeriksaan lebih lanjut menggunakan metode biakan atau tes molekuler cepat (TCM) untuk konfirmasi.
                </p>
            </div>
            <div class="w-1/3 text-center">
                <p class="text-sm mb-16">Dokter Pemeriksa,</p>
                <div class="border-b border-gray-400 w-3/4 mx-auto mb-2"></div>
                <p class="font-bold">Dr. <?= htmlspecialchars($user['name']) ?></p>
            </div>
        </div>
    </div>

</body>
</html>
