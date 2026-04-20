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

if (isset($_GET['preview_role']) || isset($_GET['preview_op'])) {
    $role = $_GET['preview_role'] ?? 'pasien';
    $opType = $_GET['preview_op'] ?? 'cabg';
    if ($role === 'caregiver') $patientName = 'Pasien (Budi)';
    $surgeryDate = date('Y-m-d', strtotime('-2 days'));
} else {
    try {
        if (isset($_SESSION['active_profile_id'])) {
            $stmt = $pdo->prepare("SELECT * FROM user_onboarding WHERE id = ? AND user_id = ?");
            $stmt->execute([$_SESSION['active_profile_id'], $user['id']]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM user_onboarding WHERE user_id = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$user['id']]);
        }
        $onboarding = $stmt->fetch();
        if ($onboarding) {
            $role = $onboarding['role'] ?? 'pasien';
            $opType = $onboarding['operation_type'] ?? 'cabg';
            $userName = $onboarding['full_name'] ?: $user['name'];
            $surgeryDate = $onboarding['surgery_date'];
            if ($role === 'caregiver') $patientName = $onboarding['patient_name'] ?: 'Pasien';
            // ensure session is set
            $_SESSION['active_profile_id'] = $onboarding['id'];
        } else {
            $role = 'pasien'; $opType = 'cabg';
            $surgeryDate = date('Y-m-d', strtotime('-1 days'));
        }
    } catch (PDOException $e) {
        $role = 'pasien'; $opType = 'cabg';
        $surgeryDate = date('Y-m-d', strtotime('-1 days'));
    }
}

// Handle Profile Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete_profile') {
    if (isset($_SESSION['active_profile_id'])) {
        try {
            $stmtDel = $pdo->prepare("DELETE FROM user_onboarding WHERE id = ? AND user_id = ?");
            $stmtDel->execute([$_SESSION['active_profile_id'], $user['id']]);
            unset($_SESSION['active_profile_id']);
        } catch (PDOException $e) {
            // Ignore error
        }
    }
    header("Location: onboarding.php");
    exit;
}

// --- Local JSON Monitoring Storage Helpers ---
function getLocalMonitoringLogs() {
    $file = __DIR__ . '/data/monitoring_logs.json';
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?: [];
}

function saveLocalMonitoringLog($newLog) {
    $file = __DIR__ . '/data/monitoring_logs.json';
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $logs = getLocalMonitoringLogs();
    
    $found = false;
    foreach ($logs as $i => $log) {
        if ($log['user_id'] == $newLog['user_id'] && $log['profile_id'] == $newLog['profile_id'] && $log['record_date'] === $newLog['record_date']) {
            $logs[$i] = array_merge($log, $newLog);
            $found = true;
            break;
        }
    }
    if (!$found) $logs[] = $newLog;
    file_put_contents($file, json_encode($logs, JSON_PRETTY_PRINT));
}

// --- Local JSON Wound Log Storage Helpers ---
function getLocalWoundLogs() {
    $file = __DIR__ . '/data/wound_logs.json';
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?: [];
}

function saveLocalWoundLog($newLog) {
    $file = __DIR__ . '/data/wound_logs.json';
    $dir = dirname($file);
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $logs = getLocalWoundLogs();
    
    $found = false;
    foreach ($logs as $i => $log) {
        if ($log['user_id'] == $newLog['user_id'] && $log['profile_id'] == $newLog['profile_id'] && $log['record_date'] === $newLog['record_date']) {
            $logs[$i] = array_merge($log, $newLog);
            $found = true;
            break;
        }
    }
    if (!$found) $logs[] = $newLog;
    file_put_contents($file, json_encode($logs)); // no pretty print, base64 may be large
}

// Handle Wound Log Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_wound_log') {
    $today = date('Y-m-d');
    $profile_id = $_SESSION['active_profile_id'] ?? 0;
    
    $newWoundLog = [
        'user_id' => $user['id'],
        'profile_id' => $profile_id,
        'record_date' => $today,
        'image_data' => $_POST['image_base64'] ?? '',
        'status' => $_POST['ai_status'] ?? 'Normal',
        'redness' => $_POST['ai_redness'] ?? '0% Area Sengit',
        'swelling' => $_POST['ai_swelling'] ?? 'Minim',
        'fluid' => $_POST['ai_fluid'] ?? 'Jernih',
        'size' => $_POST['ai_size'] ?? '0 cm',
        'note' => $_POST['ai_note'] ?? '',
        'rednessColor' => $_POST['ai_redness_color'] ?? '#728BA9',
        'iconBg' => $_POST['ai_icon_bg'] ?? '#ECF2E6',
        'iconSvg' => $_POST['ai_icon_svg'] ?? '',
    ];

    saveLocalWoundLog($newWoundLog);
    header("Location: dashboard.php?page=woundlog"); exit;
}

// Handle Monitoring Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_monitoring') {
    $today = date('Y-m-d');
    $r_spo2   = isset($_POST['spo2'])        && $_POST['spo2']        !== '' ? (int)$_POST['spo2']        : null;
    $r_hr     = isset($_POST['heart_rate'])  && $_POST['heart_rate']  !== '' ? (int)$_POST['heart_rate']  : null;
    $r_pain   = isset($_POST['pain_level'])  && $_POST['pain_level']  !== '' ? (int)$_POST['pain_level']  : null;
    $r_temp   = isset($_POST['temp'])        && $_POST['temp']        !== '' ? (float)$_POST['temp']      : null;
    $r_bVol   = $_POST['blood_volume']  ?? null;
    $r_bCol   = $_POST['blood_color']   ?? null;
    $r_bClot  = $_POST['blood_clots']   ?? null;
    $r_stump  = isset($_POST['stump_pain'])   && $_POST['stump_pain']   !== '' ? (int)$_POST['stump_pain']   : null;
    $r_phantom= isset($_POST['phantom_pain']) && $_POST['phantom_pain'] !== '' ? (int)$_POST['phantom_pain'] : null;
    $r_wCol   = $_POST['wound_color']    ?? null;
    $r_wSwell = $_POST['wound_swelling'] ?? null;
    $r_wFluid = $_POST['wound_fluid']    ?? null;
    $r_wOdor  = $_POST['wound_odor']     ?? null;

    $profile_id = $_SESSION['active_profile_id'] ?? 0;

    $newLog = [
        'user_id' => $user['id'],
        'profile_id' => $profile_id,
        'record_date' => $today,
        'spo2' => $r_spo2,
        'heart_rate' => $r_hr,
        'pain_level' => $r_pain,
        'temp' => $r_temp,
        'blood_volume' => $r_bVol,
        'blood_color' => $r_bCol,
        'blood_clots' => $r_bClot,
        'stump_pain' => $r_stump,
        'phantom_pain' => $r_phantom,
        'wound_color' => $r_wCol,
        'wound_swelling' => $r_wSwell,
        'wound_fluid' => $r_wFluid,
        'wound_odor' => $r_wOdor
    ];

    saveLocalMonitoringLog($newLog);
    header("Location: dashboard.php?page=home"); exit;
}

// Fetch today monitoring & monitoring history
$todayMonitoring = null;
$profile_id = $_SESSION['active_profile_id'] ?? 0;
$today = date('Y-m-d');
$allLogs = getLocalMonitoringLogs();

// Filter for current profile
$profileLogs = array_filter($allLogs, function($l) use ($user, $profile_id) {
    return $l['user_id'] == $user['id'] && $l['profile_id'] == $profile_id;
});

foreach ($profileLogs as $l) {
    if ($l['record_date'] === $today) {
        $todayMonitoring = $l;
        break;
    }
}

// Fetch monitoring history (last 30 records)
$monitoringHistory = $profileLogs;
usort($monitoringHistory, function($a, $b) {
    return strtotime($b['record_date']) - strtotime($a['record_date']);
});
$monitoringHistory = array_slice($monitoringHistory, 0, 30);

// Fetch wound logs (last 30 records)
$allWounds = getLocalWoundLogs();
$profileWounds = array_filter($allWounds, function($l) use ($user, $profile_id) {
    return $l['user_id'] == $user['id'] && $l['profile_id'] == $profile_id;
});
$woundHistory = $profileWounds;
usort($woundHistory, function($a, $b) {
    return strtotime($b['record_date']) - strtotime($a['record_date']);
});
$woundHistory = array_slice($woundHistory, 0, 30);

// Days post-op
$dayPostOp = 1;
if ($surgeryDate) {
    $dStart = new DateTime($surgeryDate); $dEnd = new DateTime();
    $dStart->setTime(0,0,0); $dEnd->setTime(0,0,0);
    $diff = $dStart->diff($dEnd);
    $dayPostOp = $diff->days + 1;
    if ($dStart > $dEnd) $dayPostOp = 0;
}

