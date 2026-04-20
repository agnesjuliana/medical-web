<?php
// Pastikan pengguna sudah login
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../config/database.php';

requireLogin();
startSession();

if (empty($_SESSION['modul_11_authorized'])) {
    header("Location: login.php");
    exit;
}

$user = getCurrentUser();
$userName = $user['name'] ?? 'Pengguna';
$userInitials = getUserInitials();

// 1. Tangkap ID Analisis dari URL (misal: result.php?id=5)
$id_analisis = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_analisis === 0) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='index.php';</script>";
    exit();
}

// 2. Tarik data pasien dan foto spesifik berdasarkan ID tersebut
$pdo = getDBConnection();
$sql = "SELECT p.*, s.foto_rontgen, s.waktu_upload, s.data_landmark, s.hasil_diagnosis 
        FROM modul_11_sefalometri s
        JOIN modul_11_pasien p ON s.id_pasien = p.id_pasien
        WHERE s.id_analisis = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_analisis]);
$data = $stmt->fetch();

if (!$data) {
    echo "<script>alert('Rekam medis tidak valid!'); window.location.href='index.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Diagnosis - Modul 11</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');
        
        :root {
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --primary: #0ea5e9;
            --primary-hover: #0284c7;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0; 
            color: var(--text-main); 
            background-color: #020617; 
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* --- 3D MOVING BACKGROUND EFFECTS --- */
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; overflow: hidden; }
        .shape { position: absolute; border-radius: 50%; filter: blur(80px); animation: float 20s infinite ease-in-out alternate; opacity: 0.6; }
        .shape-1 { width: 500px; height: 500px; background: #0284c7; top: -10%; left: -10%; animation-delay: 0s; }
        .shape-2 { width: 400px; height: 400px; background: #4f46e5; bottom: -10%; right: -5%; animation-delay: -5s; }
        .shape-3 { width: 300px; height: 300px; background: #0ea5e9; top: 40%; left: 40%; animation-delay: -10s; }
        
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, 30px) scale(1.1); }
            100% { transform: translate(-30px, 50px) scale(0.9); }
        }

        /* --- TOPBAR --- */
        .topbar { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 15px 40px; 
            background: rgba(15, 23, 42, 0.6); 
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--glass-border);
            margin-bottom: 30px; 
            position: sticky; top: 0; z-index: 100;
        }
        .topbar-brand { font-weight: 700; font-size: 1.2rem; display: flex; align-items: center; gap: 10px; }
        .topbar-user { display: flex; align-items: center; gap: 15px; }
        .avatar { background: linear-gradient(135deg, #0ea5e9, #4f46e5); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; box-shadow: 0 4px 10px rgba(14, 165, 233, 0.3); }
        .user-info { font-size: 0.9rem; text-align: right; }
        .user-name { font-weight: 600; }
        .user-email { color: var(--text-muted); font-size: 0.8rem; }
        
        .container { max-width: 1000px; margin: 0 auto; padding: 0 20px 50px 20px; position: relative;}
        .btn-back { color: var(--text-muted); text-decoration: none; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; font-weight: 500; transition: color 0.3s; }
        .btn-back:hover { color: white; }

        /* Card (Glassmorphism) */
        .glass-card { 
            background: var(--glass-bg); 
            backdrop-filter: blur(16px); 
            -webkit-backdrop-filter: blur(16px);
            padding: 25px; 
            border-radius: 20px; 
            border: 1px solid var(--glass-border); 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        /* Patient Header Card */
        .patient-card { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .patient-info h2 { margin: 0 0 5px 0; font-size: 1.4rem; background: linear-gradient(to right, #fff, #93c5fd); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .patient-info p { margin: 0; color: var(--text-muted); font-size: 0.95rem; }
        .badge-status { background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #fbbf24; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; display: inline-flex; align-items: center; gap: 8px; }

        /* Grid Layout Utama */
        .result-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        
        /* Area Foto Kiri */
        .image-title { font-weight: 600; margin-bottom: 15px; color: #f8fafc; font-size: 1.1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 12px; }
        .img-container { width: 100%; border-radius: 12px; overflow: hidden; background-color: #0f172a; position: relative; border: 1px solid var(--glass-border);}
        .img-container img { width: 100%; height: auto; display: block; opacity: 0.8; }
        /* Overlay Loading AI */
        .ai-scanning-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(2, 6, 23, 0.7); backdrop-filter: blur(4px); display: flex; justify-content: center; align-items: center; color: #38bdf8; font-weight: bold; flex-direction: column; }
        .spinner { border: 3px solid rgba(56, 189, 248, 0.2); border-top: 3px solid #38bdf8; border-radius: 50%; width: 45px; height: 45px; animation: spin 1s linear infinite; margin-bottom: 15px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Area Metrik Kanan */
        .angle-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 25px; }
        .angle-box { background: rgba(15, 23, 42, 0.5); border: 1px solid var(--glass-border); border-radius: 12px; padding: 18px 10px; text-align: center; transition: all 0.3s ease; }
        .angle-box:hover { background: rgba(15, 23, 42, 0.8); border-color: rgba(14, 165, 233, 0.4); transform: translateY(-2px); }
        .angle-name { font-size: 0.8rem; color: var(--text-muted); font-weight: bold; margin-bottom: 8px; letter-spacing: 1px; }
        .angle-value { font-size: 1.6rem; color: #475569; font-weight: 700; text-shadow: 0 0 10px rgba(0,0,0,0.5); } 
        
        /* Asisten Pintar */
        .assistant-box { background: linear-gradient(145deg, rgba(14, 165, 233, 0.05), rgba(14, 165, 233, 0.02)); border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 12px; padding: 20px; position: relative; overflow: hidden;}
        .assistant-header { display: flex; align-items: center; gap: 10px; color: #38bdf8; font-weight: bold; margin-bottom: 10px; font-size: 1.1rem; }
        .assistant-content { color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; }
        
        /* Tombol Eksekusi Python */
        .btn-run-ai { background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 16px; border-radius: 12px; cursor: pointer; font-weight: 700; width: 100%; margin-top: 25px; font-size: 1.05rem; transition: transform 0.3s ease, box-shadow 0.3s ease; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); letter-spacing: 0.5px;}
        .btn-run-ai:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5); }

        /* LIQUID CURSOR EFFECT CSS */
        .water-droplet { position: absolute; width: 45px; height: 45px; border-radius: 50%; background: radial-gradient(circle, rgba(56,189,248,0.9) 0%, rgba(3,105,161,0.5) 100%); pointer-events: none; transform: translate(-50%, -50%); box-shadow: 0 0 25px rgba(14, 165, 233, 0.4); }
        .liquid-filter { filter: url(#goo); position: fixed; top:0; left:0; width:100vw; height:100vh; pointer-events: none; z-index: -1; overflow: hidden; }
    </style>
</head>
<body>

<div class="bg-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
</div>

<!-- LIQUID CURSOR SVG -->
<svg style="visibility: hidden; position: absolute;" width="0" height="0" xmlns="http://www.w3.org/2000/svg" version="1.1">
    <defs><filter id="goo"><feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur" /><feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 22 -10" result="goo" /><feBlend in="SourceGraphic" in2="goo" /></filter></defs>
</svg>
<div class="liquid-filter" id="blob-container"></div>

<div class="topbar">
    <div class="topbar-brand">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        MedWeb AI
    </div>
    <div class="topbar-user">
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($userName) ?></div>
        </div>
        <div class="avatar"><?= htmlspecialchars($userInitials) ?></div>
    </div>
</div>

<div class="container">
    <a href="index.php" class="btn-back">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Kembali ke Form Upload
    </a>
    
    <div class="glass-card patient-card">
        <div class="patient-info">
            <h2>Pasien: <?= htmlspecialchars($data['nama_pasien']) ?></h2>
            <p>ID Medis: <?= htmlspecialchars($data['nik']) ?> &nbsp;|&nbsp; <?= htmlspecialchars($data['usia']) ?> Tahun &nbsp;|&nbsp; <?= htmlspecialchars($data['jenis_kelamin']) ?></p>
        </div>
        <div class="badge-status">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            Menunggu Proses AI
        </div>
    </div>

    <div class="result-grid">
        
        <div class="glass-card image-card">
            <div class="image-title">Citra Rontgen Sefalogram</div>
            <div class="img-container">
                <img id="patientImg" src="uploads/<?= htmlspecialchars($data['foto_rontgen']) ?>" alt="Rontgen Pasien">
                
                <!-- KANVAS PENONTON TITIK AI -->
                <canvas id="landmarkCanvas" style="position:absolute; top:0; left:0; width:100%; height:100%; z-index:10; pointer-events:none;"></canvas>

                <div id="overlayAi" class="ai-scanning-overlay">
                    <div class="spinner"></div>
                    <div style="letter-spacing: 1px;">Sistem Siap Eksekusi...</div>
                </div>
            </div>
        </div>

        <div class="glass-card metrics-card">
            <div class="image-title">Kalkulasi Geometris Otomatis</div>
            
            <div class="angle-grid">
                <div class="angle-box">
                    <div class="angle-name">SUDUT SNA</div>
                    <div class="angle-value">--°</div>
                </div>
                <div class="angle-box">
                    <div class="angle-name">SUDUT SNB</div>
                    <div class="angle-value">--°</div>
                </div>
                <div class="angle-box">
                    <div class="angle-name">SUDUT ANB</div>
                    <div class="angle-value">--°</div>
                </div>
            </div>

            <div class="assistant-box">
                <div class="assistant-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"></path><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    Asisten Pintar Diagnosis
                </div>
                <div class="assistant-content">
                    <p style="margin-top: 0; margin-bottom: 0;">Infrastruktur landamarking berada dalam mode *standby*. Silakan klik tombol inisialisasi di bawah untuk memulai pemrosesan citra medis (ekstraksi batas keras & jaringan lunak rahang).</p>
                </div>
            </div>

            <button id="btnRunAi" class="btn-run-ai">Jalankan Analisis AI Sekarang</button>
        </div>

    </div>
</div>

<script>
    // --- SKRIP INTEGRASI ALGORITMA AI PYTHON ---
    const idAnalisis = <?= $id_analisis ?>;
    const btnRunAi = document.getElementById('btnRunAi');
    const overlayAi = document.getElementById('overlayAi');
    const img = document.getElementById('patientImg');
    const canvas = document.getElementById('landmarkCanvas');
    const ctx = canvas.getContext('2d');

    // Sembunyikan overlay saat pertama kali load
    overlayAi.style.display = 'none';

    btnRunAi.addEventListener('click', async () => {
        // Tampilkan animasi skeleton loading
        overlayAi.style.display = 'flex';
        overlayAi.innerHTML = '<div class="spinner"></div><div style="letter-spacing: 1px;">AI Python sedang menelaah Rontgen...</div>';
        
        try {
            // Meluncurkan misil data ke Jembatan PHP kita
            const response = await fetch(`api_bridge.php?id=${idAnalisis}`);
            const result = await response.json();
            
            if (result.status === 'success') {
                overlayAi.style.display = 'none';
                
                // Pastikan kanvas presisi diukur berdasarkan skala gambar di layar pengguna!
                canvas.width = img.clientWidth;
                canvas.height = img.clientHeight;
                
                // Mencari kalkulasi rasio Resolusi Asli Rontgen vs Resolusi Frame Web
                const ratioX = img.clientWidth / img.naturalWidth;
                const ratioY = img.clientHeight / img.naturalHeight;
                
                // Gambar 19 Pasukan Titik!
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                result.landmarks.forEach((point, index) => {
                    const cx = point.x * ratioX;
                    const cy = point.y * ratioY;
                    
                    // Titik Inti
                    ctx.beginPath();
                    ctx.arc(cx, cy, 4, 0, 2 * Math.PI);
                    ctx.fillStyle = '#fde047'; // Kuning menyala
                    ctx.fill();
                    ctx.lineWidth = 1.5;
                    ctx.strokeStyle = '#000';
                    ctx.stroke();
                    
                    // Tulisan Label Medis (Kecil)
                    ctx.fillStyle = '#fff';
                    ctx.font = '11px Plus Jakarta Sans';
                    ctx.fillText(index+1, cx + 6, cy + 4);
                });
                
                // Update teks Asisten untuk estetika UX
                document.querySelector('.assistant-content').innerHTML = `<p style="color:#10b981; margin:0;"><b>Sukses 100%!</b><br>AI Python berhasil mendeteksi dan memetakan 19 titik Cephalometri di atas kanvas citra pasien secara presisi.</p>`;
                
            } else {
                overlayAi.innerHTML = `<div style="color:#ef4444; font-size:0.9rem; text-align:center; padding: 20px;"><b>KONEKSI GAGAL</b><br>${result.message}</div>`;
            }
        } catch (error) {
            overlayAi.innerHTML = `<div style="color:#ef4444; font-size:0.9rem; text-align:center; padding: 20px;"><b>FATAL ERROR.</b><br>Gagal menyambung ke Mesin AI. Pastikan file app.py menyala di latar belakang!</div>`;
        }
    });

    // JS for gooey blob trail
    const containerBlob = document.getElementById('blob-container');
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
</script>

</body>
</html>