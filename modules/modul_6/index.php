<?php
// ===== SSO INTEGRATION (MEDWEB) =====
require_once __DIR__ . '/../../core/auth.php';

// Pastikan session aktif
startSession();

// Cek login (kalau belum login → redirect ke login utama)
requireLogin();

// Ambil data user dari sistem utama
$user = getCurrentUser();
$userInitials = getUserInitials();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NeuroAI System | Advanced Brain Tumor Segmentation</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
<script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
            heading: ['Outfit', 'sans-serif'],
          },
          colors: {
            deep: '#030712',
            medical: {
                cyan: '#06b6d4',
                blue: '#3b82f6',
                accent: '#00f2fe'
            }
          }
        }
      }
    }
</script>
<style>
    body {
        background-color: #030712;
        color: #e2e8f0;
        overflow-x: hidden;
    }

    /* Grid background for technical feel */
    .bg-grid {
        background-size: 40px 40px;
        background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                          linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        mask-image: radial-gradient(ellipse at center, black 40%, transparent 80%);
        -webkit-mask-image: radial-gradient(ellipse at center, black 40%, transparent 80%);
        z-index: -1;
    }

    /* Ambient glows */
    .glow-cyan {
        background: radial-gradient(circle, rgba(6, 182, 212, 0.15) 0%, rgba(0,0,0,0) 70%);
        filter: blur(80px);
    }
    .glow-blue {
        background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, rgba(0,0,0,0) 70%);
        filter: blur(80px);
    }

    /* Glassmorphism Classes */
    .glass-card {
        background: rgba(17, 24, 39, 0.6);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .glass-card:hover {
        transform: translateY(-5px);
        border-color: rgba(6, 182, 212, 0.4);
        box-shadow: 0 0 30px rgba(6, 182, 212, 0.15);
    }

    .glass-card-static {
        background: rgba(17, 24, 39, 0.6);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    }

    /* Holographic Text */
    .text-holo {
        background: linear-gradient(to right, #ffffff, #00f2fe, #4facfe);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        color: transparent;
    }

    /* Scanner Animation */
    .brain-container {
        position: relative;
        display: inline-block;
    }

    .brain-img {
        width: 320px;
        filter: drop-shadow(0 0 30px rgba(6, 182, 212, 0.4)) contrast(1.1);
        animation: pulse-glow 4s ease-in-out infinite alternate;
    }

    .scanner-line {
        position: absolute;
        width: 120%;
        left: -10%;
        height: 2px;
        background: #00f2fe;
        box-shadow: 0 0 20px 5px rgba(0, 242, 254, 0.5);
        animation: scanline 4s linear infinite;
        z-index: 10;
    }

    .scanner-area {
        position: absolute;
        width: 100%;
        left: 0;
        top: 0;
        background: linear-gradient(to bottom, rgba(0,242,254,0.2), transparent);
        animation: scanarea 4s linear infinite;
        pointer-events: none;
    }

    @keyframes scanline {
        0% { top: 5%; opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { top: 95%; opacity: 0; }
    }

    @keyframes scanarea {
        0% { height: 0; top: 5%; opacity: 0;}
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { height: 50px; top: 95%; opacity: 0;}
    }

    @keyframes pulse-glow {
        0% { filter: drop-shadow(0 0 15px rgba(6, 182, 212, 0.3)); }
        100% { filter: drop-shadow(0 0 35px rgba(6, 182, 212, 0.7)); }
    }

    /* Floating Data Nodes */
    .data-node {
        position: absolute;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #00f2fe;
        box-shadow: 0 0 10px #00f2fe;
    }

    .connecting-line {
        position: absolute;
        background: linear-gradient(90deg, rgba(0,242,254,0.8), transparent);
        height: 1px;
        transform-origin: left center;
        opacity: 0.6;
    }

    /* Avatar Styling */
    .avatar-frame {
        position: relative;
        padding: 4px;
        background: linear-gradient(135deg, rgba(6,182,212,0.6), rgba(59,130,246,0.6));
        border-radius: 50%;
    }
    
    .avatar-inner {
        border-radius: 50%;
        border: 4px solid #030712;
        overflow: hidden;
    }
    
    /* Medical Cross Accent overlay */
    .medical-cross-overlay {
        background-image: radial-gradient(circle at center, transparent 40%, #030712 100%), 
                          url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="none"><path fill="rgba(6,182,212,0.03)" d="M45 0h10v100H45zM0 45h100v10H0z"/></svg>');
    }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 100;
        background: rgba(0,0,0,0.7);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    .modal-overlay.active {
        opacity: 1;
        pointer-events: all;
    }
    .modal-content {
        background: rgba(17, 24, 39, 0.95);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 1.5rem;
        box-shadow: 0 25px 80px rgba(0,0,0,0.5), 0 0 60px rgba(6,182,212,0.1);
        max-width: 600px;
        width: 95%;
        max-height: 90vh;
        overflow-y: auto;
        transform: translateY(30px) scale(0.95);
        transition: transform 0.35s cubic-bezier(0.4,0,0.2,1);
    }
    .modal-overlay.active .modal-content {
        transform: translateY(0) scale(1);
    }

    /* Drag-Drop Zone */
    .dropzone {
        border: 2px dashed rgba(6,182,212,0.3);
        border-radius: 1rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .dropzone:hover, .dropzone.dragover {
        border-color: rgba(6,182,212,0.8);
        background: rgba(6,182,212,0.05);
        box-shadow: inset 0 0 30px rgba(6,182,212,0.08);
    }

    /* Form Input Styles */
    .form-input {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 0.75rem;
        color: #e2e8f0;
        padding: 0.75rem 1rem;
        width: 100%;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }
    .form-input:focus {
        outline: none;
        border-color: rgba(6,182,212,0.6);
        box-shadow: 0 0 0 3px rgba(6,182,212,0.15);
        background: rgba(255,255,255,0.06);
    }
    .form-input::placeholder { color: rgba(255,255,255,0.25); }
    .form-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #94a3b8;
        margin-bottom: 0.5rem;
    }

    select.form-input {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        padding-right: 2.5rem;
    }

    /* Table Styles */
    .scan-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .scan-table thead th {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #64748b;
        padding: 1rem 1rem;
        text-align: left;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .scan-table tbody tr {
        transition: background 0.2s;
    }
    .scan-table tbody tr:hover {
        background: rgba(6,182,212,0.04);
    }
    .scan-table tbody td {
        padding: 0.875rem 1rem;
        font-size: 0.85rem;
        border-bottom: 1px solid rgba(255,255,255,0.03);
        vertical-align: middle;
    }

    /* Status Badges */
    .badge { 
        display: inline-flex; align-items: center; gap: 0.375rem;
        padding: 0.25rem 0.75rem; border-radius: 9999px;
        font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;
    }
    .badge-pending { background: rgba(234,179,8,0.12); color: #facc15; }
    .badge-processing { background: rgba(59,130,246,0.12); color: #60a5fa; }
    .badge-completed { background: rgba(34,197,94,0.12); color: #4ade80; }

    /* Toast */
    .toast-container {
        position: fixed; top: 1.5rem; right: 1.5rem; z-index: 200;
        display: flex; flex-direction: column; gap: 0.75rem;
    }
    .toast {
        padding: 1rem 1.5rem; border-radius: 1rem;
        font-size: 0.875rem; font-weight: 500;
        box-shadow: 0 10px 40px rgba(0,0,0,0.4);
        transform: translateX(120%);
        transition: transform 0.4s cubic-bezier(0.4,0,0.2,1);
        max-width: 400px;
    }
    .toast.show { transform: translateX(0); }
    .toast-success { background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.3); color: #4ade80; }
    .toast-error { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #f87171; }

    /* Progress bar */
    .upload-progress-bar {
        height: 4px;
        background: linear-gradient(90deg, #06b6d4, #3b82f6);
        border-radius: 999px;
        transition: width 0.3s ease;
    }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: rgba(255,255,255,0.02); }
    ::-webkit-scrollbar-thumb { background: rgba(6,182,212,0.3); border-radius: 999px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(6,182,212,0.5); }

</style>
</head>
<body class="relative min-h-screen antialiased selection:bg-medical-cyan selection:text-white">

    <!-- Background Elements -->
    <div class="fixed inset-0 bg-grid pointer-events-none"></div>
    <div class="fixed inset-0 medical-cross-overlay pointer-events-none z-[-1]"></div>
    <div class="fixed top-[-20%] left-[-10%] w-[800px] h-[800px] glow-cyan rounded-full pointer-events-none"></div>
    <div class="fixed bottom-[-20%] right-[-10%] w-[800px] h-[800px] glow-blue rounded-full pointer-events-none"></div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Navigation -->
    <nav class="fixed w-full z-50 glass-card-static border-b border-white/5 !rounded-none py-4 px-8">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-medical-cyan to-medical-blue flex items-center justify-center shadow-[0_0_15px_rgba(6,182,212,0.5)]">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <span class="font-heading font-bold text-xl tracking-wider text-white">Neuro<span class="text-medical-cyan">AI</span></span>
            </div>
            <div class="hidden md:flex gap-8 text-sm font-medium text-gray-400">
                <a href="#hero" class="hover:text-white transition-colors">Beranda</a>
                <a href="#features" class="hover:text-white transition-colors">Kemampuan</a>
                <a href="#scan-history" class="hover:text-white transition-colors">Riwayat Scan</a>
                <a href="#team" class="hover:text-white transition-colors">Peneliti</a>
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden sm:flex items-center gap-2 text-sm text-gray-400">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-medical-cyan to-medical-blue flex items-center justify-center text-white text-xs font-bold">
                        <?= htmlspecialchars($userInitials) ?>
                    </div>
                    <span class="text-gray-300 font-medium"><?= htmlspecialchars($user['name'] ?? 'User') ?></span>
                </div>
                <a href="<?= BASE_URL ?>/auth/logout.php" class="px-4 py-2 rounded-full bg-white/5 border border-white/10 text-gray-400 hover:text-red-400 hover:border-red-400/30 transition-all text-sm font-medium">
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="hero" class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 px-6 overflow-hidden">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 items-center">
            
            <div class="relative z-10 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass-card-static border-medical-cyan/30 mb-8 border border-white/5">
                    <span class="w-2 h-2 rounded-full bg-medical-cyan animate-pulse"></span>
                    <span class="text-xs font-medium text-medical-cyan uppercase tracking-widest">Sistem Online v2.0</span>
                </div>
                
                <h1 class="text-5xl lg:text-7xl font-heading font-extrabold leading-tight mb-6">
                    Akurasi Masa Depan<br/>
                    <span class="text-holo">Segmentasi Otak</span>
                </h1>
                
                <p class="text-lg text-gray-400 mb-10 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    Sistem Artificial Intelligence medis tingkat lanjut. Dirancang khusus untuk menganalisis pemindaian MRI dan mendeteksi tumor dengan presisi tinggi.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <button onclick="openUploadModal()" id="btnUploadHero" class="px-8 py-4 rounded-xl bg-gradient-to-r from-medical-cyan to-medical-blue text-white font-semibold flex items-center justify-center gap-3 hover:shadow-[0_0_25px_rgba(6,182,212,0.4)] transition-all transform hover:-translate-y-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Upload MRI Scan
                    </button>
                    <a href="#scan-history" class="px-8 py-4 rounded-xl glass-card-static border border-white/10 text-white font-semibold flex items-center justify-center gap-3 hover:bg-white/5 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        Riwayat Scan
                    </a>
                </div>
            </div>

            <div class="relative z-10 flex justify-center mt-12 lg:mt-0">
                <div class="brain-container">
                    <img src="assets/images/BrainTumor.jpg" alt="AI Brain Analysis" class="brain-img relative z-10 rounded-2xl" style="object-fit: cover;">
                    <div class="scanner-line"></div>
                    <div class="scanner-area"></div>
                    
                    <!-- Floating Data Points indicating AI Analysis -->
                    <div class="absolute top-[20%] left-[10%] hidden md:block z-20">
                        <div class="data-node"></div>
                        <div class="connecting-line w-16 -rotate-45"></div>
                        <div class="absolute -top-6 -left-24 glass-card-static px-3 py-1.5 text-[11px] font-medium text-medical-accent rounded border-[0.5px] border-medical-cyan/30 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-medical-cyan animate-pulse"></span>
                            Lobus Frontal: Normal
                        </div>
                    </div>
                    
                    <div class="absolute top-[60%] right-[5%] hidden md:block z-20">
                        <div class="data-node"></div>
                        <div class="connecting-line w-20 rotate-12"></div>
                        <div class="absolute -top-4 left-24 glass-card-static px-3 py-1.5 text-[11px] font-medium text-red-400 rounded border-[0.5px] border-red-500/30 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                            Deteksi Anomali
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-10 border-y border-white/5 bg-white/[0.01]">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-white/5">
                <div>
                    <div class="text-4xl font-heading font-bold text-white mb-2">99.8%</div>
                    <div class="text-sm text-gray-500 uppercase tracking-wider font-medium">Akurasi</div>
                </div>
                <div>
                    <div class="text-4xl font-heading font-bold text-white mb-2">&lt;2s</div>
                    <div class="text-sm text-gray-500 uppercase tracking-wider font-medium">Waktu Proses</div>
                </div>
                <div>
                    <div class="text-4xl font-heading font-bold text-white mb-2">3D</div>
                    <div class="text-sm text-gray-500 uppercase tracking-wider font-medium">Visualisasi</div>
                </div>
                <div>
                    <div class="text-4xl font-heading font-bold text-white mb-2">DICOM</div>
                    <div class="text-sm text-gray-500 uppercase tracking-wider font-medium">Dukungan File</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-24 px-6 relative z-10">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-heading font-bold mb-4">Kemampuan <span class="text-medical-cyan">Inti</span></h2>
                <p class="text-gray-400 max-w-2xl mx-auto">NeuroAI membantu radiologis menganalisis pemindaian MRI menggunakan algoritma mutakhir yang dilatih secara khusus untuk mendeteksi berbagai jenis anomali otak.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass-card p-8 rounded-2xl group cursor-pointer relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-medical-cyan/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="w-16 h-16 rounded-xl bg-medical-cyan/10 flex items-center justify-center mb-6 group-hover:bg-medical-cyan/20 transition-colors border border-medical-cyan/20 group-hover:border-medical-cyan/50 relative z-10">
                        <svg class="w-8 h-8 text-medical-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    </div>
                    <h3 class="text-xl font-heading font-semibold text-white mb-3 relative z-10">Upload MRI Aman</h3>
                    <p class="text-gray-400 leading-relaxed text-sm relative z-10">Unggah file medis (DICOM, NIfTI) secara aman dengan enkripsi tingkat rumah sakit. Perlindungan privasi pasien terjamin.</p>
                </div>

                <!-- Feature 2 -->
                <div class="glass-card p-8 rounded-2xl group cursor-pointer relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-medical-blue/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="w-16 h-16 rounded-xl bg-medical-blue/10 flex items-center justify-center mb-6 group-hover:bg-medical-blue/20 transition-colors border border-medical-blue/20 group-hover:border-medical-blue/50 relative z-10 shadow-[0_0_30px_rgba(59,130,246,0.3)]">
                        <svg class="w-8 h-8 text-medical-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-heading font-semibold text-white mb-3 relative z-10">Segmentasi AI Otomatis</h3>
                    <p class="text-gray-400 leading-relaxed text-sm relative z-10">Isolasi dan klasifikasi otomatis menggunakan model Machine Learning yang memisahkan area edema dan jaringan tumor dalam hitungan detik.</p>
                </div>

                <!-- Feature 3 -->
                <div class="glass-card p-8 rounded-2xl group cursor-pointer relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="w-16 h-16 rounded-xl bg-white/5 flex items-center justify-center mb-6 border border-white/10 group-hover:border-white/30 transition-colors relative z-10">
                        <svg class="w-8 h-8 text-gray-300 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"></path></svg>
                    </div>
                    <h3 class="text-xl font-heading font-semibold text-white mb-3 relative z-10">Visualisasi Intuitif 3D</h3>
                    <p class="text-gray-400 leading-relaxed text-sm relative z-10">Eksplorasi hasil segmentasi secara volumetrik! Putar, potong, dan teliti organ dan area anomali langsung melalui browser.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================================================== -->
    <!-- SCAN HISTORY SECTION (CRUD) -->
    <!-- ================================================================== -->
    <section id="scan-history" class="py-24 px-6 relative z-10">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
                <div>
                    <h2 class="text-3xl lg:text-4xl font-heading font-bold mb-2">Riwayat <span class="text-medical-cyan">MRI Scan</span></h2>
                    <p class="text-gray-400">Kelola dan pantau semua pemindaian MRI yang telah diunggah.</p>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Search -->
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" id="searchInput" placeholder="Cari pasien..." class="form-input pl-10 w-48 md:w-64 text-sm">
                    </div>
                    <!-- Upload Button -->
                    <button onclick="openUploadModal()" class="px-6 py-3 rounded-xl bg-gradient-to-r from-medical-cyan to-medical-blue text-white font-semibold flex items-center gap-2 hover:shadow-[0_0_25px_rgba(6,182,212,0.4)] transition-all text-sm whitespace-nowrap">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Upload Baru
                    </button>
                </div>
            </div>

            <!-- Data Table -->
            <div class="glass-card-static rounded-2xl overflow-hidden border border-white/5">
                <!-- Loading State -->
                <div id="tableLoading" class="py-20 text-center">
                    <div class="inline-block w-8 h-8 border-2 border-medical-cyan/30 border-t-medical-cyan rounded-full animate-spin mb-4"></div>
                    <p class="text-gray-500 text-sm">Memuat data...</p>
                </div>

                <!-- Empty State -->
                <div id="tableEmpty" class="py-20 text-center hidden">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-white/5 flex items-center justify-center border border-white/10">
                        <svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="text-lg font-heading font-semibold text-white mb-2">Belum Ada Data Scan</h3>
                    <p class="text-gray-500 text-sm mb-6">Mulai dengan mengunggah file MRI pertama Anda.</p>
                    <button onclick="openUploadModal()" class="px-6 py-3 rounded-xl bg-gradient-to-r from-medical-cyan to-medical-blue text-white font-semibold text-sm hover:shadow-[0_0_25px_rgba(6,182,212,0.4)] transition-all inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Upload MRI Pertama
                    </button>
                </div>

                <!-- Table -->
                <div id="tableContainer" class="hidden overflow-x-auto">
                    <table class="scan-table">
                        <thead>
                            <tr>
                                <th>Preview</th>
                                <th>Pasien</th>
                                <th>Tipe Scan</th>
                                <th>Detail</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="scanTableBody">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div id="paginationContainer" class="hidden px-6 py-4 border-t border-white/5 flex justify-between items-center">
                    <div class="text-xs text-gray-500" id="paginationInfo"></div>
                    <div class="flex items-center gap-2" id="paginationButtons"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section id="team" class="py-24 px-6 relative z-10 bg-[rgba(3,7,18,0.7)] border-y border-white/5">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-heading font-bold mb-4">Tim <span class="text-medical-blue">Pengembang</span></h2>
                <p class="text-gray-400 max-w-2xl mx-auto">Tim mahasiswa yang mengembangkan inovasi dengan menggabungkan Artificial Intelligence dan teknologi medis.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-12 lg:gap-8 max-w-5xl mx-auto">
                <!-- Team 1 -->
                <div class="text-center group">
                    <div class="avatar-frame w-40 h-40 mx-auto mb-6 transition-transform duration-500 group-hover:translate-y-[-10px] group-hover:shadow-[0_0_30px_rgba(6,182,212,0.4)]">
                        <img src="assets/images/Tsamarah Amelia Putri Ginting-foto.jpeg" class="avatar-inner w-full h-full object-cover grayscale opacity-80 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-500" alt="Tsamarah Amelia" onerror="this.src='https://ui-avatars.com/api/?name=Tsamarah+Amelia&background=0D8ABC&color=fff&size=200&font-size=0.33'">
                    </div>
                    <h3 class="text-xl font-heading font-semibold text-white">Tsamarah Amelia Putri Ginting</h3>
                    <div class="flex items-center justify-center gap-2 mt-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-medical-cyan"></span>
                        <p class="text-sm text-medical-cyan uppercase tracking-wider font-medium">Lead AI Researcher</p>
                    </div>
                </div>

                <!-- Team 2 -->
                <div class="text-center group">
                    <div class="avatar-frame w-40 h-40 mx-auto mb-6 transition-transform duration-500 group-hover:translate-y-[-10px] group-hover:shadow-[0_0_30px_rgba(59,130,246,0.4)]">
                        <img src="assets/images/Kezia Martha Stephanie Silaban.jpeg" class="avatar-inner w-full h-full object-cover grayscale opacity-80 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-500" alt="Kezia Martha" onerror="this.src='https://ui-avatars.com/api/?name=Kezia+Martha&background=0D8ABC&color=fff&size=200&font-size=0.33'">
                    </div>
                    <h3 class="text-xl font-heading font-semibold text-white">Kezia Martha Stephanie Silaban</h3>
                    <div class="flex items-center justify-center gap-2 mt-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-medical-blue"></span>
                        <p class="text-sm text-medical-blue uppercase tracking-wider font-medium">Medical Image Analyst</p>
                    </div>
                </div>

                <!-- Team 3 -->
                <div class="text-center group">
                    <div class="avatar-frame w-40 h-40 mx-auto mb-6 transition-transform duration-500 group-hover:translate-y-[-10px] group-hover:shadow-[0_0_30px_rgba(0,242,254,0.4)]">
                        <img src="assets/images/Cintya Melati Sianipar.jpeg" class="avatar-inner w-full h-full object-cover grayscale opacity-80 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-500" alt="Cintya Melati" onerror="this.src='https://ui-avatars.com/api/?name=Cintya+Melati&background=0D8ABC&color=fff&size=200&font-size=0.33'">
                    </div>
                    <h3 class="text-xl font-heading font-semibold text-white">Cintya Melati Sianipar</h3>
                    <div class="flex items-center justify-center gap-2 mt-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-medical-accent"></span>
                        <p class="text-sm text-medical-accent uppercase tracking-wider font-medium">Fullstack UI/UX Engineer</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 relative z-10 bg-[#030712] border-t border-white/5">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-md bg-gradient-to-br from-medical-cyan to-medical-blue flex items-center justify-center shadow-[0_0_10px_rgba(6,182,212,0.3)]">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <span class="font-heading font-bold text-lg tracking-wider text-white">Neuro<span class="text-medical-cyan">AI</span></span>
            </div>
            <p class="text-gray-500 text-sm font-medium">© 2026 NeuroAI System. Mendiagnosis Masa Depan.</p>
        </div>
    </footer>

    <!-- ================================================================== -->
    <!-- UPLOAD MODAL -->
    <!-- ================================================================== -->
    <div class="modal-overlay" id="uploadModal">
        <div class="modal-content p-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-medical-cyan to-medical-blue flex items-center justify-center shadow-[0_0_20px_rgba(6,182,212,0.3)]">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-heading font-bold text-white">Upload MRI Scan</h3>
                        <p class="text-xs text-gray-500">Unggah file pemindaian MRI baru</p>
                    </div>
                </div>
                <button onclick="closeUploadModal()" class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-gray-400 hover:text-white hover:border-white/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="uploadForm" enctype="multipart/form-data">
                <!-- Dropzone -->
                <div class="dropzone p-8 text-center mb-6" id="dropzone">
                    <input type="file" name="mri_file" id="mriFileInput" class="hidden" accept=".jpg,.jpeg,.png,.gif,.webp,.bmp,.dcm,.nii">
                    <div id="dropzoneDefault">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-medical-cyan/10 flex items-center justify-center border border-medical-cyan/20">
                            <svg class="w-8 h-8 text-medical-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </div>
                        <p class="text-white font-medium mb-1">Drag & drop file MRI disini</p>
                        <p class="text-xs text-gray-500 mb-4">atau klik untuk memilih file</p>
                        <p class="text-[11px] text-gray-600">JPG, PNG, DICOM (.dcm), NIfTI (.nii) • Maks 50MB</p>
                    </div>
                    <div id="dropzonePreview" class="hidden">
                        <div class="flex items-center gap-4 text-left">
                            <div class="w-16 h-16 rounded-xl bg-medical-cyan/10 border border-medical-cyan/20 flex items-center justify-center flex-shrink-0 overflow-hidden" id="filePreviewThumb">
                                <svg class="w-8 h-8 text-medical-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-white font-medium text-sm truncate" id="selectedFileName">-</p>
                                <p class="text-xs text-gray-500" id="selectedFileSize">-</p>
                            </div>
                            <button type="button" onclick="clearFileSelection(event)" class="text-gray-500 hover:text-red-400 transition-colors p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div id="uploadProgressContainer" class="hidden mb-6">
                    <div class="flex justify-between text-xs text-gray-500 mb-2">
                        <span>Mengunggah...</span>
                        <span id="uploadProgressText">0%</span>
                    </div>
                    <div class="w-full bg-white/5 rounded-full overflow-hidden">
                        <div class="upload-progress-bar" id="uploadProgressBar" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="col-span-2">
                        <label class="form-label">Nama Pasien <span class="text-red-400">*</span></label>
                        <input type="text" name="patient_name" class="form-input" placeholder="Masukkan nama lengkap pasien" required>
                    </div>
                    <div>
                        <label class="form-label">Usia</label>
                        <input type="number" name="patient_age" class="form-input" placeholder="Usia" min="0" max="150">
                    </div>
                    <div>
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="patient_gender" class="form-input">
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tipe Scan</label>
                        <select name="scan_type" class="form-input">
                            <option value="T1">T1-Weighted</option>
                            <option value="T2">T2-Weighted</option>
                            <option value="FLAIR">FLAIR</option>
                            <option value="DWI">DWI</option>
                            <option value="SWI">SWI</option>
                            <option value="Other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">&nbsp;</label>
                    </div>
                    <div class="col-span-2">
                        <label class="form-label">Catatan / Deskripsi</label>
                        <textarea name="description" class="form-input" rows="3" placeholder="Catatan klinis (opsional)"></textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-white/5">
                    <button type="button" onclick="closeUploadModal()" class="px-6 py-3 rounded-xl bg-white/5 border border-white/10 text-gray-400 hover:text-white hover:border-white/20 font-medium text-sm transition-all">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitUpload" class="px-8 py-3 rounded-xl bg-gradient-to-r from-medical-cyan to-medical-blue text-white font-semibold text-sm hover:shadow-[0_0_25px_rgba(6,182,212,0.4)] transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Upload & Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================================================================== -->
    <!-- DETAIL MODAL -->
    <!-- ================================================================== -->
    <div class="modal-overlay" id="detailModal">
        <div class="modal-content p-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-heading font-bold text-white">Detail MRI Scan</h3>
                <button onclick="closeDetailModal()" class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-gray-400 hover:text-white hover:border-white/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="detailContent">
                <!-- Populated by JS -->
            </div>
        </div>
    </div>

    <!-- ================================================================== -->
    <!-- EDIT MODAL -->
    <!-- ================================================================== -->
    <div class="modal-overlay" id="editModal">
        <div class="modal-content p-8">
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center border border-amber-500/20">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-heading font-bold text-white">Edit Data MRI</h3>
                        <p class="text-xs text-gray-500">Perbarui informasi pemindaian</p>
                    </div>
                </div>
                <button onclick="closeEditModal()" class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-gray-400 hover:text-white hover:border-white/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="editForm">
                <input type="hidden" name="id" id="editId">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="col-span-2">
                        <label class="form-label">Nama Pasien <span class="text-red-400">*</span></label>
                        <input type="text" name="patient_name" id="editPatientName" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Usia</label>
                        <input type="number" name="patient_age" id="editPatientAge" class="form-input" min="0" max="150">
                    </div>
                    <div>
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="patient_gender" id="editPatientGender" class="form-input">
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tipe Scan</label>
                        <select name="scan_type" id="editScanType" class="form-input">
                            <option value="T1">T1-Weighted</option>
                            <option value="T2">T2-Weighted</option>
                            <option value="FLAIR">FLAIR</option>
                            <option value="DWI">DWI</option>
                            <option value="SWI">SWI</option>
                            <option value="Other">Lainnya</option>
                        </select>
                    </div>
                    <div><label class="form-label">&nbsp;</label></div>
                    <div class="col-span-2">
                        <label class="form-label">Catatan / Deskripsi</label>
                        <textarea name="description" id="editDescription" class="form-input" rows="3"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-white/5">
                    <button type="button" onclick="closeEditModal()" class="px-6 py-3 rounded-xl bg-white/5 border border-white/10 text-gray-400 hover:text-white font-medium text-sm transition-all">Batal</button>
                    <button type="submit" id="btnSubmitEdit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold text-sm hover:shadow-[0_0_25px_rgba(234,179,8,0.4)] transition-all flex items-center gap-2 disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================================================================== -->
    <!-- DELETE CONFIRM MODAL -->
    <!-- ================================================================== -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-content p-8 max-w-md">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-red-500/10 flex items-center justify-center border border-red-500/20">
                    <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 class="text-lg font-heading font-bold text-white mb-2">Hapus Data MRI?</h3>
                <p class="text-sm text-gray-400 mb-6" id="deleteConfirmText">Data ini akan dihapus secara permanen termasuk file yang terunggah.</p>
                <div class="flex justify-center gap-3">
                    <button onclick="closeDeleteModal()" class="px-6 py-3 rounded-xl bg-white/5 border border-white/10 text-gray-400 hover:text-white font-medium text-sm transition-all">Batal</button>
                    <button onclick="confirmDelete()" id="btnConfirmDelete" class="px-8 py-3 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white font-semibold text-sm hover:shadow-[0_0_25px_rgba(239,68,68,0.4)] transition-all flex items-center gap-2 disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus Permanen
                    </button>
                </div>
            </div>
        </div>
    </div>


<!-- ================================================================== -->
<!-- JAVASCRIPT -->
<!-- ================================================================== -->
<script>
const API_BASE = 'api';
let currentPage = 1;
let searchTimeout = null;
let deleteTargetId = null;

// ── Toast ───────────────────────────────────────────────────────────
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${type === 'success' 
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>' 
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'}
            </svg>
            <span>${message}</span>
        </div>
    `;
    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 50);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

// ── Upload Modal ────────────────────────────────────────────────────
function openUploadModal() {
    document.getElementById('uploadModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeUploadModal() {
    document.getElementById('uploadModal').classList.remove('active');
    document.body.style.overflow = '';
    document.getElementById('uploadForm').reset();
    clearFileSelection();
    document.getElementById('uploadProgressContainer').classList.add('hidden');
}

// ── Detail Modal ────────────────────────────────────────────────────
function openDetailModal(id) {
    const modal = document.getElementById('detailModal');
    const content = document.getElementById('detailContent');
    content.innerHTML = '<div class="py-10 text-center"><div class="inline-block w-6 h-6 border-2 border-medical-cyan/30 border-t-medical-cyan rounded-full animate-spin mb-3"></div><p class="text-gray-500 text-sm">Memuat...</p></div>';
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    fetch(`${API_BASE}/detail.php?id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) { showToast(res.message, 'error'); closeDetailModal(); return; }
            const d = res.data;
            const isImage = d.file_type && d.file_type.startsWith('image/');
            content.innerHTML = `
                <div class="mb-6 rounded-xl overflow-hidden bg-black/30 border border-white/5 flex items-center justify-center" style="max-height:300px;">
                    ${isImage 
                        ? `<img src="${d.thumbnail_url}" alt="MRI" class="max-h-[300px] object-contain w-full">`
                        : `<div class="py-16 text-center"><svg class="w-16 h-16 mx-auto text-gray-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><p class="text-gray-500 text-sm">${d.file_name}</p></div>`
                    }
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="glass-card-static p-4 rounded-xl border border-white/5">
                        <p class="text-[10px] uppercase tracking-widest text-gray-500 mb-1">Pasien</p>
                        <p class="text-white font-medium">${escHtml(d.patient_name)}</p>
                    </div>
                    <div class="glass-card-static p-4 rounded-xl border border-white/5">
                        <p class="text-[10px] uppercase tracking-widest text-gray-500 mb-1">Usia / Gender</p>
                        <p class="text-white font-medium">${d.patient_age || '-'} / ${d.patient_gender || '-'}</p>
                    </div>
                    <div class="glass-card-static p-4 rounded-xl border border-white/5">
                        <p class="text-[10px] uppercase tracking-widest text-gray-500 mb-1">Tipe Scan</p>
                        <p class="text-medical-cyan font-semibold">${d.scan_type}</p>
                    </div>
                    <div class="glass-card-static p-4 rounded-xl border border-white/5">
                        <p class="text-[10px] uppercase tracking-widest text-gray-500 mb-1">Ukuran File</p>
                        <p class="text-white font-medium">${d.file_size_formatted}</p>
                    </div>
                    <div class="glass-card-static p-4 rounded-xl border border-white/5">
                        <p class="text-[10px] uppercase tracking-widest text-gray-500 mb-1">Status</p>
                        <span class="badge badge-${d.diagnosis_status}"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>${statusLabel(d.diagnosis_status)}</span>
                    </div>
                    <div class="glass-card-static p-4 rounded-xl border border-white/5">
                        <p class="text-[10px] uppercase tracking-widest text-gray-500 mb-1">Tanggal Upload</p>
                        <p class="text-white font-medium text-sm">${formatDate(d.created_at)}</p>
                    </div>
                    ${d.description ? `<div class="col-span-2 glass-card-static p-4 rounded-xl border border-white/5"><p class="text-[10px] uppercase tracking-widest text-gray-500 mb-1">Catatan</p><p class="text-gray-300 text-sm">${escHtml(d.description)}</p></div>` : ''}
                </div>
            `;
        })
        .catch(() => {
            showToast('Gagal memuat detail.', 'error');
            closeDetailModal();
        });
}
function closeDetailModal() {
    document.getElementById('detailModal').classList.remove('active');
    document.body.style.overflow = '';
}

// ── Edit Modal ──────────────────────────────────────────────────────
function openEditModal(id) {
    fetch(`${API_BASE}/detail.php?id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) { showToast(res.message, 'error'); return; }
            const d = res.data;
            document.getElementById('editId').value = d.id;
            document.getElementById('editPatientName').value = d.patient_name;
            document.getElementById('editPatientAge').value = d.patient_age || '';
            document.getElementById('editPatientGender').value = d.patient_gender || '';
            document.getElementById('editScanType').value = d.scan_type;
            document.getElementById('editDescription').value = d.description || '';
            document.getElementById('editModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        })
        .catch(() => showToast('Gagal memuat data.', 'error'));
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
    document.body.style.overflow = '';
}

// ── Delete Modal ────────────────────────────────────────────────────
function openDeleteModal(id, name) {
    deleteTargetId = id;
    document.getElementById('deleteConfirmText').textContent = `Data MRI untuk "${name}" akan dihapus secara permanen termasuk file yang terunggah.`;
    document.getElementById('deleteModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.body.style.overflow = '';
    deleteTargetId = null;
}

// ── File Selection & Drag-Drop ──────────────────────────────────────
const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('mriFileInput');

dropzone.addEventListener('click', () => fileInput.click());
dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('dragover'); });
dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        showFilePreview(e.dataTransfer.files[0]);
    }
});
fileInput.addEventListener('change', () => {
    if (fileInput.files.length) showFilePreview(fileInput.files[0]);
});

function showFilePreview(file) {
    document.getElementById('selectedFileName').textContent = file.name;
    document.getElementById('selectedFileSize').textContent = formatBytes(file.size);
    document.getElementById('dropzoneDefault').classList.add('hidden');
    document.getElementById('dropzonePreview').classList.remove('hidden');

    // Show image thumbnail if possible
    const thumb = document.getElementById('filePreviewThumb');
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            thumb.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(file);
    }
}

function clearFileSelection(e) {
    if (e) e.stopPropagation();
    fileInput.value = '';
    document.getElementById('dropzoneDefault').classList.remove('hidden');
    document.getElementById('dropzonePreview').classList.add('hidden');
    document.getElementById('filePreviewThumb').innerHTML = '<svg class="w-8 h-8 text-medical-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
}

// ── Upload Form Submit ──────────────────────────────────────────────
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitUpload');
    const progressContainer = document.getElementById('uploadProgressContainer');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressText = document.getElementById('uploadProgressText');

    btn.disabled = true;
    btn.innerHTML = '<div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div> Mengupload...';
    progressContainer.classList.remove('hidden');

    const formData = new FormData(this);
    const xhr = new XMLHttpRequest();

    xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
            const pct = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = pct + '%';
            progressText.textContent = pct + '%';
        }
    });

    xhr.addEventListener('load', () => {
        try {
            const res = JSON.parse(xhr.responseText);
            if (res.success) {
                showToast(res.message, 'success');
                closeUploadModal();
                loadScans();
            } else {
                showToast(res.message, 'error');
            }
        } catch {
            showToast('Respons server tidak valid.', 'error');
        }
        resetUploadBtn();
    });

    xhr.addEventListener('error', () => {
        showToast('Gagal menghubungi server.', 'error');
        resetUploadBtn();
    });

    xhr.open('POST', `${API_BASE}/upload.php`);
    xhr.send(formData);

    function resetUploadBtn() {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg> Upload & Simpan';
    }
});

