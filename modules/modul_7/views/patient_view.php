<?php
/**
 * Patient View for Dermalyze.AI with WebRTC Camera Support
 */
?>

<style>
    /* Dermalyze AI Custom Premium CSS - Light Theme */
    :root {
        --derm-primary: #00d2ff;
        --derm-secondary: #3a7bd5;
        --derm-dark: #0f172a;
        --derm-card: #ffffff;
        --derm-glass-border: #e2e8f0;
        --derm-glass-bg: #f8fafc;
    }

    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    }

    .derm-container {
        font-family: 'Inter', sans-serif;
        color: #334155;
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 32px;
        padding: 40px;
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
    }

    .derm-container::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: conic-gradient(from 0deg at 50% 50%, rgba(0, 210, 255, 0.05) 0deg, transparent 60deg, transparent 300deg, rgba(0, 210, 255, 0.05) 360deg);
        animation: rotateBg 25s linear infinite;
        pointer-events: none;
    }

    @keyframes rotateBg {
        100% {
            transform: rotate(360deg);
        }
    }

    .derm-header {
        text-align: center;
        margin-bottom: 30px;
        position: relative;
        z-index: 10;
    }

    .derm-title {
        font-size: 2.8rem;
        font-weight: 800;
        background: linear-gradient(to right, var(--derm-secondary), var(--derm-primary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 12px;
        letter-spacing: -0.02em;
    }

    .derm-subtitle {
        color: #64748b;
        font-size: 1.15rem;
    }

    /* Tabs */
    .mode-tabs {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-bottom: 30px;
        position: relative;
        z-index: 10;
    }

    .mode-tab {
        padding: 12px 30px;
        border-radius: 30px;
        background: #f1f5f9;
        color: #64748b;
        font-weight: 700;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.3s;
    }

    .mode-tab.active {
        background: #eff6ff;
        color: var(--derm-secondary);
        border-color: var(--derm-secondary);
        box-shadow: 0 10px 20px rgba(58, 123, 213, 0.15);
    }

    /* Upload Area */
    .upload-area {
        position: relative;
        z-index: 10;
        background: var(--derm-glass-bg);
        border: 2px dashed #cbd5e1;
        border-radius: 24px;
        padding: 60px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .upload-area:hover {
        background: #eff6ff;
        border-color: var(--derm-secondary);
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(58, 123, 213, 0.1);
    }

    .upload-icon {
        font-size: 4rem;
        color: var(--derm-secondary);
        margin-bottom: 15px;
    }

    .upload-area h3 {
        color: #1e293b;
        margin-bottom: 10px;
    }

    /* Camera Area */
    .camera-area {
        display: none;
        /* hidden by default */
        position: relative;
        z-index: 10;
        background: #0f172a;
        border-radius: 24px;
        overflow: hidden;
        text-align: center;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        padding-bottom: 20px;
    }

    #webcamVideo {
        width: 100%;
        max-height: 400px;
        object-fit: cover;
        background: #000;
        transform: scaleX(-1);
        /* mirror effect */
    }

    .capture-btn {
        background: linear-gradient(135deg, #00d2ff, #3a7bd5);
        color: white;
        padding: 15px 40px;
        border-radius: 30px;
        font-size: 1.1rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
        margin-top: -30px;
        position: relative;
        z-index: 5;
        box-shadow: 0 10px 25px rgba(58, 123, 213, 0.4);
        border: 4px solid #0f172a;
        transition: transform 0.2s;
    }

    .capture-btn:hover {
        transform: scale(1.05);
    }

    .cam-error {
        color: #ef4444;
        padding: 30px;
        background: #fef2f2;
        border-radius: 12px;
        margin: 20px;
        display: none;
    }

    /* Scanner Animation */
    .scanner-overlay {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.85);
        z-index: 30;
        border-radius: 20px;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(8px);
    }

    .scan-line {
        width: 80%;
        height: 6px;
        background: var(--derm-secondary);
        box-shadow: 0 0 15px var(--derm-primary), 0 0 30px var(--derm-primary);
        animation: scanAnim 2s infinite ease-in-out alternate;
        margin-bottom: 20px;
        border-radius: 10px;
    }

    @keyframes scanAnim {
        0% {
            transform: translateY(-60px);
            opacity: 0.3;
        }

        50% {
            opacity: 1;
        }

        100% {
            transform: translateY(60px);
            opacity: 0.3;
        }
    }

    .scan-text {
        color: var(--derm-secondary);
        font-weight: 700;
        letter-spacing: 1px;
        animation: pulse 1.5s infinite;
        font-size: 1.1rem;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.6;
        }
    }

    /* Results Dashboard */
    .results-dashboard {
        display: none;
        position: relative;
        z-index: 10;
        margin-top: 40px;
        animation: fadeIn 0.8s ease forwards;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .derm-glass-card {
        background: #ffffff;
        border: 1px solid var(--derm-glass-border);
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        margin-bottom: 24px;
    }

    .severity-badge {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 30px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.85rem;
    }

    .severity-Mild {
        background: #ecfdf5;
        color: #10b981;
    }

    .severity-Moderate {
        background: #fffbeb;
        color: #f59e0b;
    }

    .severity-Severe {
        background: #fef2f2;
        color: #ef4444;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-top: 20px;
    }

    .stat-box {
        background: #f8fafc;
        border-radius: 16px;
        padding: 20px 16px;
        text-align: center;
        border: 1px solid var(--derm-glass-border);
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--derm-dark);
        margin-bottom: 4px;
    }

    .ingredient-tag {
        display: inline-block;
        background: linear-gradient(135deg, var(--derm-secondary), var(--derm-primary));
        color: white;
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 0.95rem;
        margin-right: 10px;
        margin-bottom: 10px;
        box-shadow: 0 4px 10px rgba(58, 123, 213, 0.2);
        font-weight: 600;
    }

    .reset-btn {
        background: transparent;
        color: #64748b;
        border: 2px solid #cbd5e1;
        padding: 15px 24px;
        border-radius: 30px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        display: block;
        width: 100%;
        margin-top: 30px;
        font-size: 1.05rem;
    }

    .reset-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #94a3b8;
    }
