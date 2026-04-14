<?php
/**
 * Patient View - Upload and Result
 */
?>

<!-- Interface for Patient -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    
    <!-- Upload Section -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Skrining Jerawat via AI</h2>
        <p class="text-sm text-gray-500 mb-6">Unggah foto wajah Anda dari depan dengan pencahayaan yang cukup. Sistem Deep Learning kami akan memindai jenis jerawat dan tingkat keparahannya.</p>

        <form id="uploadForm" enctype="multipart/form-data">
            <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-cyan-500 hover:bg-cyan-50 transition-colors cursor-pointer relative" id="dropZone">
                <input type="file" id="imageInput" name="image" accept="image/jpeg, image/png, image/jpg" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required>
                <div id="uploadPlaceholder">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" />
                    </svg>
                    <p class="text-sm text-gray-600 font-medium">Seret foto ke sini atau klik untuk memilih file</p>
                    <p class="text-xs text-gray-400 mt-1">PNG, JPG, max 5MB</p>
                </div>
                <!-- Image Preview -->
                <img id="imagePreview" class="hidden max-h-64 mx-auto rounded-lg shadow-sm" alt="Preview"/>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" id="submitBtn" class="bg-cyan-600 hover:bg-cyan-700 text-white font-medium py-2.5 px-6 rounded-xl text-sm shadow-sm transition-colors flex items-center justify-center gap-2 w-full md:w-auto hidden">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Mulai Analisis
                </button>
            </div>
        </form>

        <!-- Loading State -->
        <div id="loadingState" class="hidden mt-8 text-center py-10">
            <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 shadow-sm border-gray-200 border-t-cyan-600 mb-4"></div>
            <p class="text-sm font-medium text-gray-700 animate-pulse">AI sedang menganalisis piksel gambar...</p>
            <p class="text-xs text-gray-400 mt-1">Mengidentifikasi Papule, Pustule, dan Blackhead.</p>
        </div>
    </div>

    <!-- Results Section -->
    <div id="resultsContainer" class="hidden">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Hasil Diagnosa AI</h2>
        
        <!-- Severe Alert -->
        <div id="severeAlert" class="hidden mb-6 p-5 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <h3 class="text-red-800 font-bold">Kondisi Memerlukan Perhatian Medis (Parah)</h3>
                    <p class="text-red-700 text-sm mt-1">Sistem mendeteksi bahwa tingkat jerawat Anda cukup parah. Kasus Anda telah <strong>diteruskan ke Dokter Spesialis kami secara otomatis</strong>.</p>
                    <p class="text-red-700 text-sm mt-1">Mohon tunggu resep dan saran penanganan dari Dokter yang akan muncul di profil Anda segera.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Severity Banner -->
            <div id="severityBanner" class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <span class="text-sm font-medium text-gray-500 uppercase tracking-wide">Tingkat Keparahan</span>
                <span id="severityBadge" class="px-3 py-1 rounded-full text-sm font-bold shadow-sm"></span>
            </div>
            
            <div class="p-6">
                <h4 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">Metrik Deteksi Objek</h4>
                <div class="space-y-4">
                    
                    <div class="flex justify-between items-center group">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-pink-400 shadow-sm"></div>
                            <span class="text-sm font-medium text-gray-700">Papula (Benjolan Merah)</span>
                        </div>
                        <span id="countPapule" class="font-mono font-bold text-gray-900 group-hover:text-cyan-600 transition-colors">0</span>
                    </div>

                    <div class="flex justify-between items-center group">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-yellow-400 shadow-sm"></div>
                            <span class="text-sm font-medium text-gray-700">Pustula (Bernanah)</span>
                        </div>
                        <span id="countPustule" class="font-mono font-bold text-gray-900 group-hover:text-cyan-600 transition-colors">0</span>
                    </div>

                    <div class="flex justify-between items-center group">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-gray-800 shadow-sm"></div>
                            <span class="text-sm font-medium text-gray-700">Blackhead (Komedo Hitam)</span>
                        </div>
                        <span id="countBlackhead" class="font-mono font-bold text-gray-900 group-hover:text-cyan-600 transition-colors">0</span>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 hidden" id="recommendationBlock">
                    <h4 class="text-sm font-bold text-gray-700 mb-2">Rekomendasi Dasar (Perawatan Mandiri)</h4>
                    <ul class="text-sm text-gray-600 space-y-2 list-disc pl-4" id="recommendationList">
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('uploadPlaceholder');
    const submitBtn = document.getElementById('submitBtn');
    const uploadForm = document.getElementById('uploadForm');
    const loadingState = document.getElementById('loadingState');
    const resultsContainer = document.getElementById('resultsContainer');

    fileInput.addEventListener('change', function(e) {
        if(this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.classList.remove('hidden');
                placeholder.classList.add('hidden');
                submitBtn.classList.remove('hidden');
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Hide form contents, show loading
        submitBtn.classList.add('hidden');
        loadingState.classList.remove('hidden');
        resultsContainer.classList.add('hidden');
        
        const formData = new FormData(uploadForm);

        fetch('<?= BASE_URL ?>/modules/modul_7/api/scan_and_triage.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            setTimeout(() => { // Artificial delay to simulate ML processing
                loadingState.classList.add('hidden');
                submitBtn.classList.remove('hidden');
                
                if(data.success) {
                    renderResults(data.result);
                } else {
                    alert('Error: ' + data.message);
                }
            }, 1500); 
        })
        .catch(err => {
            console.error(err);
            loadingState.classList.add('hidden');
            submitBtn.classList.remove('hidden');
            alert('Kesalahan jaringan. Harap coba lagi.');
        });
    });

    function renderResults(result) {
        resultsContainer.classList.remove('hidden');
        
        // Counter animation
        document.getElementById('countPapule').textContent = result.counts.papule;
        document.getElementById('countPustule').textContent = result.counts.pustule;
        document.getElementById('countBlackhead').textContent = result.counts.blackhead;

        // Severity logic
        const badge = document.getElementById('severityBadge');
        const alert = document.getElementById('severeAlert');
        const recBlock = document.getElementById('recommendationBlock');
        const recList = document.getElementById('recommendationList');

        badge.textContent = result.severity;
        badge.className = 'px-3 py-1 rounded-full text-sm font-bold shadow-sm'; // reset
        alert.classList.add('hidden');
        recBlock.classList.add('hidden');

        if (result.severity === 'Severe') {
            badge.classList.add('bg-red-100', 'text-red-700');
            badge.textContent = 'Parah (Severe)';
            alert.classList.remove('hidden');
        } else if (result.severity === 'Moderate') {
            badge.classList.add('bg-orange-100', 'text-orange-700');
            badge.textContent = 'Sedang (Moderate)';
            recBlock.classList.remove('hidden');
            recList.innerHTML = `
                <li>Cuci muka 2 kali sehari menggunakan sabun berbahan lembut.</li>
                <li>Gunakan obat totol dengan kandungan Salicylic Acid atau Benzoyl Peroxide.</li>
                <li>Jangan memencet jerawat untuk menghindari radang.</li>
            `;
        } else {
            badge.classList.add('bg-green-100', 'text-green-700');
            badge.textContent = 'Ringan (Mild)';
            recBlock.classList.remove('hidden');
            recList.innerHTML = `
                <li>Tetap jaga kebersihan wajah dengan basic skincare (CTM).</li>
                <li>Gunakan sunscreen setiap pagi.</li>
            `;
        }
    }
});
</script>
