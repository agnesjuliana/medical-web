<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>NeuroAI System</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #020617;
    overflow-x: hidden;
}

/* gradient glow background */
.bg-glow {
    position: absolute;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(99,102,241,0.3), transparent);
    filter: blur(120px);
    z-index: 0;
}

/* particles */
.particle {
    position: absolute;
    width: 3px;
    height: 3px;
    background: #22d3ee;
    opacity: 0.5;
    border-radius: 50%;
    animation: float 12s linear infinite;
}

@keyframes float {
    from { transform: translateY(0); }
    to { transform: translateY(-100vh); }
}

/* brain animation */
.brain {
    width: 280px;
    animation: rotate 12s linear infinite;
    filter: drop-shadow(0 0 20px #6366f1);
}

@keyframes rotate {
    0% { transform: rotateY(0deg); }
    100% { transform: rotateY(360deg); }
}

/* scan line */
.scan {
    position: absolute;
    width: 100%;
    height: 4px;
    background: linear-gradient(to right, transparent, #22d3ee, transparent);
    animation: scan 3s linear infinite;
}

@keyframes scan {
    0% { top: 0; opacity: 0; }
    50% { opacity: 1; }
    100% { top: 100%; opacity: 0; }
}

/* card */
.card {
    background: rgba(15,23,42,0.6);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.1);
    transition: 0.4s;
}

.card:hover {
    transform: translateY(-10px);
    box-shadow: 0 0 25px rgba(99,102,241,0.4);
}

/* neon text */
.neon {
    background: linear-gradient(to right, #6366f1, #8b5cf6, #22d3ee);
    -webkit-background-clip: text;
    color: transparent;
}
</style>
</head>

<body class="text-gray-200">

<!-- BACKGROUND -->
<div class="bg-glow top-0 left-0"></div>

<!-- PARTICLES -->
<script>
for(let i=0;i<40;i++){
    let p=document.createElement("div");
    p.className="particle";
    p.style.left=Math.random()*100+"%";
    p.style.animationDuration=(5+Math.random()*10)+"s";
    document.body.appendChild(p);
}
</script>

<!-- HERO -->
<section class="min-h-screen flex flex-col items-center justify-center text-center relative z-10 px-6">

<h1 class="text-6xl font-bold neon">NeuroAI System</h1>

<p class="mt-6 max-w-2xl text-gray-400">
AI-powered Brain Tumor Segmentation from MRI scans using deep learning technology.
</p>

<!-- brain -->
<div class="relative mt-10">
    <img src="assets/images/brain.png" class="brain mx-auto">
    <div class="scan"></div>
</div>

</section>

<!-- ABOUT -->
<section class="py-20 text-center px-6">

<h2 class="text-4xl neon">About NeuroAI</h2>

<p class="max-w-3xl mx-auto mt-6 text-gray-400">
NeuroAI helps radiologists analyze MRI scans with advanced AI algorithms,
providing fast and accurate tumor detection.
</p>

<img src="https://cdn-icons-png.flaticon.com/512/4149/4149673.png"
class="w-32 mx-auto mt-10 opacity-80">

</section>

<!-- FEATURES -->
<section class="py-20 text-center px-6">

<h2 class="text-4xl neon">Core Features</h2>

<div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto mt-12">

<div class="card p-8 rounded-xl">
<img src="https://cdn-icons-png.flaticon.com/512/2991/2991148.png" class="w-16 mx-auto mb-4">
<h3 class="text-xl">MRI Upload</h3>
<p class="text-gray-400 mt-3">Upload medical images securely.</p>
</div>

<div class="card p-8 rounded-xl">
<img src="https://cdn-icons-png.flaticon.com/512/4712/4712027.png" class="w-16 mx-auto mb-4">
<h3 class="text-xl">AI Segmentation</h3>
<p class="text-gray-400 mt-3">Automatic tumor detection using AI.</p>
</div>

<div class="card p-8 rounded-xl">
<img src="https://cdn-icons-png.flaticon.com/512/2784/2784445.png" class="w-16 mx-auto mb-4">
<h3 class="text-xl">3D Visualization</h3>
<p class="text-gray-400 mt-3">Interactive brain visualization.</p>
</div>

</div>

</section>

<!-- TEAM -->
<section class="py-20 text-center px-6">

<h2 class="text-4xl neon">Our Team</h2>

<div class="grid md:grid-cols-3 gap-10 max-w-6xl mx-auto mt-12">

<div class="card p-6 rounded-xl">
<img src="assets/images/Tsamarah Amelia Putri Ginting-foto.jpeg"
class="w-32 h-32 mx-auto rounded-full object-cover border-2 border-indigo-500">
<h3 class="mt-4">Tsamarah Amelia</h3>
</div>

<div class="card p-6 rounded-xl">
<img src="assets/images/Kezia Martha Stephanie Silaban.jpeg"
class="w-32 h-32 mx-auto rounded-full object-cover border-2 border-indigo-500">
<h3 class="mt-4">Kezia Martha</h3>
</div>

<div class="card p-6 rounded-xl">
<img src="assets/images/Cintya Melati Sianipar.jpeg"
class="w-32 h-32 mx-auto rounded-full object-cover border-2 border-indigo-500">
<h3 class="mt-4">Cintya Melati</h3>
</div>

</div>

</section>

<!-- FOOTER -->
<footer class="text-center py-8 border-t border-gray-700">
<p class="text-gray-500">© 2026 NeuroAI System</p>
</footer>

</body>
</html>