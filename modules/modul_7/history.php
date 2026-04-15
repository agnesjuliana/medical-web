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

<main class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Riwayat Analisis Kulit</h1>

    <?php if (empty($history)): ?>
        <div class="bg-white p-8 rounded-xl shadow-sm text-center">
            <p class="text-gray-500">Belum ada riwayat analisis. Yuk, mulai scan pertama kamu!</p>
            <a href="scanner.php" class="inline-block mt-4 text-cyan-600 font-semibold underline">Mulai Scan</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($history as $row): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-5">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-bold px-2 py-1 rounded bg-orange-100 text-orange-600 uppercase">
                                <?= htmlspecialchars($row['ml_severity_level']) ?>
                            </span>
                            <span class="text-gray-400 text-xs"><?= date('d M Y', strtotime($row['created_at'])) ?></span>
                        </div>
                        <div class="space-y-2 text-sm text-gray-600">
                            <p>🟢 Papule: <strong><?= $row['ml_papule_count'] ?></strong></p>
                            <p>🔵 Pustule: <strong><?= $row['ml_pustule_count'] ?></strong></p>
                            <p>⚫ Blackhead: <strong><?= $row['ml_blackhead_count'] ?></strong></p>
                        </div>
                        <hr class="my-4 border-gray-50">
                        <a href="results.php?id=<?= $row['id'] ?>" class="text-cyan-600 text-sm font-semibold hover:text-cyan-700">Lihat Detail →</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>