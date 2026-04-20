<?php
/**
 * MRI List API
 * 
 * Returns paginated list of MRI scans for the current user.
 * Accepts: GET with optional ?page=&limit=&search=
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

$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = min(50, max(1, (int)($_GET['limit'] ?? 10)));
$search = trim($_GET['search'] ?? '');
$offset = ($page - 1) * $limit;

try {
    $pdo = getDBConnectionMRI();

    // Build WHERE clause
    $where = "WHERE user_id = :user_id";
    $params = [':user_id' => $user['id']];

    if ($search !== '') {
        $where .= " AND (patient_name LIKE :search OR description LIKE :search2)";
        $params[':search']  = "%{$search}%";
        $params[':search2'] = "%{$search}%";
    }

    // Count total
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM mri_scans {$where}");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    // Fetch records
    $stmt = $pdo->prepare("
        SELECT id, patient_name, patient_age, patient_gender, scan_type, 
               description, file_name, file_path, file_size, file_type,
               diagnosis_status, diagnosis_result, created_at, updated_at
        FROM mri_scans 
        {$where}
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    
    // Bind separately because PDO limit/offset need int type
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $scans = $stmt->fetchAll();

    // Format file sizes and add thumbnail URL
    foreach ($scans as &$scan) {
        $scan['file_size_formatted'] = formatFileSize($scan['file_size']);
        $scan['thumbnail_url'] = getBaseUrl() . '/modules/modul_6/' . $scan['file_path'];
    }
    unset($scan);

    echo json_encode([
        'success' => true,
        'data'    => $scans,
        'pagination' => [
            'current_page' => $page,
            'per_page'     => $limit,
            'total'        => $total,
            'total_pages'  => (int)ceil($total / $limit),
        ],
    ]);

} catch (PDOException $e) {
    error_log("MRI List DB Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil data.']);
}

// ── Helpers ─────────────────────────────────────────────────────────

function formatFileSize(int $bytes): string
{
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

function getBaseUrl(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . BASE_URL;
}
