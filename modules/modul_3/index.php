<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
require_once __DIR__ . '/config/database3.php'; 

requireLogin(BASE_URL . '/modules/modul_3/login.php');
startSession();

$user = getCurrentUser();
$pageTitle = 'PulmoAI - TBC Detection';

global $db;
$histories = [];
$patientsList = [];

if (isset($db) && $db !== null) {
    try {
        // Ambil riwayat join dengan nama pasien
        $stmt = $db->prepare("
            SELECT h.*, p.name as patient_name 
            FROM modul3_history h 
            LEFT JOIN modul3_patients p ON h.patient_id = p.id 
            WHERE h.user_id = ? 
            ORDER BY h.created_at DESC
        ");
        $stmt->execute([$user['id']]);
        $histories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Ambil list pasien untuk dropdown
        $stmt2 = $db->prepare("SELECT id, name FROM modul3_patients WHERE user_id = ? ORDER BY name ASC");
        $stmt2->execute([$user['id']]);
        $patientsList = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $histories = [];
        $patientsList = [];
    }
}
?>

<?php require_once __DIR__ . '/../../layout/header.php'; ?>
<!-- Menggunakan Lucide Icon dari CDN -->
<script src="https://unpkg.com/lucide@latest"></script>

<style>
/* CSS Variables & Keyframes dari file konfigurasi Lovable */
:root {
  --radius: 0.875rem;
  --background: oklch(0.99 0.005 220);
  --foreground: oklch(0.18 0.04 240);
  --primary: oklch(0.58 0.14 210);
  --primary-foreground: oklch(0.99 0.005 220);
  --secondary: oklch(0.96 0.015 220);
  --secondary-foreground: oklch(0.25 0.05 240);
  --muted: oklch(0.96 0.01 220);
  --muted-foreground: oklch(0.5 0.03 230);
  --accent: oklch(0.72 0.15 195);
  --destructive: oklch(0.6 0.22 25);
  --destructive-foreground: oklch(0.99 0 0);
  --success: oklch(0.65 0.16 155);
  --success-foreground: oklch(0.99 0 0);
  --border: oklch(0.92 0.01 220);
  
  --gradient-hero: linear-gradient(135deg, oklch(0.22 0.06 240) 0%, oklch(0.35 0.1 215) 50%, oklch(0.55 0.14 195) 100%);
  --gradient-primary: linear-gradient(135deg, oklch(0.58 0.14 210), oklch(0.72 0.15 195));

  --shadow-soft: 0 4px 20px -8px oklch(0.4 0.1 220 / 0.15);
  --shadow-glow: 0 0 40px oklch(0.65 0.15 200 / 0.35);
  --shadow-elegant: 0 20px 50px -20px oklch(0.3 0.08 230 / 0.3);

  --glass-bg: oklch(1 0 0 / 0.7);
  --glass-border: oklch(1 0 0 / 0.3);
}

@keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
@keyframes slide-up { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
@keyframes scale-in { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
@keyframes pulse-glow {
  0%, 100% { box-shadow: 0 0 20px oklch(0.65 0.15 200 / 0.4); }
  50% { box-shadow: 0 0 40px oklch(0.65 0.15 200 / 0.7); }
}
@keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }

/* Base Utilities */
body { background-color: var(--background); color: var(--foreground); }
html { scroll-behavior: smooth; scroll-padding-top: 80px; }

.glass { background: var(--glass-bg); backdrop-filter: blur(16px) saturate(180%); border: 1px solid var(--glass-border); }
.gradient-hero { background: var(--gradient-hero); }
.gradient-primary { background: var(--gradient-primary); }
.gradient-text { background: var(--gradient-primary); -webkit-background-clip: text; color: transparent; }
.shadow-elegant { box-shadow: var(--shadow-elegant); }
.shadow-glow { box-shadow: var(--shadow-glow); }
.shadow-soft { box-shadow: var(--shadow-soft); }

.animate-fade-in { animation: fade-in 0.6s ease-out; }
.animate-slide-up { animation: slide-up 0.7s cubic-bezier(0.16, 1, 0.3, 1); }
.animate-scale-in { animation: scale-in 0.4s ease-out; }
.animate-pulse-glow { animation: pulse-glow 2.5s ease-in-out infinite; }
.animate-float { animation: float 6s ease-in-out infinite; }

/* Custom colors untuk menggantikan standard Tailwind jika belum di config */
.bg-background { background-color: var(--background); }
.text-foreground { color: var(--foreground); }
.bg-primary { background-color: var(--primary); }
.text-primary { color: var(--primary); }
.text-primary-foreground { color: var(--primary-foreground); }
.bg-secondary { background-color: var(--secondary); }
.text-secondary-foreground { color: var(--secondary-foreground); }
.bg-muted { background-color: var(--muted); }
.text-muted-foreground { color: var(--muted-foreground); }
.bg-accent { background-color: var(--accent); }
.text-accent { color: var(--accent); }
.bg-destructive { background-color: var(--destructive); }
.text-destructive { color: var(--destructive); }
.text-destructive-foreground { color: var(--destructive-foreground); }
.bg-success { background-color: var(--success); }
.text-success { color: var(--success); }
.text-success-foreground { color: var(--success-foreground); }

/* Opacity variants */
.bg-primary\/10 { background-color: oklch(0.58 0.14 210 / 0.1); }
.bg-primary\/20 { background-color: oklch(0.58 0.14 210 / 0.2); }
.bg-accent\/20 { background-color: oklch(0.72 0.15 195 / 0.2); }
.bg-accent\/30 { background-color: oklch(0.72 0.15 195 / 0.3); }
.bg-muted\/50 { background-color: oklch(0.96 0.01 220 / 0.5); }
.bg-destructive\/10 { background-color: oklch(0.6 0.22 25 / 0.1); }
.bg-success\/10 { background-color: oklch(0.65 0.16 155 / 0.1); }
.border-destructive\/20 { border-color: oklch(0.6 0.22 25 / 0.2); }
.border-success\/20 { border-color: oklch(0.65 0.16 155 / 0.2); }
.bg-muted\/30 { background-color: oklch(0.96 0.01 220 / 0.3); }
.border-border { border-color: var(--border); }
</style>

<div class="bg-background text-foreground shrink-0 pb-10">

    <!-- NAVBAR -->
    <header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 py-3" id="navbar">
      <div class="max-w-7xl mx-auto px-4">
        <nav class="glass flex items-center justify-between rounded-2xl px-5 py-3 shadow-soft">
          <a href="#home" class="flex items-center gap-2.5">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl gradient-primary shadow-glow">
              <i data-lucide="activity" class="h-5 w-5 text-primary-foreground stroke-[2.5px]"></i>
            </div>
            <div class="flex flex-col leading-none">
              <span class="text-base font-bold text-black">PulmoAI</span>
              <span class="text-[10px] text-muted-foreground">TBC Detection</span>
            </div>
          </a>

          <ul class="hidden md:flex items-center gap-1">
            <li><a href="#home" class="px-4 py-2 text-sm font-medium text-foreground/80 hover:text-foreground rounded-lg hover:bg-accent/20 transition-colors">Beranda</a></li>
            <li><a href="#detection" class="px-4 py-2 text-sm font-medium text-foreground/80 hover:text-foreground rounded-lg hover:bg-accent/20 transition-colors">Deteksi AI</a></li>
            <li><a href="#history" class="px-4 py-2 text-sm font-medium text-foreground/80 hover:text-foreground rounded-lg hover:bg-accent/20 transition-colors">Riwayat</a></li>
            <li><a href="patients.php" class="px-4 py-2 text-sm font-medium text-foreground/80 hover:text-foreground rounded-lg hover:bg-accent/20 transition-colors">Data Pasien</a></li>
            <li><a href="#about" class="px-4 py-2 text-sm font-medium text-foreground/80 hover:text-foreground rounded-lg hover:bg-accent/20 transition-colors">Tentang Kami</a></li>
          </ul>

          <div class="hidden md:flex items-center gap-2">
            <span class="text-sm font-bold opacity-70 text-black">Hi, <?= htmlspecialchars($user['name'] ?? 'User') ?>!</span>
            <a href="../../index.php" class="px-4 py-2 rounded-md gradient-primary text-primary-foreground text-sm font-medium shadow-soft hover:opacity-90">Dashboard</a>
            <a href="logout.php" class="p-2 ml-1 rounded-md bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Keluar"><i data-lucide="log-out" class="w-5 h-5"></i></a>
          </div>
        </nav>
      </div>
    </header>

    <!-- HERO -->
    <section id="home" class="relative min-h-screen flex items-center pt-28 pb-16 overflow-hidden gradient-hero">
      <div class="absolute top-1/4 -left-32 h-96 w-96 rounded-full bg-accent/30 blur-3xl animate-float"></div>
      <div class="absolute bottom-0 right-0 h-[28rem] w-[28rem] rounded-full bg-primary/20 blur-3xl animate-float" style="animation-delay: 2s;"></div>

      <div class="max-w-7xl mx-auto px-4 relative z-10 w-full">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <div class="text-primary-foreground animate-slide-up">
            

            <h1 class="text-4xl md:text-5xl lg:text-5xl font-bold tracking-tighter text-white flex flex-col leading-none">
              <span class="mb-[-10px]">Skrining Awal</span>
              <span class="bg-gradient-to-r from-accent to-white bg-clip-text text-transparent mb-[-10px]">Tuberkulosis</span>
              <span class="mb-4">Lewat Citra Rontgen</span>
            </h1>

            <p class="text-lg text-primary-foreground/80 max-w-xl mb-8 leading-relaxed text-white">
              Unggah hasil rontgen dada Anda dan dapatkan analisis indikasi TBC dalam hitungan detik.
            </p>

            <div class="flex flex-wrap gap-3 mb-10">
              <a href="#detection" class="inline-flex items-center justify-center bg-white text-black font-semibold hover:bg-white/90 shadow-elegant h-12 px-6 rounded-md">
                Mulai Skrining <i data-lucide="arrow-right" class="ml-1.5 h-4 w-4"></i>
              </a>
              <a href="#about" class="inline-flex items-center justify-center bg-transparent border border-white/30 text-white font-medium hover:bg-white/10 h-12 px-6 rounded-md">
                Pelajari Lebih Lanjut
              </a>
            </div>

            <div class="grid grid-cols-3 gap-4 max-w-lg">
              <div class="glass rounded-xl p-3 border-white/10 text-white">
                <i data-lucide="zap" class="h-4 w-4 text-accent mb-1.5"></i>
                <div class="text-lg font-bold leading-none">< 5 dtk</div>
                <div class="text-xs mt-1 opacity-70">Cepat</div>
              </div>
              <div class="glass rounded-xl p-3 border-white/10 text-white">
                <i data-lucide="shield-check" class="h-4 w-4 text-accent mb-1.5"></i>
                <div class="text-lg font-bold leading-none">94%+</div>
                <div class="text-xs mt-1 opacity-70">Akurat</div>
              </div>
              <div class="glass rounded-xl p-3 border-white/10 text-white">
                <i data-lucide="sparkles" class="h-4 w-4 text-accent mb-1.5"></i>
                <div class="text-lg font-bold leading-none">Privat</div>
                <div class="text-xs mt-1 opacity-70">Aman</div>
              </div>
            </div>
          </div>

          <div class="relative animate-scale-in flex justify-center lg:justify-end">
            <div class="absolute inset-0 gradient-primary blur-3xl opacity-30 rounded-full"></div>
  
            <div class="relative group w-full max-w-md">
              <div class="absolute -inset-1 bg-gradient-to-r from-accent to-primary rounded-[2.2rem] blur opacity-25 group-hover:opacity-40 transition duration-1000"></div>
    
              <div class="relative glass p-3 rounded-[2rem] border border-white/20 shadow-elegant overflow-hidden">
                <img src="assets/gambar1.jpeg" 
                     alt="Rontgen Paru" 
                     class="w-full h-auto rounded-[1.6rem] object-cover filter brightness-110 contrast-125 min-h-[350px] bg-black/40">
      
                <div class="absolute inset-0 bg-gradient-to-b from-transparent via-accent/30 to-transparent h-1/2 w-full animate-float pointer-events-none"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- DETECTION -->
    <section id="detection" class="py-24 bg-background">
      <div class="max-w-7xl mx-auto px-4">
        <div class="text-center max-w-2xl mx-auto mb-12">
          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/10 text-primary mb-4">
            <i data-lucide="brain" class="h-3.5 w-3.5"></i>
            <span class="text-xs font-semibold">AI-Powered Detection</span>
          </div>
          <h2 class="text-3xl md:text-4xl font-bold mb-4 text-black">
            Deteksi TBC dari <span class="gradient-text">Rontgen Dada</span>
          </h2>
          <p class="text-muted-foreground">Unggah gambar rontgen thorax untuk skrining mandiri.</p>
        </div>

        <div class="max-w-3xl mx-auto">
          <!-- INPUT CARD -->
          <div class="bg-white p-6 md:p-10 shadow-soft rounded-2xl border border-gray-100">
            <h3 class="text-xl font-bold mb-1 text-black">Unggah Citra Rontgen</h3>
            <p class="text-sm text-muted-foreground mb-8">Format yang diizinkan meliputi JPG atau PNG.</p>

            <form id="uploadForm" action="process.php" method="POST" enctype="multipart/form-data">
                
                <div class="mb-5">
                    <label class="block font-bold text-black mb-2 text-sm">Lampirkan ke Profil Pasien (Opsional)</label>
                    <div class="relative">
                        <select name="patient_id" class="w-full px-4 py-3 text-gray-700 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#5B88D6]/30 focus:border-[#5B88D6] shadow-sm appearance-none">
                            <option value="">-- Lakukan Scan Anonim (Tanpa Profil) --</option>
                            <?php foreach($patientsList as $p): ?>
                                <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    </div>
                </div>

                <div id="dropZone" class="space-y-3">
                  <label class="block border-2 border-dashed border-gray-300 bg-gray-50 rounded-2xl p-10 text-center cursor-pointer hover:border-primary hover:bg-primary/5 transition-colors duration-300">
                    <input type="file" name="thorax_image" id="imageInput" accept="image/*" class="hidden" required>
                    <div class="mx-auto mb-4 h-16 w-16 rounded-2xl gradient-primary flex items-center justify-center shadow-glow">
                      <i data-lucide="upload-cloud" class="h-8 w-8 text-primary-foreground"></i>
                    </div>
                    <p class="font-bold text-black text-lg">Pilih file rontgen thorax</p>
                    <p class="text-sm text-muted-foreground mt-1">atau tarik dan lepas ('drag and drop') file ke sini.</p>
                  </label>
                </div>

                <div id="previewArea" class="hidden space-y-6 mt-4">
                  <div class="relative rounded-2xl overflow-hidden border border-gray-200 bg-black flex justify-center items-center h-80 shadow-inner">
                    <img id="previewImg" src="" alt="Preview" class="max-w-full max-h-full object-contain">
                    <button type="button" id="resetBtn" class="absolute top-3 right-3 h-10 w-10 rounded-full bg-white flex items-center justify-center shadow-md text-black hover:bg-gray-100 transition">
                      <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                    <div id="loadingOverlay" class="hidden absolute inset-0 bg-background/80 backdrop-blur-sm flex flex-col items-center justify-center gap-3">
                        <i data-lucide="loader-2" class="h-10 w-10 text-primary animate-spin"></i>
                        <p class="text-sm font-bold text-black mt-2">AI sedang menganalisis piksel citra...</p>
                    </div>
                  </div>
                  <button type="submit" id="submitBtn" class="w-full h-14 flex items-center justify-center gap-2 gradient-primary text-primary-foreground font-bold text-lg rounded-xl border-0 shadow-soft hover:shadow-elegant transition-all hover:-translate-y-1">
                    <i data-lucide="scan-line" class="h-5 w-5"></i> Mulai Analisis Sekarang
                  </button>
                </div>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- HISTORY SECTION -->
    <section id="history" class="py-24 bg-muted/30">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
          <div>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/10 text-primary mb-4">
              <i data-lucide="database" class="h-3.5 w-3.5"></i>
              <span class="text-xs font-semibold">Cloud Memory</span>
            </div>
            <h2 class="text-3xl md:text-4xl font-bold mb-2 text-black">
              Riwayat <span class="gradient-text">Pemeriksaan Anda</span>
            </h2>
            <p class="text-muted-foreground">Semua hasil scan tersimpan aman.</p>
          </div>
          <div class="flex gap-3">
            <div class="px-5 py-4 shadow-soft bg-white rounded-xl border border-gray-100 min-w-[120px]">
              <div class="text-xs text-muted-foreground">Total Scan</div>
              <div class="text-3xl font-bold text-black mt-1"><?= count($histories) ?></div>
            </div>
          </div>
        </div>

        <?php if (empty($histories)): ?>
            <div class="border-2 border-dashed border-gray-300 bg-white rounded-3xl p-16 text-center max-w-2xl mx-auto shadow-sm">
                <i data-lucide="folder-open" class="h-12 w-12 text-muted-foreground mx-auto mb-4"></i>
                <p class="text-lg font-medium text-black">Belum ada riwayat pemeriksaan.</p>
            </div>
        <?php else: ?>
            <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
              <?php foreach ($histories as $h): ?>
                <?php 
                    $isTbc = $h['status'] !== 'Normal' && $h['confidence_score'] > 50; 
                    $badgeBg = $isTbc ? "bg-destructive text-destructive-foreground" : "bg-success text-success-foreground";
                    $barBg = $isTbc ? "bg-destructive" : "bg-success";
                ?>
                <div class="overflow-hidden shadow-soft hover:shadow-elegant hover:-translate-y-1 transition-all duration-300 group bg-white rounded-2xl border border-gray-100 flex flex-col">
                  <div class="h-44 bg-gradient-to-br from-secondary to-muted relative flex items-center justify-center overflow-hidden border-b border-gray-100">
                    <img src="uploads/<?= htmlspecialchars($h['filename']) ?>" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 group-hover:scale-105 transition-all duration-500">
                    <span class="absolute bottom-3 left-3 px-3 py-1 text-xs font-bold rounded-lg shadow-md <?= $badgeBg ?>">
                      <?= htmlspecialchars($h['status']) ?>
                    </span>
                  </div>
                  <div class="p-5 flex-1 flex flex-col">
                    <div class="flex items-center justify-between text-xs font-medium text-muted-foreground mb-5 pb-3 border-b border-gray-100">
                      <span class="flex items-center gap-1">
                        <i data-lucide="calendar" class="h-3 w-3"></i>
                        <?= date('d M Y', strtotime($h['created_at'])) ?>
                      </span>
                      <span class="flex items-center gap-1 font-semibold text-primary truncate max-w-[120px]" title="<?= htmlspecialchars($h['patient_name'] ?? 'Anonim') ?>">
                        <i data-lucide="user" class="h-3 w-3"></i>
                        <?= htmlspecialchars($h['patient_name'] ?? 'Anonim') ?>
                      </span>
                    </div>
                    
                    <div class="mt-auto">
                        <div class="flex items-center justify-between mb-2">
                          <span class="text-xs text-muted-foreground flex items-center gap-1 font-semibold">
                            <i data-lucide="activity" class="h-3 w-3"></i> SCORE AI
                          </span>
                          <span class="text-base font-bold text-black"><?= htmlspecialchars($h['confidence_score']) ?>%</span>
                        </div>
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                          <div class="h-full rounded-full <?= $barBg ?>" style="width: <?= htmlspecialchars($h['confidence_score']) ?>%"></div>
                        </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- ABOUT SECTION -->
    <section id="about" class="py-24 bg-background">
      <div class="max-w-7xl mx-auto px-4">
        <div class="text-center max-w-2xl mx-auto mb-16">
          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/10 text-primary mb-4">
            <i data-lucide="users" class="h-3.5 w-3.5"></i>
            <span class="text-xs font-semibold">Tim Pengembang</span>
          </div>
          <h2 class="text-3xl md:text-4xl font-bold mb-4 text-black">
            Dikembangkan oleh <span class="gradient-text">Kelompok 3</span>
          </h2>
        </div>

        <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-6 max-w-3xl mx-auto">
          <!-- Tim Sesuai Data Asli Sistem -->
          <div class="p-8 text-center shadow-soft hover:shadow-elegant hover:-translate-y-2 transition-all bg-white rounded-3xl border border-gray-100">
            <div class="mx-auto mb-5 h-24 w-24 rounded-[2rem] bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white text-3xl font-black shadow-glow">AR</div>
            <h3 class="font-bold text-lg text-black">Andhika Rastra</h3>
            <p class="text-xs mt-1 text-primary font-semibold tracking-wider">NRP: 5049231115</p>
          </div>
          <div class="p-8 text-center shadow-soft hover:shadow-elegant hover:-translate-y-2 transition-all bg-white rounded-3xl border border-gray-100">
            <div class="mx-auto mb-5 h-24 w-24 rounded-[2rem] bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white text-3xl font-black shadow-lg">MA</div>
            <h3 class="font-bold text-lg text-black">Muhammad Aisar</h3>
            <p class="text-xs mt-1 text-primary font-semibold tracking-wider">NRP: 5049231116</p>
          </div>
          <div class="p-8 text-center shadow-soft hover:shadow-elegant hover:-translate-y-2 transition-all bg-white rounded-3xl border border-gray-100">
            <div class="mx-auto mb-5 h-24 w-24 rounded-[2rem] bg-gradient-to-br from-violet-500 to-purple-500 flex items-center justify-center text-white text-3xl font-black shadow-lg">MS</div>
            <h3 class="font-bold text-lg text-black">M Siham</h3>
            <p class="text-xs mt-1 text-primary font-semibold tracking-wider">NRP: 5049231048</p>
          </div>
        </div>

        <div class="mt-20 max-w-4xl mx-auto">
          <div class="p-10 md:p-14 gradient-hero text-primary-foreground rounded-[2.5rem] border-0 shadow-elegant overflow-hidden relative">
            <div class="absolute top-0 right-0 h-64 w-64 rounded-full bg-accent/30 blur-3xl"></div>
            <div class="relative z-10 text-center">
              <h3 class="text-3xl font-bold mb-4 text-white">Misi Kami</h3>
              <p class="text-white/80 leading-relaxed max-w-2xl mx-auto text-lg">
                Membiaskan masa depan teknologi medis dengan memanfaatkan Deep Learning AI untuk mempercepat skrining awal TBC, terutama di daerah dengan akses tenaga medis terbatas demi Indonesia Sehat.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="border-t border-gray-100 bg-white py-10 mt-10">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl gradient-primary flex items-center justify-center shadow-sm">
              <i data-lucide="activity" class="h-5 w-5 text-primary-foreground stroke-[2.5px]"></i>
            </div>
            <div>
              <div class="font-bold text-black">PulmoAI</div>
              <div class="text-xs text-muted-foreground font-medium">TBC Detection System Modul 3</div>
            </div>
          </div>
          <p class="text-sm font-medium text-muted-foreground flex items-center gap-2">
            Dibuat dengan <i data-lucide="heart" class="h-4 w-4 text-destructive fill-destructive"></i> oleh Tim PulmoAI © 2026
          </p>
        </div>
      </div>
    </footer>

</div>

<script>
    // Initialize Lucide Icons
    lucide.createIcons();

    // JS Form Upload UI Logic
    const imgInput = document.getElementById('imageInput');
    const dropZone = document.getElementById('dropZone');
    const previewArea = document.getElementById('previewArea');
    const previewImg = document.getElementById('previewImg');
    const resetBtn = document.getElementById('resetBtn');
    const uploadForm = document.getElementById('uploadForm');
    const submitBtn = document.getElementById('submitBtn');
    const loadingOverlay = document.getElementById('loadingOverlay');

    function showPreview(file) {
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImg.src = e.target.result;
            dropZone.classList.add('hidden');
            previewArea.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }

    imgInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            showPreview(this.files[0]);
        }
    });

    resetBtn.addEventListener('click', function() {
        imgInput.value = '';
        previewImg.src = '';
        previewArea.classList.add('hidden');
        dropZone.classList.remove('hidden');
    });

    uploadForm.addEventListener('submit', function() {
        // Show loading animations
        loadingOverlay.classList.remove('hidden');
        loadingOverlay.classList.add('flex');
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-wait');
    });

    // Drag and Drop (Opsional UI)
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-primary', 'bg-primary/5');
    });
    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'bg-primary/5');
    });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'bg-primary/5');
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            imgInput.files = e.dataTransfer.files;
            showPreview(e.dataTransfer.files[0]);
        }
    });
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>