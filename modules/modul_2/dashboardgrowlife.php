<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$motherName = $_SESSION['motherName'] ?? 'Bunda';
$userId = $_SESSION['user_id'];
$children = [];
$stmt = $conn->prepare("SELECT id, name, type FROM children WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $children[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrowLife - Dashboard Utama</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --pink-dark: #fb6f92;
            --pink-light: #ffafcc;
            --bg-soft: #fffcfd;
            --text: #4a4a4a;
        }

        * {
            box-sizing: border-box;
            max-width: 100%;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-soft);
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
            width: 100%;
            overflow-x: hidden;
            color: var(--text);
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 260px;
            background: white;
            height: 100vh;
            position: fixed;
            box-sizing: border-box;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.03);
            padding: 25px;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--pink-dark);
            margin-bottom: 40px;
        }

        .nav-links {
            list-style: none;
            padding: 0;
            flex-grow: 1;
        }

        .nav-links li {
            margin-bottom: 10px;
        }

        .nav-links a {
            text-decoration: none;
            color: #888;
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 18px;
            border-radius: 15px;
            transition: 0.3s;
        }

        .nav-links a.active,
        .nav-links a:hover {
            background: #fff0f3;
            color: var(--pink-dark);
            font-weight: 600;
        }

        /* Main Content */
        .main-content {
            margin-left: 310px;
            padding: 40px;
            width: calc(100% - 310px);
        }

        .welcome-header {
            background: linear-gradient(135deg, var(--pink-dark), var(--pink-light));
            color: white;
            padding: 45px;
            border-radius: 35px;
            margin-bottom: 40px;
        }

        .welcome-header h1 {
            margin: 0;
            font-size: 2rem;
        }

        .welcome-header p {
            opacity: 0.9;
            margin-top: 10px;
            font-weight: 300;
        }

        .grid-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            max-width: 1200px;
        }

        /* Feature Cards */
        .card {
            background: white;
            padding: 30px;
            border-radius: 28px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.02);
            transition: 0.4s ease;
            border: 1px solid #f1f1f1;
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: inherit;
        }

        .card:hover {
            transform: translateY(-10px);
            border-color: var(--pink-light);
        }

        .card.disabled {
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
            background: #fafafa;
        }

        .icon-box {
            width: 65px;
            height: 65px;
            background: #fff0f3;
            color: var(--pink-dark);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 25px;
        }

        .card h3 {
            margin: 0 0 12px 0;
            font-size: 1.2rem;
            color: #333;
        }

        .card p {
            font-size: 0.9rem;
            color: #777;
            line-height: 1.6;
            margin-bottom: 25px;
            flex-grow: 1;
        }

        .status-tag {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 6px 16px;
            border-radius: 50px;
            display: inline-block;
            align-self: flex-start;
        }

        .status-ready {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-soon {
            background: #f5f5f5;
            color: #999;
        }

        footer {
            margin-top: 60px;
            padding: 25px 0;
            border-top: 1px solid #eee;
            font-size: 0.85rem;
            color: #bbb;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding: 25px 10px;
                align-items: center;
            }

            .sidebar span,
            .logo span {
                display: none;
            }

            .main-content {
                margin-left: 100px;
                width: calc(100% - 100px);
            }
        }
    </style>
</head>

