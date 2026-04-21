<?php
/**
 * Patient Form (Create/Edit)
 */
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/config/database3.php';

requireLogin(BASE_URL . '/modules/modul_3/login.php');
startSession();

$user = getCurrentUser();
global $db;

$isEdit = false;
$patient = [
    'name' => '',
    'age' => '',
    'gender' => '',
    'symptoms' => ''
];

if (isset($_GET['id'])) {
    $isEdit = true;
    $stmt = $db->prepare("SELECT * FROM modul3_patients WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([$_GET['id'], $user['id']]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        setFlash('error', 'Data pasien tidak ditemukan.');
        header('Location: patients.php');
        exit;
    }
}

$pageTitle = $isEdit ? 'Ubah Data Pasien' : 'Tambah Data Pasien';
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>

<!-- Include Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>

<style>
:root {
  --background: oklch(0.99 0.005 220);
  --primary: oklch(0.58 0.14 210);
  --gradient-primary: linear-gradient(135deg, oklch(0.58 0.14 210), oklch(0.72 0.15 195));
}
.gradient-primary { background: var(--gradient-primary); }
</style>

<div class="min-h-screen pb-12" style="background-color: var(--background);">

    <!-- Simple Navbar -->
    <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 py-4 shadow-sm sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
            <a href="patients.php" class="flex items-center gap-2 text-gray-500 hover:text-gray-900 transition-colors font-medium">
                <i data-lucide="arrow-left" class="w-5 h-5"></i> Kembali ke Daftar
            </a>
            <div class="font-bold text-lg text-gray-800">Manajemen Pasien</div>
        </div>
    </header>

    <div class="max-w-xl mx-auto px-4 mt-10">
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
            <h2 class="text-2xl font-bold text-gray-800 mb-6"><?= $isEdit ? 'Ubah Data Pasien' : 'Pendaftaran Pasien Baru' ?></h2>

            <form action="patient_process.php" method="POST">
                <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($patient['id']) ?>">
                <?php endif; ?>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5 flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4 text-gray-400"></i> Nama Lengkap Pasien
                    </label>
                    <input type="text" name="name" value="<?= htmlspecialchars($patient['name']) ?>" required
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#5B88D6]/30 focus:border-[#5B88D6] focus:bg-white transition-all shadow-sm">
                </div>

                <div class="grid grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5 flex items-center gap-2">
                            <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i> Usia
                        </label>
                        <input type="number" name="age" value="<?= htmlspecialchars($patient['age']) ?>" required min="1" max="150"
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#5B88D6]/30 focus:border-[#5B88D6] focus:bg-white transition-all shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5 flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4 text-gray-400"></i> Jenis Kelamin
                        </label>
                        <select name="gender" required
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#5B88D6]/30 focus:border-[#5B88D6] focus:bg-white transition-all shadow-sm appearance-none">
                            <option value="">Pilih...</option>
                            <option value="Laki-laki" <?= ($patient['gender'] === 'Laki-laki') ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="Perempuan" <?= ($patient['gender'] === 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5 flex items-center gap-2">
                        <i data-lucide="stethoscope" class="w-4 h-4 text-gray-400"></i> Gejala / Catatan Klinis
                    </label>
                    <textarea name="symptoms" rows="4" 
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#5B88D6]/30 focus:border-[#5B88D6] focus:bg-white transition-all shadow-sm"><?= htmlspecialchars($patient['symptoms']) ?></textarea>
                </div>

                <button type="submit" 
                        class="w-full gradient-primary hover:opacity-90 text-white font-bold py-3.5 px-4 rounded-xl text-sm shadow-md transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambahkan Pasien' ?>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
