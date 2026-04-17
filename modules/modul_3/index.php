<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
// Path database disesuaikan dengan struktur folder kamu di modul_3/config/
require_once __DIR__ . '/config/database3.php'; 

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'TB-Scan AI';

// Ambil riwayat dari database
global $db;
$histories = [];

// Pastikan koneksi DB tersedia sebelum menjalankan query
if (isset($db) && $db !== null) {
    try {
        $stmt = $db->prepare("SELECT * FROM modul3_history WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        $histories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Jika tabel belum dibuat, history akan kosong tanpa merusak halaman
        $histories = [];
    }
}
?>

<?php require_once __DIR__ . '/../../layout/header.php'; ?>

<style>
    html { scroll-behavior: smooth; }
    .section-padding { padding: 100px 0; }
    .nav-glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px); }
    .img-zoom { transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
    .group:hover .img-zoom { transform: scale(1.1); }
    .text-gradient { background: linear-gradient(to right, #0891b2, #06b6d4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
</style>

<nav class="sticky top-0 z-50 nav-glass border-b border-cyan-50">
    <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-cyan-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-cyan-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <span class="font-black text-gray-800 text-2xl tracking-tighter">TB-SCAN<span class="text-cyan-600">.AI</span></span>
        </div>
        <div class="hidden md:flex gap-10 text-xs font-black text-gray-400 uppercase tracking-[0.2em]">
            <a href="#home" class="hover:text-cyan-600 transition-colors">Home</a>
            <a href="#deteksi" class="hover:text-cyan-600 transition-colors">Deteksi</a>
            <a href="#history" class="hover:text-cyan-600 transition-colors">Riwayat</a>
            <a href="#about" class="hover:text-cyan-600 transition-colors">Team</a>
        </div>
    </div>
</nav>

<main>
    <section id="home" class="section-padding bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-16 items-center">
            <div class="relative z-10">
                <span class="px-4 py-2 bg-cyan-50 text-cyan-600 rounded-lg text-xs font-black uppercase tracking-widest mb-6 inline-block border border-cyan-100">Smart Medical Solution</span>
                <h1 class="text-7xl font-black text-gray-900 leading-[1.05] tracking-tight">Cek Paru <br><span class="text-gradient italic">Pake AI.</span></h1>
                <p class="text-gray-500 mt-8 text-xl leading-relaxed max-w-lg font-medium">Platform skrining awal Tuberkulosis berbasis Deep Learning. Cepat, akurat, dan dapat diakses kapan saja.</p>
                <div class="mt-12 flex flex-wrap items-center gap-6">
                    <a href="#deteksi" class="px-10 py-5 bg-cyan-600 text-white rounded-2xl font-black shadow-xl shadow-cyan-200 hover:bg-cyan-700 hover:-translate-y-1 transition-all uppercase text-sm tracking-wider">Mulai Scan Sekarang</a>
                </div>
            </div>
            <div class="relative">
                <div class="absolute -inset-10 bg-cyan-100 rounded-full blur-[100px] opacity-30"></div>
                <img src="https://img.freepik.com/free-vector/health-professional-team-illustration_23-2148496884.jpg" class="relative rounded-[3rem] shadow-2xl border-8 border-white">
            </div>
        </div>
    </section>

    <section id="deteksi" class="section-padding bg-slate-50">
        <div class="max-w-3xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black text-gray-900 italic uppercase">Main Page Deteksi</h2>
                <div class="h-2 w-16 bg-cyan-500 mx-auto mt-4 rounded-full"></div>
            </div>
            
            <div class="bg-white p-12 rounded-[4rem] shadow-2xl shadow-cyan-100/50 border border-white">
                <form action="process.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                    <label class="group flex flex-col items-center justify-center w-full h-80 border-4 border-dashed border-slate-100 rounded-[3rem] cursor-pointer hover:bg-cyan-50 hover:border-cyan-200 transition-all">
                        <div class="w-20 h-20 bg-cyan-600 rounded-3xl flex items-center justify-center text-white mb-6 group-hover:scale-110 group-hover:rotate-12 transition-all shadow-xl shadow-cyan-200">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2"/></svg>
                        </div>
                        <span class="font-black text-slate-700 text-lg uppercase tracking-widest">Pilih Citra Thorax</span>
                        <p class="text-slate-400 text-sm mt-2 font-bold">Ambil Foto atau Upload Berkas</p>
                        <input type="file" name="thorax_image" id="imageInput" class="hidden" accept="image/*" capture="environment" required />
                    </label>
                    
                    <div id="filePreview" class="hidden mt-8 p-5 bg-slate-50 rounded-3xl border border-slate-100 items-center gap-4">
                        <div class="p-2 bg-white rounded-xl shadow-sm"><svg class="w-6 h-6 text-cyan-600" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"/></svg></div>
                        <span id="fileName" class="text-sm font-black text-slate-800 truncate"></span>
                    </div>

                    <button type="submit" id="submitBtn" class="w-full mt-10 py-6 bg-gray-900 text-white rounded-[2.5rem] font-black text-xl hover:bg-cyan-600 hover:shadow-2xl hover:shadow-cyan-100 transition-all flex items-center justify-center gap-3 tracking-widest uppercase">
                        JALANKAN ANALISIS 
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section id="history" class="section-padding bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-5xl font-black text-gray-900 tracking-tighter italic mb-12">Riwayat Scan.</h2>

            <?php if (empty($histories)): ?>
                <div class="bg-slate-50 border-4 border-dashed border-slate-100 rounded-[4rem] p-24 text-center">
                    <p class="text-slate-300 font-black text-2xl italic">Belum ada data pemeriksaan terbaru.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                    <?php foreach ($histories as $h): ?>
                    <div class="group bg-white rounded-[3rem] border border-gray-100 shadow-sm hover:shadow-2xl transition-all overflow-hidden">
                        <div class="relative overflow-hidden h-64 bg-slate-900">
                            <img src="uploads/<?= htmlspecialchars($h['filename']) ?>" class="w-full h-full object-cover img-zoom opacity-80 group-hover:opacity-100">
                            <div class="absolute bottom-4 left-4">
                                <span class="px-5 py-2 text-[10px] font-black rounded-xl shadow-2xl <?= $h['confidence_score'] > 50 ? 'bg-red-500 text-white' : 'bg-green-500 text-white' ?> uppercase tracking-widest">
                                    <?= htmlspecialchars($h['status']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="p-8">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]"><?= date('d M Y', strtotime($h['created_at'])) ?></span>
                                <span class="text-[10px] font-black text-cyan-600 tracking-widest">AI RESULT</span>
                            </div>
                            <p class="font-black text-5xl text-gray-900 italic tracking-tighter"><?= htmlspecialchars($h['confidence_score']) ?><span class="text-2xl text-gray-200">%</span></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section id="about" class="section-padding bg-gray-950 text-white rounded-t-[5rem]">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h2 class="text-6xl font-black mb-24 italic tracking-tighter uppercase underline decoration-cyan-600 underline-offset-[15px] decoration-8">The Team.</h2>
            <div class="grid md:grid-cols-3 gap-20">
                <div class="group">
                    <div class="w-44 h-44 bg-cyan-600 rounded-[3.5rem] mx-auto mb-10 flex items-center justify-center font-black text-6xl shadow-2xl shadow-cyan-900 group-hover:rotate-12 transition-transform duration-500">AR</div>
                    <h3 class="text-3xl font-black italic tracking-tight">Andhika Rastra</h3>
                    <p class="text-cyan-500 font-black uppercase tracking-[0.3em] text-xs mt-3">Lead Project</p>
                    <p class="text-gray-600 text-xs mt-5 font-bold uppercase">NRP: 502XXXXXXXX</p>
                </div>
                <div class="group">
                    <div class="w-44 h-44 bg-gray-800 rounded-[3.5rem] mx-auto mb-10 flex items-center justify-center font-black text-6xl group-hover:-rotate-12 transition-transform duration-500">T1</div>
                    <h3 class="text-3xl font-black italic tracking-tight text-gray-300">Nama Teman 1</h3>
                    <p class="text-gray-600 font-black uppercase tracking-[0.3em] text-xs mt-3">Machine Learning</p>
                </div>
                <div class="group">
                    <div class="w-44 h-44 bg-gray-800 rounded-[3.5rem] mx-auto mb-10 flex items-center justify-center font-black text-6xl group-hover:scale-110 transition-transform duration-500 text-gray-300">T2</div>
                    <h3 class="text-3xl font-black italic tracking-tight text-gray-300">Nama Teman 2</h3>
                    <p class="text-gray-600 font-black uppercase tracking-[0.3em] text-xs mt-3">Data Scientist</p>
                </div>
            </div>
            
            <div id="contact" class="mt-48 pt-20 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-10">
                <div class="text-left">
                    <p class="font-black text-gray-400 italic text-xl uppercase tracking-tighter">TB-SCAN.AI</p>
                    <p class="text-gray-700 font-bold text-xs mt-1">Sistem Pendeteksi Tuberkulosis Mandiri</p>
                </div>
                <div class="flex gap-10 font-black text-xs uppercase tracking-[0.3em] text-gray-500">
                    <a href="mailto:dhikaaaa12@gmail.com" class="hover:text-cyan-500 transition-colors">Business Inquiry</a>
                    <span class="text-white/10">|</span>
                    <span class="text-white">SURABAYA, IDN</span>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    // Logic Preview Nama File
    const imgInput = document.getElementById('imageInput');
    const filePrev = document.getElementById('filePreview');
    const nameDisp = document.getElementById('fileName');
    const btnSubmit = document.getElementById('submitBtn');

    imgInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            nameDisp.textContent = this.files[0].name;
            filePrev.classList.remove('hidden');
            filePrev.classList.add('flex');
        }
    });

    // Animasi Loading Tombol
    document.getElementById('uploadForm').addEventListener('submit', function() {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `
            <svg class="animate-spin h-6 w-6 text-white" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg> 
            <span class="ml-2 uppercase tracking-widest">Processing Image...</span>
        `;
        btnSubmit.classList.add('opacity-50', 'cursor-wait');
    });
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>