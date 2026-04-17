<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
requireLogin();
startSession();

if (!isset($_SESSION['modul3_result'])) { header('Location: index.php'); exit; }
$res = $_SESSION['modul3_result'];
?>

<?php require_once __DIR__ . '/../../layout/header.php'; ?>
<?php require_once __DIR__ . '/../../layout/navbar.php'; ?>

<main class="max-w-4xl mx-auto px-4 py-16">
    <div class="bg-white rounded-[3rem] shadow-2xl overflow-hidden border border-gray-100">
        <div class="bg-gray-900 p-8 text-white flex justify-between items-center">
            <h1 class="text-xl font-black text-cyan-400 italic uppercase">Laporan Analisis AI</h1>
            <a href="index.php" class="text-xs bg-white/10 px-4 py-2 rounded-xl">Kembali</a>
        </div>
        <div class="p-10 grid md:grid-cols-2 gap-10 items-center">
            <img src="uploads/<?= $res['filename'] ?>" class="w-full rounded-3xl shadow-lg border-4 border-white">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Hasil Klasifikasi</span>
                <h2 class="text-4xl font-black mt-2 <?= $res['score'] > 50 ? 'text-red-600' : 'text-green-600' ?>">
                    <?= $res['status'] ?>
                </h2>
                <p class="mt-4 font-black text-6xl text-gray-800 tracking-tighter"><?= $res['score'] ?><span class="text-2xl text-gray-300">%</span></p>
                <div class="mt-10 p-5 bg-blue-50 border border-blue-100 rounded-2xl">
                    <p class="text-xs text-blue-800 leading-relaxed italic">Hasil ini adalah skrining awal. Harap konsultasikan ke Dokter Spesialis Paru untuk diagnosa medis resmi.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>