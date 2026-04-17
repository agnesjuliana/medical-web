<?php
/**
 * Modul 6 — Landing Page
 * 
 * Initial page for Modul 6
 * Each module uses the shared auth system (SSO)
 * and can define its own database schema.
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'Modul 1';
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #ffffff;
    color: #1f2937;
}
</style>

<!-- NAVBAR -->
<div class="w-full border-b bg-white">
    <div class="max-w-6xl mx-auto flex justify-between items-center px-8 py-4">
        <h1 class="text-xl font-bold text-[#14B8A6]">NeuroAI</h1>
        <div class="flex gap-6 text-sm text-gray-600">
            <a href="#home" class="hover:text-[#14B8A6]">Home</a>
            <a href="#about" class="hover:text-[#14B8A6]">About</a>
            <a href="#features" class="hover:text-[#14B8A6]">Features</a>
            <a href="#workflow" class="hover:text-[#14B8A6]">Workflow</a>
            <a href="#team" class="hover:text-[#14B8A6]">Team</a>
        </div>
    </div>
</div>

<!-- HERO -->
<section id="home" class="min-h-screen flex items-center justify-center text-center px-8">
    <div>
        <h1 class="text-4xl md:text-5xl font-bold text-[#14B8A6]">
            Brain Tumor Segmentation System
        </h1>
        <p class="mt-4 text-gray-600 max-w-xl mx-auto">
            NeuroAI is a web-based system that utilizes artificial intelligence 
            to perform 3D segmentation of brain tumors from MRI images.
        </p>
        <div class="mt-6">
            <a href="#about" 
               class="px-6 py-2 bg-[#14B8A6] text-white rounded-lg hover:bg-[#0F766E] transition">
                Learn More
            </a>
        </div>
    </div>
</section>

<!-- ABOUT -->
<section id="about" class="py-20 px-8 text-center bg-[#F0FDFA]">
    <h2 class="text-3xl font-bold mb-4 text-[#14B8A6]">About NeuroAI</h2>
    <p class="max-w-3xl mx-auto text-gray-600">
        NeuroAI is designed to assist medical analysis by automatically identifying 
        and segmenting tumor regions in MRI scans. This system aims to improve 
        accuracy and efficiency in medical imaging analysis.
    </p>
</section>

<!-- FEATURES -->
<section id="features" class="py-20 px-8 text-center">
    <h2 class="text-3xl font-bold mb-10 text-[#14B8A6]">Features</h2>

    <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
        <div class="p-6 border rounded-lg hover:border-[#14B8A6] hover:shadow-md transition">
            MRI Upload
        </div>
        <div class="p-6 border rounded-lg hover:border-[#14B8A6] hover:shadow-md transition">
            Automatic Segmentation
        </div>
        <div class="p-6 border rounded-lg hover:border-[#14B8A6] hover:shadow-md transition">
            3D Visualization
        </div>
    </div>
</section>

<!-- WORKFLOW -->
<section id="workflow" class="py-20 px-8 text-center bg-[#F0FDFA]">
    <h2 class="text-3xl font-bold mb-10 text-[#14B8A6]">How It Works</h2>

    <div class="space-y-4 text-gray-700">
        <p>1. Upload MRI Image</p>
        <p>2. AI Processes Data</p>
        <p>3. System Generates Tumor Segmentation</p>
    </div>
</section>

<!-- TEAM -->
<section id="team" class="py-20 px-8 text-center">
    <h2 class="text-3xl font-bold mb-10 text-[#14B8A6]">Our Team</h2>

    <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
        <div class="p-6 border rounded-lg hover:border-[#14B8A6] transition">Nama 1</div>
        <div class="p-6 border rounded-lg hover:border-[#14B8A6] transition">Nama 2</div>
        <div class="p-6 border rounded-lg hover:border-[#14B8A6] transition">Nama 3</div>
    </div>
</section>

<!-- DISCLAIMER -->
<section class="py-16 text-center bg-[#F0FDFA]">
    <p class="text-sm text-gray-500 max-w-xl mx-auto">
         NeuroAI is designed to assist healthcare professionals in analyzing MRI data. 
    This system serves as a clinical decision support tool and should be used alongside professional medical judgment.
    </p>
</section>

<!-- FOOTER -->
<footer class="py-6 text-center text-sm text-gray-500 border-t">
    © 2026 NeuroAI
</footer>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>