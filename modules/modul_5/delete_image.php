<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$data      = json_decode(file_get_contents("php://input"), true);
$imagePath = $data['path'] ?? '';

try {
    $pdo = getAppDBConnection();

    if (!$imagePath) {
        throw new Exception("Path kosong");
    }

    // Validasi path: cegah path traversal
    $imagePath = ltrim($imagePath, '/');
    if (strpos($imagePath, '..') !== false) {
        throw new Exception("Path tidak valid");
    }

    // Cari project yang menyimpan gambar ini
    $stmt = $pdo->query("SELECT id, documentation FROM projects");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $images = json_decode($row['documentation'], true);
        if (!is_array($images)) continue;

        if (in_array($imagePath, $images)) {
            $images = array_values(array_filter($images, fn($img) => $img !== $imagePath));

            $pdo->prepare("UPDATE projects SET documentation = ? WHERE id = ?")
                ->execute([
                    count($images) > 0 ? json_encode($images) : null,
                    $row['id']
                ]);
            break;
        }
    }

    // Hapus file fisik
    $fullPath = __DIR__ . '/../../' . $imagePath;
    $deleted  = false;

    if (file_exists($fullPath)) {
        $deleted = unlink($fullPath);
    }

    echo json_encode(['success' => true, 'file_deleted' => $deleted]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}