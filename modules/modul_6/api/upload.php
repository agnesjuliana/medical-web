<?php
/**
 * MRI Upload API
 * 
 * Handles MRI file upload with validation.
 * Accepts: POST with multipart/form-data
 * Returns: JSON response
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/../../../config/database.php';

startSession();

// Must be logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Silakan login terlebih dahulu.']);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$user = getCurrentUser();

// ── Validate form fields ────────────────────────────────────────────
$patientName   = trim($_POST['patient_name'] ?? '');
$patientAge    = isset($_POST['patient_age']) && $_POST['patient_age'] !== '' ? (int)$_POST['patient_age'] : null;
$patientGender = $_POST['patient_gender'] ?? null;
$scanType      = $_POST['scan_type'] ?? 'T1';
$description   = trim($_POST['description'] ?? '');

$errors = [];

if ($patientName === '') {
    $errors[] = 'Nama pasien wajib diisi.';
}

if ($patientGender !== null && !in_array($patientGender, ['Laki-laki', 'Perempuan'])) {
    $errors[] = 'Gender tidak valid.';
}

$validScanTypes = ['T1', 'T2', 'FLAIR', 'DWI', 'SWI', 'Other'];
if (!in_array($scanType, $validScanTypes)) {
    $errors[] = 'Tipe scan tidak valid.';
}

// ── Validate file ───────────────────────────────────────────────────
if (!isset($_FILES['mri_file']) || $_FILES['mri_file']['error'] === UPLOAD_ERR_NO_FILE) {
    $errors[] = 'File MRI wajib diunggah.';
} elseif ($_FILES['mri_file']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE   => 'File terlalu besar (melebihi batas server).',
        UPLOAD_ERR_FORM_SIZE  => 'File terlalu besar (melebihi batas form).',
        UPLOAD_ERR_PARTIAL    => 'File hanya terunggah sebagian.',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan.',
        UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
        UPLOAD_ERR_EXTENSION  => 'Upload dihentikan oleh ekstensi.',
    ];
    $errors[] = $errorMessages[$_FILES['mri_file']['error']] ?? 'Upload error tidak dikenal.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

$file = $_FILES['mri_file'];

// Allowed file types
$allowedMime = [
    'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp',
    'application/dicom', 'application/octet-stream',
    'application/x-nifti', 'application/gzip',
];

$allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'dcm', 'nii', 'nii.gz'];

$maxFileSize = 50 * 1024 * 1024; // 50 MB

$fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$fileMime = mime_content_type($file['tmp_name']) ?: $file['type'];

if ($file['size'] > $maxFileSize) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'File terlalu besar. Maksimum 50MB.']);
    exit;
}

if (!in_array($fileExt, $allowedExt)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Tipe file tidak didukung. Gunakan JPG, PNG, DICOM (.dcm), atau NIfTI (.nii).']);
    exit;
}

// ── Move uploaded file ──────────────────────────────────────────────
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$uniqueName = date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $fileExt;
$destination = $uploadDir . $uniqueName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan file. Silakan coba lagi.']);
    exit;
}

// ── Save to database ────────────────────────────────────────────────
try {
    $pdo = getDBConnectionMRI();

    $stmt = $pdo->prepare("
        INSERT INTO mri_scans 
            (user_id, patient_name, patient_age, patient_gender, scan_type, description, file_name, file_path, file_size, file_type)
        VALUES 
            (:user_id, :patient_name, :patient_age, :patient_gender, :scan_type, :description, :file_name, :file_path, :file_size, :file_type)
    ");

    $stmt->execute([
        ':user_id'        => $user['id'],
        ':patient_name'   => $patientName,
        ':patient_age'    => $patientAge,
        ':patient_gender' => $patientGender,
        ':scan_type'      => $scanType,
        ':description'    => $description ?: null,
        ':file_name'      => $file['name'],
        ':file_path'      => 'uploads/' . $uniqueName,
        ':file_size'      => $file['size'],
        ':file_type'      => $fileMime,
    ]);

    $scanId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'MRI berhasil diunggah!',
        'data'    => [
            'id'           => (int)$scanId,
            'patient_name' => $patientName,
            'scan_type'    => $scanType,
            'file_name'    => $file['name'],
            'file_size'    => $file['size'],
            'status'       => 'pending',
        ],
    ]);

} catch (PDOException $e) {
    error_log("MRI Upload DB Error: " . $e->getMessage());
    // Clean up file if DB insert fails
    if (file_exists($destination)) {
        unlink($destination);
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data ke database.']);
}