// ── Edit Form Submit ────────────────────────────────────────────────
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitEdit');
    btn.disabled = true;

    const formData = new FormData(this);

    fetch(`${API_BASE}/update.php`, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast(res.message, 'success');
                closeEditModal();
                loadScans();
            } else {
                showToast(res.message, 'error');
            }
        })
        .catch(() => showToast('Gagal menghubungi server.', 'error'))
        .finally(() => btn.disabled = false);
});

// ── Delete Confirm ──────────────────────────────────────────────────
function confirmDelete() {
    if (!deleteTargetId) return;
    const btn = document.getElementById('btnConfirmDelete');
    btn.disabled = true;

    const fd = new FormData();
    fd.append('id', deleteTargetId);

    fetch(`${API_BASE}/delete.php`, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast(res.message, 'success');
                closeDeleteModal();
                loadScans();
            } else {
                showToast(res.message, 'error');
            }
        })
        .catch(() => showToast('Gagal menghubungi server.', 'error'))
        .finally(() => btn.disabled = false);
}

// ── Load Scans (Main data loader) ───────────────────────────────────
function loadScans(page = 1) {
    currentPage = page;
    const search = document.getElementById('searchInput').value.trim();
    const loading = document.getElementById('tableLoading');
    const empty = document.getElementById('tableEmpty');
    const container = document.getElementById('tableContainer');
    const pagination = document.getElementById('paginationContainer');

    loading.classList.remove('hidden');
    empty.classList.add('hidden');
    container.classList.add('hidden');
    pagination.classList.add('hidden');

    const params = new URLSearchParams({ page, limit: 10 });
    if (search) params.append('search', search);

    fetch(`${API_BASE}/list.php?${params}`)
        .then(r => r.json())
        .then(res => {
            loading.classList.add('hidden');
            if (!res.success) { showToast(res.message, 'error'); return; }

            if (res.data.length === 0) {
                empty.classList.remove('hidden');
                return;
            }

            container.classList.remove('hidden');
            renderTable(res.data);
            renderPagination(res.pagination);
        })
        .catch(() => {
            loading.classList.add('hidden');
            empty.classList.remove('hidden');
        });
}

