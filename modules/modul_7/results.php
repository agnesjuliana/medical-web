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

<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">

<style>
    body {
        background: linear-gradient(135deg, #FFF5F7 0%, #F0F7FF 100%);
        font-family: 'Quicksand', sans-serif;
    }
    .card-report {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid #FFF0F5;
    }
    .text-pastel-pink { color: #FFB7CE; }
    .bg-pastel-pink { background: linear-gradient(135deg, #FFB7CE, #FFD1DC); }
    .badge-severity {
        background-color: #FFF0F5;
        color: #FF8AAB;
        border: 1px solid #FFD1DC;
    }
</style>

<main class="max-w-4xl mx-auto px-4 py-10" style="font-family: 'Quicksand', 'Inter', sans-serif;">
    
    <?php if(isset($_GET['notif'])): ?>
    <div class="mb-6 bg-gradient-to-r from-pink-400 to-rose-400 p-4 rounded-2xl text-white flex items-center gap-4 shadow-lg animate-bounce">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <p class="text-sm font-medium">Analisis selesai! Salinan digital telah dikirim ke <b><?= htmlspecialchars($user['email']) ?></b> ✨</p>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-pink-50 p-8">
        <h2 class="text-center text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-rose-500 mb-8 italic">Dermalyze.AI</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <div class="rounded-3xl overflow-hidden bg-gray-50 border-4 border-white shadow-md aspect-square">
                <img src="<?= $data['image_path'] ?>" class="w-full h-full object-cover">
            </div>

            <div class="flex flex-col justify-center">
                <div class="mb-6 flex justify-between items-center">
                    <span class="text-xs font-bold px-4 py-1 rounded-full bg-pink-100 text-pink-500 uppercase italic tracking-wide border border-pink-200"><?= $data['ml_severity_level'] ?></span>
                    <span class="text-gray-400 text-xs italic">Dipindai: <?= date('d/m/Y', strtotime($data['created_at'])) ?></span>
                </div>
                
                <div class="grid grid-cols-3 gap-3 mb-8 text-center">
                    <div class="p-3 bg-pink-50/50 rounded-2xl border border-pink-100">
                        <p class="text-xl font-bold text-pink-600"><?= $data['ml_papule_count'] ?></p>
                        <p class="text-[10px] text-pink-400 uppercase font-bold">Papule</p>
                    </div>
                    <div class="p-3 bg-pink-50/50 rounded-2xl border border-pink-100">
                        <p class="text-xl font-bold text-pink-600"><?= $data['ml_pustule_count'] ?></p>
                        <p class="text-[10px] text-pink-400 uppercase font-bold">Pustule</p>
                    </div>
                    <div class="p-3 bg-pink-50/50 rounded-2xl border border-pink-100">
                        <p class="text-xl font-bold text-pink-600"><?= $data['ml_blackhead_count'] ?></p>
                        <p class="text-[10px] text-pink-400 uppercase font-bold">Blackhead</p>
                    </div>
                </div>

                <a href="history.php" class="w-full py-3 rounded-full text-center bg-gray-50 text-gray-400 font-bold hover:bg-pink-50 hover:text-pink-400 transition-all duration-300 border border-transparent hover:border-pink-100">
                    Lihat Semua Riwayat
                </a>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>