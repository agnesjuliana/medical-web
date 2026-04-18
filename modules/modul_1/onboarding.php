<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
require_once __DIR__ . '/../../config/database.php';

requireLogin();
startSession();

$user = getCurrentUser();

$pdo = getDBConnection();
$isOnboarded = false;

try {
    $stmt = $pdo->prepare("SELECT id FROM user_onboarding WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user['id']]);
    if ($stmt->fetch()) {
        $isOnboarded = true;
    }
} catch (PDOException $e) {
    // Table doesn't exist yet — treat as not onboarded
}

if ($isOnboarded) {
    header('Location: dashboard.php');
    exit;
}

// Handle form submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? null;
    $fullName = $_POST['fullName'] ?? null;
    $patientName = $_POST['patientName'] ?? null;
    $operationType = $_POST['operationType'] ?? null;
    $surgeryDate = $_POST['surgeryDate'] ?? null;
    $painLevel = isset($_POST['painLevel']) ? (int)$_POST['painLevel'] : 0;
    $mobility = $_POST['mobility'] ?? null;
    $symptoms = $_POST['symptoms'] ?? '';

    try {
        $stmtInsert = $pdo->prepare("INSERT INTO user_onboarding (user_id, role, full_name, patient_name, operation_type, surgery_date, pain_level, mobility, symptoms) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtInsert->execute([$user['id'], $role, $fullName, $patientName, $operationType, $surgeryDate, $painLevel, $mobility, $symptoms]);

        header('Location: dashboard.php');
        exit;
    } catch (PDOException $e) {
        die("Error saving onboarding data: " . $e->getMessage());
    }
}

$pageTitle = 'Onboarding - RuangPulih';
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
    body, body * {
        font-family: 'Poppins', sans-serif !important;
    }
    
    .bg-app {
        background-color: #F8FCFF;
        background-image: radial-gradient(circle at top left, #ECF2E6 0%, #F8FCFF 40%);
    }

    .step-enter {
        animation: slideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    
    /* Range Slider Styling */
    input[type=range] {
        -webkit-appearance: none;
        width: 100%;
        background: transparent;
    }
    input[type=range]::-webkit-slider-thumb {
        -webkit-appearance: none;
        height: 24px;
        width: 24px;
        border-radius: 50%;
        background: #728BA9;
        cursor: pointer;
        margin-top: -8px;
        box-shadow: 0 4px 10px rgba(114, 139, 169, 0.4);
    }
    input[type=range]::-webkit-slider-thumb:hover {
        transform: scale(1.1);
    }
    input[type=range]::-webkit-slider-runnable-track {
        width: 100%;
        height: 8px;
        cursor: pointer;
        background: #DAE3EC;
        border-radius: 4px;
    }
    
    /* Hide all steps by default */
    .step-container > div.step-block {
        display: none;
    }
    .step-container > div.step-block.active {
        display: block;
    }
</style>

<div class="min-h-screen bg-app flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    
    <div class="w-full max-w-5xl">
        <!-- Header & Progress -->
        <div class="mb-8 text-center">
            <div class="flex items-center justify-center gap-2 mb-4">
                <img src="assets/images/logo.png" alt="RuangPulih Logo" class="h-10 opacity-80 filter grayscale">
                <span class="font-extrabold text-2xl tracking-tight leading-none text-[#B8C9DD]">Ruang<span class="text-[#A3ACA0]">Pulih</span></span>
            </div>
            
            <div class="max-w-md mx-auto">
                <div class="flex justify-between text-xs font-bold text-[#A3ACA0] mb-2 uppercase tracking-wider">
                    <span>Step <span id="current-step-text">1</span> of 5</span>
                </div>
                <div class="w-full bg-[#DAE3EC] rounded-full h-2">
                    <div id="progress-bar" class="bg-[#728BA9] h-2 rounded-full transition-all duration-500 ease-out" style="width: 20%"></div>
                </div>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="bg-white rounded-[24px] shadow-[0_15px_50px_rgb(0,0,0,0.05)] p-10 md:p-14 min-h-[500px] flex flex-col border border-white/60 relative overflow-hidden backdrop-blur-sm shadow-xl">
            
            <form id="onboarding-form" action="onboarding.php" method="POST" class="flex-1 flex flex-col step-container justify-between h-full">
                
                <!-- STEP 1: ROLE SELECTION -->
                <div id="step-1" class="step-block active step-enter">
                    <div class="text-center mb-10">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-[#728BA9] mb-3 font-medium">Mulai Pemulihan</h2>
                        <p class="text-[#7F7F7F] font-medium text-lg">Kamu menggunakan RuangPulih sebagai:</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl mx-auto mb-10">
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="role" value="pasien" class="peer sr-only" required>
                            <div class="p-8 rounded-[16px] border-2 border-[#DAE3EC] peer-checked:border-[#728BA9] peer-checked:bg-[#F8FCFF] hover:border-[#B8C9DD] transition-all duration-300 bg-white shadow-sm hover:shadow text-center">
                                <div class="w-16 h-16 mx-auto bg-[#ECF2E6] text-[#A3ACA0] rounded-full flex items-center justify-center mb-6 peer-checked:bg-[#728BA9] peer-checked:text-white transition-colors duration-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <h3 class="text-2xl font-bold text-[#728BA9] mb-3">Pasien</h3>
                                <p class="text-[15px] text-[#7F7F7F] font-medium">Saya menjalani pemulihan pasca operasi secara mandiri.</p>
                            </div>
                        </label>
                        
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="role" value="caregiver" class="peer sr-only">
                            <div class="p-8 rounded-[16px] border-2 border-[#DAE3EC] peer-checked:border-[#728BA9] peer-checked:bg-[#F8FCFF] hover:border-[#B8C9DD] transition-all duration-300 bg-white shadow-sm hover:shadow text-center">
                                <div class="w-16 h-16 mx-auto bg-[#F8FCFF] text-[#B8C9DD] rounded-full flex items-center justify-center mb-6 peer-checked:bg-[#728BA9] peer-checked:text-white transition-colors duration-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                </div>
                                <h3 class="text-2xl font-bold text-[#728BA9] mb-3">Caregiver</h3>
                                <p class="text-[15px] text-[#7F7F7F] font-medium">Saya memantau dan membantu pemulihan pasien.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- STEP 2: PERSONAL INFO -->
                <div id="step-2" class="step-block">
                    <div class="text-center mb-10">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-[#728BA9] mb-3 font-medium">Data Diri</h2>
                        <p class="text-[#7F7F7F] font-medium text-lg">Mari kenalan lebih jauh.</p>
                    </div>
                    
                    <div class="max-w-md mx-auto py-10 w-full mb-10 space-y-8">
                        <div>
                            <label class="block text-[#728BA9] font-bold mb-4 text-lg text-center">Nama Lengkap</label>
                            <input type="text" name="fullName" placeholder="Masukkan nama kamu" class="w-full px-6 py-5 rounded-[16px] border-2 border-[#DAE3EC] focus:border-[#728BA9] focus:outline-none focus:ring-[6px] focus:ring-[#F8FCFF] transition-all text-[#5A6C7A] text-xl shadow-sm placeholder-[#CDCDCD]" required autocomplete="off">
                        </div>
                        <div id="patient-name-field" class="hidden">
                            <label class="block text-[#728BA9] font-bold mb-4 text-lg text-center">Nama Pasien yang Dipantau</label>
                            <input type="text" name="patientName" placeholder="Masukkan nama pasien" class="w-full px-6 py-5 rounded-[16px] border-2 border-[#DAE3EC] focus:border-[#728BA9] focus:outline-none focus:ring-[6px] focus:ring-[#F8FCFF] transition-all text-[#5A6C7A] text-xl shadow-sm placeholder-[#CDCDCD]" autocomplete="off">
                        </div>
                    </div>
                </div>

                <!-- STEP 3: OPERATION SELECTION -->
                <div id="step-3" class="step-block">
                    <div class="text-center mb-10">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-[#728BA9] mb-3">Pemulihan pasca operasi apa?</h2>
                        <p class="text-[#7F7F7F] font-medium text-lg">Roadmap akan disesuaikan secara khusus dengan tindakan medismu.</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto mb-10">
                        <!-- Card 1 -->
                        <label class="relative cursor-pointer">
                            <input type="radio" name="operationType" value="cabg" class="peer sr-only" required>
                            <div class="h-full p-8 rounded-[16px] border-2 border-[#DAE3EC] peer-checked:border-[#A3ACA0] peer-checked:bg-[#ECF2E6] peer-checked:shadow-lg hover:border-[#D1D9CA] transition-all bg-white flex flex-col justify-center items-center text-center">
                                <div class="w-14 h-14 bg-red-50 text-red-500 rounded-xl flex items-center justify-center mb-5 shrink-0 opacity-80">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                </div>
                                <h3 class="text-xl font-extrabold text-[#728BA9] mb-3 leading-tight">Operasi Jantung<br>(CABG)</h3>
                                <p class="text-sm text-[#7F7F7F] font-medium leading-relaxed">Fokus memulihkan pola pernapasan, adaptasi daya tahan jantung, & fungsi area dada.</p>
                            </div>
                        </label>

                        <!-- Card 2 -->
                        <label class="relative cursor-pointer">
                            <input type="radio" name="operationType" value="sc" class="peer sr-only">
                            <div class="h-full p-8 rounded-[16px] border-2 border-[#DAE3EC] peer-checked:border-[#B8C9DD] peer-checked:bg-[#F8FCFF] peer-checked:shadow-lg hover:border-[#DAE3EC] transition-all bg-white flex flex-col justify-center items-center text-center">
                                <div class="w-14 h-14 bg-purple-50 text-purple-400 rounded-xl flex items-center justify-center mb-5 shrink-0 opacity-80">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                </div>
                                <h3 class="text-xl font-extrabold text-[#728BA9] mb-3 leading-tight">Sectio<br>Caesarea</h3>
                                <p class="text-sm text-[#7F7F7F] font-medium leading-relaxed">Fokus terhadap intensitas perdarahan serta meninjau pemulihan luka area dalam.</p>
                            </div>
                        </label>

                        <!-- Card 3 -->
                        <label class="relative cursor-pointer">
                            <input type="radio" name="operationType" value="amputation" class="peer sr-only">
                            <div class="h-full p-8 rounded-[16px] border-2 border-[#DAE3EC] peer-checked:border-[#D1D9CA] peer-checked:bg-[#ECF2E6] peer-checked:shadow-lg hover:border-[#D1D9CA] transition-all bg-white flex flex-col justify-center items-center text-center">
                                <div class="w-14 h-14 bg-orange-50 text-orange-400 rounded-xl flex items-center justify-center mb-5 shrink-0 opacity-80">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                </div>
                                <h3 class="text-xl font-extrabold text-[#728BA9] mb-3 leading-tight">Tindakan<br>Amputasi</h3>
                                <p class="text-sm text-[#7F7F7F] font-medium leading-relaxed">Fokus terhadap penyembuhan luka pasca amputasi, phantom pain, & mobilisasi aman.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- STEP 4: SURGERY DATE -->
                <div id="step-4" class="step-block">
                    <div class="text-center mb-10">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-[#728BA9] mb-3">Kapan operasi dilakukan?</h2>
                        <p class="text-[#7F7F7F] font-medium text-lg">Membantu kami menentukan jadwal harian roadmap kamu.</p>
                    </div>
                    
                    <div class="max-w-md mx-auto py-8 mb-10">
                        <label class="block text-[#728BA9] font-bold mb-4 text-center text-lg">Tanggal Operasi</label>
                        <input type="date" id="surgeryDate" name="surgeryDate" class="w-full px-6 py-5 rounded-[16px] border-2 border-[#DAE3EC] focus:border-[#728BA9] focus:outline-none focus:ring-[6px] focus:ring-[#F8FCFF] transition-all text-[#5A6C7A] text-xl shadow-sm bg-white" required>
                        
                        <div class="mt-8 p-5 rounded-[16px] bg-[#F8FCFF] border-l-4 border-l-[#B8C9DD] flex items-start gap-4">
                            <span class="text-3xl mt-1 opacity-80">🩺</span>
                            <div>
                                <p class="font-extrabold text-[#728BA9] text-base mb-1">Status Pemulihan</p>
                                <p id="day-counter" class="text-[#7F7F7F] font-medium text-[15px] leading-relaxed">Silakan pilih tanggal untuk melihat status hari pemulihan.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 5: CURRENT CONDITION -->
                <div id="step-5" class="step-block">
                    <div class="text-center mb-10">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-[#728BA9] mb-3">Kondisi Saat Ini</h2>
                        <p class="text-[#7F7F7F] font-medium text-lg">Coba ceritakan bagaimana perasaanmu sekarang?</p>
                    </div>
                    
                    <div class="max-w-2xl mx-auto space-y-8 mb-10">
                        <!-- Pain Level -->
                        <div class="bg-[#F8FCFF] p-8 rounded-[16px] border border-[#DAE3EC]">
                            <div class="flex justify-between items-center mb-6">
                                <label class="text-[#728BA9] font-extrabold text-xl">Tingkat Nyeri (0–10)</label>
                                <span id="pain-val" class="font-extrabold text-3xl text-[#728BA9] bg-white w-14 h-14 rounded-full flex items-center justify-center shadow-sm">0</span>
                            </div>
                            <input type="range" name="painLevel" min="0" max="10" value="0" id="pain-slider" class="mb-4">
                            <div class="flex justify-between text-xs font-bold text-[#A3ACA0] uppercase tracking-widest">
                                <span>Tidak Sakit</span>
                                <span>Sangat Sakit</span>
                            </div>
                        </div>

                        <!-- Mobility -->
                        <div>
                            <label class="block text-[#728BA9] font-extrabold mb-5 text-xl">Tingkat Aktivitas Fisik</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <label class="cursor-pointer group">
                                    <input type="radio" name="mobility" value="bedrest" class="peer sr-only" required>
                                    <div class="py-4 px-4 rounded-[12px] border-2 border-[#DAE3EC] text-center peer-checked:border-[#728BA9] peer-checked:bg-[#728BA9] peer-checked:text-white transition-all bg-white text-[#728BA9] font-bold text-base shadow-sm group-hover:bg-[#F8FCFF]">
                                        Bed Rest
                                    </div>
                                </label>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="mobility" value="duduk" class="peer sr-only">
                                    <div class="py-4 px-4 rounded-[12px] border-2 border-[#DAE3EC] text-center peer-checked:border-[#728BA9] peer-checked:bg-[#728BA9] peer-checked:text-white transition-all bg-white text-[#728BA9] font-bold text-base shadow-sm group-hover:bg-[#F8FCFF]">
                                        Mulai Duduk
                                    </div>
                                </label>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="mobility" value="jalan" class="peer sr-only">
                                    <div class="py-4 px-4 rounded-[12px] border-2 border-[#DAE3EC] text-center peer-checked:border-[#728BA9] peer-checked:bg-[#728BA9] peer-checked:text-white transition-all bg-white text-[#728BA9] font-bold text-base shadow-sm group-hover:bg-[#F8FCFF]">
                                        Sudah Bisa Jalan
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Optional Symptoms -->
                        <div>
                            <label class="block text-[#728BA9] font-extrabold mb-3 text-xl">Keluhan Lainnya <span class="text-sm font-medium text-[#CDCDCD] ml-1">(Opsional)</span></label>
                            <textarea name="symptoms" rows="2" placeholder="Contoh: pusing luar biasa, mual, dll" class="w-full px-6 py-4 rounded-[16px] border-2 border-[#DAE3EC] focus:border-[#728BA9] focus:outline-none focus:ring-[6px] focus:ring-[#F8FCFF] transition-all text-[#5A6C7A] text-lg shadow-sm resize-none placeholder-[#CDCDCD]"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="pt-6 mt-auto border-t border-[#F8FCFF] flex justify-between items-center w-full">
                    <button type="button" id="btn-prev" class="hidden px-8 py-4 rounded-full border border-[#DAE3EC] text-[#7F7F7F] hover:bg-[#F8FCFF] transition-all font-bold tracking-wide text-lg">
                        Kembali
                    </button>
                    <div class="ml-auto w-full flex justify-end">
                        <button type="button" id="btn-next" class="px-12 py-4 rounded-full bg-[#B8C9DD] text-white font-extrabold text-xl hover:bg-[#728BA9] transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            Lanjutkan
                        </button>
                        <button type="submit" id="btn-submit" class="hidden px-12 py-4 rounded-full bg-[#A3ACA0] text-white font-extrabold text-xl hover:bg-[#7F7F7F] transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            Mulai Pemulihan
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let currentStep = 1;
    const totalSteps = 5;
    
    const elements = {
        btnNext: document.getElementById('btn-next'),
        btnPrev: document.getElementById('btn-prev'),
        btnSubmit: document.getElementById('btn-submit'),
        progressText: document.getElementById('current-step-text'),
        progressBar: document.getElementById('progress-bar'),
        surgeryDate: document.getElementById('surgeryDate'),
        dayCounter: document.getElementById('day-counter'),
        painSlider: document.getElementById('pain-slider'),
        painVal: document.getElementById('pain-val'),
    };

    // Update Pain Slider value
    elements.painSlider.addEventListener('input', (e) => {
        elements.painVal.textContent = e.target.value;
    });

    // Toggle patient name field based on role selection
    const roleRadios = document.querySelectorAll('input[name="role"]');
    const patientNameField = document.getElementById('patient-name-field');
    roleRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (radio.value === 'caregiver' && radio.checked) {
                patientNameField.classList.remove('hidden');
            } else {
                patientNameField.classList.add('hidden');
            }
        });
    });

    // Update Day Counter dynamically
    elements.surgeryDate.addEventListener('change', (e) => {
        const val = e.target.value;
        if (!val) {
            elements.dayCounter.textContent = "Silakan pilih tanggal untuk melihat status hari pemulihan.";
            return;
        }
        const selectedDate = new Date(val);
        const today = new Date();
        
        // Reset time part to accurately get day difference
        today.setHours(0,0,0,0);
        selectedDate.setHours(0,0,0,0);

        const diffTime = Math.abs(today - selectedDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
        
        if (selectedDate > today) {
            elements.dayCounter.innerHTML = `<span class="text-red-500 font-bold">Tanggal tidak valid.</span> Mohon pilih tanggal masa lalu atau hari ini.`;
            elements.surgeryDate.setCustomValidity('Tanggal operasi tidak boleh di masa depan.');
        } else {
            elements.surgeryDate.setCustomValidity('');
            if (diffDays === 0) {
                elements.dayCounter.innerHTML = `Hari ini adalah <strong class="text-[#728BA9] text-lg">Hari H Operasi</strong>.`;
            } else {
                elements.dayCounter.innerHTML = `Kamu akan memulai <strong class="text-[#728BA9] text-lg">Hari ke-${diffDays + 1} Pemulihan.</strong>`;
            }
        }
    });

    // Form Validation per step
    const validateStep = (step) => {
        const currentContainer = document.getElementById(`step-${step}`);
        const inputs = currentContainer.querySelectorAll('input[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (input.type === 'radio') {
                const name = input.name;
                const checked = document.querySelector(`input[name="${name}"]:checked`);
                if (!checked) isValid = false;
            } else if (!input.value.trim()) {
                isValid = false;
            }
        });

        if (!isValid) {
            const firstInvalid = Array.from(inputs).find(input => {
                if(input.type === 'radio') {
                    return !document.querySelector(`input[name="${input.name}"]:checked`);
                }
                return !input.value.trim();
            });
            if(firstInvalid) firstInvalid.reportValidity();
            return false;
        }

        if (step === 4 && !elements.surgeryDate.checkValidity()) {
             elements.surgeryDate.reportValidity();
             return false;
        }

        return true;
    };

    const updateUI = (fromStep, toStep) => {
        const outContainer = document.getElementById(`step-${fromStep}`);
        const inContainer = document.getElementById(`step-${toStep}`);
        
        outContainer.classList.remove('active', 'step-enter');
        inContainer.classList.add('active', 'step-enter');
        
        elements.progressText.textContent = toStep;
        elements.progressBar.style.width = `${(toStep / totalSteps) * 100}%`;

        if (toStep === 1) {
            elements.btnPrev.classList.add('hidden');
        } else {
            elements.btnPrev.classList.remove('hidden');
        }

        if (toStep === totalSteps) {
            elements.btnNext.classList.add('hidden');
            elements.btnSubmit.classList.remove('hidden');
        } else {
            elements.btnNext.classList.remove('hidden');
            elements.btnSubmit.classList.add('hidden');
        }
    };

    elements.btnNext.addEventListener('click', () => {
        if (!validateStep(currentStep)) return;
        
        if (currentStep < totalSteps) {
            updateUI(currentStep, currentStep + 1);
            currentStep++;
        }
    });

    elements.btnPrev.addEventListener('click', () => {
        if (currentStep > 1) {
            updateUI(currentStep, currentStep - 1);
            currentStep--;
        }
    });

    document.getElementById('onboarding-form').addEventListener('keydown', (e) => {
        // Only trigger 'Enter' to next if it's not text area
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
            if (currentStep < totalSteps) {
                elements.btnNext.click();
            } else {
                elements.btnSubmit.click();
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
