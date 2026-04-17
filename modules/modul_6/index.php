<?php
$pageTitle = 'NeuroAI';
?>

<?php require_once __DIR__ . '/../../layout/header.php'; ?>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: radial-gradient(circle at top, #020617, #020617);
    color: #e5e7eb;
    font-weight: bold;
    overflow-x: hidden;
}

/* Glow background element */
.bg-glow {
    position: absolute;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(99,102,241,0.25), transparent 70%);
    filter: blur(100px);
    z-index: 0;
}

/* Floating orb */
.orb {
    position: absolute;
    width: 200px;
    height: 200px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.4;
    animation: float 8s ease-in-out infinite;
}

@keyframes float {
    0%,100% { transform: translateY(0px); }
    50% { transform: translateY(-30px); }
}

/* Text */
h1, h2, h3, h4, p {
    color: #e5e7eb;
}

/* Neon */
.neon {
    color: #6366f1;
    text-shadow: 0 0 15px rgba(99,102,241,0.9);
}

/* Divider line */
.divider {
    width: 80px;
    height: 4px;
    margin: 16px auto;
    background: linear-gradient(to right, #6366f1, #8b5cf6);
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(99,102,241,0.8);
}

/* Card */
.card {
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(148, 163, 184, 0.15);
    transition: 0.4s;
}

.card:hover {
    transform: translateY(-8px) scale(1.02);
    border-color: #6366f1;
    box-shadow: 0 0 25px rgba(99, 102, 241, 0.4);
}

/* Image hover */
.card img {
    transition: 0.4s;
}

.card:hover img {
    transform: scale(1.05);
}

/* Fade animation */
.fade-in {
    animation: fade 1.2s ease-in;
}

@keyframes fade {
    from { opacity: 0; transform: translateY(20px);}
    to { opacity: 1; transform: translateY(0);}
}
</style>

<!-- Background effects -->
<div class="bg-glow top-0 left-0"></div>
<div class="orb top-20 right-20"></div>

<!-- NAVBAR -->
<div class="w-full border-b border-slate-700 bg-[#020617]/80 backdrop-blur-md relative z-10">
<div class="max-w-7xl mx-auto flex justify-between items-center px-8 py-4">

<h1 class="text-2xl neon">NeuroAI</h1>

<div class="flex gap-6 text-gray-300">
<a href="#home" class="hover:text-indigo-400">Home</a>
<a href="#about">About</a>
<a href="#features">Features</a>
<a href="#team">Team</a>
</div>

</div>
</div>

<!-- HERO -->
<section id="home"
class="min-h-screen flex items-center justify-center text-center px-8 relative z-10">

<div class="fade-in">

<h1 class="text-6xl neon">
NeuroAI System
</h1>

<div class="divider"></div>

<p class="mt-6 text-gray-300 max-w-2xl mx-auto">
AI-powered 3D Brain Tumor Segmentation from MRI scans
for fast, accurate, and intelligent medical diagnosis.
</p>

</div>
</section>

<!-- ABOUT -->
<section id="about" class="py-20 text-center relative z-10 fade-in">

<h2 class="text-4xl neon">About NeuroAI</h2>
<div class="divider"></div>

<p class="max-w-3xl mx-auto text-gray-300 mt-4">
NeuroAI is an intelligent medical system designed to assist
radiologists and clinicians in detecting and segmenting
brain tumors from MRI scans using deep learning technology.
</p>

</section>

<!-- FEATURES -->
<section id="features" class="py-20 text-center relative z-10">

<h2 class="text-4xl neon">Core Features</h2>
<div class="divider"></div>

<div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto mt-10">

<div class="card p-8 rounded-xl">
<h3 class="text-xl">MRI Upload</h3>
<p class="text-gray-300 mt-3">Secure medical image upload system.</p>
</div>

<div class="card p-8 rounded-xl">
<h3 class="text-xl">AI Segmentation</h3>
<p class="text-gray-300 mt-3">Automatic tumor detection using AI.</p>
</div>

<div class="card p-8 rounded-xl">
<h3 class="text-xl">3D Visualization</h3>
<p class="text-gray-300 mt-3">Interactive tumor visualization.</p>
</div>

</div>

</section>

<!-- TEAM -->
<section id="team" class="py-20 text-center relative z-10">

<h2 class="text-4xl neon">Our Team</h2>
<div class="divider"></div>

<div class="grid md:grid-cols-3 gap-10 max-w-6xl mx-auto mt-10">

<div class="card p-6 rounded-xl">
<img src="assets/images/Tsamarah Amelia Putri Ginting-foto.jpeg"
class="w-32 h-32 mx-auto rounded-full object-cover border-2 border-indigo-500">
<h3 class="mt-4">Tsamarah Amelia Putri Ginting</h3>
<p class="text-gray-300">NRP: 5049231018</p>
</div>

<div class="card p-6 rounded-xl">
<img src="assets/images/Kezia Martha Stephanie Silaban.jpeg"
class="w-32 h-32 mx-auto rounded-full object-cover border-2 border-indigo-500">
<h3 class="mt-4">Kezia Martha Stephanie Silaban</h3>
<p class="text-gray-300">NRP: 5049231090</p>
</div>

<div class="card p-6 rounded-xl">
<img src="assets/images/Cintya Melati Sianipar.jpeg"
class="w-32 h-32 mx-auto rounded-full object-cover border-2 border-indigo-500">
<h3 class="mt-4">Cintya Melati Sianipar</h3>
<p class="text-gray-300">NRP: 5049231095</p>
</div>

</div>

</section>

<!-- FOOTER -->
<footer class="py-8 text-center border-t border-slate-700 relative z-10">

<p class="text-gray-400">
© 2026 NeuroAI System
</p>

</footer>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>