<?php
$pageTitle = 'NeuroAI';
?>

<?php require_once __DIR__ . '/../../layout/header.php'; ?>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #020617;
    color: #e5e7eb;
    overflow-x: hidden;
}

/* COLOR SYSTEM */
:root {
    --primary: #6366f1;
    --secondary: #22d3ee;
}

/* PARTICLES */
.particles span {
    position: absolute;
    width: 3px;
    height: 3px;
    background: var(--secondary);
    opacity: 0.5;
    border-radius: 50%;
    animation: floatParticle 12s linear infinite;
}

@keyframes floatParticle {
    from { transform: translateY(0); }
    to { transform: translateY(-100vh); }
}

/* 3D BRAIN */
.brain-wrapper {
    perspective: 1000px;
}

.brain {
    width: 240px;
    height: 240px;
    margin: 40px auto;
    border-radius: 50%;
    position: relative;
    transform-style: preserve-3d;
    animation: rotateBrain 12s linear infinite;
}

.brain::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: radial-gradient(circle at 30% 30%, #22d3ee, #6366f1);
    box-shadow:
        0 0 50px rgba(34,211,238,0.5),
        inset 0 0 40px rgba(255,255,255,0.2);
}

.brain::after {
    content: '';
    position: absolute;
    inset: 10px;
    border-radius: 50%;
    background:
        repeating-radial-gradient(circle,
            rgba(255,255,255,0.08) 0px,
            rgba(255,255,255,0.08) 2px,
            transparent 3px,
            transparent 8px
        );
    animation: pulse 3s infinite ease-in-out;
}

.scan {
    position: absolute;
    width: 100%;
    height: 4px;
    background: linear-gradient(to right, transparent, #22d3ee, transparent);
    animation: scanMove 3s linear infinite;
}

@keyframes rotateBrain {
    0% { transform: rotateY(0deg) rotateX(10deg); }
    100% { transform: rotateY(360deg) rotateX(10deg); }
}

@keyframes scanMove {
    0% { top: 0%; opacity: 0; }
    50% { opacity: 1; }
    100% { top: 100%; opacity: 0; }
}

@keyframes pulse {
    0%,100% { opacity: 0.6; }
    50% { opacity: 1; }
}

/* TEXT */
.neon {
    background: linear-gradient(to right, var(--primary), var(--secondary));
    -webkit-background-clip: text;
    color: transparent;
}

/* DIVIDER */
.divider {
    width: 100px;
    height: 4px;
    margin: 16px auto;
    background: linear-gradient(to right, var(--primary), var(--secondary));
    border-radius: 10px;
}

/* CARD */
.card {
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(148, 163, 184, 0.15);
    transition: 0.4s;
}

.card:hover {
    transform: translateY(-10px) scale(1.03);
    box-shadow: 0 0 30px rgba(99,102,241,0.4);
}

/* ICON */
.icon {
    font-size: 40px;
    color: var(--secondary);
    margin-bottom: 10px;
}

/* FADE */
.fade-in {
    animation: fade 1.2s ease-in;
}

@keyframes fade {
    from { opacity: 0; transform: translateY(20px);}
    to { opacity: 1; transform: translateY(0);}
}
</style>

<!-- PARTICLES -->
<div class="particles">
<?php for($i=0;$i<50;$i++): ?>
<span style="left:<?= rand(0,100) ?>%; animation-duration:<?= rand(6,15) ?>s;"></span>
<?php endfor; ?>
</div>

<!-- NAVBAR -->
<div class="w-full border-b border-slate-700 bg-[#020617]/80 backdrop-blur-md relative z-10">
<div class="max-w-7xl mx-auto flex justify-between items-center px-8 py-4">
<h1 class="text-2xl neon">NeuroAI</h1>
<div class="flex gap-6 text-gray-300">
<a href="#home">Home</a>
<a href="#about">About</a>
<a href="#features">Features</a>
<a href="#team">Team</a>
</div>
</div>
</div>

<!-- HERO -->
<section id="home" class="min-h-screen flex items-center justify-center text-center px-8 relative z-10">

<div class="fade-in">

<h1 class="text-6xl neon">
NeuroAI System
</h1>

<div class="brain-wrapper">
    <div class="brain">
        <div class="scan"></div>
    </div>
</div>

<div class="divider"></div>

<p class="mt-6 text-gray-300 max-w-2xl mx-auto">
AI-powered 3D Brain Tumor Segmentation from MRI scans
for fast, accurate, and intelligent medical diagnosis.
</p>

</div>

</section>

<!-- ABOUT -->
<section id="about" class="py-20 text-center fade-in">

<h2 class="text-4xl neon">About NeuroAI</h2>
<div class="divider"></div>

<p class="max-w-3xl mx-auto text-gray-300 mt-4">
NeuroAI is an intelligent medical system designed to assist
radiologists and clinicians in detecting and segmenting
brain tumors from MRI scans using deep learning technology.
</p>

</section>

<!-- FEATURES -->
<section id="features" class="py-20 text-center">

<h2 class="text-4xl neon">Core Features</h2>
<div class="divider"></div>

<div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto mt-10">

<div class="card p-8 rounded-xl">
<div class="icon">🧠</div>
<h3>MRI Upload</h3>
<p class="text-gray-300 mt-3">Secure medical image upload system.</p>
</div>

<div class="card p-8 rounded-xl">
<div class="icon">⚡</div>
<h3>AI Segmentation</h3>
<p class="text-gray-300 mt-3">Automatic tumor detection using AI.</p>
</div>

<div class="card p-8 rounded-xl">
<div class="icon">📊</div>
<h3>3D Visualization</h3>
<p class="text-gray-300 mt-3">Interactive tumor visualization.</p>
</div>

</div>

</section>

<!-- TEAM -->
<section id="team" class="py-20 text-center">

<h2 class="text-4xl neon">Our Team</h2>
<div class="divider"></div>

<div class="grid md:grid-cols-3 gap-10 max-w-6xl mx-auto mt-10">

<div class="card p-6 rounded-xl">
<img src="assets/images/Tsamarah Amelia Putri Ginting-foto.jpeg"
class="w-32 h-32 mx-auto rounded-full object-cover border-2 border-cyan-400">
<h3 class="mt-4">Tsamarah Amelia Putri Ginting</h3>
<p class="text-gray-300">NRP: 5049231018</p>
</div>

<div class="card p-6 rounded-xl">
<img src="assets/images/Kezia Martha Stephanie Silaban.jpeg"
class="w-32 h-32 mx-auto rounded-full object-cover border-2 border-cyan-400">
<h3 class="mt-4">Kezia Martha Stephanie Silaban</h3>
<p class="text-gray-300">NRP: 5049231090</p>
</div>

<div class="card p-6 rounded-xl">
<img src="assets/images/Cintya Melati Sianipar.jpeg"
class="w-32 h-32 mx-auto rounded-full object-cover border-2 border-cyan-400">
<h3 class="mt-4">Cintya Melati Sianipar</h3>
<p class="text-gray-300">NRP: 5049231095</p>
</div>

</div>

</section>

<!-- FOOTER -->
<footer class="py-8 text-center border-t border-slate-700">

<p class="text-gray-400">
© 2026 NeuroAI System
</p>

</footer>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>