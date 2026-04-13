<?php
/**
 * Modul 3 — Landing Page
 * 
 * Initial page for Modul 3.
 * Each module uses the shared auth system (SSO)
 * and can define its own database schema.
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'Modul 3';
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>
<?php require_once __DIR__ . '/../../layout/navbar.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-6">
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-cyan-600 transition-colors">Module Hub</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium">Modul 3</span>
    </nav>

    <!-- Header -->
    <div class="mb-10 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-cyan-100 text-cyan-600 mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Deteksi TBC Berbasis Citra Thorax</h1>
        <p class="text-gray-500 max-w-2xl mx-auto text-lg">Sistem ini membantu identifikasi awal Tuberkulosis (TBC) melalui analisis citra rontgen dada (Thorax). Unggah gambar rontgen Anda untuk memulai.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        
        <!-- Left Column: Upload Form -->
        <div class="lg:col-span-1">
            <?= component_card([
                'title' => 'Unggah Foto Rontgen',
                'subtitle' => 'Format didukung: JPG, PNG, JPEG',
                'padding' => false,
                'content' => '
                    <form action="process.php" method="POST" enctype="multipart/form-data" class="p-6">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File Citra Thorax</label>
                            <label class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 rounded-2xl cursor-pointer hover:bg-cyan-50 hover:border-cyan-400 transition-colors bg-white">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4">
                                    <svg class="w-10 h-10 text-cyan-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    <p class="mb-1 text-sm text-gray-500"><span class="font-semibold text-cyan-600 hover:underline">Klik untuk unggah</span></p>
                                    <p class="text-xs text-gray-400 mt-1">SVG, PNG, JPG (MAX. 5MB)</p>
                                </div>
                                <input id="dropzone-file" name="thorax_image" type="file" class="hidden" accept="image/jpeg, image/png, image/jpg" required />
                            </label>
                            <div class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-100 hidden" id="file-info-container">
                                <p class="text-sm font-medium text-gray-700 truncate flex items-center gap-2">
                                <svg class="w-4 h-4 text-cyan-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span id="file-name-display"></span></p>
                            </div>
                        </div>

                        <script>
                            document.getElementById("dropzone-file").addEventListener("change", function(e) {
                                var fileName = e.target.files[0] ? e.target.files[0].name : "";
                                if (fileName) {
                                    document.getElementById("file-info-container").classList.remove("hidden");
                                    document.getElementById("file-name-display").innerText = fileName;
                                }
                            });
                        </script>
                ',
                'footer' => component_button('Mulai Analisis', ['type' => 'submit', 'fullWidth' => true, 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>']) . '</form>'
            ]) ?>
        </div>

        <!-- Right Column: Education Info -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            
            <?= component_card([
                'title' => 'Apa itu TBC Paru?',
                'content' => '
                    <p class="text-gray-600 text-sm leading-relaxed mb-5">
                        Tuberkulosis (TBC) adalah penyakit menular yang disebabkan oleh kuman <span class="text-gray-800 font-medium italic">Mycobacterium tuberculosis</span>. Penyakit ini paling sering menyerang paru-paru dan dapat menyebabkan kerusakan serius jika tidak segera ditangani secara medis.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="bg-amber-50/50 rounded-2xl p-5 border border-amber-100/50 hover:bg-amber-50 hover:scale-[1.02] transition-transform">
                            <h4 class="font-semibold text-amber-800 text-sm mb-3 flex items-center gap-2">
                                <div class="p-1.5 bg-amber-100 text-amber-600 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
                                Gejala Utama
                            </h4>
                            <ul class="text-sm text-gray-600 space-y-2">
                                <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">•</span> Batuk berdahak menerus > 2 minggu</li>
                                <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">•</span> Batuk kadang bercampur darah</li>
                                <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">•</span> Demam meriang di sore/malam hari</li>
                                <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">•</span> Berkeringat walau tanpa aktivitas</li>
                                <li class="flex items-start gap-2"><span class="text-amber-500 mt-0.5">•</span> Penurunan berat badan tak wajar</li>
                            </ul>
                        </div>
                        <div class="bg-green-50/50 rounded-2xl p-5 border border-green-100/50 hover:bg-green-50 hover:scale-[1.02] transition-transform">
                            <h4 class="font-semibold text-green-800 text-sm mb-3 flex items-center gap-2">
                                <div class="p-1.5 bg-green-100 text-green-600 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                                Langkah Pencegahan
                            </h4>
                            <ul class="text-sm text-gray-600 space-y-2">
                                <li class="flex items-start gap-2"><span class="text-green-500 mt-0.5">•</span> Vaksinasi BCG selagi masa anak</li>
                                <li class="flex items-start gap-2"><span class="text-green-500 mt-0.5">•</span> Pakai masker saat di ruang padat</li>
                                <li class="flex items-start gap-2"><span class="text-green-500 mt-0.5">•</span> Etika batuk: tutup dengan lengan</li>
                                <li class="flex items-start gap-2"><span class="text-green-500 mt-0.5">•</span> Buka jendela tiap pagi demi sirkulasi</li>
                                <li class="flex items-start gap-2"><span class="text-green-500 mt-0.5">•</span> Jaga imunitas dan makan bergizi</li>
                            </ul>
                        </div>
                    </div>
                '
            ]) ?>

            <?= component_alert('Hasil dari AI/Machine Learning ini hanya prediksi sistem komputer melalui ekstraksi fitur rontgen, BUKAN pengganti diagnosis resmi tenaga kesehatan/dokter spesialis.', 'info', ['title' => 'Disclaimer Medis']) ?>

        </div>
    </div>

</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