</style>

<div class="derm-container">

    <div class="derm-header">
        <h1 class="derm-title">Dermalyze.AI</h1>
        <p class="derm-subtitle">Platform Skrining Jerawat Mandiri & Rekomendasi Terapi</p>
    </div>

    <!-- Mode Selector -->
    <div class="mode-tabs" id="modeTabs">
        <div class="mode-tab active" onclick="switchMode('upload')">Unggah Foto</div>
        <div class="mode-tab" onclick="switchMode('camera')">Kamera Web</div>
    </div>

    <!-- Interface Wrapper -->
    <div id="interfaceWrapper" style="position: relative;">
        <!-- Upload Section -->
        <div class="upload-area" id="uploadArea" onclick="document.getElementById('fileInput').click()">
            <div class="upload-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
            </div>
            <h3>Pilih Foto Wajah</h3>
            <p style="color: #64748b;">Klik panel ini untuk memilih foto dari perangkat Anda.</p>
            <input type="file" id="fileInput" style="display: none;" accept="image/*" onchange="handleFileUpload()">
        </div>

        <!-- Camera Section -->
        <div class="camera-area" id="cameraArea">
            <video id="webcamVideo" autoplay playsinline></video>
            <canvas id="cameraCanvas" style="display:none;"></canvas>
            <button class="capture-btn" onclick="capturePhoto()">
                <svg style="display:inline-block; vertical-align:middle; margin-right:5px; margin-bottom:3px;"
                    width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                AMBIL FOTO
            </button>
            <div id="camErrorMsg" class="cam-error">
                <strong>Kamera tidak dapat diakses!</strong> Pastikan Anda telah memberikan izin (Allow) keamanan di
                browser ini.
            </div>
        </div>

        <!-- Shared Overlay Animation -->
        <div class="scanner-overlay" id="scannerOverlay">
            <div class="scan-line"></div>
            <div class="scan-text" id="scanText">Memproses Gambar...</div>
        </div>
    </div>

    <!-- Results Section -->
    <div class="results-dashboard" id="resultsDashboard">
        <div class="derm-glass-card" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="font-size: 1.2rem; color: #64748b; margin-bottom: 8px;">Status Keparahan:</h3>
                <div id="resSeverity" class="severity-badge severity-Mild">Mild</div>
            </div>
            <div style="text-align: right; color: #64748b;">
                Skrining selesai <br><span style="color: #10b981; font-weight: 600;">&#10003; Completed by AI</span>
            </div>
        </div>

        <div class="derm-glass-card">
            <h3
                style="font-size: 1.2rem; margin-bottom: 15px; border-bottom: 1px solid var(--derm-glass-border); padding-bottom: 10px; color: #1e293b;">
                Deteksi Lesi</h3>
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-value" id="resPapule">0</div>
                    <div style="color: #64748b; font-size: 0.95rem; font-weight: 600;">Papule</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" id="resPustule">0</div>
                    <div style="color: #64748b; font-size: 0.95rem; font-weight: 600;">Pustule</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" id="resBlackhead">0</div>
                    <div style="color: #64748b; font-size: 0.95rem; font-weight: 600;">Blackhead</div>
                </div>
            </div>
        </div>

        <div class="derm-glass-card" style="background: rgba(0, 210, 255, 0.03); border-color: rgba(0, 210, 255, 0.1);">
            <h3
                style="font-size: 1.25rem; font-weight: 700; margin-bottom: 20px; color: var(--derm-secondary); display: flex; align-items: center; gap: 8px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                </svg>
                Rekomendasi Bahan Aktif
            </h3>

            <div id="resIngredients" style="margin-bottom: 20px;"></div>

            <p id="resAdvice" style="color: #475569; line-height: 1.7; margin-bottom: 20px; font-size: 1.05rem;"></p>

            <div
                style="background: #eff6ff; padding: 20px; border-radius: 12px; border-left: 5px solid var(--derm-secondary);">
                <strong style="color: #3a7bd5; font-size: 0.9rem; text-transform: uppercase; font-weight: 800;">SARAN
                    RESEP:</strong>
                <p id="resPrescription" style="margin-top: 8px; color: #0f172a; font-weight: 500; line-height: 1.5;">
                </p>
            </div>
        </div>

        <button class="reset-btn" onclick="hardResetApp()">Lakukan Skrining Ulang</button>
    </div>
