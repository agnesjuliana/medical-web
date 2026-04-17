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
    background: linear-gradient(to bottom, #020617, #020617);
    color: #e2e8f0;
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

/* Sidebar */
.sidebar {
    background: #020617;
    border-right: 1px solid rgba(148,163,184,0.1);
}

.nav-item {
    padding: 12px 16px;
    border-radius: 10px;
    transition: 0.2s;
    cursor: pointer;
}

.nav-item:hover {
    background: rgba(99,102,241,0.15);
}

.active {
    background: rgba(99,102,241,0.25);
}

/* Topbar */
.topbar {
    background: rgba(2,6,23,0.8);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(148,163,184,0.1);
}
</style>

<div class="flex min-h-screen">

<!-- SIDEBAR -->
<div class="sidebar w-64 p-6">

    <h1 class="text-2xl font-bold text-indigo-400 mb-8">
        NeuroAI
    </h1>

    <p class="text-xs text-gray-400 mb-3">
        NAVIGATION
    </p>

    <div class="space-y-2">

        <div class="nav-item active">
            Dashboard
        </div>

        <div class="nav-item">
            Upload MRI
        </div>

        <div class="nav-item">
            3D Segmentation
        </div>

        <div class="nav-item">
            Patient Records
        </div>

    </div>

    <p class="text-xs text-gray-400 mt-8 mb-3">
        ANALYSIS
    </p>

    <div class="space-y-2">

        <div class="nav-item">
            AI Analysis
        </div>

        <div class="nav-item">
            Tumor Detection
        </div>

        <div class="nav-item">
            Reports
        </div>

    </div>

</div>

<!-- MAIN -->
<div class="flex-1">

<!-- TOPBAR -->
<div class="topbar px-8 py-4 flex justify-between items-center">

    <h2 class="text-lg font-semibold">
        Dashboard
    </h2>

    <div class="flex items-center gap-4">

        <input
            type="text"
            placeholder="Search patients..."
            class="px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg text-sm"
        >

        <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center">
            <?= strtoupper(substr($user['name'],0,2)) ?>
        </div>

    </div>

</div>

<!-- CONTENT -->
<div class="p-8 space-y-6">

<!-- WELCOME -->
<div class="card p-6 rounded-xl flex justify-between items-center">

    <div>

        <h2 class="text-xl font-semibold">
            Welcome to NeuroAI
        </h2>

        <p class="text-gray-400 text-sm">
            AI-powered 3D Brain Tumor Segmentation System
        </p>

    </div>

    <button class="px-5 py-2 bg-indigo-500 rounded-lg hover:bg-indigo-600 transition">
        Upload MRI Scan
    </button>

</div>

<!-- STATS -->
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6">

<div class="card p-5 rounded-xl">
    <p class="text-gray-400 text-sm">Total Patients</p>
    <h3 class="text-2xl font-bold mt-2">1,284</h3>
</div>

<div class="card p-5 rounded-xl">
    <p class="text-gray-400 text-sm">MRI Scans</p>
    <h3 class="text-2xl font-bold mt-2">4,739</h3>
</div>

<div class="card p-5 rounded-xl">
    <p class="text-gray-400 text-sm">Detected Tumors</p>
    <h3 class="text-2xl font-bold mt-2">318</h3>
</div>

<div class="card p-5 rounded-xl">
    <p class="text-gray-400 text-sm">AI Accuracy</p>
    <h3 class="text-2xl font-bold mt-2">97.4%</h3>
</div>

<div class="card p-5 rounded-xl">
    <p class="text-gray-400 text-sm">Active Cases</p>
    <h3 class="text-2xl font-bold mt-2">42</h3>
</div>

<div class="card p-5 rounded-xl">
    <p class="text-gray-400 text-sm">Reports</p>
    <h3 class="text-2xl font-bold mt-2">2,156</h3>
</div>

</div>

<!-- CHART AREA -->
<div class="grid md:grid-cols-2 gap-6">

<div class="card p-6 rounded-xl">

    <h3 class="font-semibold mb-4">
        Tumor Detection Trend
    </h3>

    <canvas id="chart1"></canvas>

</div>

<div class="card p-6 rounded-xl">

    <h3 class="font-semibold mb-4">
        MRI Upload Activity
    </h3>

    <canvas id="chart2"></canvas>

</div>

</div>

<!-- OUR TEAM -->
<div class="card p-6 rounded-xl">

    <h3 class="text-xl font-semibold mb-6">
        Our Team
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Tsamarah -->
        <div class="text-center">

            <img
                src="assets/images/Tsamarah Amelia Putri Ginting-foto.jpeg"
                class="w-28 h-28 mx-auto rounded-full object-cover border-2 border-indigo-500 shadow-lg hover:scale-105 transition"
            >

            <h4 class="mt-4 font-semibold">
                Tsamarah Amelia Putri Ginting
            </h4>

            <p class="text-sm text-gray-400">
                NRP: 5049231018
            </p>

        </div>

        <!-- Kezia -->
        <div class="text-center">

            <img
                src="assets/images/Kezia Martha Stephanie Silaban.jpeg"
                class="w-28 h-28 mx-auto rounded-full object-cover border-2 border-indigo-500 shadow-lg hover:scale-105 transition"
            >

            <h4 class="mt-4 font-semibold">
                Kezia Martha Stephanie Silaban
            </h4>

            <p class="text-sm text-gray-400">
                NRP: 5049231090
            </p>

        </div>

        <!-- Cintya -->
        <div class="text-center">

            <img
                src="assets/images/Cintya Melati Sianipar.jpeg"
                class="w-28 h-28 mx-auto rounded-full object-cover border-2 border-indigo-500 shadow-lg hover:scale-105 transition"
            >

            <h4 class="mt-4 font-semibold">
                Cintya Melati Sianipar
            </h4>

            <p class="text-sm text-gray-400">
                NRP: 5049231095
            </p>

        </div>

    </div>

</div>

</div>

</div>

</div>

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

new Chart(
    document.getElementById('chart1'),
    {
        type: 'line',
        data: {
            labels: ['Jan','Feb','Mar','Apr','May','Jun'],
            datasets: [
                {
                    label: 'Tumors',
                    data: [25,30,28,35,42,38]
                }
            ]
        }
    }
)

new Chart(
    document.getElementById('chart2'),
    {
        type: 'bar',
        data: {
            labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
            datasets: [
                {
                    label: 'Uploads',
                    data: [18,24,21,32,28,15,10]
                }
            ]
        }
    }
)

</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>