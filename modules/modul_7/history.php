<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
require_once __DIR__ . '/config/database.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'Riwayat Analisis — Dermalyze.AI';

// Ambil riwayat dari database kelompokmu
$history = getPatientScreeningsHistory($user['id']);

require_once __DIR__ . '/../../layout/header.php';
require_once __DIR__ . '/../../layout/navbar.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8" style="font-family: 'Quicksand', sans-serif;">
    
    <div class="mb-6">
        <a href="index.php" class="inline-flex items-center gap-2 text-pink-400 font-bold hover:text-pink-500 transition-all group">
            <div class="p-2 rounded-full bg-white shadow-sm border border-pink-50 group-hover:shadow-pink-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </div>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

    <h1 class="text-3xl font-extrabold text-gray-800 mb-8">Riwayat Analisis Kulit ✨</h1>

    <?php if (empty($history)): ?>
        <div class="bg-white p-12 rounded-3xl shadow-sm text-center border border-pink-50">
            <p class="text-gray-400 text-lg">Belum ada riwayat analisis. Yuk, mulai scan pertama kamu!</p>
            <a href="scanner.php" class="inline-block mt-6 px-8 py-3 bg-pink-400 text-white rounded-full font-bold shadow-lg shadow-pink-100 hover:bg-pink-500 transition-all">Mulai Scan</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($history as $row): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-pink-50 overflow-hidden hover:shadow-lg hover:shadow-pink-100/50 transition-all duration-300">
                    <div class="p-5">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-bold px-3 py-1 rounded-full bg-pink-100 text-pink-500 uppercase tracking-wider">
                                <?= htmlspecialchars($row['ml_severity_level']) ?>
                            </span>
                            <span class="text-gray-400 text-xs italic"><?= date('d M Y', strtotime($row['created_at'])) ?></span>
                        </div>
            
                        <div class="space-y-2 text-sm text-gray-600 font-medium">
                            <p>🟢 Papule: <span class="text-gray-800"><?= $row['ml_papule_count'] ?></span></p>
                            <p>🔵 Pustule: <span class="text-gray-800"><?= $row['ml_pustule_count'] ?></span></p>
                            <p>⚫ Blackhead: <span class="text-gray-800"><?= $row['ml_blackhead_count'] ?></span></p>
                        </div>
                        <hr class="my-4 border-pink-50">
                        <a href="results.php?id=<?= $row['id'] ?>" class="text-pink-400 text-sm font-bold hover:text-pink-500 flex items-center gap-1 transition-colors">
                            Lihat Detail 
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>