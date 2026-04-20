<?php
require_once __DIR__ . '/config.php';

$db = getDB();
$userEmail = requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $row = $db->prepare(
        'SELECT tdee, bmr, activity, weight, height, age, gender, recorded_at
           FROM tdee_log WHERE user_email = ?
           ORDER BY recorded_at DESC LIMIT 1'
    );
    $row->execute([$userEmail]);
    $data = $row->fetch();
    jsonSuccess($data ?: []);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body     = json_decode(file_get_contents('php://input'), true);
    $tdee     = (int)($body['tdee']       ?? 0);
    $weight   = (float)($body['weight']   ?? 0);
    $height   = (float)($body['height']   ?? 0);
    $age      = (int)($body['age']         ?? 0);

    if (!$tdee || !$weight || !$height || !$age) jsonError('Data tidak lengkap.');

    $ins = $db->prepare(
        'INSERT INTO tdee_log (user_email, tdee, bmr, activity, weight, height, age, gender, user_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)'
    );
    $ins->execute([
        $userEmail, $tdee, (int)($body['bmr'] ?? 0), 
        (float)($body['activity'] ?? 1.55), 
        $weight, $height, $age, 
        $body['gender'] ?? 'male'
    ]);

    jsonSuccess(['id' => (int)$db->lastInsertId()], 'TDEE berhasil disimpan.');
}

jsonError('Method tidak diizinkan.', 405);