</div>

<script>
    let currentStream = null;

    function switchMode(mode) {
        document.getElementById('modeTabs').children[0].className = (mode === 'upload') ? 'mode-tab active' : 'mode-tab';
        document.getElementById('modeTabs').children[1].className = (mode === 'camera') ? 'mode-tab active' : 'mode-tab';

        const uploadArea = document.getElementById('uploadArea');
        const cameraArea = document.getElementById('cameraArea');
        const errorMsg = document.getElementById('camErrorMsg');

        if (mode === 'upload') {
            cameraArea.style.display = 'none';
            uploadArea.style.display = 'block';
            stopCamera();
        } else {
            uploadArea.style.display = 'none';
            cameraArea.style.display = 'block';
            errorMsg.style.display = 'none';
            startCamera();
        }
    }

    function startCamera() {
        const video = document.getElementById('webcamVideo');
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (stream) {
                    currentStream = stream;
                    video.srcObject = stream;
                })
                .catch(function (error) {
                    console.error("Camera error:", error);
                    document.getElementById('camErrorMsg').style.display = 'block';
                });
        } else {
            document.getElementById('camErrorMsg').style.display = 'block';
        }
    }

    function stopCamera() {
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
            currentStream = null;
        }
    }

    function handleFileUpload() {
        const fileInput = document.getElementById('fileInput');
        if (!fileInput.files || fileInput.files.length === 0) return;

        const file = fileInput.files[0];
        const reader = new FileReader();
        reader.onload = function (e) {
            // Visual feedback
            const uploadIcon = document.querySelector('.upload-icon');
            document.querySelector('#uploadArea h3').style.display = 'none';
            document.querySelector('#uploadArea p').style.display = 'none';
            uploadIcon.innerHTML = `<img src="${e.target.result}" style="max-width: 100%; max-height: 250px; border-radius: 12px; object-fit: cover; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">`;

            triggerScanProcess(file);
        }
        reader.readAsDataURL(file);
    }

    function capturePhoto() {
        const video = document.getElementById('webcamVideo');
        const canvas = document.getElementById('cameraCanvas');

        if (!currentStream) return alert("Kamera mati atau tidak diizinkan.");

        // Visual flash effect
        const overlay = document.getElementById('scannerOverlay');
        overlay.style.background = 'white';
        overlay.style.display = 'flex';
        setTimeout(() => { overlay.style.background = 'rgba(255,255,255,0.85)'; }, 150);

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const context = canvas.getContext('2d');
        // Because CSS transformed video with scaleX(-1), drawing shouldn't affect backend file
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob((blob) => {
            const file = new File([blob], "webcam_snap.jpg", { type: "image/jpeg" });
            stopCamera(); // turn off camera immediately
            triggerScanProcess(file);
        }, 'image/jpeg', 0.9);
    }

    function triggerScanProcess(file) {
        document.getElementById('modeTabs').style.display = 'none';

        const overlay = document.getElementById('scannerOverlay');
        const scanText = document.getElementById('scanText');
        overlay.style.display = 'flex';
        overlay.style.borderRadius = "24px";

        let steps = ['Menganalisis tekstur kulit...', 'Mendeteksi inflamasi...', 'Mengkalkulasi tingkat keparahan...'];
        let currentStep = 0;

        const interval = setInterval(() => {
            if (currentStep < steps.length) {
                scanText.innerText = steps[currentStep];
                currentStep++;
            }
        }, 800);

        const formData = new FormData();
        formData.append('photo', file);

        setTimeout(() => {
            fetch('results.php', { method: 'POST', body: formData })
                .then(response => {
                    clearInterval(interval);
                    overlay.style.display = 'none';
                    if (response.ok) {
                        window.location.href = response.url; 
                    } else {
                        alert("Gagal menyimpan data ke database.");
                        window.location.reload();
                    }
                })
                .catch(err => {
                    clearInterval(interval);
                    overlay.style.display = 'none';
                    console.error(err);
                    alert('Terjadi kesalahan koneksi.');
                    hardResetApp();
                });
        }, 3000);
    }

    function showResults(result) {
        document.getElementById('interfaceWrapper').style.display = 'none';

        const severityEl = document.getElementById('resSeverity');
        severityEl.className = 'severity-badge severity-' + result.severity;
        severityEl.innerText = result.severity;

        document.getElementById('resPapule').innerText = result.counts.papule;
        document.getElementById('resPustule').innerText = result.counts.pustule;
        document.getElementById('resBlackhead').innerText = result.counts.blackhead;

        const recs = result.recommendations;
        const tagsContainer = document.getElementById('resIngredients');
        tagsContainer.innerHTML = '';
        recs.ingredients.forEach(ing => {
            let span = document.createElement('span');
            span.className = 'ingredient-tag';
            span.innerText = ing;
            tagsContainer.appendChild(span);
        });

        document.getElementById('resAdvice').innerText = recs.advice;
        document.getElementById('resPrescription').innerText = recs.prescription;

        document.getElementById('resultsDashboard').style.display = 'block';
    }

    function hardResetApp() {
        // Stop any feed if lingering
        stopCamera();
        // Uses the redirect reset function from scanner.php outer file usually
        if (typeof resetApp === 'function') resetApp();
        else window.location.reload();
    }
</script>