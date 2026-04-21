<?php
/**
 * Patients List (READ CRUD)
 */
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/config/database3.php';

requireLogin(BASE_URL . '/modules/modul_3/login.php');
startSession();

$user = getCurrentUser();
global $db;

$success = getFlash('success');
$error   = getFlash('error');

// Get patients
$patients = [];
try {
    $stmt = $db->prepare("SELECT * FROM modul3_patients WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user['id']]);
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Gagal memuat data pasien.";
}

// Get Aggregate Statistics
try {
    $stmtStats = $db->prepare("
        SELECT 
            COUNT(id) as total_scan,
            SUM(CASE WHEN status != 'Normal / AMAN' AND confidence_score > 50 THEN 1 ELSE 0 END) as total_tbc,
            SUM(CASE WHEN status = 'Normal / AMAN' OR confidence_score <= 50 THEN 1 ELSE 0 END) as total_normal
        FROM modul3_history 
        WHERE user_id = ?
    ");
    $stmtStats->execute([$user['id']]);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats = ['total_scan' => 0, 'total_tbc' => 0, 'total_normal' => 0];
}

$pageTitle = 'Daftar Pasien - PulmoAI';
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
    <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 py-3 shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="index.php" class="flex items-center gap-2 text-gray-500 hover:text-gray-900 transition-colors font-medium">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i> Dashboard PulmoAI
                </a>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm font-bold opacity-70 text-black">Dr. <?= htmlspecialchars($user['name']) ?></span>
                <a href="logout.php" class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Keluar">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                </a>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 mt-12 animate-fade-in">
        
        <!-- Dashboard Statistics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col justify-center">
                <div class="flex items-center gap-2 text-gray-500 mb-2">
                    <i data-lucide="users" class="w-4 h-4"></i> <span class="text-xs font-bold tracking-wider">TOTAL PASIEN</span>
                </div>
                <div class="text-3xl font-black text-gray-900"><?= count($patients) ?></div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col justify-center">
                <div class="flex items-center gap-2 text-blue-500 mb-2">
                    <i data-lucide="scan" class="w-4 h-4"></i> <span class="text-xs font-bold tracking-wider">TOTAL RONTGEN</span>
                </div>
                <div class="text-3xl font-black text-blue-600"><?= htmlspecialchars($stats['total_scan'] ?? 0) ?></div>
            </div>
            <div class="bg-red-50 rounded-2xl p-5 border border-red-100 shadow-sm flex flex-col justify-center">
                <div class="flex items-center gap-2 text-red-500 mb-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i> <span class="text-xs font-bold tracking-wider">INDIKASI TBC</span>
                </div>
                <div class="text-3xl font-black text-red-600"><?= htmlspecialchars($stats['total_tbc'] ?? 0) ?></div>
            </div>
            <div class="bg-green-50 rounded-2xl p-5 border border-green-100 shadow-sm flex flex-col justify-center">
                <div class="flex items-center gap-2 text-green-600 mb-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> <span class="text-xs font-bold tracking-wider">NORMAL</span>
                </div>
                <div class="text-3xl font-black text-green-700"><?= htmlspecialchars($stats['total_normal'] ?? 0) ?></div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#5B88D6]/10 text-[#5B88D6] mb-3">
                    <i data-lucide="folder-open" class="w-4 h-4"></i>
                    <span class="text-xs font-semibold tracking-wide">REKAM MEDIS</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Manajemen Data Pasien</h1>
                <p class="text-gray-500 mt-1">Kelola data pasien Anda untuk keperluan skrining PulmoAI.</p>
            </div>
            <a href="patient_form.php" class="inline-flex items-center justify-center gap-2 gradient-primary text-white font-bold py-3 px-6 rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95">
                <i data-lucide="user-plus" class="w-5 h-5"></i> Tambah Pasien Baru
            </a>
        </div>

        <?php if ($success): ?>
            <div class="mb-6 flex items-center gap-2 px-4 py-3 bg-green-50 text-green-700 text-sm rounded-xl border border-green-100">
                <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-6 flex items-center gap-2 px-4 py-3 bg-red-50 text-red-700 text-sm rounded-xl border border-red-100">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($patients)): ?>
            <div class="bg-white border-2 border-dashed border-gray-200 rounded-3xl p-16 text-center shadow-sm">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 mb-4">
                    <i data-lucide="users" class="w-10 h-10 text-gray-400"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Belum Ada Data Pasien</h3>
                <p class="text-gray-500 mb-6 max-w-sm mx-auto">Anda belum mendaftarkan satupun pasien ke dalam sistem PulmoAI.</p>
                <a href="patient_form.php" class="inline-flex border border-gray-300 bg-white text-gray-700 font-semibold py-2.5 px-5 rounded-lg hover:bg-gray-50 transition-colors">
                    Daftarkan Pasien Pertama
                </a>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($patients as $p): ?>
                <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-6 hover:shadow-md transition-shadow relative group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#5B88D6]/20 to-[#7C9CE1]/20 flex items-center justify-center text-[#5B88D6] font-bold text-lg">
                                <?= strtoupper(substr($p['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900 leading-tight"><?= htmlspecialchars($p['name']) ?></h3>
                                <p class="text-sm text-gray-500 font-medium">Usia: <?= htmlspecialchars($p['age']) ?> thn • <?= htmlspecialchars($p['gender']) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-xl p-4 mb-4 text-sm text-gray-600 line-clamp-2 h-16">
                        <?= htmlspecialchars($p['symptoms'] ?: 'Tidak ada catatan gejala yang dilampirkan.') ?>
                    </div>

                    <div class="flex items-center justify-between border-t border-gray-100 pt-4 mt-auto">
                        <span class="text-xs text-gray-400 font-medium">
                            <i data-lucide="clock" class="w-3 h-3 inline-block -mt-0.5"></i> <?= date('d M Y', strtotime($p['created_at'])) ?>
                        </span>
                        <div class="flex items-center gap-2">
                            <a href="patient_detail.php?id=<?= $p['id'] ?>" class="p-2 rounded-lg text-[#5B88D6] hover:bg-[#5B88D6]/10 transition-colors" title="Lihat Rekam Medis">
                                <i data-lucide="folder-search" class="w-4 h-4"></i>
                            </a>
                            <a href="patient_form.php?id=<?= $p['id'] ?>" class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Edit Data">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </a>
                            <form action="patient_process.php" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pasien ini secara permanen?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="p-2 rounded-lg text-red-500 hover:bg-red-50 transition-colors" title="Hapus Data">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
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
