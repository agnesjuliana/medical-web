<?php
/**
 * MRI Delete API
 * 
 * Deletes an MRI scan record and its file.
 * Accepts: POST with id parameter
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

$id = (int)($input['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID scan tidak valid.']);
    exit;
}

try {
    $pdo = getDBConnectionMRI();

    // Fetch the record first (check ownership)
    $stmt = $pdo->prepare("SELECT id, file_path FROM mri_scans WHERE id = :id AND user_id = :user_id");
    $stmt->execute([':id' => $id, ':user_id' => $user['id']]);
    $scan = $stmt->fetch();

    if (!$scan) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Data MRI tidak ditemukan.']);
        exit;
    }

    // Delete the file
    $filePath = __DIR__ . '/../' . $scan['file_path'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Delete record from database
    $deleteStmt = $pdo->prepare("DELETE FROM mri_scans WHERE id = :id AND user_id = :user_id");
    $deleteStmt->execute([':id' => $id, ':user_id' => $user['id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Data MRI berhasil dihapus.',
    ]);

} catch (PDOException $e) {
    error_log("MRI Delete DB Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus data.']);
}
