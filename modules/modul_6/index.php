<?php
require_once __DIR__. '/../../core/auth.php';
require_once __DIR__. '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'NeuroAI Dashboard';
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #0f766e, #14b8a6);
}

.card {
    background: white;
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

.button-primary {
    background: #14B8A6;
    color: white;
    padding: 12px 20px;
    border-radius: 12px;
    transition: 0.3s;
}

.button-primary:hover {
    background: #0F766E;
}
</style>

<div class="min-h-screen flex justify-center items-start pt-10 px-4">

    <div class="w-full max-w-md">

        <!-- LOGO -->
        <div class="flex justify-center mb-4">
            <img 
                src="<?= BASE_URL ?>/assets/images/logo-neuroai.png"
                alt="NeuroAI Logo"
                class="h-20 object-contain drop-shadow-[0_0_10px_#22D3EE]"
            >
        </div>

        <!-- HEADER -->
        <div class="text-white mb-6 text-center">
            <h2 class="text-lg">Hi, <?php echo $user['name']; ?></h2>
            <h1 class="text-2xl font-bold">NeuroAI System</h1>
            <p class="text-sm opacity-80">Brain Tumor 3D Segmentation</p>
        </div>

        <!-- SEARCH -->
        <div class="card mb-6">
            <input 
                type="text" 
                placeholder="Search feature..."
                class="w-full px-4 py-3 rounded-full bg-gray-100 focus:outline-none"
            >
        </div>

        <!-- FEATURES -->
        <div class="mb-6">
            <h3 class="text-white mb-3 font-semibold">Features</h3>

            <div class="grid grid-cols-3 gap-3">

                <div class="card text-center hover:border hover:border-teal-400">
                    🧠
                    <p class="text-sm mt-2">MRI Upload</p>
                </div>

                <div class="card text-center hover:border hover:border-teal-400">
                    🤖
                    <p class="text-sm mt-2">AI Process</p>
                </div>

                <div class="card text-center hover:border hover:border-teal-400">
                    📊
                    <p class="text-sm mt-2">3D Result</p>
                </div>

            </div>
        </div>

        <!-- MAIN CARD -->
        <div class="card mb-6 bg-gradient-to-r from-teal-400 to-cyan-500 text-white">

            <h3 class="text-lg font-semibold">
                Brain Tumor Segmentation
            </h3>

            <p class="text-sm opacity-90 mt-2">
                AI-powered analysis from MRI images for accurate tumor detection
            </p>

            <div class="mt-4">
                <button class="button-primary">
                    Start Analysis
                </button>
            </div>

        </div>

        <!-- WORKFLOW -->
        <div class="card mb-6">

            <h3 class="font-semibold mb-4">Workflow</h3>

            <div class="space-y-3 text-sm text-gray-600">

                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center">1</div>
                    Upload MRI Image
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center">2</div>
                    AI Segmentation
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center">3</div>
                    3D Visualization Output
                </div>

            </div>

        </div>

        <!-- BUTTON -->
        <div class="text-center mb-10">
            <button class="button-primary w-full">
                Upload MRI Scan
            </button>
        </div>

    </div>

</div>

<!-- TEAM -->
<div class="card max-w-4xl mx-auto mb-10">

    <h3 class="font-semibold text-lg mb-6 text-center">
        Our Development Team
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">

        <!-- TSAMARAH -->
        <div class="p-4 border rounded-xl hover:border-teal-400 transition text-center">
            <img 
                src="<?= BASE_URL ?>/assets/images/Tsamarah Amelia Putri Ginting-foto.jpeg"
                class="w-24 h-24 mx-auto rounded-full object-cover mb-3 shadow-[0_0_15px_rgba(20,184,166,0.5)]"
                alt="Tsamarah"
            >
            <p class="font-semibold">Tsamarah Amelia Putri Ginting</p>
            <p class="text-gray-500">5049231018</p>
        </div>

        <!-- KEZIA -->
        <div class="p-4 border rounded-xl hover:border-teal-400 transition text-center">
            <img 
                src="<?= BASE_URL ?>/assets/images/Kezia Martha Stephanie Silaban.jpeg"
                class="w-24 h-24 mx-auto rounded-full object-cover mb-3 shadow-[0_0_15px_rgba(20,184,166,0.5)]"
                alt="Kezia"
            >
            <p class="font-semibold">Kezia Martha Stephanie Silaban</p>
            <p class="text-gray-500">5049231090</p>
        </div>

        <!-- CINTYA -->
        <div class="p-4 border rounded-xl hover:border-teal-400 transition text-center">
            <img 
                src="<?= BASE_URL ?>/assets/images/Cintya Melati Sianipar.jpeg"
                class="w-24 h-24 mx-auto rounded-full object-cover mb-3 shadow-[0_0_15px_rgba(20,184,166,0.5)]"
                alt="Cintya"
            >
            <p class="font-semibold">Cintya Melati Sianipar</p>
            <p class="text-gray-500">5049231095</p>
        </div>

    </div>

</div>

<!-- FOOTER -->
<div class="text-center text-white text-sm pb-6 opacity-80">
    © 2026 NeuroAI — Clinical Decision Support for Brain Tumor Analysis
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
