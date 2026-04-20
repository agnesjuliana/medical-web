<?php
/**
 * MRI Detail API
 * 
 * Returns a single MRI scan record.
 * Accepts: GET with ?id=
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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$user = getCurrentUser();
$id   = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID scan tidak valid.']);
    exit;
}

try {
    $pdo = getDBConnectionMRI();

    $stmt = $pdo->prepare("
        SELECT * FROM mri_scans 
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->execute([':id' => $id, ':user_id' => $user['id']]);
    $scan = $stmt->fetch();

    if (!$scan) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Data MRI tidak ditemukan.']);
        exit;
    }

    // Add formatted info
    $scan['file_size_formatted'] = formatFileSize($scan['file_size']);
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . BASE_URL;
    $scan['thumbnail_url'] = $baseUrl . '/modules/modul_6/' . $scan['file_path'];

    echo json_encode([
        'success' => true,
        'data'    => $scan,
    ]);

} catch (PDOException $e) {
    error_log("MRI Detail DB Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil data.']);
}

function formatFileSize(int $bytes): string
{
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}
