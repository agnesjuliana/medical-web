<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = getAppDBConnection();

    $problem     = $_POST['problem'] ?? '';
    $solution    = $_POST['title'] ?? '';
    $methodology = $_POST['methodology'] ?? '';
    $skills      = $_POST['skills'] ?? '';
    $result      = $_POST['result'] ?? '';
    $impact      = $_POST['impact'] ?? '';

    if (empty($problem) || empty($solution)) {
        throw new Exception('Problem dan Solution wajib diisi.');
    }

    $projectId = $_POST['id'] ?? null;
    $editMode  = isset($_POST['editMode']) && $_POST['editMode'] === 'true';

    // ======================
    // INSERT / UPDATE PROJECT
    // ======================
    if ($editMode && $projectId) {

        $stmt = $pdo->prepare("
            UPDATE projects 
            SET problem=?, title=?, methodology=?, skills=?, result=?, impact=?
            WHERE id=?
        ");

        $stmt->execute([
            $problem,
            $solution,
            $methodology,
            $skills,
            $result,
            $impact,
            $projectId
        ]);

    } else {

        $stmt = $pdo->prepare("
            INSERT INTO projects (problem, title, methodology, skills, result, impact, documentation)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $problem,
            $solution,
            $methodology,
            $skills,
            $result,
            $impact,
            null
        ]);

        $projectId = $pdo->lastInsertId();
    }

    // ======================
    // UPLOAD IMAGES (SIMPLE)
    // ======================
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $images = [];

    if (!empty($_FILES['images']['name'][0])) {

        foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {

            $fileName = time() . '_' . basename($_FILES['images']['name'][$i]);
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $filePath)) {
                $images[] = $fileName;
            }
        }

        // simpan semua gambar ke DB (JSON)
        $pdo->prepare("
            UPDATE projects 
            SET documentation = ?
            WHERE id = ?
        ")->execute([
            json_encode($images),
            $projectId
        ]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Project berhasil disimpan.',
        'projectId' => $projectId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}