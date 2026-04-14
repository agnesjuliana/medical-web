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
    <title>Sefalometri Digital AI - Modul 11</title>
    <style>
        /* Mengatur font dan background dasar */
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
            background-color: #020617; /* Biru malam sangat gelap */
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

        /* --- TOPBAR GLASSMORPHISM --- */
        .topbar { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 15px 40px; 
            background: rgba(15, 23, 42, 0.6); 
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--glass-border);
            margin-bottom: 40px; 
            position: sticky; top: 0; z-index: 100;
        }
        .topbar-brand { font-weight: 700; font-size: 1.2rem; display: flex; align-items: center; gap: 10px; }
        .topbar-user { display: flex; align-items: center; gap: 15px; }
        .avatar { background: linear-gradient(135deg, #0ea5e9, #4f46e5); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; box-shadow: 0 4px 10px rgba(14, 165, 233, 0.3); }
        .user-info { font-size: 0.9rem; text-align: right; }
        .user-name { font-weight: 600; }
        .user-email { color: var(--text-muted); font-size: 0.8rem; }

        .container { max-width: 900px; margin: 0 auto; padding: 0 20px 50px 20px; position: relative; }
        
        .btn-back { color: var(--text-muted); text-decoration: none; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; font-weight: 500; transition: color 0.3s; }
        .btn-back:hover { color: white; }

        /* --- CARD GLASSMORPHISM --- */
        .card { 
            background: var(--glass-bg); 
            backdrop-filter: blur(16px); 
            -webkit-backdrop-filter: blur(16px);
            padding: 40px; 
            border-radius: 24px; 
            border: 1px solid var(--glass-border); 
            margin-bottom: 30px; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover { transform: translateY(-2px); box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3); }
        
        .header-title { margin: 0 0 8px 0; font-size: 1.8rem; font-weight: 700; background: linear-gradient(to right, #fff, #93c5fd); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .header-subtitle { margin: 0 0 35px 0; color: var(--text-muted); font-size: 1rem; line-height: 1.5; }

        /* --- FORM ELEMENTS --- */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 25px; }
        .full-width { grid-column: span 2; }
        .form-group { display: flex; flex-direction: column; }
        label { margin-bottom: 10px; font-weight: 600; color: #cbd5e1; font-size: 0.9rem; letter-spacing: 0.5px; }
        
        input[type="text"], input[type="number"], select { 
            padding: 14px 18px; 
            border: 1px solid var(--glass-border); 
            border-radius: 12px; 
            font-size: 1rem; 
            background-color: rgba(15, 23, 42, 0.4); 
            color: white;
            font-family: inherit;
            transition: all 0.3s ease; 
        }
        input:focus, select:focus { outline: none; border-color: var(--primary); background-color: rgba(15, 23, 42, 0.8); box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15); }
        select option { background-color: #0f172a; color: white; }

        /* --- ELEGANT UPLOAD AREA --- */
        .upload-area { 
            border: 2px dashed rgba(14, 165, 233, 0.4); 
            border-radius: 16px; 
            padding: 40px 30px; 
            text-align: center; 
            background: rgba(14, 165, 233, 0.03); 
            cursor: pointer; 
            position: relative; 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        .upload-area:hover { border-color: var(--primary); background: rgba(14, 165, 233, 0.08); transform: scale(1.01); }
        .upload-area input[type="file"] { position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; z-index: 10; }
        
        .upload-icon { margin-bottom: 15px; color: #7dd3fc; transition: all 0.3s ease; }
        .upload-text { font-weight: 600; color: #e0f2fe; margin-bottom: 8px; font-size: 1.1rem; }
        .upload-hint { font-size: 0.9rem; color: #94a3b8; }

        /* --- BUTTON --- */
        .btn-submit { 
            background: linear-gradient(135deg, #0ea5e9, #3b82f6); 
            color: white; 
            border: none; 
            padding: 16px 24px; 
            border-radius: 12px; 
            cursor: pointer; 
            font-weight: 700; 
            font-size: 1.05rem;
            width: 100%; 
            margin-top: 20px; 
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4);
            letter-spacing: 0.5px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(14, 165, 233, 0.6); }

        /* --- TABLE STYLES --- */
        table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 15px; }
        th, td { padding: 16px 20px; text-align: left; border-bottom: 1px solid var(--glass-border); font-size: 0.95rem; }
        th { background: rgba(255, 255, 255, 0.02); color: #cbd5e1; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
        th:first-child { border-top-left-radius: 12px; }
        th:last-child { border-top-right-radius: 12px; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255, 255, 255, 0.02); }
        
        .badge { background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.3); color: #7dd3fc; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;}
        .badge svg { width: 14px; height: 14px; }
    </style>
</head>
<body>

<div class="bg-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
</div>

<div class="topbar">
    <div class="topbar-brand">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        MedWeb AI
    </div>
    <div class="topbar-user">
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($userName) ?></div>
            <div class="user-email"><?= htmlspecialchars($userEmail) ?></div>
        </div>
        <div class="avatar">DR</div>
    </div>
</div>

<div class="container">
    <a href="../../index.php" class="btn-back">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Kembali ke Dashboard
    </a>
    
    <div class="card">
        <h2 class="header-title">Analisis Sefalometri Cerdas</h2>
        <p class="header-subtitle">Integrasi AI untuk ekstraksi fitur rahang otomatis. Lengkapi parameter klinis di bawah ini.</p>
        
        <form action="process_upload.php" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nama_pasien">NAMA LENGKAP</label>
                    <input type="text" id="nama_pasien" name="nama_pasien" placeholder="Masukkan nama pasien..." required>
                </div>
                <div class="form-group">
                    <label for="nik">NOMOR REKAM MEDIS (NIK)</label>
                    <input type="number" id="nik" name="nik" placeholder="Masukkan 16 digit ID" required>
                </div>
                <div class="form-group">
                    <label for="usia">USIA KLINIS</label>
                    <input type="number" id="usia" name="usia" min="1" placeholder="Contoh: 25" required>
                </div>
                <div class="form-group">
                    <label for="jenis_kelamin">JENIS KELAMIN</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="" disabled selected>Pilih klasifikasi...</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group full-width">
                <label>CITRA RONTGEN SEFALOGRAM LATERAL</label>
                <div class="upload-area" id="drop-area">
                    <input type="file" id="foto_rontgen" name="foto_rontgen" accept="image/jpeg, image/png" required style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; z-index: 10;">
                    
                    <div id="upload-ui">
                        <div class="upload-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
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
        <h2 class="header-title" style="font-size: 1.4rem;">Arsip Pemindaian</h2>
        <p class="header-subtitle" style="margin-bottom: 15px;">Daftar rekam medis yang menunggu atau telah diproses.</p>
        
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
                                <span style="color: var(--text-muted); font-size: 0.85rem; font-family: monospace;"><?= htmlspecialchars($row['nik']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['usia']) ?> Thn<br><span style="color: var(--text-muted); font-size: 0.85rem;"><?= htmlspecialchars($row['jenis_kelamin']) ?></span></td>
                            <td style="font-family: monospace; color: #7dd3fc; font-size: 0.85rem;"><?= substr(htmlspecialchars($row['foto_rontgen']), 0, 15) ?>...</td>
                            <td>
                                <span class="badge">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
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
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 10px; opacity: 0.5;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                <p style="margin:0;">Database masih kosong. Inisialisasi data pasien pertama.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    const fileInput = document.getElementById('foto_rontgen');
    const uploadUI = document.getElementById('upload-ui');
    const dropArea = document.getElementById('drop-area');

    fileInput.addEventListener('change', function() {
        if(this.files && this.files.length > 0) {
            const fileName = this.files[0].name;
            
            // Mengganti UI dengan SVG Checkmark yang elegan
            uploadUI.innerHTML = `
                <div class="upload-icon" style="color: #10b981;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <div class="upload-text" style="color: #fff; font-weight: 700; margin-bottom: 5px;">Citra Tersandi: ${fileName}</div>
                <div class="upload-hint" style="color: #34d399; font-weight: 500;">Berkas tervalidasi. Siap untuk dieksekusi.</div>
            `;
            
            // Efek glow hijau elegan
            dropArea.style.borderColor = '#10b981';
            dropArea.style.background = 'rgba(16, 185, 129, 0.05)';
            dropArea.style.boxShadow = '0 0 20px rgba(16, 185, 129, 0.1)';
        }
    });
</script>

</body>
</html>