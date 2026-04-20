<?php
require_once __DIR__ . '/../../core/auth.php';

requireLogin();
startSession();

// Jika sudah terotorisasi, langsung masuk ke modul 11 index.php
if (!empty($_SESSION['modul_11_authorized'])) {
    header("Location: index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    $credential_id = $_POST['credential_id'] ?? '';
    $password = $_POST['password'] ?? '';

    // Dummy bypass: Asalkan isi form, kita izinkan masuk untuk keperluan showcase UI.
    if (!empty($role) && !empty($credential_id) && !empty($password)) {
        $_SESSION['modul_11_authorized'] = true;
        $_SESSION['modul_11_role'] = $role; // Opsional: Untuk disimpan ke log nanti
        header("Location: index.php");
        exit;
    } else {
        $error = "Mohon lengkapi semua parameter kredensial Anda.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otorisasi Pakar - Cephalo AI</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        
        :root {
            --primary: #0ea5e9;
            --glass-bg: rgba(15, 23, 42, 0.4);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            background-color: #020617;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        /* 3D Shapes */
        .bg-shapes { position: absolute; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; }
        .shape { position: absolute; border-radius: 50%; filter: blur(80px); animation: float 15s infinite ease-in-out alternate; opacity: 0.5; }
        .shape-1 { width: 500px; height: 500px; background: #0284c7; top: -20%; left: -10%; }
        .shape-2 { width: 400px; height: 400px; background: #4f46e5; bottom: -20%; right: -10%; animation-delay: -5s; }
        
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(40px, 40px) scale(1.1); }
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--glass-border);
            padding: 50px 40px;
            border-radius: 24px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
            z-index: 10;
        }

        .icon-brand {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #0ea5e9, #4f46e5);
            border-radius: 16px;
            display: inline-flex; justify-content: center; align-items: center;
            margin-bottom: 20px;
            box-shadow: 0 10px 20px rgba(14, 165, 233, 0.3);
        }

        .title { font-size: 1.8rem; font-weight: 800; margin: 0 0 10px 0; letter-spacing: -0.5px; }
        .subtitle { color: #94a3b8; font-size: 0.95rem; margin-bottom: 30px; line-height: 1.5; }

        .form-group { text-align: left; margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #cbd5e1; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }
        
        input, select {
            width: 100%; padding: 14px 18px; box-sizing: border-box;
            background: rgba(2, 6, 23, 0.5); border: 1px solid var(--glass-border);
            border-radius: 12px; color: white; font-size: 1rem;
            transition: all 0.3s;
        }
        input:focus, select:focus { outline: none; border-color: var(--primary); background: rgba(2, 6, 23, 0.8); }
        select option { background: #0f172a; }

        .btn-submit {
            background: #fff; color: #0f172a; width: 100%; border: none; padding: 16px;
            border-radius: 12px; font-weight: 700; font-size: 1.05rem; cursor: pointer;
            margin-top: 10px; transition: all 0.3s;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(255, 255, 255, 0.15); }

        .error { color: #ef4444; background: rgba(239, 68, 68, 0.1); padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; border: 1px solid rgba(239, 68, 68, 0.2); }
        
        .back-link { display: inline-block; margin-top: 25px; color: #94a3b8; text-decoration: none; font-size: 0.9rem; transition: color 0.3s; }
        .back-link:hover { color: white; }
    </style>
</head>
<body>

<div class="bg-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
</div>

<div class="login-card">
    <div class="icon-brand">
        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
    </div>
    
    <h1 class="title">Otorisasi Klinis</h1>
    <p class="subtitle">Validasi peran Dokter Spesialis atau AI Engineer untuk mengakses Cephalo AI.</p>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label>Peran Otoritas</label>
            <select name="role" required>
                <option value="" disabled selected>Pilih hak akses...</option>
                <option value="Dokter Ortodonti">Dokter Ortodonti</option>
                <option value="AI Engineer">AI Engineer</option>
                <option value="Peneliti (Guest)">Peneliti (Guest)</option>
            </select>
        </div>

        <div class="form-group">
            <label>ID Kredensial / NIK</label>
            <input type="text" name="credential_id" placeholder="Masukkan ID verifikasi..." required>
        </div>

        <div class="form-group">
            <label>Kata Sandi Akses</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-submit">Buka Akses AI</button>
    </form>

    <a href="../../index.php" class="back-link">← Kembali ke Dashboard Utama</a>
</div>

</body>
</html>