function renderTable(scans) {
    const tbody = document.getElementById('scanTableBody');
    tbody.innerHTML = scans.map(s => {
        const isImage = s.file_type && s.file_type.startsWith('image/');
        return `
        <tr>
            <td>
                <div class="w-12 h-12 rounded-lg overflow-hidden bg-white/5 border border-white/10 flex items-center justify-center">
                    ${isImage
                        ? `<img src="${s.thumbnail_url}" alt="" class="w-full h-full object-cover">`
                        : `<svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`
                    }
                </div>
            </td>
            <td>
                <p class="text-white font-medium text-sm">${escHtml(s.patient_name)}</p>
                <p class="text-gray-500 text-xs mt-0.5">${s.patient_gender || '-'}, ${s.patient_age || '-'} thn</p>
            </td>
            <td><span class="text-medical-cyan font-semibold text-xs">${s.scan_type}</span></td>
            <td>
                <p class="text-gray-400 text-xs">${s.file_name}</p>
                <p class="text-gray-600 text-[11px]">${s.file_size_formatted}</p>
            </td>
            <td><span class="badge badge-${s.diagnosis_status}"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>${statusLabel(s.diagnosis_status)}</span></td>
            <td><span class="text-gray-400 text-xs">${formatDate(s.created_at)}</span></td>
            <td>
                <div class="flex items-center justify-end gap-1">
                    <button onclick="openDetailModal(${s.id})" title="Detail" class="p-2 rounded-lg hover:bg-white/5 text-gray-500 hover:text-medical-cyan transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                    <button onclick="openEditModal(${s.id})" title="Edit" class="p-2 rounded-lg hover:bg-white/5 text-gray-500 hover:text-amber-400 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button onclick="openDeleteModal(${s.id}, '${escHtml(s.patient_name)}')" title="Hapus" class="p-2 rounded-lg hover:bg-white/5 text-gray-500 hover:text-red-400 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function renderPagination(pg) {
    if (pg.total_pages <= 1) return;
    const container = document.getElementById('paginationContainer');
    container.classList.remove('hidden');
    document.getElementById('paginationInfo').textContent = `Halaman ${pg.current_page} dari ${pg.total_pages} (${pg.total} data)`;

    const btns = document.getElementById('paginationButtons');
    btns.innerHTML = '';

    // Previous
    if (pg.current_page > 1) {
        btns.innerHTML += `<button onclick="loadScans(${pg.current_page - 1})" class="px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-gray-400 hover:text-white text-xs font-medium transition-all">← Prev</button>`;
    }

    // Page numbers
    const start = Math.max(1, pg.current_page - 2);
    const end = Math.min(pg.total_pages, pg.current_page + 2);
    for (let i = start; i <= end; i++) {
        const active = i === pg.current_page;
        btns.innerHTML += `<button onclick="loadScans(${i})" class="px-3 py-1.5 rounded-lg ${active ? 'bg-medical-cyan/20 border-medical-cyan/40 text-medical-cyan' : 'bg-white/5 border-white/10 text-gray-400 hover:text-white'} border text-xs font-medium transition-all">${i}</button>`;
    }

    // Next
    if (pg.current_page < pg.total_pages) {
        btns.innerHTML += `<button onclick="loadScans(${pg.current_page + 1})" class="px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-gray-400 hover:text-white text-xs font-medium transition-all">Next →</button>`;
    }
}

// ── Search debounce ─────────────────────────────────────────────────
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => loadScans(1), 400);
});

// ── Helpers ─────────────────────────────────────────────────────────
function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
}

function formatBytes(bytes) {
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return bytes + ' B';
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

function statusLabel(status) {
    const labels = { pending: 'Menunggu', processing: 'Memproses', completed: 'Selesai' };
    return labels[status] || status;
}

// ── Close modal on overlay click ────────────────────────────────────
document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

// ── Close modal on ESC key ──────────────────────────────────────────
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(m => {
            m.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});

// ── Init ────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => loadScans());
</script>

</body>
</html>