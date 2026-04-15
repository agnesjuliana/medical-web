<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$db = getModul7DBConnection();
$user = getCurrentUser();

// ==========================================
// BAGIAN 1: LOGIKA SIMPAN (Jika ada POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photo'])) {
    $target_dir = "assets/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $file = $_FILES['photo'];
    $new_filename = "scan_" . time() . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Data Dummy (Nanti diganti AI kamu)
        $severity = 'MODERATE';
        $papule = rand(1, 5); $pustule = rand(1, 5); $blackhead = rand(5, 10);

        $sql = "INSERT INTO screening_results 
                (patient_id, image_path, ml_severity_level, ml_papule_count, ml_pustule_count, ml_blackhead_count, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$user['id'], $target_file, $severity, $papule, $pustule, $blackhead, 'Completed']);
        
        // Setelah simpan, redirect ke diri sendiri via GET untuk nampilin hasilnya
        $new_id = $db->lastInsertId();
        header("Location: results.php?id=" . $new_id . "&notif=success");
        exit;
    }
}

// ==========================================
// BAGIAN 2: TAMPILAN (Jika ada ID di URL)
// ==========================================
$scan_id = $_GET['id'] ?? null;
if (!$scan_id) { header("Location: history.php"); exit; }

$stmt = $db->prepare("SELECT * FROM screening_results WHERE id = ?");
$stmt->execute([$scan_id]);
$data = $stmt->fetch();

if (!$data) die("Laporan tidak ditemukan.");

$pageTitle = 'Laporan Dermalyze.AI';
require_once __DIR__ . '/../../layout/header.php';
require_once __DIR__ . '/../../layout/navbar.php';
?>

<main class="max-w-4xl mx-auto px-4 py-10" style="font-family: 'Inter', sans-serif;">
    
    <?php if(isset($_GET['notif'])): ?>
    <div class="mb-6 bg-gradient-to-r from-cyan-500 to-blue-500 p-4 rounded-2xl text-white flex items-center gap-4 shadow-lg animate-bounce">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <p class="text-sm">Analisis selesai! Salinan digital telah dikirim ke <b><?= htmlspecialchars($user['email']) ?></b></p>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 p-8">
        <h2 class="text-center text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-cyan-500 to-blue-600 mb-8 italic">Dermalyze.AI</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <div class="rounded-3xl overflow-hidden bg-gray-50 border-4 border-white shadow-inner aspect-square">
                <img src="<?= $data['image_path'] ?>" class="w-full h-full object-cover">
            </div>

            <div class="flex flex-col justify-center">
                <div class="mb-6 flex justify-between items-center">
                    <span class="text-xs font-bold px-4 py-1 rounded-full bg-orange-100 text-orange-600 uppercase italic"><?= $data['ml_severity_level'] ?></span>
                    <span class="text-gray-400 text-xs italic">Dipindai: <?= date('d/m/Y', strtotime($data['created_at'])) ?></span>
                </div>
                
                <div class="grid grid-cols-3 gap-2 mb-8 text-center">
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-xl font-bold text-gray-800"><?= $data['ml_papule_count'] ?></p>
                        <p class="text-[10px] text-gray-400 uppercase">Papule</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-xl font-bold text-gray-800"><?= $data['ml_pustule_count'] ?></p>
                        <p class="text-[10px] text-gray-400 uppercase">Pustule</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-xl font-bold text-gray-800"><?= $data['ml_blackhead_count'] ?></p>
                        <p class="text-[10px] text-gray-400 uppercase">Blackhead</p>
                    </div>
                </div>

                <a href="history.php" class="w-full py-3 rounded-full text-center bg-gray-50 text-gray-500 font-semibold hover:bg-gray-100 transition">Lihat Semua Riwayat</a>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>