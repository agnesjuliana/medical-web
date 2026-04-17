<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'NeuroAI Dashboard';
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: radial-gradient(circle at top, #0f172a, #020617);
    color: #e2e8f0;
}

/* Neon text */
.neon-text {
    color: #14B8A6;
    text-shadow: 0 0 8px #14B8A6;
}

/* Glow */
.glow {
    box-shadow: 0 0 20px rgba(20,184,166,0.4);
}

/* Card futuristic */
.card {
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(20,184,166,0.2);
    transition: all 0.3s ease;
}
.card:hover {
    border-color: #14B8A6;
    transform: translateY(-6px);
    box-shadow: 0 0 25px rgba(20,184,166,0.3);
}
</style>

<!-- NAVBAR -->
<div class="w-full border-b border-teal-500/20 bg-[#020617]/80 backdrop-blur-md">
    <div class="max-w-6xl mx-auto flex justify-between items-center px-8 py-4">
        <h1 class="text-xl font-bold neon-text">NeuroAI</h1>
        <div class="flex gap-6 text-sm text-gray-300">
            <a href="#home" class="hover:text-teal-400">Home</a>
            <a href="#about" class="hover:text-teal-400">About</a>
            <a href="#features" class="hover:text-teal-400">Features</a>
            <a href="#workflow" class="hover:text-teal-400">Workflow</a>
            <a href="#team" class="hover:text-teal-400">Team</a>
        </div>
    </div>
</div>

<!-- HERO -->
<section id="home" class="min-h-screen flex items-center justify-center text-center px-8">
    <div>
        <h1 class="text-5xl font-bold neon-text">
            Brain Tumor Segmentation AI
        </h1>
        <p class="mt-4 text-gray-400 max-w-xl mx-auto">
            Advanced AI-powered medical system for precise 3D brain tumor segmentation from MRI scans.
        </p>
        <div class="mt-6">
            <a href="#about" 
               class="px-6 py-3 bg-teal-500 text-black font-semibold rounded-lg glow hover:bg-teal-400 transition">
                Explore System
            </a>
        </div>
    </div>
</section>

<!-- ABOUT -->
<section id="about" class="py-20 px-8 text-center">
    <h2 class="text-3xl font-bold mb-4 neon-text">About NeuroAI</h2>
    <p class="max-w-3xl mx-auto text-gray-400">
        NeuroAI leverages deep learning to automatically detect and segment brain tumors in MRI scans. 
        Designed for medical efficiency, accuracy, and decision support.
    </p>
</section>

<!-- FEATURES -->
<section id="features" class="py-20 px-8 text-center">
    <h2 class="text-3xl font-bold mb-10 neon-text">Core Features</h2>

    <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">

        <div class="card p-6 rounded-xl glow">
            <h3 class="text-lg font-semibold text-teal-400">MRI Upload</h3>
            <p class="text-sm text-gray-400 mt-2">Upload patient MRI data securely.</p>
        </div>

        <div class="card p-6 rounded-xl glow">
            <h3 class="text-lg font-semibold text-teal-400">AI Segmentation</h3>
            <p class="text-sm text-gray-400 mt-2">Automatic tumor detection using AI.</p>
        </div>

        <div class="card p-6 rounded-xl glow">
            <h3 class="text-lg font-semibold text-teal-400">3D Visualization</h3>
            <p class="text-sm text-gray-400 mt-2">Interactive 3D tumor rendering.</p>
        </div>

    </div>
</section>

<!-- WORKFLOW -->
<section id="workflow" class="py-20 px-8 text-center">
    <h2 class="text-3xl font-bold mb-10 neon-text">System Workflow</h2>

    <div class="space-y-4 text-gray-400">
        <p>1. Upload MRI Scan</p>
        <p>2. AI Model Processing</p>
        <p>3. Tumor Segmentation Output</p>
    </div>
</section>

<!-- TEAM -->
<section id="team" class="py-20 px-8 text-center">
    <h2 class="text-3xl font-bold mb-10 neon-text">Our Team</h2>

    <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">

        <!-- MEMBER 1 -->
        <div class="card p-6 rounded-xl text-center">
            <div class="flex justify-center">
                <img src="assets/images/Tsamarah Amelia Putri Ginting-foto.jpeg" 
                     class="w-32 h-32 object-cover rounded-full border-2 border-teal-400 glow hover:scale-105 transition duration-300">
            </div>
            <h3 class="text-lg font-semibold text-teal-400 mt-4">
                Tsamarah Amelia Putri Ginting
            </h3>
            <p class="text-gray-400 text-sm mt-1">NRP: 5049231018</p>
        </div>

        <!-- MEMBER 2 -->
        <div class="card p-6 rounded-xl text-center">
            <div class="flex justify-center">
                <img src="assets/images/Cintya Melati Sianipar.jpeg" 
                     class="w-32 h-32 object-cover rounded-full border-2 border-teal-400 glow hover:scale-105 transition duration-300">
            </div>
            <h3 class="text-lg font-semibold text-teal-400 mt-4">
                Cintya Melati Sianipar
            </h3>
            <p class="text-gray-400 text-sm mt-1">NRP: 5049231095</p>
        </div>

        <!-- MEMBER 3 -->
        <div class="card p-6 rounded-xl text-center">
            <div class="flex justify-center">
                <img src="assets/images/Kezia Martha Stephanie Silaban.jpeg" 
                     class="w-32 h-32 object-cover rounded-full border-2 border-teal-400 glow hover:scale-105 transition duration-300">
            </div>
            <h3 class="text-lg font-semibold text-teal-400 mt-4">
                Kezia Martha Stephanie Silaban
            </h3>
            <p class="text-gray-400 text-sm mt-1">NRP: 5049231090</p>
        </div>

    </div>
</section>

<!-- DISCLAIMER -->
<section class="py-16 text-center border-t border-teal-500/10">
    <p class="text-sm text-gray-500 max-w-xl mx-auto">
        NeuroAI is a clinical decision support system and should be used alongside professional medical judgment.
    </p>
</section>

<!-- FOOTER -->
<footer class="py-6 text-center text-sm text-gray-500 border-t border-teal-500/10">
    © 2026 NeuroAI System
</footer>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>