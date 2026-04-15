<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = getAppDBConnection();

    // Ambil project ID dari URL
    $projectId = $_GET['id'] ?? null;

    if (!$projectId) {
        throw new Exception("Project ID tidak valid");
    }

    // Ambil semua file gambar berdasarkan project
    $stmt = $pdo->prepare("
        SELECT file_path 
        FROM project_files
        WHERE project_id = ?
        AND file_type LIKE 'image/%'
        ORDER BY is_preview DESC, id ASC
    ");
    $stmt->execute([$projectId]);

    $files = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Return sebagai JSON
    echo json_encode($files);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}