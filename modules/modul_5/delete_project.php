<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? 0;

$pdo = getAppDBConnection();

try {
    // Ambil semua file dulu
    $stmt = $pdo->prepare("SELECT file_path FROM project_files WHERE project_id = ?");
    $stmt->execute([$id]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hapus file dari folder
    foreach ($files as $file) {
        $path = __DIR__ . '/' . $file['file_path'];
        if (file_exists($path)) {
            unlink($path);
        }
    }

    // Hapus dari DB
    $pdo->prepare("DELETE FROM project_files WHERE project_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}