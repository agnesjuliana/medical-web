<?php
/**
 * Patient Detail (Timeline Rekam Medis)
 */
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/config/database3.php';

requireLogin(BASE_URL . '/modules/modul_3/login.php');
startSession();

$user = getCurrentUser();
global $db;

$patient_id = $_GET['id'] ?? null;
if (!$patient_id) { header('Location: patients.php'); exit; }

// Ambil Data Pasien
$stmt = $db->prepare("SELECT * FROM modul3_patients WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->execute([$patient_id, $user['id']]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    setFlash('error', 'Data pasien tidak ditemukan.');
    header('Location: patients.php');
    exit;
}

// Ambil Seluruh Riwayat Scan Khusus Pasien Ini
$stmt2 = $db->prepare("SELECT * FROM modul3_history WHERE patient_id = ? AND user_id = ? ORDER BY created_at DESC");
$stmt2->execute([$patient_id, $user['id']]);
$histories = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Rekam Medis: ' . htmlspecialchars($patient['name']);
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>

<!-- Include Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>

<style>
:root {
  --background: oklch(0.99 0.005 220);
  --primary: oklch(0.58 0.14 210);
  --accent: oklch(0.72 0.15 195);
  --gradient-primary: linear-gradient(135deg, oklch(0.58 0.14 210), oklch(0.72 0.15 195));
  --glass-bg: oklch(1 0 0 / 0.7);
  --glass-border: oklch(1 0 0 / 0.3);
  --shadow-soft: 0 4px 20px -8px oklch(0.4 0.1 220 / 0.15);
}
.glass { background: var(--glass-bg); backdrop-filter: blur(16px) saturate(180%); border: 1px solid var(--glass-border); }
.gradient-primary { background: var(--gradient-primary); }
</style>

<div class="min-h-screen pb-12" style="background-color: var(--background);">

    <!-- Simple Navbar -->
    <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 py-4 shadow-sm sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 flex items-center justify-between">
            <a href="patients.php" class="flex items-center gap-2 text-gray-500 hover:text-gray-900 transition-colors font-medium">
                <i data-lucide="arrow-left" class="w-5 h-5"></i> Manajemen Pasien
            </a>
            <div class="font-bold text-lg text-gray-800">Detail Rekam Medis</div>
        </div>
    </header>

    <div class="max-w-5xl mx-auto px-4 mt-10">
        
        <!-- Header Info Pasien -->
        <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-soft flex flex-col md:flex-row gap-8 items-start mb-10">
            <div class="w-24 h-24 shrink-0 rounded-2xl bg-gradient-to-br from-[#5B88D6]/20 to-[#7C9CE1]/20 flex items-center justify-center text-[#5B88D6] font-black text-4xl mt-1">
                <?= strtoupper(substr($patient['name'], 0, 1)) ?>
            </div>
            <div class="flex-1">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-green-50 text-green-700 rounded-full text-xs font-bold mb-3 border border-green-100">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Pasien Aktif
                </div>
                <h1 class="text-3xl font-black text-gray-900 mb-2"><?= htmlspecialchars($patient['name']) ?></h1>
                
                <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-500 font-medium mb-6">
                    <span class="flex items-center gap-1.5"><i data-lucide="user" class="w-4 h-4"></i> <?= htmlspecialchars($patient['gender']) ?></span>
                    <span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-4 h-4"></i> Usia <?= htmlspecialchars($patient['age']) ?> Tahun</span>
                    <span class="flex items-center gap-1.5"><i data-lucide="clock" class="w-4 h-4"></i> Terdaftar sejak <?= date('d M Y', strtotime($patient['created_at'])) ?></span>
                </div>

                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-200">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Simtoma / Gejala Penyakit</h3>
                    <p class="text-gray-700 text-sm leading-relaxed"><?= htmlspecialchars($patient['symptoms'] ?: 'Belum ada keluhan khusus yang dicatat.') ?></p>
                </div>
            </div>
            <div class="shrink-0 flex flex-col gap-3">
                <a href="patient_form.php?id=<?= $patient['id'] ?>" class="px-5 py-2.5 bg-white border border-gray-200 hover:border-blue-500 text-blue-600 font-bold rounded-xl text-center transition-all shadow-sm">
                    Ubah Profil Pasien
                </a>
                <a href="index.php" class="px-5 py-2.5 gradient-primary hover:opacity-90 text-white font-bold rounded-xl text-center shadow-md transition-all">
                    + Scan Rontgen Baru
                </a>
            </div>
        </div>

        <!-- Riwayat Scan Pasien -->
        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <i data-lucide="history" class="w-5 h-5 text-[#5B88D6]"></i> Histori Scan AI (<?= count($histories) ?>)
        </h2>

        <?php if (empty($histories)): ?>
            <div class="bg-white border-2 border-dashed border-gray-200 rounded-3xl p-12 text-center shadow-sm">
                <i data-lucide="scan" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Belum Ada Rekam Rontgen AI</h3>
                <p class="text-gray-500 text-sm">Pasien ini belum pernah melakukan pemeriksaan AI PulmoAI.</p>
            </div>
        <?php else: ?>
            <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-6">
                <?php foreach ($histories as $h): ?>
                <?php 
                    $isTbc = $h['status'] !== 'Normal / AMAN' && $h['confidence_score'] > 50; 
                    $badgeBg = $isTbc ? "bg-red-500 text-white" : "bg-green-500 text-white";
                    $barBg = $isTbc ? "bg-red-500" : "bg-green-500";
                ?>
                <div class="bg-white rounded-2xl shadow-soft border border-gray-100 flex flex-col relative group overflow-hidden">
                    <div class="h-40 bg-gray-900 relative flex items-center justify-center overflow-hidden border-b border-gray-100">
                        <img src="uploads/<?= htmlspecialchars($h['filename']) ?>" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 group-hover:scale-105 transition-all duration-500 mix-blend-screen" style="filter: contrast(1.1);">
                        <span class="absolute top-3 right-3 px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md shadow-md <?= $badgeBg ?>">
                            <?= htmlspecialchars($h['status']) ?>
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex items-center mb-4 pb-4 border-b border-gray-100 justify-between">
                            <span class="text-xs font-semibold text-gray-500 flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5"></i> <?= date('d M Y', strtotime($h['created_at'])) ?></span>
                            <a href="print_result.php?id=<?= $h['id'] ?>" target="_blank" class="text-[#5B88D6] hover:text-blue-800" title="Cetak PDF"><i data-lucide="printer" class="w-4 h-4"></i></a>
                        </div>
                        
                        <div class="mt-auto">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-gray-500 flex items-center gap-1 font-bold">
                                    <i data-lucide="activity" class="h-3 w-3"></i> SCORE AI
                                </span>
                                <span class="text-base font-black text-gray-900"><?= htmlspecialchars($h['confidence_score']) ?>%</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full <?= $barBg ?>" style="width: <?= htmlspecialchars($h['confidence_score']) ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    lucide.createIcons();
</script>
<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
