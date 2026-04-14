<?php
// Pastikan pengguna sudah login
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../config/database.php';

$userName = $_SESSION['user_name'] ?? 'Ghaly Rakha Okusara';
$userEmail = $_SESSION['user_email'] ?? 'rakha.okusara@gmail.com';

// Tarik data dari database (Join tabel pasien dan sefalometri)
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
    <title>Sefalometri Digital - Modul 11</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f8fafc; margin: 0; color: #334155; }
        .topbar { display: flex; justify-content: space-between; align-items: center; padding: 15px 40px; background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 40px; }
        .topbar-brand { font-weight: 700; font-size: 1.2rem; color: #0f172a; }
        .topbar-user { display: flex; align-items: center; gap: 12px; }
        .avatar { background-color: #0ea5e9; color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; }
        .user-info { font-size: 0.9rem; text-align: right; }
        .user-name { font-weight: 600; color: #1e293b; }
        .user-email { color: #64748b; font-size: 0.8rem; }

        .container { max-width: 850px; margin: 0 auto; padding: 0 20px 50px 20px; }
        .btn-back { color: #64748b; text-decoration: none; font-size: 0.95rem; display: inline-block; margin-bottom: 20px; font-weight: 500; transition: color 0.2s; }
        .btn-back:hover { color: #0f172a; }
        
        .card { background: #ffffff; padding: 40px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #f1f5f9; margin-bottom: 30px; }
        .header-title { margin: 0 0 5px 0; color: #0f172a; font-size: 1.5rem; }
        .header-subtitle { margin: 0 0 30px 0; color: #64748b; font-size: 0.95rem; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .full-width { grid-column: span 2; }
        .form-group { display: flex; flex-direction: column; }
        label { margin-bottom: 8px; font-weight: 500; color: #475569; font-size: 0.9rem; }
        input[type="text"], input[type="number"], select { padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; background-color: #f8fafc; transition: all 0.2s; }
        input:focus, select:focus { outline: none; border-color: #0ea5e9; background-color: #ffffff; box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15); }

        .upload-area { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 30px; text-align: center; background-color: #f8fafc; cursor: pointer; position: relative; transition: all 0.3s ease; }
        .upload-area:hover { border-color: #0ea5e9; background-color: #f0f9ff; }
        .upload-area input[type="file"] { position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; }
        .upload-icon { font-size: 2rem; color: #94a3b8; margin-bottom: 10px; transition: all 0.3s ease; }
        .upload-text { font-weight: 500; color: #334155; margin-bottom: 5px; }
        .upload-hint { font-size: 0.85rem; color: #64748b; }

        .btn-submit { background-color: #0ea5e9; color: white; border: none; padding: 14px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; width: 100%; margin-top: 15px; font-size: 1rem; transition: background-color 0.2s; }
        .btn-submit:hover { background-color: #0284c7; }

        /* Style Tabel Data */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.95rem; }
        th { background-color: #f8fafc; color: #475569; font-weight: 600; }
        .badge { background-color: #fef3c7; color: #d97706; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; }
    </style>
</head>
<body>

<div class="topbar">
    <div class="topbar-brand"><span style="background: #0ea5e9; color: white; padding: 4px 8px; border-radius: 6px; margin-right: 5px;">M</span> MedWeb</div>
    <div class="topbar-user">
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($userName) ?></div>
            <div class="user-email"><?= htmlspecialchars($userEmail) ?></div>
        </div>
        <div class="avatar">DR</div>
    </div>
</div>

<div class="container">
    <a href="../../index.php" class="btn-back">← Kembali ke Dashboard</a>
    
    <div class="card">
        <h2 class="header-title">Analisis Sefalometri Digital</h2>
        <p class="header-subtitle">Lengkapi data rekam medis pasien sebelum sistem AI melakukan auto-landmarking.</p>
        <form action="process_upload.php" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nama_pasien">Nama Lengkap Pasien</label>
                    <input type="text" id="nama_pasien" name="nama_pasien" placeholder="Masukkan nama..." required>
                </div>
                <div class="form-group">
                    <label for="nik">Nomor Induk Kependudukan (NIK)</label>
                    <input type="number" id="nik" name="nik" placeholder="16 Digit NIK" required>
                </div>
                <div class="form-group">
                    <label for="usia">Usia (Tahun)</label>
                    <input type="number" id="usia" name="usia" min="1" placeholder="Cth: 25" required>
                </div>
                <div class="form-group">
                    <label for="jenis_kelamin">Jenis Kelamin</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="" disabled selected>Pilih...</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group full-width">
                <label>File Rontgen Sefalogram Lateral</label>
                <div class="upload-area" id="drop-area">
                    <input type="file" id="foto_rontgen" name="foto_rontgen" accept="image/jpeg, image/png" required style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; z-index: 10;">
                    
                    <div id="upload-ui">
                        <div class="upload-icon">📄</div>
                        <div class="upload-text">Klik atau tarik file foto rontgen ke area ini</div>
                        <div class="upload-hint">Mendukung format JPG atau PNG (Maks 5MB)</div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Simpan & Mulai Analisis AI</button>
        </form>
    </div>

    <div class="card">
        <h2 class="header-title">Riwayat Analisis Pasien</h2>
        <p class="header-subtitle">Daftar pasien yang telah diunggah foto sefalogramnya.</p>
        
        <?php if (count($riwayat_pasien) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nama Pasien</th>
                        <th>Umur / JK</th>
                        <th>File Sefalogram</th>
                        <th>Status AI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat_pasien as $row): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['nama_pasien']) ?></strong><br><small><?= htmlspecialchars($row['nik']) ?></small></td>
                        <td><?= htmlspecialchars($row['usia']) ?> Thn / <?= htmlspecialchars($row['jenis_kelamin']) ?></td>
                        <td><small style="color: #64748b;"><?= htmlspecialchars($row['foto_rontgen']) ?></small></td>
                        <td><span class="badge">Menunggu AI...</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #94a3b8; padding: 20px;">Belum ada data pasien. Silakan unggah form di atas.</p>
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
            
            // JavaScript hanya mengubah teks di dalam div upload-ui, file tetap aman!
            uploadUI.innerHTML = `
                <div class="upload-icon" style="color: #10b981; font-size: 2rem; margin-bottom: 10px;">✅</div>
                <div class="upload-text" style="color: #0f172a; font-weight: bold; margin-bottom: 5px;">File siap: ${fileName}</div>
                <div class="upload-hint" style="color: #10b981; font-weight: 500; font-size: 0.85rem;">Klik "Simpan & Mulai Analisis AI" di bawah</div>
            `;
            dropArea.style.borderColor = '#10b981';
            dropArea.style.backgroundColor = '#ecfdf5';
        }
    });
</script>

</body>
</html>