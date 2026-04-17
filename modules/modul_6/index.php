<?php
/* ======================================================
   LANDING PAGE - THEME MATCH WITH DASHBOARD
   No Login, Direct to Dashboard
   File: pages/landing.php
   ====================================================== */

$pageTitle = 'NeuroAI';
?>

<?php require_once __DIR__ . '/../../layout/header.php'; ?>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(to bottom, #020617, #020617);
    color: #c7d2fe; /* light purple matching theme */
    font-weight: 700; /* bold */
}

/* Card */
.card {
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(148, 163, 184, 0.15);
    transition: 0.3s;
}

.card:hover {
    transform: translateY(-5px);
    border-color: #6366f1;
    box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
}

/* Navbar */
.navbar {
    background: rgba(2,6,23,0.8);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(148,163,184,0.1);
}

.neon {
    color: #6366f1;
    text-shadow: 0 0 10px rgba(99,102,241,0.8);
}
</style>

<!-- NAVBAR -->
<div class="navbar w-full px-8 py-4 flex justify-between items-center">

    <h1 class="text-2xl font-bold text-indigo-400">
        NeuroAI
    </h1>

    <div class="flex gap-6 text-gray-300">

        <a href="#home" class="hover:text-indigo-400">
            Home
        </a>

        <a href="#features">
            Features
        </a>

        <a href="#team">
            Team
        </a>

    </div>

</div>

<!-- HERO -->
<section id="home" class="min-h-screen flex items-center justify-center text-center px-8">

<div>

<h1 class="text-6xl neon">
NeuroAI Brain Tumor Detection
</h1>

<p class="mt-6 text-gray-400 max-w-2xl mx-auto">
AI-powered MRI tumor detection and segmentation system
for fast and accurate medical analysis.
</p>

<a href="dashboard.php"
class="mt-8 inline-block px-8 py-3 bg-indigo-500 rounded-lg hover:bg-indigo-600 transition">

Get Started

</a>

</div>

</section>

<!-- ABOUT -->
<section id="about" class="py-20 text-center">

<h2 class="text-4xl mb-6 neon">
About NeuroAI
</h2>

<p class="max-w-3xl mx-auto text-gray-400">
NeuroAI is an AI-based medical imaging system designed to assist in detecting and analyzing brain tumors from MRI images. The system provides fast, accurate, and intelligent support for medical research and clinical decision-making.
</p>

</section>

<!-- FEATURES -->
<section id="features" class="py-20 text-center">

<h2 class="text-4xl mb-12 neon">
Core Features
</h2>

<div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">

<div class="card p-8 rounded-xl">

<h3 class="text-xl">
Upload MRI
</h3>

<p class="text-gray-400 mt-3">
Upload brain MRI images securely.
</p>

</div>

<div class="card p-8 rounded-xl">

<h3 class="text-xl">
AI Detection
</h3>

<p class="text-gray-400 mt-3">
Automatic tumor detection.
</p>

</div>

<div class="card p-8 rounded-xl">

<h3 class="text-xl">
Medical Reports
</h3>

<p class="text-gray-400 mt-3">
Generate diagnostic reports.
</p>

</div>

</div>

</section>

<!-- TEAM -->
<section id="team" class="py-20 text-center">

<h2 class="text-4xl mb-12 neon">
Our Team
</h2>

<div class="grid md:grid-cols-3 gap-10 max-w-6xl mx-auto">

<div class="card p-6 rounded-xl">

<img
src="assets/images/Tsamarah Amelia Putri Ginting-foto.jpeg"
class="w-28 h-28 mx-auto rounded-full object-cover border-2 border-indigo-500">

<h3 class="mt-4">
Tsamarah Amelia Putri Ginting
</h3>

<p class="text-gray-400">
NRP: 5049231018
</p>

</div>

<div class="card p-6 rounded-xl">

<img
src="assets/images/Kezia Martha Stephanie Silaban.jpeg"
class="w-28 h-28 mx-auto rounded-full object-cover border-2 border-indigo-500">

<h3 class="mt-4">
Kezia Martha Stephanie Silaban
</h3>

<p class="text-gray-400">
NRP: 5049231090
</p>

</div>

<div class="card p-6 rounded-xl">

<img
src="assets/images/Cintya Melati Sianipar.jpeg"
class="w-28 h-28 mx-auto rounded-full object-cover border-2 border-indigo-500">

<h3 class="mt-4">
Cintya Melati Sianipar
</h3>

<p class="text-gray-400">
NRP: 5049231095
</p>

</div>

</div>

</section>

<footer class="py-8 text-center border-t border-slate-700">

<p class="text-gray-400">
© 2026 NeuroAI System
</p>

</footer>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>



/* ======================================================
   BACKEND UPLOAD MRI
   File: api/upload_mri.php
   ====================================================== */

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_FILES['mri'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No file uploaded'
        ]);
        exit;
    }

    $file = $_FILES['mri'];

    $allowed = ['jpg', 'jpeg', 'png'];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid file type'
        ]);
        exit;
    }

    if ($file['size'] > 10 * 1024 * 1024) {
        echo json_encode([
            'status' => 'error',
            'message' => 'File too large (max 10MB)'
        ]);
        exit;
    }

    $uploadDir = __DIR__ . '/../uploads/';

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = uniqid('mri_') . '.' . $ext;

    $path = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $path)) {

        /* CONNECT TO DATABASE modul6_mri (phpMyAdmin) */
        $conn = new mysqli(
            "localhost",
            "root",
            "",
            "modul6_mri"
        );

        if ($conn->connect_error) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Database connection failed'
            ]);
            exit;
        }

        /* INSERT DATA MRI INTO DATABASE */
        $stmt = $conn->prepare(
            "INSERT INTO mri_uploads (filename, path, upload_date) VALUES (?, ?, NOW())"
        );

        $relativePath = 'uploads/' . $filename;

        $stmt->bind_param(
            "ss",
            $filename,
            $relativePath
        );

        $stmt->execute();

        echo json_encode([
            'status' => 'success',
            'filename' => $filename,
            'path' => $relativePath
        ]);

    } else {

        echo json_encode([
            'status' => 'error',
            'message' => 'Upload failed'
        ]);

    }
}

?>