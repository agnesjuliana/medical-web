<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$id   = isset($data['id']) ? (int)$data['id'] : 0;

try {
    $pdo = getDBConnection(); // ← ganti ke backbone_medweb

    if (!$id) {
        throw new Exception("ID project tidak valid");
    }

    // Ambil gambar dulu sebelum dihapus
    $stmt = $pdo->prepare("SELECT documentation FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $doc = $stmt->fetchColumn();

    if ($doc) {
        $images = json_decode($doc, true);

        if (is_array($images)) {
            foreach ($images as $img) {
                // Validasi path: hanya boleh di dalam folder uploads/
                $img = ltrim($img, '/');
                if (strpos($img, '..') !== false) continue;

                $path = __DIR__ . '/../../' . $img;
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }
    }

    // Hapus project dari DB
    $pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}