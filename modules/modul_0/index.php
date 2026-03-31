<?php
/**
 * Modul 0 — BMI Calculator
 * 
 * Interface kalkulator BMI dengan form input, hasil visual,
 * stat cards, dan tabel riwayat pengukuran.
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
require_once __DIR__ . '/../../config/db_modul0.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'Modul 0 — BMI Calculator';

// ── Fetch BMI history for current user ──────
$db = getModul0Connection();
$stmt = $db->prepare("SELECT * FROM bmi_records WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute(['user_id' => $user['id']]);
$records = $stmt->fetchAll();

// ── Stats ───────────────────────────────────
$totalRecords = count($records);
$avgBmi = $totalRecords > 0 ? round(array_sum(array_column($records, 'bmi_value')) / $totalRecords, 1) : 0;
$lastBmi = $totalRecords > 0 ? $records[0]['bmi_value'] : '-';
$lastCategory = $totalRecords > 0 ? $records[0]['bmi_category'] : '-';

// ── Flash messages ──────────────────────────
$flashSuccess = getFlash('success');
$flashError   = getFlash('error');

// ── Old input (on validation fail) ──────────
$oldNama          = getFlash('old_nama') ?? '';
$oldUsia          = getFlash('old_usia') ?? '';
$oldJenisKelamin  = getFlash('old_jenis_kelamin') ?? '';
$oldTinggiBadan   = getFlash('old_tinggi_badan') ?? '';
$oldBeratBadan    = getFlash('old_berat_badan') ?? '';
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
        <span class="text-gray-700 font-medium">BMI Calculator</span>
    </nav>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">BMI Calculator</h1>
                <p class="text-gray-500 text-sm">Hitung dan pantau Body Mass Index Anda</p>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if ($flashSuccess): ?>
        <div class="mb-6">
            <?= component_alert($flashSuccess, 'success', ['dismissible' => true]) ?>
        </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="mb-6">
            <?= component_alert($flashError, 'error', ['dismissible' => true]) ?>
        </div>
    <?php endif; ?>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <?= component_stat('Total Pengukuran', (string) $totalRecords, [
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>',
        ]) ?>
        <?= component_stat('Rata-rata BMI', $avgBmi > 0 ? (string) $avgBmi : '-', [
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
        ]) ?>
        <?= component_stat('BMI Terakhir', $lastBmi !== '-' ? "$lastBmi ($lastCategory)" : '-', [
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>',
        ]) ?>
    </div>

    <!-- Main Content: Form + BMI Guide -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        <!-- BMI Form (spans 2 cols) -->
        <div class="lg:col-span-2">
            <?= component_card([
                'title' => 'Hitung BMI',
                'subtitle' => 'Masukkan data untuk menghitung Body Mass Index',
                'content' => '
                    <form action="' . BASE_URL . '/modules/modul_0/process_bmi.php" method="POST" class="space-y-5">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ' . component_input('nama', [
                                'label' => 'Nama',
                                'placeholder' => 'Nama subjek pengukuran',
                                'value' => $oldNama,
                                'required' => true,
                            ]) . '
                            ' . component_input('usia', [
                                'label' => 'Usia',
                                'type' => 'number',
                                'placeholder' => 'Usia (tahun)',
                                'value' => $oldUsia,
                                'required' => true,
                                'hint' => '1–150 tahun',
                            ]) . '
                        </div>

                        <!-- Jenis Kelamin -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Jenis Kelamin <span class="text-red-400">*</span>
                            </label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" name="jenis_kelamin" value="L" 
                                           ' . ($oldJenisKelamin === 'L' ? 'checked' : '') . '
                                           class="w-4 h-4 text-cyan-600 border-gray-300 focus:ring-cyan-500" required>
                                    <span class="text-sm text-gray-700 group-hover:text-cyan-600 transition-colors">
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            Laki-laki
                                        </span>
                                    </span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" name="jenis_kelamin" value="P" 
                                           ' . ($oldJenisKelamin === 'P' ? 'checked' : '') . '
                                           class="w-4 h-4 text-cyan-600 border-gray-300 focus:ring-cyan-500">
                                    <span class="text-sm text-gray-700 group-hover:text-cyan-600 transition-colors">
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            Perempuan
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ' . component_input('tinggi_badan', [
                                'label' => 'Tinggi Badan (cm)',
                                'type' => 'number',
                                'placeholder' => 'Contoh: 170',
                                'value' => $oldTinggiBadan,
                                'required' => true,
                                'hint' => '30–300 cm',
                            ]) . '
                            ' . component_input('berat_badan', [
                                'label' => 'Berat Badan (kg)',
                                'type' => 'number',
                                'placeholder' => 'Contoh: 65',
                                'value' => $oldBeratBadan,
                                'required' => true,
                                'hint' => '2–500 kg',
                            ]) . '
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            ' . component_button('Hitung BMI', [
                                'variant' => 'primary',
                                'type' => 'submit',
                                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
                            ]) . '
                            ' . component_button('Reset', [
                                'variant' => 'outline',
                                'type' => 'reset',
                            ]) . '
                        </div>
                    </form>
                ',
            ]) ?>
        </div>

        <!-- BMI Guide Panel -->
        <div class="lg:col-span-1">
            <?= component_card([
                'title' => 'Panduan BMI',
                'subtitle' => 'Klasifikasi WHO',
                'content' => '
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl border border-blue-100">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-blue-400"></div>
                                <span class="text-sm font-medium text-blue-700">Underweight</span>
                            </div>
                            <span class="text-sm text-blue-600 font-mono">&lt; 18.5</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-xl border border-green-100">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                <span class="text-sm font-medium text-green-700">Normal</span>
                            </div>
                            <span class="text-sm text-green-600 font-mono">18.5–24.9</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-amber-50 rounded-xl border border-amber-100">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                                <span class="text-sm font-medium text-amber-700">Overweight</span>
                            </div>
                            <span class="text-sm text-amber-600 font-mono">25.0–29.9</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-xl border border-red-100">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                <span class="text-sm font-medium text-red-700">Obese</span>
                            </div>
                            <span class="text-sm text-red-600 font-mono">≥ 30.0</span>
                        </div>
                    </div>
                    <div class="mt-4 p-3 bg-gray-50 rounded-xl">
                        <p class="text-xs text-gray-500 leading-relaxed">
                            <strong>Rumus BMI:</strong><br>
                            BMI = Berat (kg) ÷ Tinggi (m)²
                        </p>
                    </div>
                ',
            ]) ?>
        </div>

    </div>

    <!-- BMI History Table -->
    <div class="mb-8">
        <?php
        // Build table rows
        $tableRows = [];
        foreach ($records as $i => $record) {
            // Category badge color
            $badgeVariant = match ($record['bmi_category']) {
                'Underweight' => 'info',
                'Normal'      => 'success',
                'Overweight'  => 'warning',
                'Obese'       => 'error',
                default       => 'default',
            };

            $jenisKelaminLabel = $record['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan';

            $tableRows[] = [
                ($i + 1),
                htmlspecialchars($record['nama']),
                $record['usia'] . ' th',
                $jenisKelaminLabel,
                $record['tinggi_badan'] . ' cm',
                $record['berat_badan'] . ' kg',
                '<span class="font-semibold text-gray-800">' . $record['bmi_value'] . '</span>',
                component_badge($record['bmi_category'], $badgeVariant),
                '<span class="text-xs text-gray-400">' . date('d M Y, H:i', strtotime($record['created_at'])) . '</span>',
                component_button('Hapus', [
                    'variant' => 'destructive',
                    'size' => 'sm',
                    'onclick' => "if(confirm('Hapus record ini?')) window.location.href='" . BASE_URL . "/modules/modul_0/delete_bmi.php?id=" . $record['id'] . "'",
                    'icon' => '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>',
                ]),
            ];
        }
        ?>

        <?php if (empty($tableRows)): ?>
            <?= component_card([
                'content' => component_empty_state(
                    'Belum ada data BMI',
                    'Gunakan form di atas untuk menghitung dan menyimpan BMI pertama Anda.',
                ),
            ]) ?>
        <?php else: ?>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Riwayat Pengukuran</h2>
                <?= component_badge($totalRecords . ' record', 'primary') ?>
            </div>
            <?= component_table(
                ['#', 'Nama', 'Usia', 'Jenis Kelamin', 'Tinggi', 'Berat', 'BMI', 'Kategori', 'Tanggal', 'Aksi'],
                $tableRows,
                ['empty' => 'Belum ada data BMI.']
            ) ?>
        <?php endif; ?>
    </div>

</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
