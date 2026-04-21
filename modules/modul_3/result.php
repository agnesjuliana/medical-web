<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
requireLogin(BASE_URL . '/modules/modul_3/login.php');
startSession();

if (!isset($_SESSION['modul3_result'])) { header('Location: index.php'); exit; }
$res = $_SESSION['modul3_result'];
$isTbc = $res['score'] > 50 && $res['status'] !== 'Normal';
?>

<?php require_once __DIR__ . '/../../layout/header.php'; ?>
<script src="https://unpkg.com/lucide@latest"></script>

<style>
/* CSS Variables & Keyframes dari file konfigurasi Lovable */
:root {
  --radius: 0.875rem;
  --background: oklch(0.99 0.005 220);
  --foreground: oklch(0.18 0.04 240);
  --primary: oklch(0.58 0.14 210);
  --primary-foreground: oklch(0.99 0.005 220);
  --muted: oklch(0.96 0.01 220);
  --muted-foreground: oklch(0.5 0.03 230);
  --destructive: oklch(0.6 0.22 25);
  --destructive-foreground: oklch(0.99 0 0);
  --success: oklch(0.65 0.16 155);
  --success-foreground: oklch(0.99 0 0);
  --border: oklch(0.92 0.01 220);
  --shadow-soft: 0 4px 20px -8px oklch(0.4 0.1 220 / 0.15);
  --shadow-elegant: 0 20px 50px -20px oklch(0.3 0.08 230 / 0.3);
}

body { background-color: var(--background); color: var(--foreground); }

.bg-background { background-color: var(--background); }
.text-foreground { color: var(--foreground); }
.bg-primary { background-color: var(--primary); }
.text-primary { color: var(--primary); }
.text-primary-foreground { color: var(--primary-foreground); }
.bg-muted { background-color: var(--muted); }
.text-muted-foreground { color: var(--muted-foreground); }
.bg-destructive { background-color: var(--destructive); }
.text-destructive { color: var(--destructive); }
.text-destructive-foreground { color: var(--destructive-foreground); }
.bg-success { background-color: var(--success); }
.text-success { color: var(--success); }
.text-success-foreground { color: var(--success-foreground); }

.shadow-soft { box-shadow: var(--shadow-soft); }
.shadow-elegant { box-shadow: var(--shadow-elegant); }

/* Opacity variants */
.bg-muted\/50 { background-color: oklch(0.96 0.01 220 / 0.5); }
.bg-destructive\/10 { background-color: oklch(0.6 0.22 25 / 0.1); }
.bg-success\/10 { background-color: oklch(0.65 0.16 155 / 0.1); }
.border-destructive\/20 { border-color: oklch(0.6 0.22 25 / 0.2); }
.border-success\/20 { border-color: oklch(0.65 0.16 155 / 0.2); }
.border-border { border-color: var(--border); }
</style>

