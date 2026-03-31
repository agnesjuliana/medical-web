<?php
/**
 * Process BMI — Modul 0
 * 
 * Handles POST from BMI calculator form.
 * Validates input, calculates BMI, saves to database.
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../config/db_modul0.php';

requireLogin();
startSession();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/modules/modul_0/index.php');
    exit;
}

$user = getCurrentUser();
if (!$user) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// ── Validate input ──────────────────────────
$nama           = trim($_POST['nama'] ?? '');
$usia           = (int) ($_POST['usia'] ?? 0);
$jenis_kelamin  = $_POST['jenis_kelamin'] ?? '';
$tinggi_badan   = (float) ($_POST['tinggi_badan'] ?? 0);
$berat_badan    = (float) ($_POST['berat_badan'] ?? 0);

$errors = [];

if ($nama === '') {
    $errors[] = 'Nama wajib diisi.';
}
if ($usia < 1 || $usia > 150) {
    $errors[] = 'Usia harus antara 1–150 tahun.';
}
if (!in_array($jenis_kelamin, ['L', 'P'])) {
    $errors[] = 'Jenis kelamin tidak valid.';
}
if ($tinggi_badan < 30 || $tinggi_badan > 300) {
    $errors[] = 'Tinggi badan harus antara 30–300 cm.';
}
if ($berat_badan < 2 || $berat_badan > 500) {
    $errors[] = 'Berat badan harus antara 2–500 kg.';
}

if (!empty($errors)) {
    setFlash('error', implode(' ', $errors));
    // Preserve old input
    setFlash('old_nama', $nama);
    setFlash('old_usia', (string) $usia);
    setFlash('old_jenis_kelamin', $jenis_kelamin);
    setFlash('old_tinggi_badan', (string) $tinggi_badan);
    setFlash('old_berat_badan', (string) $berat_badan);
    header('Location: ' . BASE_URL . '/modules/modul_0/index.php');
    exit;
}

// ── Calculate BMI ───────────────────────────
$tinggi_m = $tinggi_badan / 100;
$bmi_value = round($berat_badan / ($tinggi_m * $tinggi_m), 2);

// Determine category
if ($bmi_value < 18.5) {
    $bmi_category = 'Underweight';
} elseif ($bmi_value < 25) {
    $bmi_category = 'Normal';
} elseif ($bmi_value < 30) {
    $bmi_category = 'Overweight';
} else {
    $bmi_category = 'Obese';
}

// ── Save to database ────────────────────────
try {
    $db = getModul0Connection();

    $stmt = $db->prepare("
        INSERT INTO bmi_records 
            (user_id, nama, usia, jenis_kelamin, tinggi_badan, berat_badan, bmi_value, bmi_category)
        VALUES 
            (:user_id, :nama, :usia, :jenis_kelamin, :tinggi_badan, :berat_badan, :bmi_value, :bmi_category)
    ");

    $stmt->execute([
        'user_id'        => $user['id'],
        'nama'           => $nama,
        'usia'           => $usia,
        'jenis_kelamin'  => $jenis_kelamin,
        'tinggi_badan'   => $tinggi_badan,
        'berat_badan'    => $berat_badan,
        'bmi_value'      => $bmi_value,
        'bmi_category'   => $bmi_category,
    ]);

    setFlash('success', "BMI berhasil dihitung: $bmi_value ($bmi_category)");
} catch (PDOException $e) {
    error_log("BMI save error: " . $e->getMessage());
    setFlash('error', 'Gagal menyimpan data BMI. Silakan coba lagi.');
}

header('Location: ' . BASE_URL . '/modules/modul_0/index.php');
exit;