<body>

    <nav class="sidebar">
        <div class="logo"><i class="fas fa-leaf"></i> <span>GrowLife</span></div>
        <ul class="nav-links">
            <li><a href="#" class="active"><i class="fas fa-th-large"></i> <span>Dashboard</span></a></li>
            <li><a href="Prenatal Intelligence/index.php"><i class="fas fa-baby"></i> <span>Prenatal</span></a></li>
            <li><a href="Stunting Monitor/index.php"><i class="fas fa-chart-line"></i> <span>Stunting</span></a></li>
            <li><a href="Adaptive Reminder/index.php"><i class="fas fa-bell"></i> <span>Reminder</span></a></li>
            <li><a href="Integrated Locator/index.php"><i class="fas fa-map-marker-alt"></i> <span>Locator</span></a>
            </li>
        </ul>
        <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid #ffebee;">
            <a href="#" onclick="logout()" style="color: #e53935; font-weight: 600; text-decoration: none; font-size: 0.95rem; display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 10px; transition: 0.3s;" onmouseover="this.style.background='#ffebee'" onmouseout="this.style.background='transparent'"><i class="fas fa-sign-out-alt"></i> <span>Keluar / Log Out</span></a>
        </div>
    </nav>

    <main class="main-content">
        <header class="welcome-header">
            <h1>Halo, Bunda <span id="momNameDisplay"></span>! 👋</h1>
            <p>Pusat integrasi layanan kesehatan ibu dan anak.</p>
        </header>

        <!-- Modul Multi-Profile Switcher -->
        <div style="background: white; padding: 25px; border-radius: 25px; margin-bottom: 40px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 5px 20px rgba(0,0,0,0.03); flex-wrap: wrap; gap: 15px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="width: 50px; height: 50px; background: #fff0f3; color: var(--pink-dark); border-radius: 15px; display: flex; justify-content: center; align-items: center; font-size: 1.5rem;">
                    <i id="childActiveIcon" class="fas fa-users"></i>
                </div>
                <div>
                    <h3 style="margin: 0; color: var(--text); font-size: 1.2rem;">Profil Target Aktif</h3>
                    <p style="margin: 3px 0 0 0; font-size: 0.85rem; color: #888;">Modul yang diklik akan terintegrasi pada profil ini.</p>
                </div>
            </div>
            
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <select id="childSelector" style="padding: 12px 20px; border-radius: 15px; border: 2px solid #eee; font-family: inherit; font-size: 0.95rem; outline: none; background: #fafafa; font-weight: 600; color: #444;" onchange="changeChild()">
                    <!-- Options JS -->
                </select>
                <button onclick="addChild()" style="padding: 12px 20px; background: #fffcfd; color: var(--pink-dark); border: 2px solid var(--pink-dark); border-radius: 15px; font-weight: 600; cursor: pointer; transition: 0.3s;" onmouseover="this.style.background='var(--pink-dark)'; this.style.color='white'" onmouseout="this.style.background='#fffcfd'; this.style.color='var(--pink-dark)'">
                    <i class="fas fa-plus"></i> Tambah Profil
                </button>
            </div>
        </div>

        <section class="grid-features">
            <a href="Prenatal Intelligence/index.php" class="card">
                <div class="icon-box"><i class="fas fa-brain"></i></div>
                <h3>Prenatal Intelligence</h3>
                <p>Pantau janin per minggu, kalkulator gizi, dan deteksi risiko kehamilan secara cerdas.</p>
                <span class="status-tag status-ready">Buka Fitur <i class="fas fa-arrow-right"
                        style="margin-left: 5px; font-size: 0.7rem;"></i></span>
            </a>

            <a href="Stunting Monitor/index.php" class="card">
                <div class="icon-box"><i class="fas fa-chart-area"></i></div>
                <h3>Stunting Monitor</h3>
                <p>Grafik pertumbuhan interaktif (berdasarkan Kurva standar WHO) yang dimudahkan untuk dibaca Bunda
                    awam.</p>
                <span class="status-tag status-ready">Buka Fitur <i class="fas fa-arrow-right"
                        style="margin-left: 5px; font-size: 0.7rem;"></i></span>
            </a>

            <a href="Adaptive Reminder/index.php" class="card">
                <div class="icon-box"><i class="fas fa-bell"></i></div>
                <h3>Adaptive Reminder</h3>
                <p>Pengingat otomatis jadwal imunisasi dan kontrol rutin Bunda.</p>
                <span class="status-tag status-ready">Buka Fitur <i class="fas fa-arrow-right" style="margin-left: 5px; font-size: 0.7rem;"></i></span>
            </a>

            <a href="Integrated Locator/index.php" class="card">
                <div class="icon-box"><i class="fas fa-map-marked-alt"></i></div>
                <h3>Integrated Locator</h3>
                <p>Cari fasilitas kesehatan terdekat (Bidan, RSIA, Puskesmas) secara real-time dari posisi Anda.</p>
                <span class="status-tag status-ready">Buka Fitur <i class="fas fa-arrow-right"
                        style="margin-left: 5px; font-size: 0.7rem;"></i></span>
            </a>
        </section>

        <footer>
            &copy; 2026 GrowLife | MedTech ITS
        </footer>
    </main>

    <!-- Script Multi-Profile Anak -->
    <script>
        // 1. Cek Login Ibu via PHP
        const momName = <?= json_encode($motherName) ?>;
        document.getElementById('momNameDisplay').innerText = momName;

        // 2. State Anak dari MySQL
        let childrenProfiles = <?= json_encode($children) ?>;
        // Simpan sebentar ke localstorage buat transisi modul lain yang belum diganti
        localStorage.setItem('childrenProfiles', JSON.stringify(childrenProfiles));

        let activeChildId = localStorage.getItem('activeChildId');

        function renderChildSelector() {
            const sel = document.getElementById('childSelector');
            sel.innerHTML = "";
            
            if(childrenProfiles.length === 0) {
                sel.innerHTML = "<option value=''>Silakan tambah anak/titipan pertama</option>";
                document.getElementById('childActiveIcon').className = "fas fa-question-circle";
                return;
            }

            childrenProfiles.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.text = c.name + (c.type === 'janin' ? ' (Fase Kehamilan)' : ' (Anak Sudah Lahir)');
                if(c.id == activeChildId) opt.selected = true;
                sel.appendChild(opt);
            });

            // Set default if null
            if(!activeChildId && childrenProfiles.length > 0) {
                changeChild(childrenProfiles[0].id);
            } else if (activeChildId) {
                updateAvatarIcon();
            }
        }

        function changeChild(forcedId = null) {
            const selVal = forcedId || document.getElementById('childSelector').value;
            if(!selVal) return;

            activeChildId = selVal;
            localStorage.setItem('activeChildId', activeChildId);
            updateAvatarIcon();
        }

        function updateAvatarIcon() {
            const activeChild = childrenProfiles.find(x => x.id == activeChildId);
            const icon = document.getElementById('childActiveIcon');
            if(activeChild) {
                icon.className = activeChild.type === 'janin' ? 'fas fa-baby-carriage' : 'fas fa-child';
            }
            // Fix selector value if updated via fallback
            document.getElementById('childSelector').value = activeChildId;
        }

        async function addChild() {
            const name = prompt("Siapa nama Anak / Panggilan Calon Bayi saat ini?");
            if(!name) return;
            const isBorn = confirm("Klik OK jika anak ini sudah lahir, atau CANCEL jika masih dalam kandungan (Janin).");
            const type = isBorn ? "anak" : "janin";
            
            const formData = new FormData();
            formData.append('action', 'add_child');
            formData.append('name', name);
            formData.append('type', type);

            try {
                const response = await fetch('api.php', { method: 'POST', body: formData });
                const data = await response.json();
                
                if(data.status === 'success') {
                    const newChild = { id: data.id, name: name, type: type };
                    childrenProfiles.push(newChild);
                    localStorage.setItem('childrenProfiles', JSON.stringify(childrenProfiles));
                    
                    changeChild(newChild.id);
                    renderChildSelector();
                    
                    alert(`✅ Profil ${name} berhasil didaftarkan! Seluruh modul yang Anda klik sekarang akan merekam data untuk ${name}.`);
                } else {
                    alert('Gagal: ' + data.message);
                }
            } catch(e) {
                alert('Server error.');
            }
        }

        async function logout() {
            if(confirm("Bunda sungguh ingin Log Out dari aplikasi GrowLife?")) {
                localStorage.removeItem('motherName');
                localStorage.removeItem('activeChildId');
                
                // Panggil logout di PHP
                const fd = new FormData();
                fd.append('action', 'logout');
                await fetch('api_auth.php', { method:'POST', body: fd });
                
                document.body.style.opacity = 0;
                document.body.style.transition = "opacity 0.6s ease";
                setTimeout(() => {
                    window.location.href = "login.php";
                }, 600);
            }
        }

        window.onload = renderChildSelector;
    </script>
</body>

</html>