<main class="bg-background min-h-screen py-10 flex flex-col items-center">
    <div class="max-w-5xl w-full mx-auto px-4">
        
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <h1 class="text-2xl font-bold text-black flex items-center gap-3">
                <i data-lucide="brain-circuit" class="h-8 w-8 text-primary"></i> 
                Laporan Analisis AI
            </h1>
            <div class="flex gap-2">
                <?php if (!empty($res['history_id'])): ?>
                <a href="print_result.php?id=<?= htmlspecialchars($res['history_id']) ?>" target="_blank" class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold hover:bg-gray-50 flex items-center gap-2 text-black transition-colors shadow-sm">
                    <i data-lucide="printer" class="h-4 w-4"></i> Cetak PDF
                </a>
                <?php endif; ?>
                <a href="index.php" class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold hover:bg-gray-50 flex items-center gap-2 text-black transition-colors shadow-sm">
                    <i data-lucide="arrow-left" class="h-4 w-4 text-muted-foreground"></i> Kembali Menu
                </a>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8 items-start">
            <!-- Left: Image View (Input) -->
            <div class="bg-white p-2 shadow-soft rounded-3xl border border-gray-100 flex flex-col relative overflow-hidden">
                <div class="bg-black w-full h-[500px] rounded-2xl overflow-hidden relative flex items-center justify-center shadow-inner">
                    <img src="uploads/<?= htmlspecialchars($res['filename']) ?>" class="max-w-full max-h-full object-contain">
                    
                    <div class="absolute top-4 left-4 bg-black/60 backdrop-blur-md px-4 py-1.5 rounded-lg border border-white/20 text-white text-xs font-bold tracking-widest uppercase flex items-center gap-2">
                        <i data-lucide="image" class="h-3.5 w-3.5"></i>
                        Uploaded Image
                    </div>
                </div>
            </div>

            <!-- Right: Result Card based on Lovable Detection Section -->
            <div class="bg-white p-8 shadow-soft rounded-3xl border border-gray-100 flex flex-col justify-center h-full">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/10 text-primary w-max mb-5">
                    <i data-lucide="activity" class="h-4 w-4"></i>
                    <span class="text-xs font-bold uppercase tracking-wider">AI Diagnostics</span>
                </div>
                
                <h3 class="text-2xl font-black mb-2 text-black">Kesimpulan Hasil</h3>
                <p class="text-sm text-muted-foreground mb-8">Pendeteksian fitur abnormalitas paru menggunakan Deep Learning dari rontgen thorax.</p>

                <div class="space-y-6">
                    <!-- Status Banner -->
                    <?php if ($isTbc): ?>
                        <div class="rounded-2xl p-6 bg-destructive/10 border border-destructive/20 scale-100 transition-all hover:scale-[1.01]">
                            <div class="flex items-start gap-4">
                                <div class="h-12 w-12 rounded-xl flex items-center justify-center bg-destructive shrink-0 shadow-sm">
                                    <i data-lucide="alert-triangle" class="h-6 w-6 text-destructive-foreground"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-destructive/80 uppercase tracking-widest mb-1">Status Prediksi</div>
                                    <div class="text-2xl font-black text-destructive"><?= htmlspecialchars($res['status']) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="rounded-2xl p-6 bg-success/10 border border-success/20 scale-100 transition-all hover:scale-[1.01]">
                            <div class="flex items-start gap-4">
                                <div class="h-12 w-12 rounded-xl flex items-center justify-center bg-success shrink-0 shadow-sm">
                                    <i data-lucide="check-circle-2" class="h-6 w-6 text-success-foreground"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-success/80 uppercase tracking-widest mb-1">Status Prediksi</div>
                                    <div class="text-2xl font-black text-success">Normal / Sehat</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Confidence Bar -->
                    <div class="bg-gray-50 border border-gray-100 p-5 rounded-2xl">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-bold text-black flex items-center gap-2">
                                <i data-lucide="crosshairs" class="h-4 w-4 text-muted-foreground"></i> Confidence Score
                            </span>
                            <span class="text-xl font-black text-black"><?= htmlspecialchars($res['score']) ?>%</span>
                        </div>
                        <div class="h-3 bg-gray-200 rounded-full overflow-hidden shadow-inner">
                            <div class="h-full <?= $isTbc ? 'bg-destructive' : 'bg-success' ?> rounded-full" style="width: <?= htmlspecialchars($res['score']) ?>%;"></div>
                        </div>
                    </div>

                    <!-- Recommendation -->
                    <div class="rounded-2xl bg-muted/50 p-6 border border-gray-100 mt-2">
                        <div class="text-xs font-bold uppercase tracking-widest text-primary mb-2 flex items-center gap-2">
                            <i data-lucide="info" class="h-4 w-4"></i> Saran Tindak Lanjut
                        </div>
                        <p class="text-sm leading-relaxed text-black font-medium">
                            <?= $isTbc ? "Sistem mendeteksi adanya indikasi pola abnormal pada rontgen thorax Anda yang merujuk pada fitur Tuberkulosis. Segera kunjungi dokter spesialis paru atau puskesmas terdekat dengan membawa hasil ini untuk penegakan diagnosa medis secara klinis." : "Tidak ditemukan pola indikasi kuat ke arah Tuberkulosis dari citra yang diunggah. Namun jika Anda masih merasakan keluhan spesifik saluran pernapasan berkelanjutan, harap tetap konsultasikan ke dokter." ?>
                        </p>
                    </div>

                    <div class="flex items-start gap-3 mt-8 bg-orange-50/50 p-4 border border-orange-100 rounded-xl">
                        <i data-lucide="alert-circle" class="h-5 w-5 text-orange-500 shrink-0 mt-0.5"></i>
                        <p class="text-xs text-orange-800 font-medium leading-relaxed">
                            <strong>Disclaimer Medis:</strong> Hasil di atas dihasilkan sepenuhnya oleh model kecerdasan buatan sebagai langkah *skrining awal / triase*. Aplikasi ini bukan pengganti opini, diagnosis, maupun perawatan medis profesional dari tenaga kesehatan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    lucide.createIcons();
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>