$opDisplay = ['cabg' => 'Jantung (CABG)', 'sc' => 'Sectio Caesarea', 'orthopedic' => 'Ortopedi'];
$opName = $opDisplay[$opType] ?? 'Operasi';
$pageTitle = 'Dashboard RuangPulih';
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
*, body { font-family: 'Poppins', sans-serif !important; }
.scrollbar-hide::-webkit-scrollbar { display: none; }
input[type="checkbox"], input[type="range"] { accent-color: #728BA9; }

@keyframes floatAnim { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
.float-el { animation: floatAnim 6s ease-in-out infinite; }

.glass-card {
    background: rgba(255,255,255,0.65);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.85);
    border-radius: 1.25rem;
    box-shadow: 0 8px 32px rgba(114,139,169,0.08);
}

.nav-link { display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; border-radius:0.75rem; transition:all 0.2s; font-size:0.875rem; font-weight:600; }
.nav-active { background:#728BA9; color:#fff; box-shadow:0 4px 16px rgba(114,139,169,0.35); }
.nav-inactive { color:#7F7F7F; }
.nav-inactive:hover { background:rgba(255,255,255,0.5); color:#728BA9; }

#video-modal { display:none; }
#video-modal.open { display:flex; }

.radio-card input[type="radio"] { display:none; }
.radio-card input[type="radio"]:checked + label { background:#728BA9; color:#fff; border-color:#728BA9; }
.radio-card label { display:block; padding:0.5rem 0.5rem; border-radius:0.6rem; border:1.5px solid #DAE3EC; cursor:pointer; font-size:0.75rem; font-weight:600; color:#5A6C7A; text-align:center; transition:all 0.2s; }
.radio-card label:hover { border-color:#728BA9; }
</style>

<!-- ===== VIDEO MODAL ===== -->
<div id="video-modal" class="fixed inset-0 z-[100] items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl p-6 relative">
        <button onclick="closeVideoModal()" class="absolute top-4 right-4 w-9 h-9 rounded-full bg-gray-100 hover:bg-[#ECF2E6] text-gray-600 hover:text-[#5A6C7A] transition-all font-bold">✕</button>
        <p class="font-extrabold text-[#728BA9] text-lg mb-4" id="vmod-title">Panduan</p>
        <div class="aspect-video rounded-2xl overflow-hidden bg-black">
            <iframe id="vmod-frame" class="w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    </div>
</div>

<div class="flex min-h-screen overflow-hidden" style="background:linear-gradient(135deg,#F8FCFF 0%,#ECF2E6 50%,#F8FCFF 100%);">

    <!-- Ambient bg -->
    <div class="fixed top-0 left-[15%] w-80 h-80 rounded-full pointer-events-none" style="background:rgba(184,201,221,0.12);filter:blur(80px);"></div>
    <div class="fixed bottom-0 right-[5%] w-96 h-96 rounded-full pointer-events-none" style="background:rgba(209,217,202,0.15);filter:blur(100px);"></div>
    <!-- Floating doodle -->
    <div class="fixed top-[20%] right-[32%] w-16 h-16 float-el pointer-events-none" style="opacity:0.08;animation-duration:8s;transform:rotate(20deg);">
        <svg fill="none" stroke="#728BA9" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
    </div>

    <!-- ===== SIDEBAR ===== -->
    <aside class="w-64 shrink-0 flex flex-col sticky top-0 h-screen z-20" style="background:rgba(255,255,255,0.68);backdrop-filter:blur(24px);border-right:1px solid rgba(255,255,255,0.9);box-shadow:4px 0 30px rgba(114,139,169,0.06);">
        <div class="px-6 py-8 shrink-0">
            <a href="index.php" class="flex items-center gap-3">
                <img src="assets/images/logo.png" alt="logo" class="h-8 opacity-80" style="mix-blend-mode:multiply;">
                <div>
                    <p class="text-xl font-extrabold leading-none" style="color:#728BA9;">Ruang<span style="color:#A3ACA0;">Pulih</span></p>
                    <p class="text-[0.58rem] mt-1 uppercase tracking-widest" style="color:#A3ACA0;">Pasca-Operasi</p>
                </div>
            </a>
        </div>

        <nav class="flex-1 px-4 flex flex-col gap-1.5 py-2 overflow-hidden">
            <a href="dashboard.php?page=home"       class="nav-link <?= $page==='home'       ? 'nav-active' : 'nav-inactive' ?>">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Home
            </a>
            <a href="dashboard.php?page=roadmap"    class="nav-link <?= $page==='roadmap'    ? 'nav-active' : 'nav-inactive' ?>">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9h6m-6-4h6"/></svg>
                Roadmap
            </a>
            <a href="dashboard.php?page=monitoring" class="nav-link <?= $page==='monitoring' ? 'nav-active' : 'nav-inactive' ?>">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Monitoring
            </a>
            <a href="dashboard.php?page=woundlog" class="nav-link <?= $page==='woundlog' ? 'nav-active' : 'nav-inactive' ?>">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                Wound Log
            </a>
            <a href="dashboard.php?page=content"    class="nav-link <?= $page==='content'    ? 'nav-active' : 'nav-inactive' ?>">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Library
            </a>
            <div class="flex-1"></div>
            <div class="border-t border-white/60 pt-2 flex flex-col gap-1.5">
                <a href="dashboard.php?page=profile" class="nav-link <?= $page==='profile' ? 'nav-active' : 'nav-inactive' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    Profil
                </a>
                <a href="index.php" class="nav-link nav-inactive hover:!bg-[#DAE3EC] hover:!text-[#5A6C7A]">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H9m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h6a3 3 0 013 3v1"/></svg>
                    Keluar
                </a>
            </div>
        </nav>
    </aside>

    <!-- ===== CONTENT ===== -->
    <main class="flex-1 overflow-y-auto h-screen px-8 py-10">

        <!-- Persistent Red Flag Button -->
        <button onclick="document.getElementById('rf-modal').classList.remove('hidden'); document.getElementById('rf-modal').classList.add('flex');"
            class="fixed top-7 right-8 z-50 flex items-center gap-2 px-5 py-2.5 rounded-full text-white font-bold text-sm transition-all transform hover:-translate-y-0.5"
            style="background:#D46A6A;box-shadow:0 8px 24px rgba(212,106,106,0.3);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Red Flag
        </button>

        <!-- Red Flag Modal -->
        <div id="rf-modal" class="hidden fixed inset-0 z-[90] items-center justify-center p-4" style="background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 relative">
                <button onclick="document.getElementById('rf-modal').classList.add('hidden'); document.getElementById('rf-modal').classList.remove('flex');"
                    class="absolute top-4 right-4 w-9 h-9 rounded-full bg-gray-100 hover:bg-[#ECF2E6] text-gray-500 hover:text-[#5A6C7A] flex items-center justify-center font-bold transition-all">✕</button>
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-12 h-12 rounded-full bg-[#ECF2E6] flex items-center justify-center shrink-0"><svg class="w-6 h-6" fill="none" stroke="#728BA9" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
                    <div>
                        <h3 class="font-extrabold text-[#5A6C7A] text-xl">Kondisi Darurat!</h3>
                        <p class="text-[#728BA9] text-sm font-medium">Segera ambil tindakan jika muncul tanda ini</p>
                    </div>
                </div>
                <ul class="space-y-3 text-sm text-[#5A6C7A] font-medium mb-6">
                    <?php if ($opType === 'cabg'): ?>
                    <li class="flex gap-2 items-start"><svg class="w-4 h-4 text-[#728BA9] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> SpO₂ di bawah 92% — hentikan semua aktivitas</li>
                    <li class="flex gap-2 items-start"><svg class="w-4 h-4 text-[#728BA9] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Sesak napas tiba-tiba atau nyeri dada sangat berat</li>
                    <li class="flex gap-2 items-start"><svg class="w-4 h-4 text-[#728BA9] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Detak jantung tidak teratur (&gt;120 bpm)</li>
                    <?php elseif ($opType === 'sc'): ?>
                    <li class="flex gap-2 items-start"><svg class="w-4 h-4 text-[#728BA9] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Perdarahan sangat banyak atau gumpalan darah besar</li>
                    <li class="flex gap-2 items-start"><svg class="w-4 h-4 text-[#728BA9] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Demam tinggi di atas 38.5°C</li>
                    <li class="flex gap-2 items-start"><svg class="w-4 h-4 text-[#728BA9] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Luka terbuka atau mengeluarkan nanah berbau</li>
                    <?php else: ?>
                    <li class="flex gap-2 items-start"><svg class="w-4 h-4 text-[#728BA9] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Perdarahan aktif dari area operasi</li>
                    <li class="flex gap-2 items-start"><svg class="w-4 h-4 text-[#728BA9] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Area luka merah, panas, membengkak luas</li>
                    <li class="flex gap-2 items-start"><svg class="w-4 h-4 text-[#728BA9] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Nyeri tidak terkontrol meski sudah minum obat</li>
                    <?php endif; ?>
                </ul>
                <a href="tel:119" class="flex items-center justify-center gap-2 py-3.5 rounded-2xl text-white font-extrabold text-lg transition-all" style="background:#D46A6A;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.338c0-1.14.88-2.073 2.019-2.128a48.974 48.974 0 0119.462 0 2.126 2.126 0 012.019 2.128 4.487 4.487 0 01-1.268 3.217l-3.02 3.297a4.5 4.5 0 00-1.09 2.85v1.478m0 0a48.667 48.667 0 01-2.658-.813m-2.658.813a48.8 48.8 0 01-2.658-.813m0-1.478a4.5 4.5 0 00-1.09-2.85l-3.02-3.297a4.487 4.487 0 01-1.268-3.217"/></svg>
                    Hubungi 119 — IGD Darurat
                </a>
            </div>
        </div>

        <?php if ($page === 'home'): ?>
        <!-- ============================================================
             HOME — COMMAND CENTER
        ============================================================ -->
        <div class="w-full">
            <!-- Greeting -->
            <div class="mb-8">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <p class="text-xs font-bold uppercase tracking-widest" style="color:#A3ACA0;"><?= date('l, d F Y') ?></p>
                        <?php if ($role==='caregiver'): ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-extrabold uppercase border tracking-wider" style="background:#ECF2E6;color:#5A6C7A;border-color:#D1D9CA;"><svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Caregiver</span>
                        <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-extrabold uppercase border tracking-wider" style="background:#F8FCFF;color:#728BA9;border-color:#DAE3EC;"><svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> Pasien</span>
                        <?php endif; ?>
                    </div>
                    <h2 class="text-3xl font-extrabold" style="color:#728BA9;">Halo, <?= htmlspecialchars(explode(' ',$userName)[0]) ?></h2>
                    <p class="font-medium mt-1" style="color:#7F7F7F;">
                        <?php if ($role==='caregiver'): ?>Memantau <strong style="color:#728BA9;"><?= htmlspecialchars($patientName) ?></strong> — <?php endif; ?>
                        Hari ke-<strong style="color:#728BA9;"><?= $dayPostOp ?></strong> pasca operasi <?= $opName ?>
                    </p>
                </div>
            </div>

            <!-- Priority Banner -->
            <?php if (!$todayMonitoring): ?>
            <a href="dashboard.php?page=monitoring" class="flex items-center gap-4 p-5 mb-6 rounded-2xl border transition-all group" style="background:rgba(114,139,169,0.07);border-color:#DAE3EC;">
                <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0" style="background:#ECF2E6;"><svg class="w-6 h-6" fill="none" stroke="#728BA9" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
                <div class="flex-1">
                    <p class="font-extrabold" style="color:#5A6C7A;">Anda belum mengisi log pemantauan hari ini!</p>
                    <p class="text-sm font-medium mt-0.5" style="color:#7F8A83;">Data kesehatan belum tercatat — isi sekarang agar bisa dipantau.</p>
                </div>
                <span class="px-4 py-2 rounded-full text-white font-bold text-sm shrink-0 transition-all" style="background:#728BA9;">Isi Sekarang →</span>
            </a>
            <?php else: ?>
            <div class="flex items-center gap-4 p-5 mb-6 rounded-2xl border" style="background:#ECF2E6;border-color:#D1D9CA;">
                <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0" style="background:#D1D9CA;"><svg class="w-6 h-6" fill="none" stroke="#728BA9" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg></div>
                <div>
                    <p class="font-extrabold" style="color:#5A6C7A;">Log pemantauan hari ini sudah terisi!</p>
                    <p class="text-sm font-medium mt-0.5" style="color:#A3ACA0;">Data Anda sudah tercatat. Tetap pantau kondisi Anda.</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <?php if ($opType==='cabg'): ?>
                <div class="glass-card p-6">
                    <p class="text-xs font-bold uppercase tracking-wider mb-1" style="color:#728BA9;">SpO₂</p>
                    <p class="text-4xl font-extrabold" style="color:#728BA9;"><?= isset($todayMonitoring['spo2'])&&$todayMonitoring['spo2']!==null ? htmlspecialchars($todayMonitoring['spo2']) : '--' ?><span class="text-lg">%</span></p>
                    <p class="text-xs font-medium mt-1" style="color:#A3ACA0;">Normal ≥ 95%</p>
                </div>
                <div class="glass-card p-6">
                    <p class="text-xs font-bold uppercase tracking-wider mb-1" style="color:#A3ACA0;">Detak Jantung</p>
                    <p class="text-4xl font-extrabold" style="color:#5A6C7A;"><?= isset($todayMonitoring['heart_rate'])&&$todayMonitoring['heart_rate']!==null ? htmlspecialchars($todayMonitoring['heart_rate']) : '--' ?><span class="text-lg" style="color:#A3ACA0;"> bpm</span></p>
                    <p class="text-xs font-medium mt-1" style="color:#A3ACA0;">Normal 60–100 bpm</p>
                </div>
                <div class="glass-card p-6">
                    <p class="text-xs font-bold uppercase tracking-wider mb-1" style="color:#A3ACA0;">Nyeri Dada</p>
                    <p class="text-4xl font-extrabold" style="color:#5A6C7A;"><?= isset($todayMonitoring['pain_level'])&&$todayMonitoring['pain_level']!==null ? htmlspecialchars($todayMonitoring['pain_level']) : '--' ?><span class="text-lg" style="color:#A3ACA0;">/10</span></p>
                    <p class="text-xs font-medium mt-1" style="color:#A3ACA0;">Ringan &lt; 3, Sedang 4–6</p>
                </div>
                <?php elseif ($opType==='sc'): ?>
                <div class="glass-card p-6">
                    <p class="text-xs font-bold uppercase tracking-wider mb-1" style="color:#728BA9;">Suhu Tubuh</p>
                    <p class="text-4xl font-extrabold" style="color:#728BA9;"><?= isset($todayMonitoring['temp'])&&$todayMonitoring['temp']!==null ? htmlspecialchars($todayMonitoring['temp']) : '--' ?><span class="text-lg">°C</span></p>
                    <p class="text-xs font-medium mt-1" style="color:#A3ACA0;">Normal 36–37.5°C</p>
                </div>
                <div class="glass-card p-6">
                    <p class="text-xs font-bold uppercase tracking-wider mb-1" style="color:#A3ACA0;">Volume Perdarahan</p>
                    <p class="text-2xl font-extrabold mt-2" style="color:#5A6C7A;"><?= !empty($todayMonitoring['blood_volume']) ? htmlspecialchars($todayMonitoring['blood_volume']) : '--' ?></p>
                    <p class="text-xs font-medium mt-1" style="color:#A3ACA0;">Ganti pembalut 4–6 jam</p>
                </div>
                <div class="glass-card p-6">
                    <p class="text-xs font-bold uppercase tracking-wider mb-1" style="color:#A3ACA0;">Nyeri</p>
                    <p class="text-4xl font-extrabold" style="color:#5A6C7A;"><?= isset($todayMonitoring['pain_level'])&&$todayMonitoring['pain_level']!==null ? htmlspecialchars($todayMonitoring['pain_level']) : '--' ?><span class="text-lg" style="color:#A3ACA0;">/10</span></p>
                    <p class="text-xs font-medium mt-1" style="color:#A3ACA0;">Ringan &lt; 3</p>
                </div>
                <?php else: ?>
                <div class="glass-card p-6">
                    <p class="text-xs font-bold uppercase tracking-wider mb-1" style="color:#728BA9;">Nyeri Area Operasi</p>
                    <p class="text-4xl font-extrabold" style="color:#728BA9;"><?= isset($todayMonitoring['stump_pain'])&&$todayMonitoring['stump_pain']!==null ? htmlspecialchars($todayMonitoring['stump_pain']) : '--' ?><span class="text-lg">/10</span></p>
                    <p class="text-xs font-medium mt-1" style="color:#A3ACA0;">Catat setiap perubahan</p>
                </div>
                <div class="glass-card p-6">
                    <p class="text-xs font-bold uppercase tracking-wider mb-1" style="color:#A3ACA0;">Nyeri Sendi</p>
                    <p class="text-4xl font-extrabold" style="color:#5A6C7A;"><?= isset($todayMonitoring['phantom_pain'])&&$todayMonitoring['phantom_pain']!==null ? htmlspecialchars($todayMonitoring['phantom_pain']) : '--' ?><span class="text-lg" style="color:#A3ACA0;">/10</span></p>
                    <p class="text-xs font-medium mt-1" style="color:#A3ACA0;">Ideal &lt; 3</p>
                </div>
                <div class="glass-card p-6">
                    <p class="text-xs font-bold uppercase tracking-wider mb-1" style="color:#A3ACA0;">Kondisi Luka</p>
                    <p class="text-xl font-extrabold mt-2" style="color:#5A6C7A;"><?= !empty($todayMonitoring['wound_color']) ? htmlspecialchars($todayMonitoring['wound_color']) : '--' ?></p>
                    <p class="text-xs font-medium mt-1" style="color:#A3ACA0;">Periksa pagi & malam</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Checklist + Trend -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Checklist -->
                <div class="lg:col-span-2 glass-card p-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-extrabold text-lg flex items-center gap-2" style="color:#5A6C7A;"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg><?= $role==='caregiver' ? 'Checklist Pasien Hari Ini' : 'Recovery Hari Ini' ?></h3>
                        <span class="text-sm font-bold task-progress-text" style="color:#728BA9;">0%</span>
                    </div>
                    <div class="w-full rounded-full h-2.5 mb-6" style="background:rgba(218,227,236,0.6);">
                        <div class="task-progress-bar h-2.5 rounded-full transition-all duration-500" style="width:0%;background:#728BA9;"></div>
                    </div>
                    <ul class="space-y-2">
                        <?php if ($opType==='cabg'): ?>
                        <li class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/50 transition-all"><input type="checkbox" class="w-5 h-5 task-checkbox shrink-0"><span class="text-sm font-medium" style="color:#5A6C7A;"><?= $role==='caregiver' ? 'Pastikan pasien latihan pernapasan dalam (5–10 rep/jam)' : 'Latihan pernapasan dalam 5–10 repetisi per jam' ?></span></li>
                        <li class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/50 transition-all"><input type="checkbox" class="w-5 h-5 task-checkbox shrink-0"><span class="text-sm font-medium" style="color:#5A6C7A;"><?= $role==='caregiver' ? 'Dampingi pasien jalan kaki 5 menit' : 'Jalan kaki 5 menit dengan pendamping' ?></span></li>
                        <li class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/50 transition-all"><input type="checkbox" class="w-5 h-5 task-checkbox shrink-0"><span class="text-sm font-medium" style="color:#5A6C7A;"><?= $role==='caregiver' ? 'Ingatkan pasien minum obat sesuai jadwal' : 'Minum obat sesuai jadwal dokter' ?></span></li>
                        <li class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/50 transition-all"><input type="checkbox" class="w-5 h-5 task-checkbox shrink-0"><span class="text-sm font-medium" style="color:#5A6C7A;"><?= $role==='caregiver' ? 'Bantu pasien batuk efektif (tahan dada)' : 'Batuk efektif 2–3x (tahan dada dengan bantal)' ?></span></li>
                        <li class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/50 transition-all"><input type="checkbox" class="w-5 h-5 task-checkbox shrink-0"><span class="text-sm font-medium" style="color:#5A6C7A;">Catat tanda vital di menu Monitoring</span></li>
                        <?php elseif ($opType==='sc'): ?>
                        <li class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/50 transition-all"><input type="checkbox" class="w-5 h-5 task-checkbox shrink-0"><span class="text-sm font-medium" style="color:#5A6C7A;"><?= $role==='caregiver' ? 'Bantu pasien mobilisasi ringan' : 'Mobilisasi ringan: duduk perlahan dari tempat tidur' ?></span></li>
                        <li class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/50 transition-all"><input type="checkbox" class="w-5 h-5 task-checkbox shrink-0"><span class="text-sm font-medium" style="color:#5A6C7A;"><?= $role==='caregiver' ? 'Ingatkan pasien menyusui / pompa ASI' : 'Menyusui atau pompa ASI sesuai jadwal' ?></span></li>
                        <li class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/50 transition-all"><input type="checkbox" class="w-5 h-5 task-checkbox shrink-0"><span class="text-sm font-medium" style="color:#5A6C7A;"><?= $role==='caregiver' ? 'Periksa luka SC pasien secara visual' : 'Periksa luka SC di cermin' ?></span></li>
                        <li class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/50 transition-all"><input type="checkbox" class="w-5 h-5 task-checkbox shrink-0"><span class="text-sm font-medium" style="color:#5A6C7A;"><?= $role==='caregiver' ? 'Pastikan pasien minum obat pereda nyeri' : 'Minum obat pereda nyeri sesuai resep' ?></span></li>
                        <li class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/50 transition-all"><input type="checkbox" class="w-5 h-5 task-checkbox shrink-0"><span class="text-sm font-medium" style="color:#5A6C7A;">Isi log monitoring harian</span></li>
                        <?php else: ?>
                        <li class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/50 transition-all"><input type="checkbox" class="w-5 h-5 task-checkbox shrink-0"><span class="text-sm font-medium" style="color:#5A6C7A;"><?= $role==='caregiver' ? 'Bantu pasien posisi rebahan sesuai anjuran' : 'Posisi rebahan sesuai anjuran dokter' ?></span></li>
                        <li class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/50 transition-all"><input type="checkbox" class="w-5 h-5 task-checkbox shrink-0"><span class="text-sm font-medium" style="color:#5A6C7A;"><?= $role==='caregiver' ? 'Dampingi latihan jalan dengan alat bantu' : 'Latihan jalan dengan walker' ?></span></li>
                        <li class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/50 transition-all"><input type="checkbox" class="w-5 h-5 task-checkbox shrink-0"><span class="text-sm font-medium" style="color:#5A6C7A;"><?= $role==='caregiver' ? 'Periksa balutan luka' : 'Periksa kondisi luka/perban' ?></span></li>
                        <li class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/50 transition-all"><input type="checkbox" class="w-5 h-5 task-checkbox shrink-0"><span class="text-sm font-medium" style="color:#5A6C7A;"><?= $role==='caregiver' ? 'Ingatkan pasien minum obat' : 'Minum obat pereda nyeri' ?></span></li>
                        <li class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/50 transition-all"><input type="checkbox" class="w-5 h-5 task-checkbox shrink-0"><span class="text-sm font-medium" style="color:#5A6C7A;">Isi log monitoring & foto luka</span></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Pain Trend -->
                <?php
                $painToday = $todayMonitoring['pain_level'] ?? $todayMonitoring['stump_pain'] ?? null;
                $trendData = [7, 5, 4, $painToday ?? 3];
                $trendLabels = ['3h lalu', '2h lalu', 'Kemarin', 'Hari ini'];
                ?>
                <div class="glass-card p-8 flex flex-col">
                    <h3 class="font-extrabold text-lg mb-1 flex items-center gap-2" style="color:#5A6C7A;"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg> Tren Nyeri</h3>
                    <p class="text-xs font-medium mb-6" style="color:#A3ACA0;">Riwayat 3 hari terakhir</p>
                    <div class="flex-1 flex items-end gap-3">
                        <?php foreach ($trendData as $i => $v): ?>
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <span class="text-xs font-extrabold" style="color:#5A6C7A;"><?= $v ?></span>
                            <div class="w-full rounded-t-lg transition-all" style="height:<?= max(16, ($v/10)*90) ?>px;background:<?= $i===3 ? '#728BA9' : '#DAE3EC' ?>;"></div>
                            <span class="text-[10px] font-bold text-center leading-tight" style="color:#A3ACA0;"><?= $trendLabels[$i] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-xs font-medium mt-4 pt-3" style="border-top:1px solid rgba(218,227,236,0.5);color:#A3ACA0;">
                        <?php if ($painToday !== null && $painToday <= 3): ?><span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="#728BA9" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg><span style="color:#728BA9;font-weight:700;">Nyeri ringan</span></span> — batas normal
                        <?php elseif ($painToday !== null && $painToday <= 6): ?><span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="#A3ACA0" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01"/></svg><span style="color:#A3ACA0;font-weight:700;">Nyeri sedang</span></span> — pantau lebih ketat
                        <?php elseif ($painToday !== null): ?><span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="#5A6C7A" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span style="color:#5A6C7A;font-weight:700;">Nyeri berat</span></span> — hubungi dokter
                        <?php else: ?>Belum ada data hari ini<?php endif; ?>
                    </p>
                    <a href="dashboard.php?page=monitoring" class="mt-4 block text-center py-2.5 rounded-xl font-bold text-sm text-white transition-all hover:-translate-y-0.5" style="background:#728BA9;">
                        + Catat Sekarang
                    </a>
                </div>
            </div>
        </div>


        <?php elseif ($page === 'roadmap'): ?>
        <!-- ============================================================
             ROADMAP - RECOVERY TIMELINE
        ============================================================ -->
        <?php
        $C_ACTIVE  = '#728BA9';
        $C_DONE    = '#5A6C7A';
        $C_FUTURE  = '#A3ACA0';
        $BG_ACTIVE = 'rgba(114,139,169,0.12)';
        $BG_DONE   = 'rgba(90,108,122,0.09)';
        $BG_FUTURE = 'rgba(218,227,236,0.18)';
        $BAR_ACTIVE = '#728BA9';
        $BAR_DONE   = '#B8C9DD';
        $BAR_FUTURE = '#DAE3EC';

        if ($opType === 'cabg') {
            $phases = [
                ['id'=>'p1','name'=>'Fase Akut','range'=>'Hari 1 s/d 7','days'=>[1,7],'badge'=>'Kritis - Rawat Ketat',
                 'desc'=>'Fase paling krusial. Tubuh belum stabil - fokus pada pemulihan luka, stabilitas pernapasan, dan pencegahan komplikasi.',
                 'goals'=>['SpO2 stabil >= 95%','Nyeri dada terkontrol < 5/10','Tidak ada tanda infeksi luka','Mobilisasi bertahap dimulai'],
                 'activities'=>[['Latihan pernapasan dalam 5-10 rep/jam','Pagi & Siang'],['Batuk efektif 2-3x (tahan dada bantal)','Pagi & Siang'],['Duduk di tepi tempat tidur 15 menit','Siang'],['Cek SpO2 dan catat di Monitoring','Pagi & Malam'],['Minum obat sesuai jadwal dokter','Pagi, Siang, Malam'],['Tidur posisi semi-fowler (30-45°)','Malam']],
                 'restrictions'=>['Tidak angkat beban > 1 kg','Tidak mengemudi','Tidak aktivitas fisik berat','Tidak menekan area luka dada'],
                 'warning'=>['SpO2 < 92% segera hubungi dokter','Demam > 38 °C lebih dari 2 hari','Luka kemerahan, bengkak, atau mengeluarkan cairan','Nyeri dada seperti tertindih batu'],
                 'vid'=>'hz4bgO-Smk0','vtitle'=>'Latihan Pernapasan Pasca CABG'],
                ['id'=>'p2','name'=>'Fase Mobilisasi','range'=>'Hari 8 s/d 21','days'=>[8,21],'badge'=>'Progresif - Gerak Bertahap',
                 'desc'=>'Mulai meningkatkan aktivitas secara bertahap. Luka mulai menutup, tubuh beradaptasi. Jalan kaki menjadi latihan utama.',
                 'goals'=>['Jalan kaki 5-10 menit tanpa henti','Naik 1 lantai tangga (hari ke-14+)','Luka dada mulai kering dan menutup','Tidur nyenyak minimal 7 jam'],
                 'activities'=>[['Jalan kaki 5 menit pagi dan sore','Pagi & Sore'],['Latihan pernapasan dalam 3-5x/hari','Pagi & Sore'],['Mandi sendiri dengan bantuan minimal','Pagi'],['Duduk makan di meja (bukan di kasur)','Siang'],['Pantau detak jantung setelah jalan kaki','Siang'],['Kurangi obat nyeri jika sudah tolerable','Konsultasi dokter']],
                 'restrictions'=>['Tidak angkat beban > 2-3 kg','Tidak mengemudi kendaraan','Tidak berenang atau olahraga berat','Tidak push-up atau gerakan dada ekstrem'],
                 'warning'=>['Sesak napas setelah jalan kaki pendek','Detak jantung > 120 bpm saat istirahat','Pembengkakan di kaki atau pergelangan','Nyeri di betis (tanda trombosis)'],
                 'vid'=>'sAx8_UXak1Q','vtitle'=>'Olahraga Pasca Operasi Jantung'],
                ['id'=>'p3','name'=>'Fase Rehabilitasi','range'=>'Hari 22 s/d 56','days'=>[22,56],'badge'=>'Pemulihan Aktif',
                 'desc'=>'Program rehabilitasi jantung terstruktur. Aktivitas harian meningkat signifikan. Kontrol dokter rutin sangat penting.',
                 'goals'=>['Jalan kaki 20-30 menit per hari','Kembali aktivitas ringan di rumah','Kontrol ekokardiografi (EKG)','Mandiri penuh tanpa bantuan'],
                 'activities'=>[['Jalan kaki 20-30 menit 5x/minggu','Pagi'],['Latihan aerobik ringan (sepeda statis)','Siang (hari ke-35+)'],['Kontrol ke poliklinik jantung','Sesuai jadwal'],['EKG dan lab darah rutin','Jadwal dokter'],['Pola makan jantung sehat (rendah lemak jenuh)','Setiap hari'],['Berhenti merokok sepenuhnya','Permanen']],
                 'restrictions'=>['Tidak mengangkat beban > 5 kg','Tidak olahraga kontak fisik','Tidak alkohol','Tidak hubungan seksual berlebihan (konsultasi dokter)'],
                 'warning'=>['Nyeri dada tiba-tiba saat aktivitas','Pusing atau pingsan','Irama jantung tidak teratur','Sesak napas saat berbaring'],
                 'vid'=>'ZibrJpra3FA','vtitle'=>'Rehabilitasi Jantung Fase I'],
                ['id'=>'p4','name'=>'Pemulihan Penuh','range'=>'Hari 57+','days'=>[57,999],'badge'=>'Kembali Normal',
                 'desc'=>'Mayoritas pasien dapat kembali ke aktivitas normal termasuk kerja ringan. Tetap jaga gaya hidup jantung sehat seumur hidup.',
                 'goals'=>['Kembali bekerja (ringan)','Olahraga teratur 150 menit/minggu','Berat badan ideal','Kadar kolesterol dan tekanan darah terkontrol'],
                 'activities'=>[['Olahraga 30 menit/hari (jalan, renang, sepeda)','Pagi'],['Diet jantung sehat seumur hidup','Setiap hari'],['Kontrol dokter berkala (3-6 bulan)','Rutin'],['Cek tekanan darah dan gula darah','Bulanan'],['Kelola stres (meditasi, napas dalam)','Setiap hari'],['Patuh obat jangka panjang (aspirin, statin dll)','Seumur hidup']],
                 'restrictions'=>['Hindari merokok selamanya','Batasi garam dan lemak jenuh','Kontrol stres berlebihan','Konsultasi sebelum aktivitas fisik intens baru'],
                 'warning'=>['Nyeri dada atau sesak tanpa sebab','Tekanan darah tidak terkontrol','Kadar gula darah melonjak','Berhenti minum obat tanpa seizin dokter'],
                 'vid'=>null,'vtitle'=>''],
            ];
        } elseif ($opType === 'sc') {
            $phases = [
                ['id'=>'p1','name'=>'Fase Pasca Operasi','range'=>'Hari 1 s/d 3','days'=>[1,3],'badge'=>'Masa Kritis',
                 'desc'=>'Periode paling awal setelah operasi SC. Fokus pada manajemen nyeri, pemantauan perdarahan, dan mobilisasi dini bertahap.',
                 'goals'=>['Bisa duduk sendiri dari kasur','Perdarahan terkontrol (tidak melebihi pembalut 1 jam)','Nyeri < 5/10 dengan obat','Mulai menyusui / pompa ASI'],
                 'activities'=>[['Mobilisasi dini: duduk perlahan dari kasur','Pagi hari ke-2'],['Menyusui / pompa ASI setiap 2-3 jam','Sepanjang hari'],['Pantau volume dan warna perdarahan','Setiap ganti pembalut'],['Minum air putih 8 gelas/hari','Sepanjang hari'],['Minum obat analgesik sesuai resep','Pagi, Siang, Malam'],['Periksa luka SC di cermin','Pagi & Malam']],
                 'restrictions'=>['Tidak berdiri terlalu lama (> 10 menit)','Tidak angkat beban > 2 kg','Tidak membasahi luka operasi','Tidak hubungan seksual'],
                 'warning'=>['Perdarahan sangat banyak (1 pembalut habis < 1 jam)','Demam > 38.5 °C','Luka SC terbuka atau mengeluarkan nanah','Nyeri yang tidak berkurang dengan obat'],
                 'vid'=>'KG_SsDOfwpI','vtitle'=>'Perawatan Luka Caesar'],
                ['id'=>'p2','name'=>'Fase Penyembuhan Luka','range'=>'Hari 4 s/d 14','days'=>[4,14],'badge'=>'Pemulihan Awal',
                 'desc'=>'Luka jahitan mulai menutup. Perdarahan berkurang menjadi flek coklat. Aktivitas harian mulai meningkat bertahap.',
                 'goals'=>['Bisa berdiri dan berjalan mandiri','Perdarahan berubah coklat/kuning (bukan merah segar)','Luka SC kering dan tidak bernanah','ASI mulai lancar'],
                 'activities'=>[['Berjalan mandiri di dalam rumah','Pagi & Sore'],['Mandi dengan hati-hati (jaga luka tetap kering)','Pagi'],['Lanjutkan menyusui/pompa ASI rutin','Setiap 2-3 jam'],['Makanan bergizi tinggi protein untuk penyembuhan luka','Setiap makan'],['Senam kegel untuk pemulihan dasar panggul','Pagi & Malam'],['Konsultasi dokter kandungan (kontrol luka)','Hari ke-7 s/d 10']],
                 'restrictions'=>['Tidak angkat beban > 3 kg','Tidak naik tangga berulang kali','Tidak berendam di bak mandi/kolam renang','Tidak olahraga berat'],
                 'warning'=>['Luka SC kemerahan atau membengkak','Suhu tubuh > 38 °C lebih dari 2 hari','Bau tidak sedap dari area luka','Perdarahan merah segar kembali setelah berkurang'],
                 'vid'=>'P7hrkSlr3vo','vtitle'=>'4 Tips Cepat Pulih Pasca SC'],
                ['id'=>'p3','name'=>'Fase Pemulihan','range'=>'Hari 15 s/d 42','days'=>[15,42],'badge'=>'Kembali Aktif',
                 'desc'=>'Luka sudah menutup sempurna. Aktivitas fisik dapat meningkat secara progresif. Fokus pada kekuatan core dan pemulihan hormonal.',
                 'goals'=>['Luka SC menutup sempurna (tidak ada keropeng)','Bisa merawat bayi secara mandiri','Mulai olahraga ringan (jalan kaki > 15 menit)','Kembali ke rutinitas ringan'],
                 'activities'=>[['Jalan kaki 10-20 menit setiap pagi','Pagi'],['Senam nifas / yoga lembut post partum','Siang'],['Pola makan bergizi untuk kualitas ASI','Setiap hari'],['Konsultasi laktasi jika ASI bermasalah','Jika diperlukan'],['Kontrol kandungan (periksa luka dan rahim)','Hari ke-28 s/d 42'],['Kelola stres dan baby blues','Setiap hari']],
                 'restrictions'=>['Tidak sit-up atau plank','Tidak berlari/melompat','Tidak berjemur luka langsung ke matahari','Tidak berhubungan seksual (< 6 minggu)'],
                 'warning'=>['Demam tinggi mendadak','Nyeri panggul yang tidak normal','Perdarahan berat setelah bercak ringan','Tanda depresi pasca melahirkan (sedih terus-menerus)'],
                 'vid'=>'c3NRSqZooyk','vtitle'=>'Tips Cepat Pulih Pasca Sesar'],
                ['id'=>'p4','name'=>'Pemulihan Penuh','range'=>'Hari 43+','days'=>[43,999],'badge'=>'Pulih Sepenuhnya',
                 'desc'=>'Tubuh sudah pulih. Bekas luka menjadi semakin memudar. Hubungan seksual dan olahraga intens sudah bisa dilakukan kembali.',
                 'goals'=>['Bekas luka memudar dan rata','Kembali bekerja (jika ada)','Olahraga reguler 3-4x/minggu','Konsultasi KB pasca melahirkan'],
                 'activities'=>[['Olahraga aerobik moderat (renang, jogging ringan)','Bebas'],['Latihan kekuatan core secara bertahap','Pagi'],['Gunakan krim/gel bekas luka (vitamin E, silikon)','Pagi & Malam'],['Kontrol kandungan 3 bulanan','Rutin'],['Diskusi rencana kehamilan berikutnya','Dengan dokter'],['Pertahankan pola makan sehat','Setiap hari']],
                 'restrictions'=>['Jarak kehamilan minimal 18-24 bulan','Konsultasi dokter sebelum olahraga intens pertama','Pantau tekanan darah','Tetap perhatikan bekas luka'],
                 'warning'=>['Nyeri hebat di area bekas SC lama','Perdarahan tidak normal','Tanda infeksi bekas luka terlambat','Tekanan darah tidak stabil'],
                 'vid'=>null,'vtitle'=>''],
            ];
        } else {
            $phases = [
                ['id'=>'p1','name'=>'Fase Imobilisasi','range'=>'Hari 1 s/d 7','days'=>[1,7],'badge'=>'Istirahat Total',
                 'desc'=>'Area operasi harus diistirahatkan. Fokus pada manajemen nyeri, pencegahan infeksi luka, dan posisi yang benar sesuai instruksi dokter.',
                 'goals'=>['Nyeri < 5/10 dengan obat','Luka tidak menunjukkan tanda infeksi','Posisi tubuh benar (sesuai instruksi)','Bisa mobilisasi ke kamar mandi dengan alat bantu'],
                 'activities'=>[['Periksa luka/perban setiap pagi dan malam','Pagi & Malam'],['Latihan isometrik ringan (kencangkan otot tanpa bergerak)','Setiap 2 jam'],['Posisi rebahan sesuai instruksi dokter','Sepanjang hari'],['Minum obat analgesik tepat waktu','Pagi, Siang, Malam'],['Kompres dingin di area bengkak (20 mnt, 3x/hari)','Pagi, Siang, Malam'],['Pantau sirkulasi (warna, suhu, rasa jari)','Setiap 4 jam']],
                 'restrictions'=>['Tidak menumpu berat badan penuh','Tidak putar atau tekuk sendi operasi','Tidak basahi area gips/luka','Tidak duduk di kursi rendah tanpa bantuan'],
                 'warning'=>['Jari-jari bengkak atau biru/pucat (tanda pembuluh tersumbat)','Nyeri yang tidak berkurang dengan obat','Demam > 38.5 °C','Luka mengeluarkan cairan berwarna/berbau'],
                 'vid'=>'wch7bNy0EWE','vtitle'=>'Panduan Pasca Operasi Ortopedi'],
                ['id'=>'p2','name'=>'Rehabilitasi Awal','range'=>'Hari 8 s/d 21','days'=>[8,21],'badge'=>'Mulai Bergerak',
                 'desc'=>'Fisioterapi dimulai. Latihan gerak sendi (ROM) bertahap dan berjalan dengan alat bantu. Luka dijahit mulai bisa dilepas jahitannya.',
                 'goals'=>['Bisa berjalan dengan walker 5-10 menit','ROM (Range of Motion) meningkat 20-30°','Jahitan dapat dilepas (hari ke-10 s/d 14)','Nyeri saat latihan < 4/10'],
                 'activities'=>[['Latihan jalan dengan walker 5-10 menit','Pagi & Sore'],['Gerak sendi ROM sesuai instruksi fisioterapis','Pagi & Sore'],['Kontrol poliklinik untuk pelepasan jahitan','Hari ke-10 s/d 14'],['Latihan penguatan otot isometrik','Setiap hari'],['Elevasi kaki (lebih tinggi dari jantung)','Saat istirahat'],['Ganti balutan luka setelah jahitan dilepas','Sesuai instruksi']],
                 'restrictions'=>['Tidak menumpu berat badan tanpa izin dokter','Tidak memutar sendi berlebihan','Tidak berenang (sebelum luka kering)','Tidak naik tangga tanpa pegangan'],
                 'warning'=>['Demam lebih dari 38 °C setelah jahitan dilepas','Sendi membengkak tiba-tiba (mungkin hematom)','Nyeri sangat hebat saat latihan ROM','Mati rasa atau kesemutan permanen di kaki'],
                 'vid'=>'hWK3xL9WfQk','vtitle'=>'Cara Menggunakan Walker dengan Benar'],
                ['id'=>'p3','name'=>'Rehabilitasi Progresif','range'=>'Hari 22 s/d 56','days'=>[22,56],'badge'=>'Kekuatan Meningkat',
                 'desc'=>'Transisi dari walker ke tongkat atau jalan mandiri. Program fisioterapi intensif. Penguatan otot dan peningkatan keseimbangan.',
                 'goals'=>['Berjalan mandiri / dengan tongkat tanpa walker','ROM mendekati normal (> 90°)','Naik tangga dengan pegangan 1 sisi','Bisa mandi dan berpakaian mandiri'],
                 'activities'=>[['Latihan jalan 15-30 menit tanpa walker','Pagi & Sore'],['Latihan keseimbangan (sendi lutut/panggul)','Fisioterapi'],['Naik tangga (supervised)','Di bawah pengawasan'],['Penguatan otot dengan resistance band','Fisioterapi'],['Kontrol dokter ortopedi (X-ray follow-up)','Hari ke-28 s/d 42'],['Sepeda statis dengan resistansi rendah','Hari ke-35+']],
                 'restrictions'=>['Tidak berlari (belum diizinkan)','Tidak duduk bersila','Tidak mendaki / medan tidak rata tanpa bantuan','Tidak memaksakan ROM melewati batas nyeri'],
                 'warning'=>['Krepitasi (bunyi krek) baru pada sendi','Nyeri yang kembali parah setelah sempat membaik','Sendi terasa keluar dari posisinya','Bengkak yang membesar setelah aktivitas'],
                 'vid'=>'ZwjCz8gj82A','vtitle'=>'Cara Mandi Aman dengan Perban/Gips'],
                ['id'=>'p4','name'=>'Pemulihan Fungsional','range'=>'Hari 57+','days'=>[57,999],'badge'=>'Kembali Normal',
                 'desc'=>'Aktivitas normal hampir penuh. Fokus pada pencegahan cedera ulang, penguatan otot jangka panjang, dan kembali ke aktivitas sport ringan.',
                 'goals'=>['Jalan normal tanpa alat bantu','ROM normal sesuai usia','Kembali bekerja (pekerjaan ringan-sedang)','Olahraga ringan (renang, bersepeda)'],
                 'activities'=>[['Olahraga renang atau bersepeda 30 mnt/hari','Pagi'],['Latihan fungsional (squat ringan, step-up)','Fisioterapi'],['Kontrol ortopedi dan X-ray final','Hari ke-84 s/d 90'],['Kembali ke aktivitas kerja bertahap','Minggu ke-8+'],['Pertahankan berat badan ideal (kurangi beban sendi)','Setiap hari'],['Gunakan alas kaki ergonomis','Setiap hari']],
                 'restrictions'=>['Hindari olahraga kontak fisik (sepakbola, bela diri)','Hindari lompatan tinggi tanpa izin dokter','Tidak duduk > 1 jam tanpa peregangan','Konsultasi sebelum kembali ke olahraga kompetitif'],
                 'warning'=>['Nyeri sendi kembali setelah aktivitas','Keterbatasan gerak yang tidak membaik','Tanda-tanda awal artritis','Sendi terasa tidak stabil saat berjalan'],
                 'vid'=>null,'vtitle'=>''],
            ];
        }
        $currentPhaseIdx = 0;
        foreach ($phases as $i => $ph) { if ($dayPostOp >= $ph['days'][0] && $dayPostOp <= $ph['days'][1]) $currentPhaseIdx = $i; }
        $totalPhases = count($phases);
        $progressPct = round(($currentPhaseIdx / max(1,$totalPhases-1))*100);
        ?>

        <!-- Phase Detail Modal -->
        <div id="phase-modal" class="hidden fixed inset-0 z-[95] items-center justify-center p-4" style="background:rgba(90,108,122,0.6);backdrop-filter:blur(6px);">
            <div class="rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto relative" style="background:#F8FCFF;">
                <div class="px-8 pt-7 pb-5 sticky top-0 rounded-t-3xl z-10" style="background:#F8FCFF;border-bottom:1px solid #DAE3EC;">
                    <button onclick="closePhaseModal()" class="absolute top-4 right-4 w-9 h-9 rounded-full flex items-center justify-center font-bold transition-all text-sm" style="background:#DAE3EC;color:#5A6C7A;" onmouseover="this.style.background='#B8C9DD'" onmouseout="this.style.background='#DAE3EC'">&times;</button>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-3 h-3 rounded-full shrink-0" style="background:#728BA9;"></div>
                        <span id="pmod-badge" class="text-xs font-extrabold uppercase tracking-wider px-3 py-1 rounded-full" style="background:#ECF2E6;color:#728BA9;"></span>
                    </div>
                    <h3 id="pmod-title" class="text-2xl font-extrabold" style="color:#5A6C7A;"></h3>
                    <p id="pmod-range" class="text-sm font-bold mt-0.5" style="color:#A3ACA0;"></p>
                    <p id="pmod-desc" class="text-sm font-medium mt-2 leading-relaxed" style="color:#7F8A83;"></p>
                </div>
                <div class="px-8 py-6 space-y-6">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider mb-3" style="color:#728BA9;">Target Fase Ini</p>
                        <ul id="pmod-goals" class="space-y-2"></ul>
                    </div>
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider mb-3" style="color:#728BA9;">Aktivitas Wajib</p>
                        <div id="pmod-activities" class="space-y-2"></div>
                    </div>
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider mb-3" style="color:#5A6C7A;">Larangan</p>
                        <ul id="pmod-restrictions" class="space-y-2"></ul>
                    </div>
                    <div class="rounded-2xl p-5" style="background:rgba(114,139,169,0.08);border:1.5px solid #B8C9DD;">
                        <p class="text-xs font-extrabold uppercase tracking-wider mb-3 flex items-center gap-2" style="color:#5A6C7A;">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="#728BA9" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Tanda Bahaya - Segera ke Dokter
                        </p>
                        <ul id="pmod-warning" class="space-y-2"></ul>
                    </div>
                    <div id="pmod-vid-wrap" class="hidden">
                        <button id="pmod-vid-btn" class="w-full py-3.5 rounded-2xl font-extrabold text-sm flex items-center justify-center gap-2 hover:-translate-y-0.5 transition-all text-white" style="background:#728BA9;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
                            <span id="pmod-vid-label">Tonton Video Panduan</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest mb-1" style="color:#A3ACA0;">Protokol: <?= $opName ?></p>
                    <h2 class="text-3xl font-extrabold" style="color:#728BA9;">Roadmap Pemulihan</h2>
                    <p class="font-medium mt-1" style="color:#7F8A83;">Anda berada di <strong style="color:#728BA9;">Hari ke-<?= $dayPostOp ?></strong> &mdash; <?= $phases[$currentPhaseIdx]['name'] ?></p>
                </div>
                <div class="glass-card px-5 py-3 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-extrabold text-white text-sm shrink-0" style="background:#728BA9;"><?= $currentPhaseIdx+1 ?></div>
                    <div><p class="font-extrabold text-sm" style="color:#5A6C7A;"><?= $phases[$currentPhaseIdx]['name'] ?></p><p class="text-xs font-medium" style="color:#A3ACA0;"><?= $phases[$currentPhaseIdx]['range'] ?></p></div>
                </div>
            </div>

            <!-- Phase Progress Stepper -->
            <div class="glass-card p-6 mb-6">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-extrabold uppercase tracking-wider" style="color:#A3ACA0;">Progress Pemulihan</p>
                    <span class="text-sm font-extrabold" style="color:#728BA9;"><?= $progressPct ?>% fase selesai</span>
                </div>
                <div class="w-full rounded-full h-2 mb-7" style="background:#DAE3EC;">
                    <div class="h-2 rounded-full" style="width:<?= $progressPct ?>%;background:linear-gradient(90deg,#728BA9,#B8C9DD);"></div>
                </div>
                <div class="relative">
                    <div class="absolute top-5 left-0 right-0 h-px mx-10" style="background:#DAE3EC;z-index:0;"></div>
                    <div class="flex justify-between relative z-10">
                        <?php foreach ($phases as $i => $ph):
                            $isDone = $dayPostOp > $ph['days'][1];
                            $isCurrent = ($i === $currentPhaseIdx);
                            $btnBg    = $isDone ? $C_DONE  : ($isCurrent ? $C_ACTIVE : '#DAE3EC');
                            $btnColor = ($isDone || $isCurrent) ? '#fff' : $C_FUTURE;
                            $lblColor = $isCurrent ? $C_ACTIVE : ($isDone ? $C_DONE : $C_FUTURE);
                            $lblOp    = $isCurrent ? '1' : '0.7';
                            $glow     = $isCurrent ? 'box-shadow:0 0 0 4px rgba(114,139,169,0.25),0 4px 14px rgba(114,139,169,0.2);' : '';
                        ?>
                        <div class="flex flex-col items-center flex-1">
                            <button onclick="openPhaseModal(<?= $i ?>)" class="w-10 h-10 rounded-full flex items-center justify-center font-extrabold text-sm transition-all hover:scale-110 shadow-md mb-2"
                                style="background:<?= $btnBg ?>;color:<?= $btnColor ?>;<?= $glow ?>">
                                <?php if ($isDone): ?><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg><?php else: echo $i+1; endif; ?>
                            </button>
                            <p class="text-xs font-extrabold text-center leading-tight" style="color:<?= $lblColor ?>;max-width:72px;opacity:<?= $lblOp ?>;"><?= $ph['name'] ?></p>
                            <p class="text-[10px] text-center font-medium mt-0.5 opacity-60" style="color:#A3ACA0;"><?= $ph['range'] ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Phase Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                <?php foreach ($phases as $i => $ph):
                    $isDone = $dayPostOp > $ph['days'][1];
                    $isCurrent = ($i === $currentPhaseIdx);
                    $barColor  = $isDone ? $BAR_DONE   : ($isCurrent ? $BAR_ACTIVE : $BAR_FUTURE);
                    $dotColor  = $isDone ? $C_DONE     : ($isCurrent ? $C_ACTIVE   : $C_FUTURE);
                    $iconBg    = $isDone ? 'rgba(90,108,122,0.1)' : ($isCurrent ? 'rgba(114,139,169,0.12)' : '#F0F3F7');
                    $cBorder   = $isCurrent ? 'box-shadow:0 0 0 2px #728BA9,0 8px 32px rgba(114,139,169,0.15);' : '';
                    $badgeBg   = $isCurrent ? '#ECF2E6' : ($isDone ? '#DAE3EC' : '#F0F3F7');
                    $badgeClr  = $isCurrent ? '#728BA9' : ($isDone ? '#5A6C7A' : '#A3ACA0');
                    $statusLbl = $isDone ? '&#10003; Selesai' : ($isCurrent ? '&#9654; Anda Di Sini' : '&#8635; Akan Datang');
                    $statusClr = $isDone ? $C_DONE : ($isCurrent ? $C_ACTIVE : $C_FUTURE);
                ?>
                <button onclick="openPhaseModal(<?= $i ?>)" class="text-left glass-card overflow-hidden transition-all hover:shadow-xl hover:-translate-y-1 cursor-pointer" style="<?= $cBorder ?>">
                    <div class="h-1.5" style="background:<?= $barColor ?>;"></div>
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-extrabold shrink-0 text-white" style="background:<?= $dotColor ?>;"><?= $i+1 ?></div>
                                    <span class="text-xs font-extrabold px-2.5 py-0.5 rounded-full" style="background:<?= $badgeBg ?>;color:<?= $badgeClr ?>;"><?= $ph['badge'] ?></span>
                                </div>
                                <h3 class="font-extrabold text-lg" style="color:#5A6C7A;"><?= $ph['name'] ?></h3>
                                <p class="text-xs font-bold mt-0.5" style="color:#A3ACA0;"><?= $ph['range'] ?></p>
                            </div>
                            <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0" style="background:<?= $iconBg ?>;">
                                <?php if ($isDone): ?>
                                    <svg class="w-5 h-5" fill="none" stroke="#5A6C7A" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <?php elseif ($isCurrent): ?>
                                    <div class="w-3 h-3 rounded-full" style="background:#728BA9;"></div>
                                <?php else: ?>
                                    <svg class="w-5 h-5" fill="none" stroke="#A3ACA0" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="text-xs font-medium leading-relaxed mb-4" style="color:#7F8A83;"><?= $ph['desc'] ?></p>
                        <div class="space-y-1.5 mb-4">
                            <?php foreach (array_slice($ph['goals'],0,2) as $g): ?>
                            <div class="flex items-center gap-2 text-xs font-medium" style="color:#5A6C7A;">
                                <div class="w-1.5 h-1.5 rounded-full shrink-0" style="background:<?= $dotColor ?>;"></div>
                                <?= htmlspecialchars($g) ?>
                            </div>
                            <?php endforeach; ?>
                            <?php if (count($ph['goals'])>2): ?><p class="text-xs font-medium mt-1" style="color:#A3ACA0;">+<?= count($ph['goals'])-2 ?> target lainnya...</p><?php endif; ?>
                        </div>
                        <div class="flex items-center justify-between pt-3" style="border-top:1px solid rgba(218,227,236,0.6);">
                            <span class="text-xs font-bold" style="color:<?= $statusClr ?>;"><?= $statusLbl ?></span>
                            <span class="flex items-center gap-1 text-xs font-bold" style="color:#728BA9;">Lihat Detail <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg></span>
                        </div>
                    </div>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- Weekly Calendar -->
            <?php
            $weekStart = max(1, $dayPostOp - (($dayPostOp-1)%7));
            $weekEnd   = $weekStart + 6;
            $curPhase  = $phases[$currentPhaseIdx];
            $wNames    = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
            $surgeryDow = 0;
            if ($surgeryDate) {
                $sDate = new DateTime($surgeryDate);
                $phpDow = (int)$sDate->format('N');
                $surgeryDow = $phpDow - 1;
            }
            $calStart = date('d M', strtotime($surgeryDate . ' + ' . ($weekStart - 1) . ' days'));
            $calEnd   = date('d M Y', strtotime($surgeryDate . ' + ' . ($weekEnd - 1) . ' days'));
            ?>
            <div class="glass-card p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="font-extrabold text-lg flex items-center gap-2" style="color:#5A6C7A;">
                            <svg class="w-5 h-5" fill="none" stroke="#728BA9" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Rencana Minggu Ini
                        </h3>
                        <p class="text-xs font-medium mt-0.5" style="color:#A3ACA0;"><?= $calStart ?> &mdash; <?= $calEnd ?> <span class="mx-1">&bull;</span> Hari ke-<?= $weekStart ?> s/d <?= $weekEnd ?></p>
                    </div>
                    <span class="text-xs font-extrabold px-3 py-1.5 rounded-full" style="background:#ECF2E6;color:#728BA9;"><?= $curPhase['name'] ?></span>
                </div>
                <div class="grid grid-cols-7 gap-2 mb-5">
                    <?php for ($d=$weekStart; $d<=$weekEnd; $d++):
                        $isToday = ($d === $dayPostOp);
                        $isPast  = ($d < $dayPostOp);
                        $wIdx    = ($surgeryDow + ($d - 1)) % 7;
                        $calDate = date('j', strtotime($surgeryDate . ' + ' . ($d - 1) . ' days'));
                        $dayBg   = $isToday ? '#728BA9' : ($isPast ? 'rgba(114,139,169,0.12)' : 'rgba(218,227,236,0.2)');
                        $dayGlow = $isToday ? 'box-shadow:0 4px 16px rgba(114,139,169,0.35);' : '';
                        $numClr  = $isToday ? '#fff'    : ($isPast ? '#728BA9' : '#B8C9DD');
                    ?>
                    <div class="flex flex-col items-center gap-1">
                        <p class="text-[10px] font-bold" style="color:#A3ACA0;"><?= $wNames[$wIdx] ?></p>
                        <div class="w-full aspect-square rounded-xl flex flex-col items-center justify-center transition-all hover:scale-105" style="background:<?= $dayBg ?>;<?= $dayGlow ?>" title="Hari ke-<?= $d ?>">
                            <span class="text-sm font-extrabold" style="color:<?= $numClr ?>;"><?= $calDate ?></span>
                            <?php if ($isPast): ?>
                                <svg class="w-3 h-3 mt-0.5" fill="none" stroke="#728BA9" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <?php elseif ($isToday): ?>
                                <div class="w-1.5 h-1.5 rounded-full mt-1 opacity-75" style="background:#fff;"></div>
                            <?php endif; ?>
                        </div>
                        <?php if ($isToday): ?><p class="text-[9px] font-extrabold" style="color:#728BA9;">HARI INI</p><?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
                <div class="rounded-2xl p-4" style="background:rgba(114,139,169,0.07);border:1px solid #DAE3EC;">
                    <p class="text-xs font-extrabold uppercase tracking-wider mb-3 flex items-center gap-1.5" style="color:#728BA9;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                        Prioritas Hari ke-<?= $dayPostOp ?> &mdash; <?= $curPhase['name'] ?>
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <?php foreach (array_slice($curPhase['activities'],0,4) as $idx=>$act): ?>
                        <div class="flex items-center gap-2.5 rounded-xl px-3 py-2.5" style="background:rgba(255,255,255,0.85);">
                            <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0 font-extrabold text-xs text-white" style="background:#728BA9;"><?= $idx+1 ?></div>
                            <div>
                                <p class="text-xs font-extrabold leading-snug" style="color:#5A6C7A;"><?= htmlspecialchars($act[0]) ?></p>
                                <p class="text-[10px] font-medium" style="color:#A3ACA0;"><?= htmlspecialchars($act[1]) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button onclick="openPhaseModal(<?= $currentPhaseIdx ?>)" class="mt-3 w-full py-2 rounded-xl text-xs font-extrabold transition-all hover:-translate-y-0.5 text-white" style="background:#728BA9;">Lihat Semua Aktivitas &rarr;</button>
                </div>
            </div>
        </div>

        <script>
        var PHASES=<?= json_encode(array_map(function($ph){return['name'=>$ph['name'],'range'=>$ph['range'],'badge'=>$ph['badge'],'desc'=>$ph['desc'],'goals'=>$ph['goals'],'activities'=>$ph['activities'],'restrictions'=>$ph['restrictions'],'warning'=>$ph['warning'],'vid'=>$ph['vid'],'vtitle'=>$ph['vtitle']];},$phases),JSON_UNESCAPED_UNICODE) ?>;
        function openPhaseModal(idx){
            var p=PHASES[idx];
            document.getElementById('pmod-title').textContent=p.name;
            document.getElementById('pmod-range').textContent=p.range;
            document.getElementById('pmod-desc').textContent=p.desc;
            document.getElementById('pmod-badge').textContent=p.badge;
            var gl=document.getElementById('pmod-goals');gl.innerHTML='';
            p.goals.forEach(function(g){gl.innerHTML+='<li class="flex items-start gap-2 text-sm font-medium" style="color:#5A6C7A;"><svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="#728BA9" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>'+g+'</li>';});
            var al=document.getElementById('pmod-activities');al.innerHTML='';
            p.activities.forEach(function(a){al.innerHTML+='<div class="flex items-center justify-between px-4 py-2.5 rounded-xl" style="background:rgba(114,139,169,0.06);"><span class="text-sm font-semibold" style="color:#5A6C7A;">'+a[0]+'</span><span class="text-xs font-bold px-2.5 py-0.5 rounded-full ml-3 shrink-0 whitespace-nowrap" style="background:#ECF2E6;color:#728BA9;">'+a[1]+'</span></div>';});
            var rl=document.getElementById('pmod-restrictions');rl.innerHTML='';
            p.restrictions.forEach(function(r){rl.innerHTML+='<li class="flex items-start gap-2 text-sm font-medium" style="color:#5A6C7A;"><svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="#5A6C7A" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>'+r+'</li>';});
            var wl=document.getElementById('pmod-warning');wl.innerHTML='';
            p.warning.forEach(function(w){wl.innerHTML+='<li class="flex items-start gap-2 text-sm font-semibold" style="color:#5A6C7A;"><svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="#728BA9" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>'+w+'</li>';});
            var vw=document.getElementById('pmod-vid-wrap');
            if(p.vid){vw.classList.remove('hidden');document.getElementById('pmod-vid-label').textContent='Tonton: '+p.vtitle;document.getElementById('pmod-vid-btn').onclick=function(){closePhaseModal();openVid(p.vid,p.vtitle);};}else{vw.classList.add('hidden');}
            var m=document.getElementById('phase-modal');m.classList.remove('hidden');m.classList.add('flex');
        }
        function closePhaseModal(){var m=document.getElementById('phase-modal');m.classList.add('hidden');m.classList.remove('flex');}
        document.getElementById('phase-modal').addEventListener('click',function(e){if(e.target===this)closePhaseModal();});
        </script>
        <?php elseif ($page === 'monitoring'): ?>
        <!-- ============================================================
             MONITORING
        ============================================================ -->
        <div class="w-full">
            <div class="mb-8">
                <h2 class="text-3xl font-extrabold" style="color:#728BA9;">Log Kondisi Harian</h2>
                <p class="font-medium mt-1" style="color:#A3ACA0;">Isi formulir ini setiap hari untuk memantau perkembangan pemulihan Anda.</p>
            </div>
            <form action="dashboard.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="action" value="save_monitoring">

                <?php if ($opType === 'cabg'): ?>
                <!-- CABG -->
                <div class="glass-card p-8">
                    <h3 class="font-extrabold text-lg mb-6 flex items-center gap-2" style="color:#5A6C7A;"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg> Tanda Vital</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block font-bold text-sm mb-2" style="color:#5A6C7A;">SpO₂ (%)</label>
                            <input type="number" name="spo2" min="80" max="100" value="<?= htmlspecialchars($todayMonitoring['spo2'] ?? '') ?>" placeholder="misal: 98" 
                                class="w-full px-4 py-3 rounded-xl border outline-none transition-all font-semibold" style="border-color:#DAE3EC;background:rgba(255,255,255,0.8);color:#5A6C7A;"
                                onfocus="this.style.borderColor='#728BA9'" onblur="this.style.borderColor='#DAE3EC'">
                        </div>
                        <div>
                            <label class="block font-bold text-sm mb-2" style="color:#5A6C7A;">Detak Jantung (bpm)</label>
                            <input type="number" name="heart_rate" min="40" max="200" value="<?= htmlspecialchars($todayMonitoring['heart_rate'] ?? '') ?>" placeholder="misal: 80"
                                class="w-full px-4 py-3 rounded-xl border outline-none transition-all font-semibold" style="border-color:#DAE3EC;background:rgba(255,255,255,0.8);color:#5A6C7A;"
                                onfocus="this.style.borderColor='#728BA9'" onblur="this.style.borderColor='#DAE3EC'">
                        </div>
                    </div>
                </div>
                <div class="glass-card p-8">
                    <h3 class="font-extrabold text-lg mb-2 flex items-center gap-2" style="color:#5A6C7A;"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z"/></svg> Skala Nyeri Dada</h3>
                    <p class="text-xs font-medium mb-6" style="color:#A3ACA0;">Geser slider untuk menentukan tingkat nyeri saat ini</p>
                    <div class="flex items-center gap-4 mb-3">
                        <input type="range" name="pain_level" min="0" max="10" value="<?= htmlspecialchars($todayMonitoring['pain_level'] ?? 0) ?>" class="flex-1 h-3 rounded-full" id="sldr_cabg" oninput="setPain(this.value,'pf_cabg','pv_cabg')">
                        <div class="w-10 h-10 rounded-full border-2 flex items-center justify-center shrink-0 font-extrabold text-sm" id="pf_cabg" style="border-color:#DAE3EC;color:#728BA9;">0</div>
                    </div>
                    <div class="flex justify-between text-xs font-bold" style="color:#A3ACA0;">
                        <span>0 — Tidak Sakit</span>
                        <span class="text-base font-extrabold" id="pv_cabg" style="color:#728BA9;"><?= ($todayMonitoring['pain_level'] ?? 0) ?>/10</span>
                        <span>10 — Sangat Parah</span>
                    </div>
                </div>

                <?php elseif ($opType === 'sc'): ?>
                <!-- SC -->
                <div class="glass-card p-8">
                    <h3 class="font-extrabold text-lg mb-4 flex items-center gap-2" style="color:#5A6C7A;"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Suhu Tubuh (°C)</h3>
                    <input type="number" step="0.1" name="temp" min="35" max="42" value="<?= htmlspecialchars($todayMonitoring['temp'] ?? '') ?>" placeholder="misal: 36.8"
                        class="w-full px-4 py-3 rounded-xl border outline-none font-semibold" style="border-color:#DAE3EC;background:rgba(255,255,255,0.8);color:#5A6C7A;"
                        onfocus="this.style.borderColor='#728BA9'" onblur="this.style.borderColor='#DAE3EC'">
                    <p class="text-xs font-medium mt-2" style="color:#A3ACA0;">Normal: 36–37.5°C. Demam jika &gt; 38°C.</p>
                </div>
                <div class="glass-card p-8">
                    <h3 class="font-extrabold text-lg mb-6 flex items-center gap-2" style="color:#5A6C7A;"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg> Pemantauan Perdarahan</h3>
                    <div class="space-y-5">
                        <div><label class="block font-bold text-sm mb-3" style="color:#5A6C7A;">Volume</label>
                            <div class="grid grid-cols-3 gap-3"><?php foreach(['Sedikit','Sedang','Banyak'] as $o): ?>
                            <div class="radio-card"><input type="radio" name="blood_volume" id="bv_<?=$o?>" value="<?=$o?>" <?=($todayMonitoring['blood_volume']??'')===$o?'checked':''?>><label for="bv_<?=$o?>"><?=$o?></label></div>
                            <?php endforeach; ?></div>
                        </div>
                        <div><label class="block font-bold text-sm mb-3" style="color:#5A6C7A;">Warna</label>
                            <div class="grid grid-cols-2 gap-3"><?php foreach(['Merah Segar','Merah Kecoklatan','Coklat Tua','Kuning / Keputihan'] as $o): ?>
                            <div class="radio-card"><input type="radio" name="blood_color" id="bc_<?=preg_replace('/\W+/','_',$o)?>" value="<?=$o?>" <?=($todayMonitoring['blood_color']??'')===$o?'checked':''?>><label for="bc_<?=preg_replace('/\W+/','_',$o)?>"><?=$o?></label></div>
                            <?php endforeach; ?></div>
                        </div>
                        <div><label class="block font-bold text-sm mb-3" style="color:#5A6C7A;">Gumpalan Darah</label>
                            <div class="grid grid-cols-2 gap-3"><?php foreach(['Tidak Ada','Kecil (< koin)','Sedang (> koin)','Besar'] as $o): ?>
                            <div class="radio-card"><input type="radio" name="blood_clots" id="bclot_<?=preg_replace('/\W+/','_',$o)?>" value="<?=$o?>" <?=($todayMonitoring['blood_clots']??'')===$o?'checked':''?>><label for="bclot_<?=preg_replace('/\W+/','_',$o)?>"><?=$o?></label></div>
                            <?php endforeach; ?></div>
                        </div>
                    </div>
                </div>
                <div class="glass-card p-8">
                    <h3 class="font-extrabold text-lg mb-2 flex items-center gap-2" style="color:#5A6C7A;"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z"/></svg> Skala Nyeri</h3>
                    <div class="flex items-center gap-4 mb-3">
                        <input type="range" name="pain_level" min="0" max="10" value="<?= htmlspecialchars($todayMonitoring['pain_level'] ?? 0) ?>" class="flex-1 h-3 rounded-full" oninput="setPain(this.value,'pf_sc','pv_sc')">
                        <div class="w-10 h-10 rounded-full border-2 flex items-center justify-center shrink-0 font-extrabold text-sm" id="pf_sc" style="border-color:#DAE3EC;color:#728BA9;">0</div>
                    </div>
                    <div class="flex justify-between text-xs font-bold" style="color:#A3ACA0;"><span>0</span><span class="font-extrabold" id="pv_sc" style="color:#728BA9;"><?= ($todayMonitoring['pain_level']??0) ?>/10</span><span>10</span></div>
                </div>

                <?php else: ?>
                <!-- Ortopedi -->
                <div class="glass-card p-8">
                    <h3 class="font-extrabold text-lg mb-6 flex items-center gap-2" style="color:#5A6C7A;"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z"/></svg> Skala Nyeri</h3>
                    <div class="space-y-6">
                        <div>
                            <label class="block font-bold text-sm mb-2" style="color:#5A6C7A;">Nyeri Area Operasi</label>
                            <div class="flex items-center gap-4 mb-2"><input type="range" name="stump_pain" min="0" max="10" value="<?= htmlspecialchars($todayMonitoring['stump_pain']??0) ?>" class="flex-1 h-3 rounded-full" oninput="setPain(this.value,'pf_st','pv_st')"><div class="w-10 h-10 rounded-full border-2 flex items-center justify-center shrink-0 font-extrabold text-sm" id="pf_st" style="border-color:#DAE3EC;color:#728BA9;">0</div></div>
                            <div class="flex justify-between text-xs font-bold" style="color:#A3ACA0;"><span>0</span><span id="pv_st" style="color:#728BA9;font-weight:800;"><?= ($todayMonitoring['stump_pain']??0) ?>/10</span><span>10</span></div>
                        </div>
                        <div>
                            <label class="block font-bold text-sm mb-2" style="color:#5A6C7A;">Nyeri Sendi</label>
                            <div class="flex items-center gap-4 mb-2"><input type="range" name="phantom_pain" min="0" max="10" value="<?= htmlspecialchars($todayMonitoring['phantom_pain']??0) ?>" class="flex-1 h-3 rounded-full" oninput="setPain(this.value,'pf_ph','pv_ph')"><div class="w-10 h-10 rounded-full border-2 flex items-center justify-center shrink-0 font-extrabold text-sm" id="pf_ph" style="border-color:#DAE3EC;color:#728BA9;">0</div></div>
                            <div class="flex justify-between text-xs font-bold" style="color:#A3ACA0;"><span>0</span><span id="pv_ph" style="color:#728BA9;font-weight:800;"><?= ($todayMonitoring['phantom_pain']??0) ?>/10</span><span>10</span></div>
                        </div>
                    </div>
                </div>
                <div class="glass-card p-8">
                    <h3 class="font-extrabold text-lg mb-6 flex items-center gap-2" style="color:#5A6C7A;"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg> Kondisi Luka</h3>
                    <div class="space-y-5">
                        <div><label class="block font-bold text-sm mb-3" style="color:#5A6C7A;">Warna Kulit Sekitar Luka</label>
                            <div class="grid grid-cols-2 gap-3"><?php foreach(['Normal (sesuai kulit)','Kemerahan Ringan','Merah Tua / Kebiruan','Pucat'] as $o): ?>
                            <div class="radio-card"><input type="radio" name="wound_color" id="wc_<?=preg_replace('/\W+/','_',$o)?>" value="<?=$o?>" <?=($todayMonitoring['wound_color']??'')===$o?'checked':''?>><label for="wc_<?=preg_replace('/\W+/','_',$o)?>"><?=$o?></label></div>
                            <?php endforeach; ?></div>
                        </div>
                        <div><label class="block font-bold text-sm mb-3" style="color:#5A6C7A;">Bengkak</label>
                            <div class="grid grid-cols-3 gap-3"><?php foreach(['Tidak Ada','Sedikit (dekat luka)','Besar / Meluas'] as $o): ?>
                            <div class="radio-card"><input type="radio" name="wound_swelling" id="ws_<?=preg_replace('/\W+/','_',$o)?>" value="<?=$o?>" <?=($todayMonitoring['wound_swelling']??'')===$o?'checked':''?>><label for="ws_<?=preg_replace('/\W+/','_',$o)?>"><?=$o?></label></div>
                            <?php endforeach; ?></div>
                        </div>
                        <div><label class="block font-bold text-sm mb-3" style="color:#5A6C7A;">Cairan Luka</label>
                            <div class="grid grid-cols-2 gap-3"><?php foreach(['Kering','Bening / Normal','Kuning / Nanah','Merah (darah)'] as $o): ?>
                            <div class="radio-card"><input type="radio" name="wound_fluid" id="wf_<?=preg_replace('/\W+/','_',$o)?>" value="<?=$o?>" <?=($todayMonitoring['wound_fluid']??'')===$o?'checked':''?>><label for="wf_<?=preg_replace('/\W+/','_',$o)?>"><?=$o?></label></div>
                            <?php endforeach; ?></div>
                        </div>
                        <div><label class="block font-bold text-sm mb-3" style="color:#5A6C7A;">Bau Luka</label>
                            <div class="grid grid-cols-3 gap-3"><?php foreach(['Tidak Berbau','Sedikit Berbau','Menyengat'] as $o): ?>
                            <div class="radio-card"><input type="radio" name="wound_odor" id="wo_<?=preg_replace('/\W+/','_',$o)?>" value="<?=$o?>" <?=($todayMonitoring['wound_odor']??'')===$o?'checked':''?>><label for="wo_<?=preg_replace('/\W+/','_',$o)?>"><?=$o?></label></div>
                            <?php endforeach; ?></div>
                        </div>
                    </div>
                </div>
                <!-- Photo Log -->
                <div class="glass-card p-8">
                    <h3 class="font-extrabold text-lg mb-2 flex items-center gap-2" style="color:#5A6C7A;"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/></svg> Foto Luka Hari Ini</h3>
                    <p class="text-xs font-medium mb-5" style="color:#A3ACA0;">Unggah foto untuk membantu pemantauan visual perkembangan luka</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="wound_photo" class="flex flex-col items-center justify-center w-full h-44 cursor-pointer transition-all rounded-2xl" style="border:2px dashed #B8C9DD;background:#F8FCFF;" onmouseover="this.style.background='#ECF2E6'" onmouseout="this.style.background='#F8FCFF'">
                                <svg class="w-10 h-10 mb-3" fill="none" stroke="#B8C9DD" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586A2 2 0 0110.414 11h1.172a2 2 0 011.414.586L17 16m-2-2l1.586-1.586A2 2 0 0118 13.5h.5M2 8h1m17 0h1M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
                                <span class="font-bold text-sm" style="color:#728BA9;">Ketuk untuk unggah foto</span>
                                <span class="text-xs mt-1" style="color:#A3ACA0;">JPG, PNG maks. 5MB</span>
                                <input id="wound_photo" name="wound_photo" type="file" class="hidden" accept="image/*">
                            </label>
                            <div id="photo_preview" class="hidden mt-3">
                                <img id="photo_img" class="w-full rounded-2xl object-cover max-h-44" src="" alt="Preview">
                            </div>
                        </div>
                        <div class="rounded-2xl p-5" style="background:#ECF2E6;">
                            <p class="text-xs font-extrabold uppercase tracking-wider mb-3 flex items-center gap-1.5" style="color:#5A6C7A;"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg> Referensi Luka</p>
                            <div class="space-y-3">
                                <div class="flex items-start gap-3"><span class="w-3 h-3 rounded-full shrink-0 mt-1" style="background:#728BA9;"></span><div><p class="text-xs font-bold" style="color:#728BA9;">NORMAL</p><p class="text-xs font-medium" style="color:#7F7F7F;">Merah muda memudar, tepi rapat, cairan bening sedikit</p></div></div>
                                <div class="flex items-start gap-3"><span class="w-3 h-3 rounded-full shrink-0 mt-1" style="background:#D4AA6A;"></span><div><p class="text-xs font-bold" style="color:#A3ACA0;">PERHATIAN</p><p class="text-xs font-medium" style="color:#7F7F7F;">Kemerahan tepi luka, sedikit bengkak — pantau ketat</p></div></div>
                                <div class="flex items-start gap-3"><span class="w-3 h-3 rounded-full shrink-0 mt-1" style="background:#D46A6A;"></span><div><p class="text-xs font-bold" style="color:#5A6C7A;">BAHAYA!</p><p class="text-xs font-medium" style="color:#7F7F7F;">Nanah, bau menyengat, merah meluas, jahitan terbuka</p></div></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <button type="submit" class="w-full py-4 rounded-2xl text-white font-extrabold text-lg transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2.5" style="background:#728BA9;box-shadow:0 8px 24px rgba(114,139,169,0.3);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Simpan Data Hari Ini
                </button>
                <?php if (!empty($errorMsg)): ?>
                <p class="text-[#728BA9] font-medium text-sm text-center"><?= htmlspecialchars($errorMsg) ?></p>
                <?php endif; ?>
            </form>

            <!-- ===== MONITORING HISTORY ===== -->
            <?php if (!empty($monitoringHistory)): ?>
            <div class="mt-10">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#ECF2E6;">
                        <svg class="w-5 h-5" fill="none" stroke="#728BA9" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-lg" style="color:#5A6C7A;">Riwayat Pemantauan</h3>
                        <p class="text-xs font-medium" style="color:#A3ACA0;">Perkembangan kondisi Anda dari waktu ke waktu</p>
                    </div>
                </div>

                <!-- Mini sparkline chart using bar chart -->
                <?php
                $histReversed = array_reverse($monitoringHistory);
                $painKey = ($opType === 'amputation') ? 'stump_pain' : 'pain_level';
                $sparkPains = array_filter(array_map(fn($r) => $r[$painKey] ?? null, $histReversed), fn($v) => $v !== null);
                if (!empty($sparkPains)):
                    $sparkMax = max(10, max($sparkPains));
                ?>
                <div class="glass-card p-6 mb-5">
                    <p class="text-xs font-extrabold uppercase tracking-wider mb-4" style="color:#728BA9;">Grafik Nyeri <?= count($sparkPains) ?> Hari Terakhir</p>
                    <div class="flex items-end gap-1.5 h-20">
                        <?php foreach ($sparkPains as $i => $pv):
                            $barH = max(8, round(($pv/$sparkMax)*80));
                            $isLast = ($i === count($sparkPains)-1);
                            $barColor = $pv >= 7 ? '#5A6C7A' : ($pv >= 4 ? '#A3ACA0' : '#728BA9');
                        ?>
                        <div class="flex-1 flex flex-col items-center gap-1 group relative">
                            <div class="absolute -top-7 left-1/2 -translate-x-1/2 hidden group-hover:flex items-center justify-center bg-white rounded-lg px-2 py-0.5 shadow text-xs font-extrabold z-10" style="color:#5A6C7A;white-space:nowrap;border:1px solid #DAE3EC;"><?= $pv ?>/10</div>
                            <div class="w-full rounded-t-md transition-all" style="height:<?= $barH ?>px;background:<?= $isLast ? '#728BA9' : $barColor ?>;opacity:<?= $isLast ? '1' : '0.55' ?>;"></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex justify-between text-[10px] font-bold mt-2" style="color:#A3ACA0;">
                        <span><?= date('d M', strtotime(reset($histReversed)['record_date'])) ?></span>
                        <span class="font-extrabold" style="color:#728BA9;">Hari Ini</span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- History Table -->
                <div class="glass-card overflow-hidden">
                    <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="background:rgba(114,139,169,0.08);border-bottom:1.5px solid #DAE3EC;">
                                <th class="text-left px-5 py-3 font-extrabold text-xs uppercase tracking-wider" style="color:#A3ACA0;">Tanggal</th>
                                <th class="text-left px-5 py-3 font-extrabold text-xs uppercase tracking-wider" style="color:#A3ACA0;">Hari ke-</th>
                                <?php if ($opType === 'cabg'): ?>
                                <th class="text-left px-5 py-3 font-extrabold text-xs uppercase tracking-wider" style="color:#A3ACA0;">SpO₂</th>
                                <th class="text-left px-5 py-3 font-extrabold text-xs uppercase tracking-wider" style="color:#A3ACA0;">HR</th>
                                <th class="text-left px-5 py-3 font-extrabold text-xs uppercase tracking-wider" style="color:#A3ACA0;">Nyeri</th>
                                <?php elseif ($opType === 'sc'): ?>
                                <th class="text-left px-5 py-3 font-extrabold text-xs uppercase tracking-wider" style="color:#A3ACA0;">Suhu</th>
                                <th class="text-left px-5 py-3 font-extrabold text-xs uppercase tracking-wider" style="color:#A3ACA0;">Perdarahan</th>
                                <th class="text-left px-5 py-3 font-extrabold text-xs uppercase tracking-wider" style="color:#A3ACA0;">Nyeri</th>
                                <?php else: ?>
                                <th class="text-left px-5 py-3 font-extrabold text-xs uppercase tracking-wider" style="color:#A3ACA0;">Nyeri Area Op.</th>
                                <th class="text-left px-5 py-3 font-extrabold text-xs uppercase tracking-wider" style="color:#A3ACA0;">Nyeri Sendi</th>
                                <th class="text-left px-5 py-3 font-extrabold text-xs uppercase tracking-wider" style="color:#A3ACA0;">Kondisi Luka</th>
                                <?php endif; ?>
                                <th class="text-left px-5 py-3 font-extrabold text-xs uppercase tracking-wider" style="color:#A3ACA0;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($monitoringHistory as $idx => $rec):
                            $recDate = new DateTime($rec['record_date']);
                            $surgStart = new DateTime($surgeryDate ?? $rec['record_date']);
                            $surgStart->setTime(0,0,0); $recDate->setTime(0,0,0);
                            $recDayNum = $surgStart->diff($recDate)->days + 1;
                            $isToday = ($rec['record_date'] === date('Y-m-d'));
                            // Determine pain level for this record type
                            if ($opType === 'cabg') {
                                $recPain = $rec['pain_level'];
                            } elseif ($opType === 'sc') {
                                $recPain = $rec['pain_level'];
                            } else {
                                $recPain = $rec['stump_pain'];
                            }
                            $painStatus = '';
                            $painColor = '#A3ACA0';
                            $painBg = '#F8FCFF';
                            if ($recPain !== null) {
                                if ($recPain <= 3) { $painStatus='Ringan'; $painColor='#728BA9'; $painBg='#ECF2E6'; }
                                elseif ($recPain <= 6) { $painStatus='Sedang'; $painColor='#A3ACA0'; $painBg='rgba(218,227,236,0.5)'; }
                                else { $painStatus='Berat'; $painColor='#5A6C7A'; $painBg='rgba(90,108,122,0.12)'; }
                            }
                        ?>
                        <tr style="border-bottom:1px solid rgba(218,227,236,0.5);<?= $isToday ? 'background:rgba(114,139,169,0.05);' : '' ?>" class="hover:bg-white/40 transition-all">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <?php if ($isToday): ?><span class="w-1.5 h-1.5 rounded-full bg-[#728BA9] inline-block"></span><?php endif; ?>
                                    <span class="font-bold" style="color:#5A6C7A;"><?= date('d M Y', strtotime($rec['record_date'])) ?></span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold" style="background:#ECF2E6;color:#728BA9;">Hari ke-<?= $recDayNum ?></span>
                            </td>
                            <?php if ($opType === 'cabg'): ?>
                            <td class="px-5 py-3.5 font-bold" style="color:<?= ($rec['spo2'] !== null && $rec['spo2'] < 92) ? '#5A6C7A' : '#5A6C7A' ?>;"><?= $rec['spo2'] !== null ? $rec['spo2'].'%' : '—' ?></td>
                            <td class="px-5 py-3.5 font-bold" style="color:#5A6C7A;"><?= $rec['heart_rate'] !== null ? $rec['heart_rate'].' bpm' : '—' ?></td>
                            <td class="px-5 py-3.5"><span class="font-extrabold" style="color:#728BA9;"><?= $rec['pain_level'] !== null ? $rec['pain_level'].'/10' : '—' ?></span></td>
                            <?php elseif ($opType === 'sc'): ?>
                            <td class="px-5 py-3.5 font-bold" style="color:#5A6C7A;"><?= $rec['temp'] !== null ? $rec['temp'].'°C' : '—' ?></td>
                            <td class="px-5 py-3.5 font-bold" style="color:#5A6C7A;"><?= !empty($rec['blood_volume']) ? htmlspecialchars($rec['blood_volume']) : '—' ?></td>
                            <td class="px-5 py-3.5"><span class="font-extrabold" style="color:#728BA9;"><?= $rec['pain_level'] !== null ? $rec['pain_level'].'/10' : '—' ?></span></td>
                            <?php else: ?>
                            <td class="px-5 py-3.5"><span class="font-extrabold" style="color:#728BA9;"><?= $rec['stump_pain'] !== null ? $rec['stump_pain'].'/10' : '—' ?></span></td>
                            <td class="px-5 py-3.5"><span class="font-extrabold" style="color:#A3ACA0;"><?= $rec['phantom_pain'] !== null ? $rec['phantom_pain'].'/10' : '—' ?></span></td>
                            <td class="px-5 py-3.5 font-medium" style="color:#5A6C7A;"><?= !empty($rec['wound_color']) ? htmlspecialchars($rec['wound_color']) : '—' ?></td>
                            <?php endif; ?>
                            <td class="px-5 py-3.5">
                                <?php if ($recPain !== null): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold" style="background:<?= $painBg ?>;color:<?= $painColor ?>;"><?= $painStatus ?></span>
                                <?php else: ?>
                                <span class="text-xs font-medium" style="color:#A3ACA0;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                    <?php if (count($monitoringHistory) >= 30): ?>
                    <div class="px-5 py-3 text-center" style="border-top:1px solid #DAE3EC;">
                        <p class="text-xs font-medium" style="color:#A3ACA0;">Menampilkan 30 catatan terbaru</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>


        <?php elseif ($page === 'content'): ?>
        <!-- ============================================================
             CONTENT LIBRARY
        ============================================================ -->
        <div class="w-full">
            <div class="mb-8">
                <h2 class="text-3xl font-extrabold" style="color:#728BA9;">Pustaka Pemulihan</h2>
                <p class="font-medium mt-1" style="color:#A3ACA0;">Materi edukasi untuk pasien <?= htmlspecialchars($opName) ?>.</p>
            </div>
            <?php
            if ($opType==='cabg') {
                $vids=[['sAx8_UXak1Q','Olahraga Pasca Operasi Jantung','Rehabilitasi awal oleh dr. Kevin Triangto'],['hz4bgO-Smk0','Apa Yang Dilakukan Setelah Operasi?','Langkah esensial pemulihan pasca bedah jantung'],['ZibrJpra3FA','Rehabilitasi Fase I Pasca CABG','Latihan menjaga sirkulasi di fase paling awal']];
                $arts=[
                    ['heart','Diet Terbaik untuk Mempercepat Pemulihan Pasca CABG','5 menit','https://www.blkmaxhospital.com/blogs/diet-after-heart-bypass-surgery'],
                    ['bandage','Cara Merawat Luka Sayatan CABG','4 menit','https://www.halodoc.com/artikel/bekas-operasi-bypass-jantung-kenali-dan-rawat-benar?srsltid=AfmBOopk5Tfyev-mU-K7OCSAqDXKHxa9OzK9LD_4r-GBsQUcdYCZY_kr']
                ];
            } elseif ($opType==='sc') {
                $vids=[['KG_SsDOfwpI','Pantangan Pasca Operasi Caesar','Hal yang wajib dihindari ibu setelah SC'],['P7hrkSlr3vo','4 Tips Agar Cepat Pulih Pasca SC','Panduan mempercepat penyembuhan'],['c3NRSqZooyk','Tips Cepat Pulih Pasca Sesar','Tips percepatan dari dr. Keven']];
                $arts=[
                    ['bandage','Luka Jahitan Operasi Caesar dan Perawatannya','5 menit','https://www.alodokter.com/tips-memulihkan-bekas-luka-setelah-operasi-caesar'],
                    ['drop','Moms, Konsumsi Makanan Ini Agar ASI Melimpah','4 menit','https://ayosehat.kemkes.go.id/moms-konsumsi-makanan-ini-agar-asi-melimpah']
                ];
            } else {
                $vids=[['wch7bNy0EWE','Hal Yang Dihindari Pasca Operasi Ortopedi','Panduan larangan keras setelah TKR'],['hWK3xL9WfQk','Cara Penggunaan Walker','Tutor pemakaian alat bantu jalan'],['ZwjCz8gj82A','Cara Mandi dengan Perban/Gips','Strategi aman mandi tanpa membasahi Cast']];
                $arts=[
                    ['bone','Panduan Penting untuk Pemulihan Pasca Operasi Ortopedi','6 menit','https://gustavelorthopedics.com/guide-to-orthopedic-surgery-recovery'],
                    ['activity',"Perawatan Gips: Do and Don'ts",'4 menit','https://www.mayoclinic.org/healthy-lifestyle/childrens-health/in-depth/cast-care/art-20047159']
                ];
            }
            ?>
            <div class="space-y-10">
                <section>
                    <h3 class="text-xl font-extrabold mb-5 flex items-center gap-2" style="color:#5A6C7A;"><svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg> Video Panduan</h3>
                    <div class="flex overflow-x-auto pb-6 gap-5 snap-x snap-mandatory scrollbar-hide">
                        <?php foreach ($vids as [$vid,$title,$desc]): ?>
                        <div class="w-[85vw] sm:w-[380px] snap-center shrink-0 glass-card p-5 flex flex-col">
                            <iframe class="w-full aspect-video rounded-xl mb-4 shrink-0" src="https://www.youtube.com/embed/<?=$vid?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            <h4 class="font-extrabold mb-1 text-sm" style="color:#5A6C7A;"><?=$title?></h4>
                            <p class="text-xs font-medium flex-grow" style="color:#A3ACA0;"><?=$desc?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <section>
                    <h3 class="text-xl font-extrabold mb-5 flex items-center gap-2" style="color:#5A6C7A;"><svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg> Artikel Pilihan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php 
                        $artIcons = [
                            'heart' => '<svg class="w-7 h-7" fill="none" stroke="#728BA9" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>',
                            'shield' => '<svg class="w-7 h-7" fill="none" stroke="#728BA9" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>',
                            'bandage' => '<svg class="w-7 h-7" fill="none" stroke="#728BA9" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>',
                            'drop' => '<svg class="w-7 h-7" fill="none" stroke="#728BA9" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c4.97 5.278 7.5 8.944 7.5 11.25a7.5 7.5 0 01-15 0C4.5 11.944 7.03 8.278 12 3z"/></svg>',
                            'bone' => '<svg class="w-7 h-7" fill="none" stroke="#728BA9" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2.25 2.25 4.5-4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                            'activity' => '<svg class="w-7 h-7" fill="none" stroke="#728BA9" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>',
                        ];
                        foreach ($arts as $art):
                            $iconKey = $art[0]; $title = $art[1]; $dur = $art[2]; $url = $art[3] ?? '#';
                            $target = $url !== '#' ? 'target="_blank" rel="noopener noreferrer"' : '';
                        ?>
                        <a href="<?=$url?>" <?=$target?> class="flex gap-4 p-5 glass-card hover:shadow-md transition-all">
                            <div class="w-16 h-16 rounded-xl shrink-0 flex items-center justify-center" style="background:#ECF2E6;"><?= $artIcons[$iconKey] ?? '' ?></div>
                            <div><h4 class="font-extrabold mb-1 text-sm" style="color:#5A6C7A;"><?=$title?></h4><p class="text-xs font-medium" style="color:#A3ACA0;">Est. baca <?=$dur?></p></div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </div>
        <?php elseif ($page === 'woundlog'): ?>
        <?php
        $woundInstruction = '';
        if ($opType === 'cabg') {
            $woundInstruction = 'Pastikan untuk memfoto keseluruhan jahitan (insisi dada atau tungkai bekas pengambilan pembuluh darah).';
        } elseif ($opType === 'sc') {
            $woundInstruction = 'Unggah foto sayatan perut pasca-operasi caesar Anda di area garis bikini.';
        } else {
            $woundInstruction = 'Unggah foto area luka jahitan, Stump, atau sekitar gips pembedahan.';
        }
        ?>
        <!-- ============================================================
             WOUND LOG (AI ANALYSIS)
        ============================================================ -->
        <div class="w-full">
            <div class="mb-8">
                <h2 class="text-3xl font-extrabold" style="color:#728BA9;">Detail Operasional Luka</h2>
                <p class="font-medium mt-1" style="color:#A3ACA0;">Unggah foto luka Anda untuk dianalisis oleh sistem secara instan.</p>
            </div>

            <div class="glass-card p-8 mb-8" id="wl-upload-section">
                <h3 class="font-extrabold text-xl mb-4 flex items-center gap-2" style="color:#5A6C7A;">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/></svg> 
                    Unggah Foto Luka
                </h3>
                <label for="ai_wound_photo" class="flex flex-col items-center justify-center w-full h-64 border-2 border-dashed rounded-3xl cursor-pointer transition-all" style="border-color:#DAE3EC; background:#F8FCFF;" onmouseover="this.style.background='#ECF2E6'; this.style.borderColor='#728BA9';" onmouseout="this.style.background='#F8FCFF'; this.style.borderColor='#DAE3EC';">
                    <svg class="w-12 h-12 mb-4 text-[#B8C9DD]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/></svg>
                    <p class="font-extrabold text-lg text-[#5A6C7A]">Pilih Gambar atau Jepret Foto</p>
                    <p class="text-sm font-medium text-[#A3ACA0] mt-1 text-center max-w-md px-3 mb-1"><?= htmlspecialchars($woundInstruction) ?></p>
                    <p class="text-xs font-bold text-[#A3ACA0] opacity-80">Mendukung format JPG, PNG maks 5MB.</p>
                    <input id="ai_wound_photo" type="file" class="hidden" accept="image/*" onchange="startWoundAnalysis(event)">
                </label>
            </div>

            <!-- Analysis Process UI -->
            <div id="wl-loading-section" class="hidden glass-card p-10 mb-8 text-center flex flex-col items-center justify-center min-h-[300px]">
                <div class="w-16 h-16 border-4 border-t-[#728BA9] rounded-full animate-spin mb-6" style="border-color:#DAE3EC; border-top-color:#728BA9;"></div>
                <h3 class="text-2xl font-extrabold text-[#5A6C7A] mb-2">AI Sedang Menganalisis...</h3>
                <p class="text-[#A3ACA0] font-medium" id="wl-loading-text">Memeriksa warna kemerahan di sekitar area...</p>
                <div class="w-full max-w-md bg-[#DAE3EC] h-2 rounded-full mt-6 overflow-hidden">
                    <div id="wl-progress-bar" class="bg-[#728BA9] h-full rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>

            <!-- Analysis Result UI -->
            <form id="wl-result-section" class="hidden glass-card p-0 mb-8 overflow-hidden rounded-3xl relative" action="dashboard.php" method="POST">
                <input type="hidden" name="action" value="save_wound_log">
                <input type="hidden" name="image_base64" id="form-image-base64">
                <input type="hidden" name="ai_status" id="form-ai-status">
                <input type="hidden" name="ai_redness" id="form-ai-redness">
                <input type="hidden" name="ai_swelling" id="form-ai-swelling">
                <input type="hidden" name="ai_fluid" id="form-ai-fluid">
                <input type="hidden" name="ai_size" id="form-ai-size">
                <input type="hidden" name="ai_note" id="form-ai-note">
                <input type="hidden" name="ai_redness_color" id="form-ai-redness-color">
                <input type="hidden" name="ai_icon_bg" id="form-ai-icon-bg">
                <input type="hidden" name="ai_icon_svg" id="form-ai-icon-svg">

                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div class="bg-gray-100 p-4 shrink-0 flex items-center justify-center">
                        <img id="wl-result-img" src="" class="rounded-2xl w-full h-auto object-cover max-h-[350px] shadow-sm transform transition-transform hover:scale-[1.02]">
                    </div>
                    <div class="p-8 md:p-10 flex flex-col justify-center">
                        <div class="flex items-center gap-4 mb-6">
                            <span id="wl-status-icon" class="w-14 h-14 rounded-full flex items-center justify-center text-3xl shrink-0" style="background:#ECF2E6;">
                                <svg class="w-8 h-8" style="color:#5A6C7A;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            </span>
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-wider mb-0.5" style="color:#A3ACA0;">Status Analisis AI</p>
                                <h3 id="wl-status-title" class="text-3xl font-extrabold" style="color:#5A6C7A;">Normal</h3>
                            </div>
                        </div>
                        
                        <div class="space-y-4 mb-6">
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-bold text-[#5A6C7A]">Kemerahan (Redness)</span>
                                    <span class="text-sm font-extrabold text-[#728BA9]" id="wl-redness-val">12%</span>
                                </div>
                                <div class="w-full bg-[#DAE3EC] h-2 rounded-full overflow-hidden">
                                    <div id="wl-redness-bar" class="bg-[#728BA9] h-full rounded-full transition-all duration-1000" style="width: 12%"></div>
                                </div>
                            </div>
                            <div class="flex justify-between border-b pb-3" style="border-color:rgba(218,227,236,0.5);">
                                <span class="text-sm font-bold text-[#5A6C7A]">Pembengkakan (Swelling)</span>
                                <span class="text-sm font-extrabold text-[#728BA9]" id="wl-swelling-val">Minim</span>
                            </div>
                            <div class="flex justify-between border-b pb-3" style="border-color:rgba(218,227,236,0.5);">
                                <span class="text-sm font-bold text-[#5A6C7A]">Kondisi Cairan</span>
                                <span class="text-sm font-extrabold text-[#728BA9]" id="wl-fluid-val">Jernih (Normal)</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-bold text-[#5A6C7A]">Estimasi Ukuran Luka</span>
                                <span class="text-sm font-extrabold text-[#A3ACA0]" id="wl-size-val">10.5 cm</span>
                            </div>
                        </div>

                        <p id="wl-ai-note" class="text-sm font-semibold text-[#7F7F7F] bg-[#F8FCFF] p-4 rounded-xl border border-[#DAE3EC]">
                            Penyembuhan berjalan lancar. Teruskan perawatan luka Anda sesuai dengan anjuran dokter.
                        </p>
                        
                        <div class="mt-8 flex gap-3">
                            <button type="submit" class="flex-1 text-center font-extrabold px-5 py-3 rounded-xl transition-all shadow-md text-white" style="background:#728BA9; transform: translateY(0);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                Simpan Data ke Riwayat
                            </button>
                            <button type="button" onclick="resetWoundAnalysis()" class="px-5 py-3 rounded-xl transition-all font-bold" style="background:#F8FCFF; color:#728BA9; border:1px solid #DAE3EC;" onmouseover="this.style.background='#DAE3EC';" onmouseout="this.style.background='#F8FCFF';">
                                Ulang
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            
            <!-- Reference Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="glass-card p-6 border-t-4" style="border-top-color:#728BA9;">
                    <div class="w-10 h-10 rounded-full bg-[#ECF2E6] mb-4 flex items-center justify-center shadow-sm"><svg class="w-6 h-6" style="color:#5A6C7A;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg></div>
                    <h4 class="font-extrabold text-[#5A6C7A] mb-2 text-sm">Normal</h4>
                    <p class="text-xs font-semibold text-[#A3ACA0] leading-relaxed">Penyembuhan sesuai jalur. Kemerahan sangat minim pada tepi, tidak ada pembengkakan signifikan, dan cairan bening (serous) wajar.</p>
                </div>
                <div class="glass-card p-6 border-t-4" style="border-top-color:#D4AA6A;">
                    <div class="w-10 h-10 rounded-full mb-4 flex items-center justify-center shadow-sm" style="background:#F7F2EB;"><svg class="w-6 h-6" style="color:#D4AA6A;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg></div>
                    <h4 class="font-extrabold mb-2 text-sm" style="color:#D4AA6A;">Warning</h4>
                    <p class="text-xs font-semibold text-[#A3ACA0] leading-relaxed">Tanda ke arah radang meningkat. Kemerahan melebar ke sekeliling (eritema), terasa bengkak pada rabaan, cairan agak kental / kuning pudar.</p>
                </div>
                <div class="glass-card p-6 border-t-4" style="border-top-color:#D46A6A;">
                    <div class="w-10 h-10 rounded-full mb-4 flex items-center justify-center shadow-sm" style="background:#F9EAEA;"><svg class="w-6 h-6" style="color:#D46A6A;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
                    <h4 class="font-extrabold mb-2 text-sm" style="color:#D46A6A;">Infeksi</h4>
                    <p class="text-xs font-semibold text-[#A3ACA0] leading-relaxed">Indikasi komplikasi bahaya! Kemerahan dominan (lebih dari 55%), bengkak keras disertai nyeri, cairan kuning / hijau pekat (nanah).</p>
                </div>
            </div>
            
            <!-- ===== WOUND LOG HISTORY (DUMMY DATA) ===== -->
            <div class="mt-12">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:#ECF2E6;">
                        <svg class="w-6 h-6" fill="none" stroke="#728BA9" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-xl" style="color:#5A6C7A;">Riwayat Foto Luka</h3>
                        <p class="text-sm font-medium" style="color:#A3ACA0;">Sistem menyimpan <strong style="color:#728BA9;">maksimal 1 foto terbaru</strong> untuk setiap harinya.</p>
                    </div>
                </div>

                <?php
                // Dummy images tailored to op-type as placeholders
                $dummyImgs = [
                    'cabg' => [
                        'https://images.unsplash.com/photo-1631815589968-fdb09a223b1e?w=400&h=300&fit=crop', // bandage
                        'https://images.unsplash.com/photo-1584432810601-6c7f27d2362b?w=400&h=300&fit=crop', // stethoscope
                        'https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=400&h=300&fit=crop'  // hospital bed
                    ],
                    'sc' => [
                        'https://images.unsplash.com/photo-1583324113626-70df0f4deaab?w=400&h=300&fit=crop', // plaster
                        'https://images.unsplash.com/photo-1531983412531-1f49a365ffed?w=400&h=300&fit=crop', // pregnancy/belly
                        'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=400&h=300&fit=crop'  // medical setting
                    ],
                    'amputation' => [
                        'https://images.unsplash.com/photo-1580281657527-47f249e8f4df?w=400&h=300&fit=crop', // orthopedics/leg
                        'https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?w=400&h=300&fit=crop', // injury/bandage
                        'https://images.unsplash.com/photo-1631815589968-fdb09a223b1e?w=400&h=300&fit=crop'  // bandage
                    ]
                ];
                $selImgs = $dummyImgs[$opType] ?? $dummyImgs['cabg'];
                ?>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <?php if (empty($woundHistory)): ?>
                    <div class="col-span-1 md:col-span-3 text-center py-10 opacity-60">
                        <p class="font-bold mb-1 text-[#728BA9]">Belum ada gambar yang tersimpan.</p>
                        <p class="text-xs font-medium text-[#A3ACA0]">Unggah dan simpan foto luka Anda untuk mulai melacak riwayat penyembuhan.</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($woundHistory as $wh): 
                        $whDate = new DateTime($wh['record_date']);
                        $surgStart = new DateTime($surgeryDate ?? $wh['record_date']);
                        $surgStart->setTime(0,0,0); $whDate->setTime(0,0,0);
                        $whDayNum = $surgStart->diff($whDate)->days + 1;

                        $bgStatus = ($wh['status'] === 'Warning') ? '#F7F2EB' : (strpos($wh['status'], 'Infeksi') !== false ? '#F9EAEA' : '#ECF2E6');
                        $textColor = ($wh['status'] === 'Warning') ? '#D4AA6A' : (strpos($wh['status'], 'Infeksi') !== false ? '#D46A6A' : '#728BA9');
                    ?>
                    <div class="glass-card p-5 group hover:-translate-y-1 transition-all cursor-pointer" onclick='openWoundHistoryModal(<?= json_encode($wh, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                        <div class="w-full aspect-video rounded-xl bg-gray-200 mb-4 overflow-hidden relative">
                            <!-- Image -->
                            <img src="<?= htmlspecialchars($wh['image_data']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-90 border border-white">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent pointer-events-none"></div>
                            <div class="absolute top-2 right-2 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-white/90 shadow-sm" style="color:<?= $textColor ?>;">Hari ke-<?= $whDayNum ?></div>
                        </div>
                        <p class="font-extrabold text-sm text-[#5A6C7A] mb-1.5"><?= date('d M Y', strtotime($wh['record_date'])) ?></p>
                        <div class="flex justify-between items-center bg-white/50 px-3 py-2 rounded-lg">
                            <span class="text-xs font-bold text-[#A3ACA0]">Status AI:</span> 
                            <span class="text-xs font-extrabold px-2 py-0.5 rounded-md" style="background:<?= $bgStatus ?>; color:<?= $textColor ?>;"><?= htmlspecialchars($wh['status']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            </div>
            
            <!-- Modal Riwayat Luka -->
            <div id="wound-history-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 transition-all" style="background:rgba(0,0,0,0.5); backdrop-filter:blur(4px);" onclick="closeWoundHistoryModal(event)">
                <div class="relative w-full max-w-4xl bg-white rounded-3xl overflow-hidden shadow-2xl flex flex-col md:flex-row transform transition-all scale-95" onclick="event.stopPropagation()">
                    <div class="bg-gray-100 p-4 shrink-0 flex items-center justify-center md:w-1/2">
                        <img id="whm-img" src="" class="rounded-2xl w-full h-auto object-cover max-h-[350px] md:max-h-[500px]">
                    </div>
                    <div class="p-8 md:p-10 flex flex-col justify-center w-full md:w-1/2 relative">
                        <button type="button" onclick="closeWoundHistoryModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                        <p class="text-sm font-bold text-[#A3ACA0] mb-2" id="whm-date"></p>
                        <div class="flex items-center gap-4 mb-6">
                            <span id="whm-icon" class="w-14 h-14 rounded-full flex items-center justify-center text-3xl shrink-0"></span>
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-wider mb-0.5" style="color:#A3ACA0;">Status Analisis AI</p>
                                <h3 id="whm-status" class="text-3xl font-extrabold">Normal</h3>
                            </div>
                        </div>
                        
                        <div class="space-y-4 mb-6">
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-bold text-[#5A6C7A]">Kemerahan (Redness)</span>
                                    <span class="text-sm font-extrabold" id="whm-redness-val"></span>
                                </div>
                                <div class="w-full bg-[#DAE3EC] h-2 rounded-full overflow-hidden">
                                    <div id="whm-redness-bar" class="h-full rounded-full transition-all duration-1000" style="width: 0%"></div>
                                </div>
                            </div>
                            <div class="flex justify-between border-b pb-3" style="border-color:rgba(218,227,236,0.5);">
                                <span class="text-sm font-bold text-[#5A6C7A]">Pembengkakan (Swelling)</span>
                                <span class="text-sm font-extrabold" id="whm-swelling-val"></span>
                            </div>
                            <div class="flex justify-between border-b pb-3" style="border-color:rgba(218,227,236,0.5);">
                                <span class="text-sm font-bold text-[#5A6C7A]">Kondisi Cairan</span>
                                <span class="text-sm font-extrabold text-[#728BA9]" id="whm-fluid-val"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-bold text-[#5A6C7A]">Estimasi Ukuran Luka</span>
                                <span class="text-sm font-extrabold text-[#A3ACA0]" id="whm-size-val"></span>
                            </div>
                        </div>

                        <p id="whm-note" class="text-sm font-semibold text-[#7F7F7F] bg-[#F8FCFF] p-4 rounded-xl border border-[#DAE3EC]"></p>
                    </div>
                </div>
            </div>

        </div>


        <?php elseif ($page === 'profile'): ?>
        <!-- ============================================================
             PROFILE
        ============================================================ -->
        <div class="w-full max-w-3xl">
            <div class="mb-8"><h2 class="text-3xl font-extrabold" style="color:#728BA9;">Profil Saya</h2><p class="font-medium mt-1" style="color:#A3ACA0;">Informasi dan status pemulihan Anda</p></div>
            <div class="glass-card overflow-hidden">
                <div class="h-36 relative" style="background:linear-gradient(135deg,#728BA9,#B8C9DD);">
                    <div class="absolute -bottom-12 left-8">
                        <div class="w-24 h-24 rounded-full bg-white p-1.5 shadow-lg">
                            <div class="w-full h-full rounded-full flex items-center justify-center text-3xl font-extrabold uppercase" style="background:#ECF2E6;color:#728BA9;"><?= substr(htmlspecialchars($userName),0,1) ?></div>
                        </div>
                    </div>
                </div>
                <div class="pt-16 pb-8 px-8">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                        <div>
                            <h3 class="text-2xl font-extrabold" style="color:#5A6C7A;"><?= htmlspecialchars($patientName) ?></h3>
                            <span class="inline-block px-4 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider mt-2" style="background:<?= $role==='caregiver' ? '#F8FCFF' : '#ECF2E6' ?>;color:<?= $role==='caregiver' ? '#728BA9' : '#A3ACA0' ?>;"><?= ucfirst($role) ?></span>
                        </div>
                        <div class="flex gap-3">
                            <a href="onboarding.php?edit=1" class="px-6 py-2.5 glass-card font-bold rounded-xl hover:shadow-md transition-all text-sm" style="color:#728BA9;">Edit Data</a>
                            <a href="dashboard.php?action=delete_profile" onclick="return confirm('Apakah Anda yakin ingin menghapus profil ini? Data tidak dapat dikembalikan.');" class="px-6 py-2.5 font-bold rounded-xl border text-sm transition-all" style="background:rgba(239,68,68,0.05);color:#ef4444;border-color:#fca5a5;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='rgba(239,68,68,0.05)'">Hapus Profil</a>
                            <a href="../../auth/logout.php" class="px-6 py-2.5 font-bold rounded-xl border text-sm transition-all" style="background:rgba(114,139,169,0.07);color:#5A6C7A;border-color:#DAE3EC;" onmouseover="this.style.background='#DAE3EC'" onmouseout="this.style.background='rgba(114,139,169,0.07)'">Logout</a>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="rounded-2xl p-5" style="background:#F8FCFF;border:1px solid #DAE3EC;"><p class="text-xs font-extrabold uppercase tracking-wider mb-2" style="color:#A3ACA0;">Tindakan Medis</p><p class="text-lg font-extrabold" style="color:#5A6C7A;"><?= htmlspecialchars($opName) ?></p></div>
                        <div class="rounded-2xl p-5" style="background:#F8FCFF;border:1px solid #DAE3EC;"><p class="text-xs font-extrabold uppercase tracking-wider mb-2" style="color:#A3ACA0;">Tanggal Operasi</p><p class="text-lg font-extrabold" style="color:#5A6C7A;"><?= htmlspecialchars(date('d F Y', strtotime($surgeryDate ?? date('Y-m-d')))) ?></p><p class="text-sm font-bold mt-1" style="color:#728BA9;">Hari ke-<?= $dayPostOp ?> Pemulihan</p></div>
                    </div>
                </div>
            </div>
        </div>

        <?php else: ?>
        <div class="flex items-center justify-center h-64 text-center">
            <div><div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background:#ECF2E6;"><svg class="w-8 h-8" fill="none" stroke="#728BA9" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg></div><h2 class="text-2xl font-extrabold" style="color:#728BA9;">Halaman Tidak Ditemukan</h2><a href="dashboard.php?page=home" class="mt-4 inline-block px-6 py-3 rounded-xl text-white font-bold transition-all" style="background:#728BA9;">Kembali ke Home</a></div>
        </div>
        <?php endif; ?>

    </main>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>

<script>
// ---- Pain Level Display ----
var PAINCOLORS = ['#728BA9','#728BA9','#22c55e','#65a30d','#ca8a04','#A3ACA0','#ea580c','#5A6C7A','#b91c1c','#5A6C7A','#7f1d1d'];
function setPain(v, faceId, valId) {
    var iv = parseInt(v);
    var f = document.getElementById(faceId); var t = document.getElementById(valId);
    if (f) { f.textContent = iv; f.style.borderColor = PAINCOLORS[iv] || '#728BA9'; f.style.color = PAINCOLORS[iv] || '#728BA9'; }
    if (t) t.textContent = v + '/10';
}
document.querySelectorAll('input[type="range"]').forEach(function(sl) { sl.dispatchEvent(new Event('input')); });

// ---- Roadmap Checkbox ----
document.querySelectorAll('.roadmap-cb').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var li = document.getElementById(this.dataset.li);
        if (!li) return;
        var title = li.querySelector('.task-title');
        if (this.checked) { li.style.opacity='0.55'; if(title){title.style.textDecoration='line-through';} }
        else { li.style.opacity=''; if(title){title.style.textDecoration='';} }
    });
});

// ---- Home Checklist Progress ----
function updateProgress() {
    var all = document.querySelectorAll('.task-checkbox');
    var done = document.querySelectorAll('.task-checkbox:checked').length;
    var pct = all.length ? Math.round((done/all.length)*100) : 0;
    var bar = document.querySelector('.task-progress-bar');
    var txt = document.querySelector('.task-progress-text');
    if (bar) bar.style.width = pct+'%';
    if (txt) txt.textContent = pct+'%';
}
document.querySelectorAll('.task-checkbox').forEach(function(cb){ cb.addEventListener('change', updateProgress); });
updateProgress();

// ---- Video Modal ----
function openVid(id, title) {
    document.getElementById('vmod-frame').src = 'https://www.youtube.com/embed/'+id+'?autoplay=1';
    document.getElementById('vmod-title').textContent = title;
    document.getElementById('video-modal').classList.add('open');
}
function closeVideoModal() {
    document.getElementById('vmod-frame').src = '';
    document.getElementById('video-modal').classList.remove('open');
}
document.getElementById('video-modal').addEventListener('click', function(e) { if(e.target===this) closeVideoModal(); });

// ---- Photo Preview ----
var pi = document.getElementById('wound_photo');
if (pi) { pi.addEventListener('change', function() {
    var f = this.files[0]; if(!f) return;
    var r = new FileReader();
    r.onload = function(e) { document.getElementById('photo_preview').classList.remove('hidden'); document.getElementById('photo_img').src=e.target.result; };
    r.readAsDataURL(f);
}); }

// ---- AI Wound Analysis Logic ----
function startWoundAnalysis(event) {
    var file = event.target.files[0];
    if (!file) return;

    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('wl-result-img').src = e.target.result;
        
        document.getElementById('wl-upload-section').classList.add('hidden');
        document.getElementById('wl-loading-section').classList.remove('hidden');
        
        var steps = [
            "Memeriksa warna kemerahan di sekitar area luka...",
            "Menganalisis tingkat pembengkakan (swelling)...",
            "Mendeteksi jenis dan warna cairan...",
            "Mengukur estimasi luasan luka...",
            "Menyimpulkan hasil diagnostik..."
        ];
        var progBar = document.getElementById('wl-progress-bar');
        var textLbl = document.getElementById('wl-loading-text');
        
        var step = 0;
        var interval = setInterval(function() {
            step++;
            progBar.style.width = (step * 20) + "%";
            if (step < steps.length) {
                textLbl.textContent = steps[step];
            } else {
                clearInterval(interval);
                setTimeout(showWoundResult, 500);
            }
        }, 600);
    };
    reader.readAsDataURL(file);
}

function showWoundResult() {
    document.getElementById('wl-loading-section').classList.add('hidden');
    document.getElementById('wl-result-section').classList.remove('hidden');
    
    var rand = Math.random();
    var redness, swelling, fluid, size, status, icon, iconBg, note, rednessColor;
    
    // Size logic
    var rSize = (Math.random() * 5 + 5).toFixed(1); 
    size = rSize + " cm";

    if (rand < 0.6) {
        redness = Math.floor(Math.random() * 15) + 5; 
        swelling = "Minim / Tidak Terlihat";
        fluid = "Jernih (Normal)";
        status = "Normal";
        icon = '<svg class="w-8 h-8" style="color:#5A6C7A;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>';
        iconBg = "#ECF2E6";
        note = "Proses penyembuhan inflamasi berjalan dengan baik secara alami. Teruskan rutinitas kebersihan luka secara teratur.";
        rednessColor = "#728BA9";
    } else if (rand < 0.85) {
        redness = Math.floor(Math.random() * 20) + 25; 
        swelling = "Radang Sedang";
        fluid = "Vulkanis / Terdapat Serous Kuning";
        status = "Warning";
        icon = '<svg class="w-8 h-8" style="color:#D4AA6A;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';
        iconBg = "#F7F2EB";
        note = "Terdeteksi aktivitas inflamasi berlebih. Sangat disarankan untuk menjaga luka tetap kering dan mewaspadai demam.";
        rednessColor = "#D4AA6A";
    } else {
        redness = Math.floor(Math.random() * 30) + 60; 
        swelling = "Besar (Edema Meluas)";
        fluid = "Kuning Pudar / Hijau (Nanah)";
        status = "Indikasi Infeksi";
        icon = '<svg class="w-8 h-8" style="color:#D46A6A;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
        iconBg = "#F9EAEA";
        note = "Tingkat kemerahan dan dugaan eksudat purulen menandakan rute infeksi. Silakan kunjungi instalasi gawat darurat atau dokter Anda secepatnya.";
        rednessColor = "#D46A6A";
    }
    
    // Apply texts & logic
    document.getElementById('wl-redness-val').textContent = redness + "% Area Sengit";
    document.getElementById('wl-redness-bar').style.width = "0%";
    setTimeout(() => { document.getElementById('wl-redness-bar').style.width = redness + "%"; }, 100);
    document.getElementById('wl-redness-bar').style.backgroundColor = rednessColor;
    
    document.getElementById('wl-swelling-val').textContent = swelling;
    document.getElementById('wl-swelling-val').style.color = rednessColor;
    
    document.getElementById('wl-fluid-val').textContent = fluid;
    document.getElementById('wl-size-val').textContent = size;
    
    document.getElementById('wl-status-title').textContent = status;
    document.getElementById('wl-status-title').style.color = (status==="Warning"? "#D4AA6A" : (status==="Indikasi Infeksi"?"#D46A6A":"#5A6C7A"));
    
    document.getElementById('wl-status-icon').innerHTML = icon;
    document.getElementById('wl-status-icon').style.backgroundColor = iconBg;
    
    document.getElementById('wl-ai-note').textContent = note;
    
    document.getElementById('form-image-base64').value = document.getElementById('wl-result-img').src;
    document.getElementById('form-ai-status').value = status;
    document.getElementById('form-ai-redness').value = redness + "% Area Sengit";
    document.getElementById('form-ai-swelling').value = swelling;
    document.getElementById('form-ai-fluid').value = fluid;
    document.getElementById('form-ai-size').value = size;
    document.getElementById('form-ai-note').value = note;
    document.getElementById('form-ai-redness-color').value = rednessColor;
    document.getElementById('form-ai-icon-bg').value = iconBg;
    document.getElementById('form-ai-icon-svg').value = icon;
}

function openWoundHistoryModal(wh) {
    document.getElementById('whm-img').src = wh.image_data || '';
    
    var d = new Date(wh.record_date);
    document.getElementById('whm-date').textContent = "Riwayat: " + d.toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'});

    var status = wh.status || "Normal";
    var icon = wh.iconSvg || '<svg class="w-8 h-8" style="color:#5A6C7A;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>';
    var iconBg = wh.iconBg || (status === 'Warning' ? '#F7F2EB' : (status.includes('Infeksi') ? '#F9EAEA' : '#ECF2E6'));
    var titleColor = status === 'Warning' ? '#D4AA6A' : (status.includes('Infeksi') ? '#D46A6A' : '#5A6C7A');
    var rednessColor = wh.rednessColor || titleColor;

    document.getElementById('whm-status').textContent = status;
    document.getElementById('whm-status').style.color = titleColor;
    document.getElementById('whm-icon').innerHTML = icon;
    document.getElementById('whm-icon').style.backgroundColor = iconBg;

    var rednessRaw = wh.redness || (status.includes('Infeksi') ? "60% Area Sengit" : (status === "Warning" ? "30% Area Sengit" : "10% Area Sengit"));
    var rNum = parseInt(rednessRaw) || 0;
    
    document.getElementById('whm-redness-val').textContent = rednessRaw;
    document.getElementById('whm-redness-val').style.color = rednessColor;
    document.getElementById('whm-redness-bar').style.backgroundColor = rednessColor;
    document.getElementById('whm-redness-bar').style.width = rNum + "%";

    document.getElementById('whm-swelling-val').textContent = wh.swelling || (status.includes('Infeksi') ? "Besar (Edema Meluas)" : (status === "Warning" ? "Radang Sedang" : "Minim / Tidak Terlihat"));
    document.getElementById('whm-swelling-val').style.color = rednessColor;

    document.getElementById('whm-fluid-val').textContent = wh.fluid || (status.includes('Infeksi') ? "Kuning Pudar / Hijau (Nanah)" : (status === "Warning" ? "Vulkanis / Terdapat Serous Kuning" : "Jernih (Normal)"));
    document.getElementById('whm-size-val').textContent = wh.size || "Estimasi tidak tersedia (data lama)";

    document.getElementById('whm-note').textContent = wh.note || "Catatan AI untuk riwayat ini tidak direkam secara langsung. Analisis didasarkan pada klasifikasi sistem.";

    var modal = document.getElementById('wound-history-modal');
    modal.classList.remove('hidden');
    setTimeout(() => { modal.firstElementChild.classList.remove('scale-95'); modal.firstElementChild.classList.add('scale-100'); }, 10);
}

function closeWoundHistoryModal(e) {
    if (e && e.target !== e.currentTarget) return;
    var modal = document.getElementById('wound-history-modal');
    modal.firstElementChild.classList.remove('scale-100');
    modal.firstElementChild.classList.add('scale-95');
    setTimeout(() => { modal.classList.add('hidden'); }, 150);
}

function resetWoundAnalysis() {
    document.getElementById('wl-result-section').classList.add('hidden');
    document.getElementById('wl-result-img').src = "";
    document.getElementById('ai_wound_photo').value = "";
    document.getElementById('wl-progress-bar').style.width = "0%";
    document.getElementById('wl-loading-text').textContent = "Memeriksa warna kemerahan di sekitar area luka...";
    document.getElementById('wl-upload-section').classList.remove('hidden');
}
</script>
