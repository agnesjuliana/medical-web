<?php
/**
 * Patient View for Dermalyze.AI with Teachable Machine Integration
 */
?>

<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest/dist/tf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@teachablemachine/image@latest/dist/teachablemachine-image.min.js"></script>

<style>
    /* Dermalyze AI Custom Premium CSS - Light Theme */
    :root {
        --derm-primary: #FFB7CE;
        --derm-secondary: #B2E2F2;
        --derm-accent: #C1E1C1;
        --derm-dark: #7D6E7D;
        --derm-card: #ffffff;
        --derm-glass-border: #FFF0F5;
        --derm-glass-bg: #FFFBFC;
    }

    body {
        background: linear-gradient(135deg, #FFF5F7 0%, #F0F7FF 100%);
        color: var(--derm-dark);
        font-family: 'Quicksand', sans-serif;
    }

    .derm-container {
        border: none;
        border-radius: 40px;
        background: #ffffff;
        box-shadow: 0 25px 50px -12px rgba(255, 183, 206, 0.25);
        padding: 30px;
        position: relative;
        overflow: hidden;
    }

    .derm-header {
        text-align: center;
        margin-bottom: 30px;
        position: relative;
        z-index: 10;
    }

    .derm-title {
        background: linear-gradient(to right, #FFB7CE, #92DFF3);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 12px;
        letter-spacing: -0.02em;
        font-size: 2.5rem;
        font-weight: 800;
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
        background: #FFB7CE;
        color: white;
        font-weight: 700;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.3s;
        box-shadow: 0 8px 15px rgba(255, 183, 206, 0.3);
    }

    .mode-tab.active {
        background: #eff6ff;
        color: #3a7bd5;
        border-color: #3a7bd5;
        box-shadow: 0 10px 20px rgba(58, 123, 213, 0.15);
    }

    /* Upload Area */
    .upload-area {
        position: relative;
        z-index: 10;
        background: #FFFBFC;
        border: 2px dashed #FFD1DC;
        border-radius: 24px;
        padding: 60px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .upload-area:hover {
        background: #FFF0F5;
        border-color: var(--derm-primary);
        transform: translateY(-5px);
    }

    .upload-icon {
        font-size: 4rem;
        color: var(--derm-secondary);
        margin-bottom: 15px;
    }

    /* Camera Area */
    .camera-area {
        display: none;
        position: relative;
        z-index: 10;
        background: #0f172a;
        border-radius: 24px;
        overflow: hidden;
        text-align: center;
        padding-bottom: 20px;
    }

    #webcamVideo {
        width: 100%;
        max-height: 400px;
        object-fit: cover;
        background: #000;
        transform: scaleX(-1);
    }

    .capture-btn {
        background: linear-gradient(135deg, #FFB7CE, #FFD1DC);
        color: white;
        padding: 15px 40px;
        border-radius: 30px;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        margin-top: -30px;
        position: relative;
        z-index: 5;
        box-shadow: 0 10px 20px rgba(255, 183, 206, 0.4);
        border: 4px solid #0f172a;
        transition: transform 0.2s;
    }

    /* Scanner Animation */
    .scanner-overlay {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        z-index: 30;
        border-radius: 40px;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(8px);
    }

    .scan-line {
        width: 80%;
        height: 6px;
        background: #FFB7CE;
        box-shadow: 0 0 15px #FFB7CE, 0 0 30px #FFD1DC;
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
        color: #7D6E7D;
        font-weight: 700;
        font-size: 1.1rem;
    }
</style>

<div class="derm-container">
    <div class="derm-header">
        <h1 class="derm-title">Dermalyze.AI</h1>
        <p class="derm-subtitle">Platform Skrining Jerawat Mandiri & Rekomendasi Terapi</p>
    </div>

    <div class="mode-tabs" id="modeTabs">
        <div class="mode-tab active" onclick="switchMode('upload')">Unggah Foto</div>
        <div class="mode-tab" onclick="switchMode('camera')">Kamera Web</div>
    </div>

    <div id="interfaceWrapper" style="position: relative;">
        <div class="upload-area" id="uploadArea" onclick="document.getElementById('fileInput').click()">
            <div class="upload-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#B2E2F2" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
            </div>
            <h3>Pilih Foto Wajah</h3>
            <p style="color: #64748b;">Klik panel ini untuk memilih foto dari perangkat Anda.</p>
            <input type="file" id="fileInput" style="display: none;" accept="image/*" onchange="handleFileUpload()">
        </div>

        <div class="camera-area" id="cameraArea">
            <video id="webcamVideo" autoplay playsinline></video>
            <canvas id="cameraCanvas" style="display:none;"></canvas>
            <button class="capture-btn" onclick="capturePhoto()">AMBIL FOTO</button>
            <div id="camErrorMsg" style="display:none; color:red; padding:20px;">Kamera tidak dapat diakses.</div>
        </div>

        <div class="scanner-overlay" id="scannerOverlay">
            <div class="scan-line"></div>
            <div class="scan-text" id="scanText">Memuat Model AI...</div>
        </div>
    </div>
</div>

<script>
    // MASUKKAN LINK GOOGLE KAMU DI SINI
    const URL_MODEL = "https://teachablemachine.withgoogle.com/models/P0MVm4rAa/";

    let model, currentStream;

    // Load model saat halaman terbuka
    async function initML() {
        try {
            const modelURL = URL_MODEL + "model.json";
            const metadataURL = URL_MODEL + "metadata.json";
            model = await tmImage.load(modelURL, metadataURL);
            console.log("Model AI Berhasil Dimuat");
        } catch (e) {
            console.error("Gagal memuat model:", e);
        }
    }
    initML();

    function switchMode(mode) {
        document.getElementById('modeTabs').children[0].className = (mode === 'upload') ? 'mode-tab active' : 'mode-tab';
        document.getElementById('modeTabs').children[1].className = (mode === 'camera') ? 'mode-tab active' : 'mode-tab';
        if (mode === 'upload') {
            document.getElementById('cameraArea').style.display = 'none';
            document.getElementById('uploadArea').style.display = 'block';
            stopCamera();
        } else {
            document.getElementById('uploadArea').style.display = 'none';
            document.getElementById('cameraArea').style.display = 'block';
            startCamera();
        }
    }

    function startCamera() {
        const video = document.getElementById('webcamVideo');
        navigator.mediaDevices.getUserMedia({ video: true }).then(s => { currentStream = s; video.srcObject = s; });
    }

    function stopCamera() {
        if (currentStream) { currentStream.getTracks().forEach(t => t.stop()); currentStream = null; }
    }

    function handleFileUpload() {
        const fileInput = document.getElementById('fileInput');
        if (fileInput.files[0]) triggerScanProcess(fileInput.files[0]);
    }

    function capturePhoto() {
        const video = document.getElementById('webcamVideo');
        const canvas = document.getElementById('cameraCanvas');
        canvas.width = video.videoWidth; canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        canvas.toBlob(b => {
            const file = new File([b], "webcam.jpg", { type: "image/jpeg" });
            stopCamera();
            triggerScanProcess(file);
        }, 'image/jpeg');
    }

    async function triggerScanProcess(file) {
        if (!model) {
            alert("Model AI sedang dimuat, mohon tunggu sebentar...");
            return;
        }

        const overlay = document.getElementById('scannerOverlay');
        const scanText = document.getElementById('scanText');
        overlay.style.display = 'flex';
        scanText.innerText = "Menganalisis jenis jerawat...";

        // 1. Buat elemen gambar untuk diproses AI
        const img = document.createElement('img');
        img.src = window.URL.createObjectURL(file);

        img.onload = async () => {
            // 2. Jalankan Prediksi Teachable Machine
            const prediction = await model.predict(img);

            // Cari hasil dengan probabilitas tertinggi
            let highest = prediction.reduce((prev, current) =>
                (prev.probability > current.probability) ? prev : current
            );

            scanText.innerText = "Menyimpan hasil analisis...";

            // 3. Kirim ke result.php
            const formData = new FormData();
            formData.append('photo', file);
            formData.append('severity', highest.className);
            formData.append('accuracy', (highest.probability * 100).toFixed(2));

            fetch('results.php', { method: 'POST', body: formData })
                .then(res => {
                    if (res.ok) window.location.href = res.url;
                    else alert("Gagal menyimpan hasil.");
                })
                .catch(err => alert("Terjadi kesalahan sistem."));
        };
    }
</script>