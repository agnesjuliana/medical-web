<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = getDBConnection(); // ← ganti ke backbone_medweb

    $problem          = $_POST['problem']          ?? '';
    $solution         = $_POST['title']            ?? '';
    $methodology      = $_POST['methodology']      ?? '';
    $skills           = $_POST['skills']           ?? '';
    $result           = $_POST['result']           ?? '';
    $impact           = $_POST['impact']           ?? '';
    $contributor_name = trim($_POST['contributor'] ?? '');

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
            SET problem=?, title=?, methodology=?, skills=?, result=?, impact=?,
                contributor_name=?, updated_at=NOW()
            WHERE id=?
        ");

        $stmt->execute([
            $problem,
            $solution,
            $methodology,
            $skills,
            $result,
            $impact,
            $contributor_name ?: null,
            (int)$projectId
        ]);

    } else {

        $stmt = $pdo->prepare("
            INSERT INTO projects (problem, title, methodology, skills, result, impact, documentation, contributor_name, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $stmt->execute([
            $problem,
            $solution,
            $methodology,
            $skills,
            $result,
            $impact,
            null,
            $contributor_name ?: null,
        ]);

        $projectId = $pdo->lastInsertId();
    }

    // ======================
    // UPLOAD IMAGES
    // ======================
    $uploadDir = __DIR__ . '/../../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Ambil gambar lama
    $stmt = $pdo->prepare("SELECT documentation FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $oldData = $stmt->fetchColumn();

    $oldImages = [];
    if ($oldData) {
        $decoded = json_decode($oldData, true);
        if (is_array($decoded)) {
            $oldImages = $decoded;
        }
    }

    // Upload gambar baru
    $newImages = [];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
            $mimeType = mime_content_type($tmpName);
            if (!in_array($mimeType, $allowedTypes)) continue;

            $ext      = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
            $fileName = time() . '_' . $i . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $filePath)) {
                $newImages[] = 'uploads/' . $fileName;
            }
        }
    }

    // Gabung lama + baru
    if (!empty($newImages)) {
        $allImages = array_merge($oldImages, $newImages);
        $pdo->prepare("UPDATE projects SET documentation = ? WHERE id = ?")
            ->execute([json_encode($allImages), $projectId]);
    }

    echo json_encode([
        'success'     => true,
        'message'     => 'Project berhasil disimpan.',
        'projectId'   => $projectId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}