<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = getAppDBConnection();

    $projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if (!$projectId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Project ID tidak valid']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT documentation FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $data = $stmt->fetchColumn();

    if (!$data) {
        echo json_encode([]);
        exit;
    }

    $images = json_decode($data, true);
    echo json_encode($images ?: []);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}