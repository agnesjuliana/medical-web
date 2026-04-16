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

    // ======================
    // AMBIL SEMUA PROJECT
    // ======================
    $stmt = $pdo->query("SELECT id, documentation FROM projects");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $images = json_decode($row['documentation'], true);

        if (!is_array($images)) continue;

        // cek apakah gambar ada di project ini
        if (in_array($imagePath, $images)) {

            // hapus gambar dari array
            $images = array_values(array_filter($images, function($img) use ($imagePath) {
                return $img !== $imagePath;
            }));

            // update DB
            $pdo->prepare("
                UPDATE projects 
                SET documentation = ?
                WHERE id = ?
            ")->execute([
                count($images) > 0 ? json_encode($images) : null,
                $row['id']
            ]);

            break;
        }
    }

    // ======================
    // HAPUS FILE FISIK
    // ======================
    $fullPath = __DIR__ . '/../../' . $imagePath;

    if (file_exists($fullPath)) {
        unlink($fullPath);
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}