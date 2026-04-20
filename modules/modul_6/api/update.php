<?php
/**
 * MRI Update API
 * 
 * Updates metadata for an existing MRI scan record.
 * Accepts: POST with form data (id, patient_name, etc.)
 * Returns: JSON response
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/../../../config/database.php';

startSession();

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$user = getCurrentUser();

// Accept both form-data and JSON body
$input = $_POST;
if (empty($input)) {
    $jsonInput = json_decode(file_get_contents('php://input'), true);
    if ($jsonInput) $input = $jsonInput;
}

$id            = (int)($input['id'] ?? 0);
$patientName   = trim($input['patient_name'] ?? '');
$patientAge    = isset($input['patient_age']) && $input['patient_age'] !== '' ? (int)$input['patient_age'] : null;
$patientGender = $input['patient_gender'] ?? null;
$scanType      = $input['scan_type'] ?? null;
$description   = trim($input['description'] ?? '');

// ── Validation ──────────────────────────────────────────────────────
$errors = [];

if ($id <= 0) {
    $errors[] = 'ID scan tidak valid.';
}
if ($patientName === '') {
    $errors[] = 'Nama pasien wajib diisi.';
}
if ($patientGender !== null && !in_array($patientGender, ['Laki-laki', 'Perempuan'])) {
    $errors[] = 'Gender tidak valid.';
}
$validScanTypes = ['T1', 'T2', 'FLAIR', 'DWI', 'SWI', 'Other'];
if ($scanType !== null && !in_array($scanType, $validScanTypes)) {
    $errors[] = 'Tipe scan tidak valid.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $pdo = getDBConnectionMRI();

    // Check ownership
    $checkStmt = $pdo->prepare("SELECT id FROM mri_scans WHERE id = :id AND user_id = :user_id");
    $checkStmt->execute([':id' => $id, ':user_id' => $user['id']]);
    
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Data MRI tidak ditemukan.']);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE mri_scans SET
            patient_name   = :patient_name,
            patient_age    = :patient_age,
            patient_gender = :patient_gender,
            scan_type      = :scan_type,
            description    = :description
        WHERE id = :id AND user_id = :user_id
    ");

    $stmt->execute([
        ':patient_name'   => $patientName,
        ':patient_age'    => $patientAge,
        ':patient_gender' => $patientGender,
        ':scan_type'      => $scanType,
        ':description'    => $description ?: null,
        ':id'             => $id,
        ':user_id'        => $user['id'],
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Data MRI berhasil diperbarui.',
    ]);

} catch (PDOException $e) {
    error_log("MRI Update DB Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data.']);
}
