<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$imagePath = $data['path'] ?? '';

try {
    $pdo = getAppDBConnection();

    if (!$imagePath) {
        throw new Exception("Path kosong");
    }

    // Ambil project_id dari gambar yang dihapus
$stmt = $pdo->prepare("SELECT project_id FROM project_files WHERE file_path = ?");
$stmt->execute([$imagePath]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

$projectId = $project['project_id'] ?? null;

// Hapus dari database
$pdo->prepare("DELETE FROM project_files WHERE file_path = ?")->execute([$imagePath]);

// Hapus file dari folder
$fullPath = __DIR__ . '/' . $imagePath;
if (file_exists($fullPath)) {
    unlink($fullPath);
}

// 🔥 AMBIL GAMBAR PERTAMA YANG MASIH ADA
$stmt = $pdo->prepare("
    SELECT id, file_path FROM project_files
    WHERE project_id = ?
    ORDER BY id ASC
    LIMIT 1
");
$stmt->execute([$projectId]);

$newImage = $stmt->fetch(PDO::FETCH_ASSOC);

// reset semua preview dulu
$pdo->prepare("UPDATE project_files SET is_preview = 0 WHERE project_id = ?")
    ->execute([$projectId]);

if ($newImage) {
    // set gambar pertama jadi preview
    $pdo->prepare("UPDATE project_files SET is_preview = 1 WHERE id = ?")
        ->execute([$newImage['id']]);

    // update ke tabel projects
    $pdo->prepare("UPDATE projects SET documentation = ? WHERE id = ?")
        ->execute([$newImage['file_path'], $projectId]);
} else {
    // kalau sudah tidak ada gambar
    $pdo->prepare("UPDATE projects SET documentation = NULL WHERE id = ?")
        ->execute([$projectId]);
}

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}