<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = getAppDBConnection();

    $projectId = $_GET['id'] ?? null;

    if (!$projectId) {
        throw new Exception("Project ID tidak valid");
    }

    $stmt = $pdo->prepare("
        SELECT documentation 
        FROM projects
        WHERE id = ?
    ");
    $stmt->execute([$projectId]);

    $data = $stmt->fetchColumn();

    if (!$data) {
        echo json_encode([]);
        exit;
    }

    $images = json_decode($data, true);

    echo json_encode($images ?: []);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}