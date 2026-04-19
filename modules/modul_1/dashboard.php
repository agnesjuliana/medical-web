<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
require_once __DIR__ . '/../../config/database.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pdo = getDBConnection();

$page = $_GET['page'] ?? 'home';

// Fetch Onboarding Data
$opType = '';
$role = '';
$userName = $user['name'] ?? 'Guest';
$patientName = 'Budi (Pasien)';
$surgeryDate = '';

// Check preview mode first
if (isset($_GET['preview_role']) || isset($_GET['preview_op'])) {
    $role = $_GET['preview_role'] ?? 'pasien';
    $opType = $_GET['preview_op'] ?? 'cabg';
    if ($role === 'caregiver') {
        $patientName = 'Pasien (Budi)';
    }
    $surgeryDate = date('Y-m-d', strtotime('-2 days')); // Mock 2 days ago
} else {
    // Try to get from Database
    try {
        $stmt = $pdo->prepare("SELECT * FROM user_onboarding WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$user['id']]);
        $onboarding = $stmt->fetch();
        
        if ($onboarding) {
            $role = $onboarding['role'] ?? 'pasien';
            $opType = $onboarding['operation_type'] ?? 'cabg';
            $userName = $onboarding['full_name'] ?: $user['name'];
            $surgeryDate = $onboarding['surgery_date'];
            if ($role === 'caregiver') {
                $patientName = $onboarding['patient_name'] ?: 'Pasien';
            }
        } else {
            // Un-onboarded, fallback to defaults
            $role = 'pasien';
            $opType = 'cabg';
            $surgeryDate = date('Y-m-d', strtotime('-1 days'));
        }
    } catch (PDOException $e) {
        $role = 'pasien';
        $opType = 'cabg';
        $surgeryDate = date('Y-m-d', strtotime('-1 days'));
    }
}

// Handle Monitoring Data Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_monitoring') {
    $today = date('Y-m-d');
    
    // Generic
    $r_spo2 = isset($_POST['spo2']) && $_POST['spo2'] !== '' ? (int)$_POST['spo2'] : null;
    $r_hr = isset($_POST['heart_rate']) && $_POST['heart_rate'] !== '' ? (int)$_POST['heart_rate'] : null;
    $r_pain = isset($_POST['pain_level']) && $_POST['pain_level'] !== '' ? (int)$_POST['pain_level'] : null;
    
    // SC
    $r_temp = isset($_POST['temp']) && $_POST['temp'] !== '' ? (float)$_POST['temp'] : null;
    $r_bVol = $_POST['blood_volume'] ?? null;
    $r_bCol = $_POST['blood_color'] ?? null;
    $r_bClot = $_POST['blood_clots'] ?? null;
    
    // Amputation
    $r_stump = isset($_POST['stump_pain']) && $_POST['stump_pain'] !== '' ? (int)$_POST['stump_pain'] : null;
    $r_phantom = isset($_POST['phantom_pain']) && $_POST['phantom_pain'] !== '' ? (int)$_POST['phantom_pain'] : null;
    $r_wCol = $_POST['wound_color'] ?? null;
    $r_wSwell = $_POST['wound_swelling'] ?? null;
    $r_wFluid = $_POST['wound_fluid'] ?? null;
    $r_wOdor = $_POST['wound_odor'] ?? null;

    try {
        $stmtIns = $pdo->prepare("INSERT INTO user_daily_monitoring 
            (user_id, record_date, spo2, heart_rate, pain_level, temp, blood_volume, blood_color, blood_clots, stump_pain, phantom_pain, wound_color, wound_swelling, wound_fluid, wound_odor) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            spo2=VALUES(spo2), heart_rate=VALUES(heart_rate), pain_level=VALUES(pain_level), temp=VALUES(temp), blood_volume=VALUES(blood_volume), blood_color=VALUES(blood_color), blood_clots=VALUES(blood_clots), stump_pain=VALUES(stump_pain), phantom_pain=VALUES(phantom_pain), wound_color=VALUES(wound_color), wound_swelling=VALUES(wound_swelling), wound_fluid=VALUES(wound_fluid), wound_odor=VALUES(wound_odor)
            ");
        $stmtIns->execute([$user['id'], $today, $r_spo2, $r_hr, $r_pain, $r_temp, $r_bVol, $r_bCol, $r_bClot, $r_stump, $r_phantom, $r_wCol, $r_wSwell, $r_wFluid, $r_wOdor]);
        header("Location: dashboard.php?page=home");
        exit;
    } catch (PDOException $e) {
        $errorMsg = "Error saving monitoring data: " . $e->getMessage();
    }
}

// Fetch today's monitoring data
$todayMonitoring = null;
try {
    $today = date('Y-m-d');
    $stmtMon = $pdo->prepare("SELECT * FROM user_daily_monitoring WHERE user_id = ? AND record_date = ? LIMIT 1");
    $stmtMon->execute([$user['id'], $today]);
    $todayMonitoring = $stmtMon->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Just ignore if table missing
}

// Calculate days post op
$dayPostOp = 1;
if ($surgeryDate) {
    $dStart = new DateTime($surgeryDate);
    $dEnd  = new DateTime();
    $dStart->setTime(0,0,0);
    $dEnd->setTime(0,0,0);
    $diff = $dStart->diff($dEnd);
    $dayPostOp = $diff->days + 1; // Hari ke-1 adalah hari H+1
    if ($dStart > $dEnd) $dayPostOp = 0;
}

// Operation type display strings
$opDisplay = [
    'cabg' => 'Jantung (CABG)',
    'sc' => 'Sectio Caesarea',
    'amputation' => 'Ortopedi'
];
$opName = $opDisplay[$opType] ?? 'Operasi';

$pageTitle = 'Dashboard RuangPulih';
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
    body, body *, .font-sans {
        font-family: 'Poppins', sans-serif !important;
    }
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    
    /* Simple Custom Checkbox via CSS since we aren't sure if tailwind forms plugin is loaded */
    input[type="checkbox"] {
        accent-color: #98b0c4;
    }
</style>

<div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
        <!-- Logo & Subtitle -->
        <div class="px-6 py-8">
            <a href="index.php" class="flex items-center gap-3">
                <img src="assets/images/logo.png" alt="RuangPulih Logo" class="h-8 opacity-80">
                <div>
                    <h1 class="text-xl font-bold text-[#b1c3ce] leading-none">Ruang<span class="text-[#98b0c4]">Pulih</span></h1>
                    <p class="text-[0.6rem] text-gray-400 mt-1 uppercase tracking-tight">Pasca-Operasi & Rehabilitasi Mandiri</p>
                </div>
            </a>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-4 space-y-1 flex flex-col py-4">
            <a href="dashboard.php?page=home" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $page === 'home' ? 'bg-[#e2e8f0] text-[#5A6C7A] font-bold' : 'text-gray-500 hover:bg-gray-50' ?>">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Home
            </a>

            <a href="dashboard.php?page=roadmap" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $page === 'roadmap' ? 'bg-[#e2e8f0] text-[#5A6C7A] font-bold' : 'text-gray-500 hover:bg-gray-50' ?>">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"></path>
                </svg>
                Recovery Roadmap
            </a>

            <a href="dashboard.php?page=monitoring" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $page === 'monitoring' ? 'bg-[#e2e8f0] text-[#5A6C7A] font-bold' : 'text-gray-500 hover:bg-gray-50' ?>">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"></path>
                </svg>
                Monitoring
            </a>

            <a href="dashboard.php?page=content" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $page === 'content' ? 'bg-[#e2e8f0] text-[#5A6C7A] font-bold' : 'text-gray-500 hover:bg-gray-50' ?>">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"></path>
                </svg>
                Content Library
            </a>

            <!-- Profile Section pushed to bottom -->
            <a href="dashboard.php?page=profile" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $page === 'profile' ? 'bg-[#e2e8f0] text-[#5A6C7A] font-bold' : 'text-gray-500 hover:bg-gray-50' ?> !mt-auto">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                </svg>
                Profile
            </a>

            <!-- Exit Link -->
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-[#728BA9] hover:bg-gray-50 rounded-lg transition-colors font-bold mt-1">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H9m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h6a3 3 0 013 3v1"></path>
                </svg>
                Exit
            </a>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 p-8">
        <?php if ($page === 'home'): ?>
            <!-- HOME VIEW -->
            <header class="flex items-start justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Halo, <?= htmlspecialchars($userName) ?></h2>
                    <?php if ($role === 'caregiver'): ?>
                        <p class="text-[#728BA9] font-semibold mt-1">Pantau perkembangan <?= htmlspecialchars($patientName) ?></p>
                        <p class="text-gray-400 text-sm mt-0.5">Hari ke-<?= $dayPostOp ?> pasca operasi <?= $opName ?></p>
                    <?php else: ?>
                        <p class="text-gray-500 mt-1">Hari ke-<?= $dayPostOp ?> pasca operasi <?= $opName ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($role === 'caregiver'): ?>
                    <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#ECF2E6] text-[#5A6C7A] text-xs font-bold border border-[#D1D9CA] uppercase tracking-wider">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Mode Caregiver
                    </span>
                <?php endif; ?>
            </header>

            <?php if ($opType === 'cabg'): ?>
            <!-- ============ CABG DASHBOARD ============ -->
            <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Vitals Row (full width) -->
                <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- SpO2 -->
                    <div class="bg-[#F8FCFF] rounded-2xl p-6 border border-[#DAE3EC] relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-[#B8C9DD] rounded-full opacity-10 -translate-y-1/2 translate-x-1/2"></div>
                        <p class="text-xs font-bold text-[#728BA9] uppercase tracking-wider mb-1">SpO₂</p>
                        <p class="text-4xl font-extrabold text-[#728BA9]"><?= isset($todayMonitoring['spo2']) && $todayMonitoring['spo2'] !== null ? htmlspecialchars($todayMonitoring['spo2']) : '--' ?><span class="text-lg font-bold">%</span></p>
                        <p class="text-xs text-[#A3ACA0] font-medium mt-1">Normal ≥ 95%</p>
                    </div>
                    <!-- Heart Rate -->
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Detak Jantung</p>
                        <p class="text-4xl font-extrabold text-gray-700"><?= isset($todayMonitoring['heart_rate']) && $todayMonitoring['heart_rate'] !== null ? htmlspecialchars($todayMonitoring['heart_rate']) : '--' ?> <span class="text-lg font-bold text-gray-400">bpm</span></p>
                        <p class="text-xs text-[#A3ACA0] font-medium mt-1">Normal 60–100 bpm</p>
                    </div>
                    <!-- Chest Pain -->
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nyeri Dada</p>
                        <p class="text-4xl font-extrabold text-gray-700"><?= isset($todayMonitoring['pain_level']) && $todayMonitoring['pain_level'] !== null ? htmlspecialchars($todayMonitoring['pain_level']) : '--' ?><span class="text-lg font-bold text-gray-400">/10</span></p>
                        <p class="text-xs text-[#A3ACA0] font-medium mt-1">Ringan — dalam batas wajar</p>
                    </div>
                </div>

                <!-- Recovery Checklist (2 col) -->
                <div class="lg:col-span-2 bg-[#ECF2E6] rounded-2xl p-8 relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-64 h-64 bg-[#D1D9CA] rounded-full opacity-40 -translate-y-1/4 translate-x-1/4"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-bold text-[#5A6C7A]"><?= $role === 'caregiver' ? 'Checklist Pasien Hari Ini' : 'Recovery Hari Ini' ?></h3>
                            <span class="text-sm font-bold text-[#98b0c4] task-progress-text">0%</span>
                        </div>
                        <div class="w-full bg-[#D1D9CA]/50 rounded-full h-2 mb-5">
                            <div class="bg-[#98b0c4] h-2 rounded-full transition-all duration-300 task-progress-bar" style="width: 0%"></div>
                        </div>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3"><input type="checkbox" class="w-5 h-5 task-checkbox"> <span class="text-[#5A6C7A] font-medium"><?= $role === 'caregiver' ? 'Pastikan pasien latihan pernapasan (deep breathing)' : 'Latihan pernapasan (deep breathing) 5–10 rep/jam' ?></span></li>
                            <li class="flex items-center gap-3"><input type="checkbox" class="w-5 h-5 task-checkbox"> <span class="text-[#5A6C7A] font-medium"><?= $role === 'caregiver' ? 'Dampingi pasien jalan kaki 5 menit' : 'Jalan kaki 5 menit dengan pendamping' ?></span></li>
                            <li class="flex items-center gap-3"><input type="checkbox" class="w-5 h-5 task-checkbox"> <span class="text-[#5A6C7A] font-medium"><?= $role === 'caregiver' ? 'Ingatkan pasien minum obat' : 'Minum obat sesuai jadwal' ?></span></li>
                            <li class="flex items-center gap-3"><input type="checkbox" class="w-5 h-5 task-checkbox"> <span class="text-[#5A6C7A] font-medium"><?= $role === 'caregiver' ? 'Bantu pasien batuk efektif (tahan dada)' : 'Batuk efektif 2–3x (dengan menahan dada)' ?></span></li>
                        </ul>
                    </div>
                </div>

                <!-- Alert (1 col) -->
                <div class="flex flex-col gap-4">
                    <div class="bg-red-50 rounded-2xl p-6 border border-red-100 flex-1">
                        <h3 class="text-sm font-bold text-red-700 mb-3 flex items-center gap-2"><span>🚨</span> Red Flag</h3>
                        <ul class="space-y-2 text-sm text-red-700 font-medium">
                            <li><?= $role === 'caregiver' ? 'Jika SpO₂ pasien < 92%, segera hubungi RS' : 'Jika SpO₂ kamu < 92%, hentikan aktivitas' ?></li>
                            <li><?= $role === 'caregiver' ? 'Perhatikan jika pasien sesak napas tiba-tiba' : 'Lapor jika ada sesak napas mendadak' ?></li>
                        </ul>
                    </div>
                    <div class="bg-[#F8FCFF] rounded-2xl p-6 border border-[#DAE3EC]">
                        <h3 class="text-sm font-bold text-[#728BA9] mb-2">📋 Quick Actions</h3>
                        <div class="space-y-2">
                            <a href="dashboard.php?page=roadmap" class="block text-sm font-semibold text-[#728BA9] hover:text-[#5A6C7A] transition-colors">→ Lihat Roadmap Hari Ini</a>
                            <a href="dashboard.php?page=monitoring" class="block text-sm font-semibold text-[#728BA9] hover:text-[#5A6C7A] transition-colors">→ Catat Tanda Vital</a>
                        </div>
                    </div>
                </div>
            </div>

            <?php elseif ($opType === 'sc'): ?>
            <!-- ============ SECTIO CAESAREA DASHBOARD ============ -->
            <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Bleeding Tracker (dominant, 2 col) -->
                <div class="lg:col-span-2 bg-purple-50/60 rounded-2xl p-8 border border-purple-100 relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-purple-200 rounded-full opacity-20"></div>
                    <h3 class="text-lg font-bold text-purple-800 mb-5 relative z-10">🩸 Pemantauan Perdarahan (Lochia)</h3>
                    <div class="grid grid-cols-3 gap-4 relative z-10">
                        <div class="bg-white rounded-xl p-4 text-center border border-purple-100">
                            <p class="text-xs font-bold text-purple-400 uppercase tracking-wider mb-1">Volume</p>
                            <p class="text-2xl font-extrabold text-purple-700 <?= empty($todayMonitoring['blood_volume']) ? 'opacity-30' : '' ?>"><?= htmlspecialchars($todayMonitoring['blood_volume'] ?? '--') ?></p>
                        </div>
                        <div class="bg-white rounded-xl p-4 text-center border border-purple-100">
                            <p class="text-xs font-bold text-purple-400 uppercase tracking-wider mb-1">Warna</p>
                            <p class="text-2xl font-extrabold text-purple-700 <?= empty($todayMonitoring['blood_color']) ? 'opacity-30' : '' ?>"><?= htmlspecialchars($todayMonitoring['blood_color'] ?? '--') ?></p>
                        </div>
                        <div class="bg-white rounded-xl p-4 text-center border border-purple-100">
                            <p class="text-xs font-bold text-purple-400 uppercase tracking-wider mb-1">Gumpalan</p>
                            <p class="text-2xl font-extrabold text-purple-700 <?= empty($todayMonitoring['blood_clots']) ? 'opacity-30' : '' ?>"><?= htmlspecialchars($todayMonitoring['blood_clots'] ?? '--') ?></p>
                        </div>
                    </div>
                    <p class="text-xs text-purple-500 font-medium mt-4 relative z-10"><?= $role === 'caregiver' ? 'Pantau penggantian pembalut pasien setiap 4–6 jam.' : 'Ganti pembalut setiap 4–6 jam dan catat perubahannya.' ?></p>
                </div>

                <!-- Wound Status (1 col) -->
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-700 mb-4">🩹 Kondisi Luka SC</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center"><span class="text-sm text-gray-500 font-medium">Kemerahan</span><span class="text-sm font-bold text-green-600">Normal</span></div>
                        <div class="flex justify-between items-center"><span class="text-sm text-gray-500 font-medium">Bengkak</span><span class="text-sm font-bold text-green-600">Tidak ada</span></div>
                        <div class="flex justify-between items-center"><span class="text-sm text-gray-500 font-medium">Cairan/nanah</span><span class="text-sm font-bold text-green-600">Tidak ada</span></div>
                        <div class="flex justify-between items-center"><span class="text-sm text-gray-500 font-medium">Jahitan</span><span class="text-sm font-bold text-[#728BA9]">Utuh</span></div>
                    </div>
                </div>

                <!-- Checklist (2 col) -->
                <div class="lg:col-span-2 bg-[#ECF2E6] rounded-2xl p-8 relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-48 h-48 bg-[#D1D9CA] rounded-full opacity-30 -translate-y-1/4 translate-x-1/4"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-bold text-[#5A6C7A]"><?= $role === 'caregiver' ? 'Checklist Pasien Hari Ini' : 'Recovery Hari Ini' ?></h3>
                            <span class="text-sm font-bold text-[#98b0c4] task-progress-text">0%</span>
                        </div>
                        <div class="w-full bg-[#D1D9CA]/50 rounded-full h-2 mb-5">
                            <div class="bg-[#98b0c4] h-2 rounded-full transition-all duration-300 task-progress-bar" style="width: 0%"></div>
                        </div>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3"><input type="checkbox" class="w-5 h-5 task-checkbox"> <span class="text-[#5A6C7A] font-medium"><?= $role === 'caregiver' ? 'Bantu pasien mobilisasi ringan (duduk → berdiri)' : 'Mobilisasi ringan: duduk → berdiri perlahan' ?></span></li>
                            <li class="flex items-center gap-3"><input type="checkbox" class="w-5 h-5 task-checkbox"> <span class="text-[#5A6C7A] font-medium"><?= $role === 'caregiver' ? 'Ingatkan pasien menyusui / pompa ASI' : 'Menyusui / pompa ASI sesuai jadwal' ?></span></li>
                            <li class="flex items-center gap-3"><input type="checkbox" class="w-5 h-5 task-checkbox"> <span class="text-[#5A6C7A] font-medium"><?= $role === 'caregiver' ? 'Pastikan pasien minum obat pereda nyeri' : 'Minum obat pereda nyeri sesuai resep' ?></span></li>
                            <li class="flex items-center gap-3"><input type="checkbox" class="w-5 h-5 task-checkbox"> <span class="text-[#5A6C7A] font-medium"><?= $role === 'caregiver' ? 'Periksa luka SC pasien secara visual' : 'Periksa luka SC di cermin (kemerahan/cairan)' ?></span></li>
                        </ul>
                    </div>
                </div>

                <!-- Alert + Vitals (1 col) -->
                <div class="flex flex-col gap-4">
                    <div class="bg-red-50 rounded-2xl p-6 border border-red-100">
                        <h3 class="text-sm font-bold text-red-700 mb-3 flex items-center gap-2"><span>🚨</span> Red Flag</h3>
                        <ul class="space-y-2 text-sm text-red-700 font-medium">
                            <li><?= $role === 'caregiver' ? 'Jika perdarahan sangat banyak / gumpalan besar → bawa ke RS' : 'Jika perdarahan sangat banyak atau ada gumpalan besar → ke RS' ?></li>
                            <li><?= $role === 'caregiver' ? 'Jika pasien demam > 38°C, segera lapor dokter' : 'Jika demam > 38°C, hubungi dokter segera' ?></li>
                        </ul>
                    </div>
                    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Suhu Tubuh</p>
                        <p class="text-3xl font-extrabold text-gray-700 <?= !isset($todayMonitoring['temp']) ? 'opacity-30' : '' ?>"><?= isset($todayMonitoring['temp']) ? htmlspecialchars($todayMonitoring['temp']) : '--' ?><span class="text-base font-bold text-gray-400">°C</span></p>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <!-- ============ ORTOPEDI DASHBOARD ============ -->
            <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Wound Monitor (dominant, 2 col) -->
                <div class="lg:col-span-2 bg-orange-50/60 rounded-2xl p-8 border border-orange-100 relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-orange-200 rounded-full opacity-20"></div>
                    <h3 class="text-lg font-bold text-orange-800 mb-5 relative z-10">🩹 Monitoring Luka Ortopedi / Pasca Operasi Tulang</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 relative z-10">
                        <div class="bg-white rounded-xl p-4 text-center border border-orange-100">
                            <p class="text-xs font-bold text-orange-400 uppercase tracking-wider mb-1">Warna Kulit</p>
                            <p class="text-lg font-extrabold text-orange-700 <?= empty($todayMonitoring['wound_color']) ? 'opacity-30' : '' ?>"><?= htmlspecialchars($todayMonitoring['wound_color'] ?? '--') ?></p>
                        </div>
                        <div class="bg-white rounded-xl p-4 text-center border border-orange-100">
                            <p class="text-xs font-bold text-orange-400 uppercase tracking-wider mb-1">Bengkak</p>
                            <p class="text-lg font-extrabold text-orange-700 <?= empty($todayMonitoring['wound_swelling']) ? 'opacity-30' : '' ?>"><?= htmlspecialchars($todayMonitoring['wound_swelling'] ?? '--') ?></p>
                        </div>
                        <div class="bg-white rounded-xl p-4 text-center border border-orange-100">
                            <p class="text-xs font-bold text-orange-400 uppercase tracking-wider mb-1">Cairan</p>
                            <p class="text-lg font-extrabold text-green-600 <?= empty($todayMonitoring['wound_fluid']) ? 'opacity-30' : '' ?>"><?= htmlspecialchars($todayMonitoring['wound_fluid'] ?? '--') ?></p>
                        </div>
                        <div class="bg-white rounded-xl p-4 text-center border border-orange-100">
                            <p class="text-xs font-bold text-orange-400 uppercase tracking-wider mb-1">Bau</p>
                            <p class="text-lg font-extrabold text-green-600 <?= empty($todayMonitoring['wound_odor']) ? 'opacity-30' : '' ?>"><?= htmlspecialchars($todayMonitoring['wound_odor'] ?? '--') ?></p>
                        </div>
                    </div>
                    <p class="text-xs text-orange-500 font-medium mt-4 relative z-10"><?= $role === 'caregiver' ? 'Periksa visual kondisi area operasi pasien setiap pagi & malam.' : 'Periksa area operasi setiap pagi dan malam. Catat setiap perubahan.' ?></p>
                </div>

                <!-- Pain Tracker (1 col) -->
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col">
                    <h3 class="text-sm font-bold text-gray-700 mb-4">⚡ Pain Tracker</h3>
                    <div class="space-y-4 flex-1">
                        <div>
                            <div class="flex justify-between mb-1"><span class="text-sm text-gray-500 font-medium">Nyeri Area Operasi</span><span class="text-sm font-bold text-gray-700"><?= isset($todayMonitoring['stump_pain']) ? htmlspecialchars($todayMonitoring['stump_pain']).'/10' : '--' ?></span></div>
                            <div class="w-full bg-gray-100 rounded-full h-2"><div class="bg-orange-400 h-2 rounded-full" style="width:<?= isset($todayMonitoring['stump_pain']) ? ((int)$todayMonitoring['stump_pain']*10).'%' : '0%' ?>"></div></div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1"><span class="text-sm text-gray-500 font-medium">Nyeri Sendi</span><span class="text-sm font-bold text-gray-700"><?= isset($todayMonitoring['phantom_pain']) ? htmlspecialchars($todayMonitoring['phantom_pain']).'/10' : '--' ?></span></div>
                            <div class="w-full bg-gray-100 rounded-full h-2"><div class="bg-red-400 h-2 rounded-full" style="width:<?= isset($todayMonitoring['phantom_pain']) ? ((int)$todayMonitoring['phantom_pain']*10).'%' : '0%' ?>"></div></div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 font-medium mt-4"><?= $role === 'caregiver' ? 'Tanyakan intensitas nyeri sendi secara berkala.' : 'Catat jika nyeri sendi muncul tiba-tiba atau makin kuat.' ?></p>
                </div>

                <!-- Checklist (2 col) -->
                <div class="lg:col-span-2 bg-[#ECF2E6] rounded-2xl p-8 relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-48 h-48 bg-[#D1D9CA] rounded-full opacity-30 -translate-y-1/4 translate-x-1/4"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-bold text-[#5A6C7A]"><?= $role === 'caregiver' ? 'Checklist Pasien Hari Ini' : 'Recovery Hari Ini' ?></h3>
                            <span class="text-sm font-bold text-[#98b0c4] task-progress-text">0%</span>
                        </div>
                        <div class="w-full bg-[#D1D9CA]/50 rounded-full h-2 mb-5">
                            <div class="bg-[#98b0c4] h-2 rounded-full transition-all duration-300 task-progress-bar" style="width: 0%"></div>
                        </div>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3"><input type="checkbox" class="w-5 h-5 task-checkbox"> <span class="text-[#5A6C7A] font-medium"><?= $role === 'caregiver' ? 'Bantu baringkan di posisi yang tepat (mengurangi tekan)' : 'Posisi rebahan sesuai anjuran' ?></span></li>
                            <li class="flex items-center gap-3"><input type="checkbox" class="w-5 h-5 task-checkbox"> <span class="text-[#5A6C7A] font-medium"><?= $role === 'caregiver' ? 'Dampingi pasien latihan jalan dgn alat bantu' : 'Latihan jalan dgn alat bantu' ?></span></li>
                            <li class="flex items-center gap-3"><input type="checkbox" class="w-5 h-5 task-checkbox"> <span class="text-[#5A6C7A] font-medium"><?= $role === 'caregiver' ? 'Ingatkan pasien minum obat sesuai jadwal' : 'Minum obat pereda nyeri sesuai resep' ?></span></li>
                            <li class="flex items-center gap-3"><input type="checkbox" class="w-5 h-5 task-checkbox"> <span class="text-[#5A6C7A] font-medium"><?= $role === 'caregiver' ? 'Periksa balutan luka ortopedi, laporkan perubahan' : 'Periksa kondisi luka/perban (ada rembesan/bau?)' ?></span></li>
                        </ul>
                    </div>
                </div>

                <!-- Alert + Mobility (1 col) -->
                <div class="flex flex-col gap-4">
                    <div class="bg-red-50 rounded-2xl p-6 border border-red-100">
                        <h3 class="text-sm font-bold text-red-700 mb-3 flex items-center gap-2"><span>🚨</span> Red Flag</h3>
                        <ul class="space-y-2 text-sm text-red-700 font-medium">
                            <li><?= $role === 'caregiver' ? 'Jika ada perdarahan aktif dari stump → tekan & ke RS' : 'Jika ada perdarahan aktif, tekan dengan kain bersih & ke RS' ?></li>
                            <li><?= $role === 'caregiver' ? 'Perhatikan tanda infeksi: merah, bengkak, panas lokal' : 'Tanda infeksi: warna kemerahan meluas, panas, dan berbau' ?></li>
                        </ul>
                    </div>
                    <div class="bg-[#F8FCFF] rounded-2xl p-5 border border-[#DAE3EC]">
                        <h3 class="text-sm font-bold text-[#728BA9] mb-2">🦽 Mobilitas</h3>
                        <p class="text-sm text-[#5A6C7A] font-medium"><?= $role === 'caregiver' ? 'Pasien saat ini dalam tahap: transfer kursi roda.' : 'Tahap kamu saat ini: transfer kursi roda.' ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        <?php elseif ($page === 'roadmap'): ?>
            <!-- ROADMAP VIEW -->
            <header>
                <h2 class="text-2xl font-bold text-gray-800">Roadmap Pemulihan Anda</h2>
                <p class="text-[#728BA9] mt-1 font-medium">Protokol: Pasca Operasi Jantung (CABG) - Hari 1-3</p>
            </header>

            <div class="mt-8 space-y-6 max-w-4xl">
                
                <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] p-8">
                    <h3 class="text-xl font-bold text-[#5A6C7A] mb-4">Posisi Tidur</h3>
                    <p class="text-gray-800 text-lg font-semibold mb-4">Semi-Fowler 30–45°</p>
                    <div class="inline-flex items-center gap-3 bg-[#F8FCFF] text-[#728BA9] text-sm px-5 py-3 rounded-xl font-medium border border-[#E2E8F0]">
                        <span class="text-lg">ℹ️</span> 
                        <span><strong class="font-bold">Info Penting:</strong> Gunakan bantal untuk menyangga dada saat batuk.</span>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] p-8">
                    <h3 class="text-xl font-bold text-[#5A6C7A] mb-6">Latihan</h3>
                    <ul class="space-y-5">
                        <li class="flex items-start gap-4 p-4 hover:bg-gray-50 rounded-xl transition-colors border border-transparent hover:border-gray-100">
                            <input type="checkbox" id="lat-1" class="w-6 h-6 mt-1 cursor-pointer">
                            <label for="lat-1" class="cursor-pointer flex-1">
                                <span class="text-gray-800 font-bold block mb-1">Latihan Pernapasan (Deep Breathing)</span>
                                <span class="text-gray-500 text-sm block">Tarik napas dalam 3 detik, tahan 2 detik, hembuskan. 5–10 repetisi/jam.</span>
                            </label>
                        </li>
                        <li class="flex items-start gap-4 p-4 hover:bg-gray-50 rounded-xl transition-colors border border-transparent hover:border-gray-100">
                            <input type="checkbox" id="lat-2" class="w-6 h-6 mt-1 cursor-pointer">
                            <label for="lat-2" class="cursor-pointer flex-1">
                                <span class="text-gray-800 font-bold block mb-1">Batuk Efektif</span>
                                <span class="text-gray-500 text-sm block">2–3x tiap sesi (dengan menahan dada).</span>
                            </label>
                        </li>
                    </ul>
                </div>

                <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] p-8">
                    <h3 class="text-xl font-bold text-[#5A6C7A] mb-6">Aktivitas Fisik</h3>
                    <ul class="space-y-5">
                        <li class="flex items-start gap-4 p-4 hover:bg-gray-50 rounded-xl transition-colors border border-transparent hover:border-gray-100">
                            <input type="checkbox" id="akt-1" class="w-6 h-6 mt-1 cursor-pointer">
                            <label for="akt-1" class="cursor-pointer flex-1">
                                <span class="text-gray-800 font-bold block mb-1">Duduk di kursi</span>
                                <span class="text-gray-500 text-sm block">15–30 menit, 2–3x/hari.</span>
                            </label>
                        </li>
                        <li class="flex items-start gap-4 p-4 hover:bg-gray-50 rounded-xl transition-colors border border-transparent hover:border-gray-100">
                            <input type="checkbox" id="akt-2" class="w-6 h-6 mt-1 cursor-pointer">
                            <label for="akt-2" class="cursor-pointer flex-1">
                                <span class="text-gray-800 font-bold block mb-1">Jalan kaki dengan pendamping</span>
                                <span class="text-gray-500 text-sm block">5 menit, 2x/hari.</span>
                            </label>
                        </li>
                    </ul>
                </div>


                <!-- Card 5: Larangan Keras -->
                <div class="bg-red-50/50 rounded-2xl p-8 border border-red-100">
                    <h3 class="text-xl font-bold text-red-700 mb-6 flex items-center gap-2">
                        <span class="text-2xl">🚫</span> Larangan Hari Ini
                    </h3>
                    <ul class="space-y-4">
                        <li class="flex items-center gap-3 text-red-800 font-medium">
                            <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0 font-bold">X</span>
                            Dilarang angkat beban &gt; 2-3 kg
                        </li>
                        <li class="flex items-center gap-3 text-red-800 font-medium">
                            <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0 font-bold">X</span>
                            Dilarang mengemudi
                        </li>
                        <li class="flex items-center gap-3 text-red-800 font-medium">
                            <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0 font-bold">X</span>
                            Dilarang mendorong/menarik benda berat
                        </li>
                    </ul>
                </div>

                <!-- Submit Action -->
                <div class="pt-4">
                    <button class="w-full bg-[#98b0c4] hover:bg-[#859eb3] text-white text-lg font-bold py-4 rounded-xl transition-colors shadow-sm">
                        Simpan Progres Hari Ini
                    </button>
                </div>
                
            </div>

        <?php elseif ($page === 'profile'): ?>
            <!-- PROFILE VIEW -->
            <header>
                <h2 class="text-2xl font-bold text-gray-800">Profil Saya</h2>
                <p class="text-[#728BA9] mt-1 font-medium">Informasi dan status pemulihan Anda</p>
            </header>

            <div class="mt-8 max-w-3xl">
                <!-- Profile Card -->
                <div class="bg-white rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 overflow-hidden">
                    <div class="h-32 bg-gradient-to-r from-[#98b0c4] to-[#B8C9DD] relative">
                        <div class="absolute -bottom-12 left-8">
                            <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center p-1.5 shadow-md">
                                <div class="w-full h-full bg-[#ECF2E6] text-[#728BA9] rounded-full flex items-center justify-center text-3xl font-extrabold uppercase">
                                    <?= substr(htmlspecialchars($patientName), 0, 1) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-16 pb-8 px-8">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                            <div>
                                <h3 class="text-2xl font-extrabold text-gray-800"><?= htmlspecialchars($patientName) ?></h3>
                                <p class="text-[#7F7F7F] font-medium mt-1 flex items-center gap-2">
                                    <span class="inline-flex py-1 px-3 rounded-full text-xs font-bold uppercase tracking-wider <?= $role === 'caregiver' ? 'bg-[#F8FCFF] text-[#B8C9DD]' : 'bg-[#ECF2E6] text-[#A3ACA0]' ?>">
                                        <?= ucfirst($role) ?>
                                    </span>
                                </p>
                            </div>
                            
                            <div class="flex gap-3 w-full md:w-auto">
                                <a href="onboarding.php?edit=1" class="flex-1 md:flex-none text-center px-6 py-2.5 bg-[#F8FCFF] text-[#728BA9] font-bold rounded-xl border border-[#DAE3EC] hover:bg-[#DAE3EC] transition-colors shadow-sm text-sm">
                                    Edit Data
                                </a>
                                <a href="../../auth/logout.php" class="flex-1 md:flex-none text-center px-6 py-2.5 bg-red-50 text-red-600 font-bold rounded-xl border border-red-100 hover:bg-red-100 transition-colors shadow-sm text-sm">
                                    Logout
                                </a>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Op Type -->
                                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tindakan Medis</p>
                                    <p class="text-lg font-bold text-[#5A6C7A]"><?= htmlspecialchars($opName) ?></p>
                                </div>
                                <!-- Surgery Date -->
                                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tanggal Operasi</p>
                                    <p class="text-lg font-bold text-[#5A6C7A]"><?= htmlspecialchars(date('d F Y', strtotime($surgeryDate ?? date('Y-m-d')))) ?></p>
                                    <p class="text-sm font-medium text-[#728BA9] mt-1">Hari ke-<?= $dayPostOp ?> Pemulihan</p>
                                </div>
                            </div>
                            
                            <?php if ($role === 'caregiver'): ?>
                                <div class="bg-[#F8FCFF] rounded-2xl p-5 border border-[#DAE3EC] flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full bg-[#B8C9DD] text-white flex items-center justify-center font-bold text-lg uppercase shrink-0">
                                        <?= substr(htmlspecialchars($userName), 0, 1) ?>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-[#728BA9] uppercase tracking-wider mb-0.5">Pemantau (Caregiver)</p>
                                        <p class="text-lg font-bold text-[#5A6C7A]"><?= htmlspecialchars($userName) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($page === 'monitoring'): ?>
            <!-- MONITORING VIEW -->
            <header>
                <h2 class="text-2xl font-bold text-gray-800">Cek Kondisi Harian</h2>
                <p class="text-gray-500 mt-1">Isi form di bawah untuk memantau pemulihan Anda hari ini.</p>
            </header>
            
            <form action="dashboard.php" method="POST" class="mt-8 max-w-2xl bg-white rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 p-8 space-y-6">
                <input type="hidden" name="action" value="save_monitoring">
                
                <?php if ($opType === 'cabg'): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">SpO₂ (%)</label>
                            <input type="number" name="spo2" value="<?= htmlspecialchars($todayMonitoring['spo2'] ?? '') ?>" placeholder="Contoh: 98" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#98b0c4] outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Detak Jantung (bpm)</label>
                            <input type="number" name="heart_rate" value="<?= htmlspecialchars($todayMonitoring['heart_rate'] ?? '') ?>" placeholder="Contoh: 82" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#98b0c4] outline-none transition-colors">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 font-bold mb-2">Skala Nyeri Dada (0-10)</label>
                            <input type="number" name="pain_level" min="0" max="10" value="<?= htmlspecialchars($todayMonitoring['pain_level'] ?? '') ?>" placeholder="Contoh: 3" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#98b0c4] outline-none transition-colors">
                        </div>
                    </div>
                <?php elseif ($opType === 'sc'): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Volume Perdarahan</label>
                            <select name="blood_volume" class="w-full px-4 py-3 rounded-xl border border-gray-200 transition-colors focus:border-[#98b0c4] outline-none">
                                <option value="">- Pilih -</option>
                                <option value="Sedikit" <?= ($todayMonitoring['blood_volume']??'')=='Sedikit'?'selected':'' ?>>Sedikit</option>
                                <option value="Sedang" <?= ($todayMonitoring['blood_volume']??'')=='Sedang'?'selected':'' ?>>Sedang</option>
                                <option value="Banyak" <?= ($todayMonitoring['blood_volume']??'')=='Banyak'?'selected':'' ?>>Banyak</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Warna</label>
                            <input type="text" name="blood_color" value="<?= htmlspecialchars($todayMonitoring['blood_color'] ?? '') ?>" placeholder="Merah, Coklat..." class="w-full px-4 py-3 rounded-xl border border-gray-200 transition-colors focus:border-[#98b0c4] outline-none">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 font-bold mb-2">Gumpalan Darah</label>
                            <input type="text" name="blood_clots" value="<?= htmlspecialchars($todayMonitoring['blood_clots'] ?? '') ?>" placeholder="Tidak, Ya (Ukuran coin)..." class="w-full px-4 py-3 rounded-xl border border-gray-200 transition-colors focus:border-[#98b0c4] outline-none">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 font-bold mb-2">Suhu Tubuh (°C)</label>
                            <input type="number" step="0.1" name="temp" value="<?= htmlspecialchars($todayMonitoring['temp'] ?? '') ?>" placeholder="36.5" class="w-full px-4 py-3 rounded-xl border border-gray-200 transition-colors focus:border-[#98b0c4] outline-none">
                        </div>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Nyeri Area Operasi (0-10)</label>
                            <input type="number" min="0" max="10" name="stump_pain" value="<?= htmlspecialchars($todayMonitoring['stump_pain'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 transition-colors focus:border-[#98b0c4] outline-none">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Nyeri Sendi (0-10)</label>
                            <input type="number" min="0" max="10" name="phantom_pain" value="<?= htmlspecialchars($todayMonitoring['phantom_pain'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 transition-colors focus:border-[#98b0c4] outline-none">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Warna Kulit Area Tempel</label>
                            <input type="text" name="wound_color" value="<?= htmlspecialchars($todayMonitoring['wound_color'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 transition-colors focus:border-[#98b0c4] outline-none" placeholder="Normal, Merah...">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Bengkak</label>
                            <input type="text" name="wound_swelling" value="<?= htmlspecialchars($todayMonitoring['wound_swelling'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 transition-colors focus:border-[#98b0c4] outline-none" placeholder="Tidak, Ringan...">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Cairan Luka</label>
                            <input type="text" name="wound_fluid" value="<?= htmlspecialchars($todayMonitoring['wound_fluid'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 transition-colors focus:border-[#98b0c4] outline-none" placeholder="Tidak ada, Bening...">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Bau Luka</label>
                            <input type="text" name="wound_odor" value="<?= htmlspecialchars($todayMonitoring['wound_odor'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 transition-colors focus:border-[#98b0c4] outline-none" placeholder="Tidak ada...">
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="pt-2">
                    <button type="submit" class="w-full bg-[#98b0c4] hover:bg-[#859eb3] text-white font-bold text-lg py-4 px-4 rounded-xl shadow-sm transition-colors transform hover:-translate-y-0.5">
                        Simpan Data Hari Ini
                    </button>
                    <?php if (!empty($errorMsg)): ?>
                        <p class="text-red-500 mt-3 font-medium text-sm text-center"><?= htmlspecialchars($errorMsg) ?></p>
                    <?php endif; ?>
                </div>
            </form>

        <?php elseif ($page === 'content'): ?>
            <!-- CONTENT LIBRARY VIEW -->
            <header class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Pustaka Pemulihan</h2>
                <p class="text-[#728BA9] mt-1 font-medium">Materi edukasi dan panduan video khusus untuk pasien <?= htmlspecialchars($opName) ?>.</p>
            </header>

            <?php if ($opType === 'cabg'): ?>
                <!-- CABG Content -->
                <div class="space-y-10 focus:outline-none">
                    <section>
                        <h3 class="text-xl font-bold text-[#5A6C7A] mb-5 flex items-center gap-2"><span class="text-2xl">📺</span> Video Fisioterapi</h3>
                        <div class="flex overflow-x-auto items-stretch pb-6 gap-6 snap-x snap-mandatory scrollbar-hide" style="-webkit-overflow-scrolling: touch; scrollbar-width: none;">
                            <div class="w-[85vw] sm:w-[400px] snap-center shrink-0 bg-white p-4 rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 flex flex-col h-full">
                                <iframe class="w-full aspect-video rounded-xl mb-4 shrink-0" src="https://www.youtube.com/embed/sAx8_UXak1Q" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                <h4 class="font-bold text-gray-800 mb-1">Olahraga Pasca Operasi Jantung</h4>
                                <p class="text-sm text-gray-500 flex-grow">Panduan rehabilitasi tahap awal bagi pasien pasca operasi oleh dr. Kevin Triangto.</p>
                            </div>
                            <div class="w-[85vw] sm:w-[400px] snap-center shrink-0 bg-white p-4 rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 flex flex-col h-full">
                                <iframe class="w-full aspect-video rounded-xl mb-4 shrink-0" src="https://www.youtube.com/embed/hz4bgO-Smk0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                <h4 class="font-bold text-gray-800 mb-1">Apa Yang Perlu Dilakukan Setelah Operasi?</h4>
                                <p class="text-sm text-gray-500 flex-grow">Bincang medis mengenai langkah esensial pemulihan dan perawatan pasca bedah jantung.</p>
                            </div>
                            <div class="w-[85vw] sm:w-[400px] snap-center shrink-0 bg-white p-4 rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 flex flex-col h-full">
                                <iframe class="w-full aspect-video rounded-xl mb-4 shrink-0" src="https://www.youtube.com/embed/ZibrJpra3FA" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                <h4 class="font-bold text-gray-800 mb-1">Rehabilitasi Fase I Pasca CABG</h4>
                                <p class="text-sm text-gray-500 flex-grow">Latihan dan gerakan di fase paling awal pasca tindakan untuk menjaga sirkulasi darah.</p>
                            </div>
                        </div>
                    </section>

                    <section>
                        <h3 class="text-xl font-bold text-[#5A6C7A] mb-5 flex items-center gap-2"><span class="text-2xl">📖</span> Artikel Rekomendasi</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <a href="#" class="flex gap-4 p-4 bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 hover:border-[#98b0c4] transition-colors group">
                                <div class="w-20 h-20 rounded-xl bg-[#ECF2E6] shrink-0 flex items-center justify-center text-3xl">🥗</div>
                                <div>
                                    <h4 class="font-bold text-gray-800 group-hover:text-[#5A6C7A] transition-colors mb-1 line-clamp-2">Aturan Pola Makan Jantung Sehat Pasca CABG</h4>
                                    <p class="text-xs text-gray-500 font-medium tracking-wide">(Est. baca 4 menit)</p>
                                </div>
                            </a>
                            <a href="#" class="flex gap-4 p-4 bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 hover:border-[#98b0c4] transition-colors group">
                                <div class="w-20 h-20 rounded-xl bg-red-50 shrink-0 flex items-center justify-center text-3xl">🫀</div>
                                <div>
                                    <h4 class="font-bold text-gray-800 group-hover:text-[#5A6C7A] transition-colors mb-1 line-clamp-2">Mengenali Tanda Bahaya pada Luka Insisi Dada</h4>
                                    <p class="text-xs text-gray-500 font-medium tracking-wide">(Est. baca 3 menit)</p>
                                </div>
                            </a>
                        </div>
                    </section>
                </div>

            <?php elseif ($opType === 'sc'): ?>
                <!-- SC Content -->
                <div class="space-y-10 focus:outline-none">
                    <section>
                        <h3 class="text-xl font-bold text-[#5A6C7A] mb-5 flex items-center gap-2"><span class="text-2xl">📺</span> Video Panduan</h3>
                        <div class="flex overflow-x-auto items-stretch pb-6 gap-6 snap-x snap-mandatory scrollbar-hide" style="-webkit-overflow-scrolling: touch; scrollbar-width: none;">
                            <div class="w-[85vw] sm:w-[400px] snap-center shrink-0 bg-white p-4 rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 flex flex-col h-full">
                                <iframe class="w-full aspect-video rounded-xl mb-4 shrink-0" src="https://www.youtube.com/embed/KG_SsDOfwpI" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                <h4 class="font-bold text-gray-800 mb-1">Pantangan Pasca Operasi Caesar</h4>
                                <p class="text-sm text-gray-500 flex-grow">Hal-hal yang wajib dihindari ibu setelah persalinan SC agar luka aman dari komplikasi.</p>
                            </div>
                            <div class="w-[85vw] sm:w-[400px] snap-center shrink-0 bg-white p-4 rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 flex flex-col h-full">
                                <iframe class="w-full aspect-video rounded-xl mb-4 shrink-0" src="https://www.youtube.com/embed/P7hrkSlr3vo" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                <h4 class="font-bold text-gray-800 mb-1">4 Tips Agar Cepat Pulih Pasca SC</h4>
                                <p class="text-sm text-gray-500 flex-grow">Panduan sederhana namun esensial untuk mempercepat penyembuhan luka jahitan.</p>
                            </div>
                            <div class="w-[85vw] sm:w-[400px] snap-center shrink-0 bg-white p-4 rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 flex flex-col h-full">
                                <iframe class="w-full aspect-video rounded-xl mb-4 shrink-0" src="https://www.youtube.com/embed/c3NRSqZooyk" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                <h4 class="font-bold text-gray-800 mb-1">Tips Cepat Pulih Pasca Lahiran Sesar</h4>
                                <p class="text-sm text-gray-500 flex-grow">Beragam tips percepatan pemulihan praktis dan observasi ibu dari dr. Keven.</p>
                            </div>
                        </div>
                    </section>

                    <section>
                        <h3 class="text-xl font-bold text-[#5A6C7A] mb-5 flex items-center gap-2"><span class="text-2xl">📖</span> Artikel Pilihan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <a href="#" class="flex gap-4 p-4 bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 hover:border-purple-300 transition-colors group">
                                <div class="w-20 h-20 rounded-xl bg-purple-50 shrink-0 flex items-center justify-center text-3xl">🩹</div>
                                <div>
                                    <h4 class="font-bold text-gray-800 group-hover:text-purple-700 transition-colors mb-1 line-clamp-2">Perawatan Mandiri Luka Operasi Caesar di Rumah</h4>
                                    <p class="text-xs text-gray-500 font-medium tracking-wide">(Est. baca 5 menit)</p>
                                </div>
                            </a>
                            <a href="#" class="flex gap-4 p-4 bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 hover:border-purple-300 transition-colors group">
                                <div class="w-20 h-20 rounded-xl bg-pink-50 shrink-0 flex items-center justify-center text-3xl">🤱</div>
                                <div>
                                    <h4 class="font-bold text-gray-800 group-hover:text-purple-700 transition-colors mb-1 line-clamp-2">Panduan Nutrisi ASI Deras Pasca Operasi</h4>
                                    <p class="text-xs text-gray-500 font-medium tracking-wide">(Est. baca 4 menit)</p>
                                </div>
                            </a>
                        </div>
                    </section>
                </div>

            <?php else: // Ortopedi ?>
                <!-- Ortho Content -->
                <div class="space-y-10 focus:outline-none">
                    <section>
                        <h3 class="text-xl font-bold text-[#5A6C7A] mb-5 flex items-center gap-2"><span class="text-2xl">📺</span> Video Fisioterapi Ortopedi</h3>
                        <div class="flex overflow-x-auto items-stretch pb-6 gap-6 snap-x snap-mandatory scrollbar-hide" style="-webkit-overflow-scrolling: touch; scrollbar-width: none;">
                            <div class="w-[85vw] sm:w-[400px] snap-center shrink-0 bg-white p-4 rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 flex flex-col h-full">
                                <iframe class="w-full aspect-video rounded-xl mb-4 shrink-0" src="https://www.youtube.com/embed/wch7bNy0EWE" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                <h4 class="font-bold text-gray-800 mb-1">Hal Yang Harus Dihindari Pasca Operasi</h4>
                                <p class="text-sm text-gray-500 flex-grow">Panduan komprehensif apa saja yang dilarang keras setelah prosedur ortopedi TKR.</p>
                            </div>
                            <div class="w-[85vw] sm:w-[400px] snap-center shrink-0 bg-white p-4 rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 flex flex-col h-full">
                                <iframe class="w-full aspect-video rounded-xl mb-4 shrink-0" src="https://www.youtube.com/embed/hWK3xL9WfQk" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                <h4 class="font-bold text-gray-800 mb-1">Edukasi Cara Penggunaan Walker</h4>
                                <p class="text-sm text-gray-500 flex-grow">Tutor langkah demi langkah pemakaian alat bantu jalan (walker) dari spesialis RSPPN.</p>
                            </div>
                            <div class="w-[85vw] sm:w-[400px] snap-center shrink-0 bg-white p-4 rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 flex flex-col h-full">
                                <iframe class="w-full aspect-video rounded-xl mb-4 shrink-0" src="https://www.youtube.com/embed/ZwjCz8gj82A" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                <h4 class="font-bold text-gray-800 mb-1">Cara Mandi dengan Perban/Gips</h4>
                                <p class="text-sm text-gray-500 flex-grow">Strategi aman membersihkan tubuh dan mandi tanpa membasahi Cast atau perban pemulihan Anda.</p>
                            </div>
                        </div>
                    </section>

                    <section>
                        <h3 class="text-xl font-bold text-[#5A6C7A] mb-5 flex items-center gap-2"><span class="text-2xl">📖</span> Edukasi Perawatan & Perban</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <a href="#" class="flex gap-4 p-4 bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 hover:border-orange-300 transition-colors group">
                                <div class="w-20 h-20 rounded-xl bg-orange-50 shrink-0 flex items-center justify-center text-3xl">🦿</div>
                                <div>
                                    <h4 class="font-bold text-gray-800 group-hover:text-orange-600 transition-colors mb-1 line-clamp-2">Tips Menjaga Kebersihan Kulit di Bawah Gips/Perban</h4>
                                    <p class="text-xs text-gray-500 font-medium tracking-wide">(Est. baca 6 menit)</p>
                                </div>
                            </a>
                            <a href="#" class="flex gap-4 p-4 bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 hover:border-orange-300 transition-colors group">
                                <div class="w-20 h-20 rounded-xl bg-teal-50 shrink-0 flex items-center justify-center text-3xl">🦴</div>
                                <div>
                                    <h4 class="font-bold text-gray-800 group-hover:text-orange-600 transition-colors mb-1 line-clamp-2">Membedakan Nyeri Normal dan Indikasi Komplikasi</h4>
                                    <p class="text-xs text-gray-500 font-medium tracking-wide">(Est. baca 4 menit)</p>
                                </div>
                            </a>
                        </div>
                    </section>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <header>
                <h2 class="text-2xl font-bold text-gray-800">404 Not Found</h2>
                <p class="text-gray-500 mt-1">Halaman tidak ditemukan.</p>
            </header>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const checkboxes = document.querySelectorAll('.task-checkbox');
        const progressBar = document.querySelector('.task-progress-bar');
        const progressText = document.querySelector('.task-progress-text');
        
        if (checkboxes.length > 0 && progressBar && progressText) {
            function updateProgress() {
                const total = checkboxes.length;
                const checked = document.querySelectorAll('.task-checkbox:checked').length;
                const percentage = Math.round((checked / total) * 100);
                
                progressBar.style.width = percentage + '%';
                progressText.textContent = percentage + '%';
            }
            
            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateProgress);
            });
            
            updateProgress();
        }
    });
</script>
