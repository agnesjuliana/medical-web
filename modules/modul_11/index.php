<?php
// Pastikan pengguna sudah login
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../config/database.php';

$userName = $_SESSION['user_name'] ?? 'Ghaly Rakha Okusara';
$userEmail = $_SESSION['user_email'] ?? 'rakha.okusara@gmail.com';

// Tarik data dari database
$pdo = getDBConnection();
$sql = "SELECT p.nama_pasien, p.nik, p.usia, p.jenis_kelamin, s.foto_rontgen, s.waktu_upload 
        FROM modul_11_pasien p 
        JOIN modul_11_sefalometri s ON p.id_pasien = s.id_pasien 
        ORDER BY s.waktu_upload DESC";
$stmt = $pdo->query($sql);
$riwayat_pasien = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cephalo AI - Teknologi Kedokteran ITS</title>
    <style>
        /* --- FONT & BASE VARIABLES --- */
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        
        :root {
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --primary: #0ea5e9;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        html { scroll-behavior: smooth; }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0; 
            color: var(--text-main); 
            background-color: #020617; 
            overflow-x: hidden;
        }

        /* --- 3D MOVING BACKGROUND EFFECTS --- */
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -2; overflow: hidden; }
        .shape { position: absolute; border-radius: 50%; filter: blur(90px); animation: float 25s infinite ease-in-out alternate; opacity: 0.4; }
        .shape-1 { width: 600px; height: 600px; background: #0284c7; top: -10%; left: -10%; animation-delay: 0s; }
        .shape-2 { width: 500px; height: 500px; background: #4f46e5; bottom: -10%; right: -5%; animation-delay: -5s; }
        .shape-3 { width: 400px; height: 400px; background: #0ea5e9; top: 40%; left: 40%; animation-delay: -10s; }
        
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(60px, 40px) scale(1.1); }
            100% { transform: translate(-40px, 60px) scale(0.9); }
        }

        /* --- TOPBAR --- */
        .topbar { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 20px 40px; 
            background: rgba(2, 6, 23, 0.4); 
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--glass-border);
            position: fixed; top: 0; width: 100%; box-sizing: border-box; z-index: 100;
        }
        .topbar-brand { font-weight: 700; font-size: 1.2rem; display: flex; align-items: center; gap: 10px; letter-spacing: 1px; text-transform: uppercase; }
        .topbar-user { display: flex; align-items: center; gap: 15px; }
        .avatar { background: linear-gradient(135deg, #0ea5e9, #4f46e5); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; }
        .user-info { font-size: 0.9rem; text-align: right; }
        .user-name { font-weight: 600; }

        /* =========================================
           SECTION 1: HERO / SPECTRA EDITORIAL STYLE 
           ========================================= */
        .hero-section {
            height: 100vh;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            position: relative; padding: 0 40px; box-sizing: border-box;
        }
        
        /* Corner Texts */
        .corner-text { position: absolute; font-size: 0.75rem; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; color: var(--text-muted); line-height: 1.5; }
        .ct-tl { top: 120px; left: 40px; }
        .ct-tr { top: 120px; right: 40px; text-align: right; }
        .ct-bl { bottom: 60px; left: 40px; }
        .ct-br { bottom: 60px; right: 40px; max-width: 400px; text-align: right; color: #cbd5e1; font-weight: 400; text-transform: none; letter-spacing: 0.5px; }
        
        .hero-main-title {
            font-size: 11vw;
            font-weight: 800;
            line-height: 0.9;
            letter-spacing: -0.04em;
            text-align: center;
            margin: 0;
            background: linear-gradient(180deg, #ffffff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            z-index: 2;
        }

        .scroll-indicator {
            position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%);
            display: flex; flex-direction: column; align-items: center; gap: 10px;
            color: var(--text-muted); font-size: 0.75rem; letter-spacing: 3px; text-decoration: none; text-transform: uppercase;
            animation: bounce 2s infinite; z-index: 10;
        }
        @keyframes bounce { 
            0%, 20%, 50%, 80%, 100% {transform: translateY(0) translateX(-50%);} 
            40% {transform: translateY(-10px) translateX(-50%);} 
            60% {transform: translateY(-5px) translateX(-50%);} 
        }

        /* =========================================
           SECTION 2: MAIN APP (FORM & TABLE)
           ========================================= */
        .app-section { min-height: 100vh; padding: 120px 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .container { max-width: 900px; width: 100%; position: relative; z-index: 10; }
        
        .card { 
            background: var(--glass-bg); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            padding: 40px; border-radius: 24px; border: 1px solid var(--glass-border); 
            margin-bottom: 40px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .header-title { margin: 0 0 8px 0; font-size: 2rem; font-weight: 700; color: #fff; letter-spacing: -0.5px; }
        .header-subtitle { margin: 0 0 35px 0; color: var(--text-muted); font-size: 1rem; line-height: 1.6; }

        /* Form Layout */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 25px; }
        .full-width { grid-column: span 2; }
        .form-group { display: flex; flex-direction: column; }
        label { margin-bottom: 10px; font-weight: 600; color: #cbd5e1; font-size: 0.8rem; letter-spacing: 1px; text-transform: uppercase; }
        
        input[type="text"], input[type="number"], select { 
            padding: 16px 20px; border: 1px solid var(--glass-border); border-radius: 12px; 
            font-size: 1rem; background-color: rgba(15, 23, 42, 0.6); color: white; transition: all 0.3s ease; 
        }
        input:focus, select:focus { outline: none; border-color: var(--primary); background-color: rgba(15, 23, 42, 0.9); }
        select option { background-color: #0f172a; color: white; }

        .upload-area { 
            border: 1px dashed rgba(14, 165, 233, 0.5); border-radius: 16px; padding: 50px 30px; 
            text-align: center; background: rgba(14, 165, 233, 0.02); cursor: pointer; position: relative; transition: all 0.3s; 
        }
        .upload-area:hover { border-color: var(--primary); background: rgba(14, 165, 233, 0.08); }
        .upload-area input[type="file"] { position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; z-index: 10; }
        
        .upload-icon { margin-bottom: 15px; color: #7dd3fc; }
        .upload-text { font-weight: 600; color: #e0f2fe; margin-bottom: 8px; font-size: 1.1rem; }
        .upload-hint { font-size: 0.9rem; color: #94a3b8; }

        .btn-submit { 
            background: #fff; color: #0f172a; border: none; padding: 18px 24px; border-radius: 12px; 
            cursor: pointer; font-weight: 700; font-size: 1.05rem; width: 100%; margin-top: 20px; transition: all 0.3s ease;
        }
        .btn-submit:hover { background: #e0f2fe; transform: translateY(-2px); box-shadow: 0 10px 25px rgba(255, 255, 255, 0.1); }

        /* Tabel Data */
        table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 15px; }
        th, td { padding: 16px 20px; text-align: left; border-bottom: 1px solid var(--glass-border); font-size: 0.95rem; }
        th { background: rgba(255, 255, 255, 0.02); color: #cbd5e1; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; }
        .badge { background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.3); color: #7dd3fc; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; text-transform: uppercase;}

        /* =========================================
           SECTION 3: TEAM MEMBERS
           ========================================= */
        .team-section { padding: 80px 20px; max-width: 1000px; margin: 0 auto; z-index: 10; position: relative; }
        .section-title { text-align: center; font-size: 2.5rem; font-weight: 800; margin-bottom: 50px; color: #fff; letter-spacing: -1px; }
        
        .team-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }
        .team-card { 
            background: rgba(15, 23, 42, 0.5); border: 1px solid var(--glass-border); border-radius: 16px; 
            padding: 30px 20px; text-align: center; transition: transform 0.3s; backdrop-filter: blur(10px);
        }
        .team-card:hover { transform: translateY(-10px); border-color: rgba(14, 165, 233, 0.5); }
        .member-photo { width: 100px; height: 100px; border-radius: 50%; background: #1e293b; margin: 0 auto 20px auto; overflow: hidden; border: 2px solid #38bdf8; }
        .member-photo img { width: 100%; height: 100%; object-fit: cover; }
        .member-name { font-size: 1.2rem; font-weight: 700; color: #fff; margin: 0 0 5px 0; }
        .member-role { font-size: 0.85rem; color: #7dd3fc; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; }
        .member-dept { font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; }

        /* =========================================
           FOOTER
           ========================================= */
        .footer { 
            text-align: center; padding: 40px 20px; background: rgba(2, 6, 23, 0.8); 
            border-top: 1px solid var(--glass-border); position: relative; z-index: 10;
        }
        .footer-logo { font-size: 1.5rem; font-weight: 800; color: #fff; letter-spacing: 1px; margin-bottom: 10px; }
        .footer-text { color: var(--text-muted); font-size: 0.85rem; line-height: 1.6; max-width: 600px; margin: 0 auto; }

        /* --- LIQUID CURSOR STYLE --- */
        .water-droplet { position: absolute; width: 45px; height: 45px; border-radius: 50%; background: radial-gradient(circle, rgba(56,189,248,0.9) 0%, rgba(3,105,161,0.5) 100%); pointer-events: none; transform: translate(-50%, -50%); box-shadow: 0 0 25px rgba(14, 165, 233, 0.4); }
        .liquid-filter { filter: url(#goo); position: fixed; top:0; left:0; width:100vw; height:100vh; pointer-events: none; z-index: 99; overflow: hidden; }
        
        @media (max-width: 768px) {
            .hero-main-title { font-size: 18vw; }
            .corner-text { position: relative; top: auto; left: auto; right: auto; bottom: auto; text-align: center; margin-bottom: 20px; }
            .ct-br { margin-top: 40px; }
            .form-grid, .team-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<svg style="visibility: hidden; position: absolute;" width="0" height="0" xmlns="http://www.w3.org/2000/svg" version="1.1">
    <defs>
        <filter id="goo">
            <feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur" />
            <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 22 -10" result="goo" />
            <feBlend in="SourceGraphic" in2="goo" />
        </filter>
    </defs>
</svg>
<div class="liquid-filter" id="blob-container"></div>

<div class="bg-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
</div>

<div class="topbar">
    <div class="topbar-brand">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        Cephalo AI
    </div>
    <div class="topbar-user">
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($userName) ?></div>
        </div>
        <a href="../../index.php" title="Kembali ke Dashboard">
            <div class="avatar">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            </div>
        </a>
    </div>
</div>

<section class="hero-section">
    <div class="corner-text ct-tl">Modul 11<br>Cephalo AI</div>
    <div class="corner-text ct-tr">Web Programming<br>Project 2026</div>
    <div class="corner-text ct-bl">Concept For A Modern<br>Orthodontic Diagnostic Tool</div>
    <div class="corner-text ct-br">
        Cephalo AI adalah sistem cerdas berbasis Machine Learning yang dirancang untuk mendeteksi landmark sefalometri secara otomatis. Sistem ini mengeliminasi human-error dan mempercepat proses diagnosis ortodonti dengan presisi algoritmik.
    </div>

    <h1 class="hero-main-title">Cephalo AI</h1>

    <a href="#upload-section" class="scroll-indicator">
        Unggah Data Pasien
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
    </a>
</section>

<section id="upload-section" class="app-section">
    <div class="container">
        
        <div class="card">
            <h2 class="header-title">Analisis Sefalometri Cerdas</h2>
            <p class="header-subtitle">Integrasi AI untuk ekstraksi fitur rahang otomatis. Lengkapi parameter klinis di bawah ini.</p>
            
            <form action="process_upload.php" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nama_pasien">Nama Lengkap</label>
                        <input type="text" id="nama_pasien" name="nama_pasien" placeholder="Nama pasien..." required>
                    </div>
                    <div class="form-group">
                        <label for="nik">Nomor Rekam Medis (NIK)</label>
                        <input type="number" id="nik" name="nik" placeholder="16 digit NIK..." required>
                    </div>
                    <div class="form-group">
                        <label for="usia">Usia Klinis</label>
                        <input type="number" id="usia" name="usia" min="1" placeholder="Contoh: 25" required>
                    </div>
                    <div class="form-group">
                        <label for="jenis_kelamin">Jenis Kelamin</label>
                        <select id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="" disabled selected>Pilih klasifikasi...</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label>Citra Rontgen Sefalogram Lateral</label>
                    <div class="upload-area" id="drop-area">
                        <input type="file" id="foto_rontgen" name="foto_rontgen" accept="image/jpeg, image/png" required style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; z-index: 10;">
                        <div id="upload-ui">
                            <div class="upload-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            </div>
                            <div class="upload-text">Tarik & Letakkan Citra Medis Disini</div>
                            <div class="upload-hint">Format yang diizinkan: JPG, PNG (Resolusi Tinggi)</div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">Inisialisasi Pemindaian AI</button>
            </form>
        </div>

        <div class="card">
            <h2 class="header-title" style="font-size: 1.5rem;">Arsip Pemindaian</h2>
            <p class="header-subtitle" style="margin-bottom: 20px;">Daftar rekam medis yang menunggu atau telah diproses.</p>
            
            <?php if (count($riwayat_pasien) > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Identitas Pasien</th>
                                <th>Demografi</th>
                                <th>ID Berkas</th>
                                <th>Status Pemrosesan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($riwayat_pasien as $row): ?>
                            <tr>
                                <td>
                                    <strong style="color: #fff; display: block; font-size: 1rem;"><?= htmlspecialchars($row['nama_pasien']) ?></strong>
                                    <span style="color: var(--text-muted); font-size: 0.8rem; font-family: monospace;"><?= htmlspecialchars($row['nik']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($row['usia']) ?> Thn<br><span style="color: var(--text-muted); font-size: 0.8rem;"><?= htmlspecialchars($row['jenis_kelamin']) ?></span></td>
                                <td style="font-family: monospace; color: #7dd3fc; font-size: 0.85rem;"><?= substr(htmlspecialchars($row['foto_rontgen']), 0, 15) ?>...</td>
                                <td>
                                    <span class="badge">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        Antrean AI
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; color: var(--text-muted); padding: 30px; border: 1px dashed var(--glass-border); border-radius: 12px; margin-top: 20px;">
                    <p style="margin:0;">Database masih kosong. Inisialisasi data pasien pertama.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="team-section">
    <h2 class="section-title">Behind the AI</h2>
    <div class="team-grid">
        
        <div class="team-card">
            <div class="member-photo">
                <img src="../../assets/img/rakha_web.JPG" alt="Ghaly Rakha Okusara">
            </div>
            <h3 class="member-name">Ghaly Rakha Okusara</h3>
            <div class="member-role">UI/UX Designer</div>
            <div class="member-dept">
                5049241026<br>
                Teknologi Kedokteran<br>
                Institut Teknologi Sepuluh Nopember (ITS)
            </div>
        </div>

        <div class="team-card">
            <div class="member-photo">
                <img src="../../assets/img/ica_web.jpeg" alt="Aisyah Rahmi Nadjib">
            </div>
            <h3 class="member-name">Aisyah Rahmi Nadjib</h3>
            <div class="member-role">AI Engineer</div>
            <div class="member-dept">
                5049241030<br>
                Teknologi Kedokteran<br>
                Institut Teknologi Sepuluh Nopember (ITS)
            </div>
        </div>

        <div class="team-card">
            <div class="member-photo">
                <img src="../../assets/img/arya_web.jpeg" alt="Arya Muhammad Duta Syafinda">
            </div>
            <h3 class="member-name">Arya Muhammad Duta Syafinda</h3>
            <div class="member-role">Data & Backend</div>
            <div class="member-dept">
                5049241077<br>
                Teknologi Kedokteran<br>
                Institut Teknologi Sepuluh Nopember (ITS)
            </div>
        </div>

    </div>
</section>

<footer class="footer">
    <div class="footer-logo">Cephalo AI Project</div>
    <div class="footer-text">
        <p style="margin-bottom: 5px; color: #fff; font-weight: 600;">Mata Kuliah<br>Pemrograman Web Untuk Teknologi Kedokteran</p>
        Departemen Teknologi Kedokteran, Fakultas Kedokteran dan Kesehatan (FKK)<br>
        Institut Teknologi Sepuluh Nopember (ITS) Surabaya © 2026
    </div>
</footer>

<script>
    // 1. Script Form Upload (Ganti warna saat foto dipilih)
    const fileInput = document.getElementById('foto_rontgen');
    const uploadUI = document.getElementById('upload-ui');
    const dropArea = document.getElementById('drop-area');

    fileInput.addEventListener('change', function() {
        if(this.files && this.files.length > 0) {
            const fileName = this.files[0].name;
            uploadUI.innerHTML = `
                <div class="upload-icon" style="color: #10b981;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <div class="upload-text" style="color: #fff; font-weight: 700; margin-bottom: 5px;">Citra Tersandi: ${fileName}</div>
                <div class="upload-hint" style="color: #34d399; font-weight: 500;">Berkas tervalidasi. Siap untuk dieksekusi.</div>
            `;
            dropArea.style.borderColor = '#10b981';
            dropArea.style.background = 'rgba(16, 185, 129, 0.05)';
        }
    });

    // 2. Script Liquid Cursor (Efek Air)
    const containerBlob = document.getElementById('blob-container');
    
    // Pastikan container ditemukan sebelum menjalankan script efek air
    if(containerBlob) {
        const blobstores = [];
        const BLOB_COUNT = 15;
        for(let i=0; i<BLOB_COUNT; i++) {
            let b = document.createElement('div');
            b.className = 'water-droplet';
            containerBlob.appendChild(b);
            blobstores.push({el: b, x: window.innerWidth/2, y: window.innerHeight/2});
        }

        let tX = window.innerWidth/2;
        let tY = window.innerHeight/2;

        document.addEventListener('mousemove', (e) => {
            tX = e.clientX;
            tY = e.clientY;
        });

        function animateBlobs() {
            let prevX = tX;
            let prevY = tY;
            for(let i=0; i<BLOB_COUNT; i++) {
                let blob = blobstores[i];
                blob.x += (prevX - blob.x) * 0.35;
                blob.y += (prevY - blob.y) * 0.35;
                blob.el.style.transform = `translate(${blob.x}px, ${blob.y}px) scale(${1 - (i/BLOB_COUNT)})`;
                prevX = blob.x;
                prevY = blob.y;
            }
            requestAnimationFrame(animateBlobs);
        }
        animateBlobs();
    }
</script>

</body>
</html>