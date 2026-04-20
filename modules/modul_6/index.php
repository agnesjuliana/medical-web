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

</style>
</head>
<body class="relative min-h-screen antialiased selection:bg-medical-cyan selection:text-white">

    <!-- Background Elements -->
    <div class="fixed inset-0 bg-grid pointer-events-none"></div>
    <div class="fixed inset-0 medical-cross-overlay pointer-events-none z-[-1]"></div>
    <div class="fixed top-[-20%] left-[-10%] w-[800px] h-[800px] glow-cyan rounded-full pointer-events-none"></div>
    <div class="fixed bottom-[-20%] right-[-10%] w-[800px] h-[800px] glow-blue rounded-full pointer-events-none"></div>

    <!-- Navigation -->
    <nav class="fixed w-full z-50 glass-card border-b-0 border-t-0 border-x-0 !rounded-none py-4 px-8">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-medical-cyan to-medical-blue flex items-center justify-center shadow-[0_0_15px_rgba(6,182,212,0.5)]">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <span class="font-heading font-bold text-xl tracking-wider text-white">Neuro<span class="text-medical-cyan">AI</span></span>
            </div>
            <div class="hidden md:flex gap-8 text-sm font-medium text-gray-400">
                <a href="#technology" class="hover:text-white transition-colors">Teknologi</a>
                <a href="#features" class="hover:text-white transition-colors">Kemampuan</a>
                <a href="#team" class="hover:text-white transition-colors">Peneliti</a>
            </div>
            <button class="px-6 py-2 rounded-full bg-medical-cyan/10 border border-medical-cyan/30 text-medical-accent hover:bg-medical-cyan/20 hover:shadow-[0_0_15px_rgba(6,182,212,0.3)] transition-all text-sm font-medium">
                Portal Medis
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 px-6 overflow-hidden">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 items-center">
            
            <div class="relative z-10 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass-card border-medical-cyan/30 mb-8 border border-white/5">
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
                    <button class="px-8 py-4 rounded-xl bg-gradient-to-r from-medical-cyan to-medical-blue text-white font-semibold flex items-center justify-center gap-3 hover:shadow-[0_0_25px_rgba(6,182,212,0.4)] transition-all transform hover:-translate-y-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Upload MRI Scan
                    </button>
                    <button class="px-8 py-4 rounded-xl glass-card text-white font-semibold flex items-center justify-center gap-3 hover:bg-white/5 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Lihat Diagnostik
                    </button>
                </div>
            </div>

            <div class="relative z-10 flex justify-center mt-12 lg:mt-0">
                <div class="brain-container">
                    <!-- Added an onerror fallback so if the local brain.png misses, it shows an online 3D brain icon -->
                    <img src="assets/images/brain.png" alt="AI Brain Analysis" class="brain-img relative z-10" onerror="this.src='https://cdn3d.iconscout.com/3d/premium/thumb/brain-4993510-4159670.png'">
                    <div class="scanner-line"></div>
                    <div class="scanner-area"></div>
                    
                    <!-- Floating Data Points indicating AI Analysis -->
                    <div class="absolute top-[20%] left-[10%] hidden md:block z-20">
                        <div class="data-node"></div>
                        <div class="connecting-line w-16 -rotate-45"></div>
                        <div class="absolute -top-6 -left-24 glass-card px-3 py-1.5 text-[11px] font-medium text-medical-accent rounded border-[0.5px] border-medical-cyan/30 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-medical-cyan animate-pulse"></span>
                            Lobus Frontal: Normal
                        </div>
                    </div>
                    
                    <div class="absolute top-[60%] right-[5%] hidden md:block z-20">
                        <div class="data-node"></div>
                        <div class="connecting-line w-20 rotate-12"></div>
                        <div class="absolute -top-4 left-24 glass-card px-3 py-1.5 text-[11px] font-medium text-red-400 rounded border-[0.5px] border-red-500/30 flex items-center gap-2">
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

    <!-- Team Section -->
    <section id="team" class="py-24 px-6 relative z-10 bg-[rgba(3,7,18,0.7)] border-y border-white/5">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-heading font-bold mb-4">Tim <span class="text-medical-blue">Pengembang</span></h2>
                <p class="text-gray-400 max-w-2xl mx-auto">Pakar yang berdedikasi tinggi menggabungkan Artifical Intelligence dengan dunia riset medis.</p>
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

</body>
</html>