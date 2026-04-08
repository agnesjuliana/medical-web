<?php
// ============================================================
//  HEALTHEDU — api/tdee.php
//  POST { token, tdee, bmr, activity, weight, height, age, gender }
//  GET  ?token=xxx  → ambil TDEE terakhir
// ============================================================
require_once __DIR__ . '/config.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $token = $_GET['token'] ?? '';
    if (!$token) jsonError('Token diperlukan.', 401);

    $s = $db->prepare('SELECT user_id, expires_at FROM sessions WHERE token = ?');
    $s->execute([$token]);
    $sess = $s->fetch();
    if (!$sess || new DateTime() > new DateTime($sess['expires_at'])) {
        jsonError('Sesi tidak valid.', 401);
    }
    $userId = (int) $sess['user_id'];

    $row = $db->prepare(
        'SELECT tdee, bmr, activity, weight, height, age, gender, recorded_at
           FROM tdee_log WHERE user_id = ?
           ORDER BY recorded_at DESC LIMIT 1'
    );
    $row->execute([$userId]);
    $data = $row->fetch();
    jsonSuccess($data ?: []);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = requireAuth();
    $body   = json_decode(file_get_contents('php://input'), true);

    $tdee     = (int)($body['tdee']       ?? 0);
    $bmr      = (int)($body['bmr']        ?? 0);
    $activity = (float)($body['activity'] ?? 1.55);
    $weight   = (float)($body['weight']   ?? 0);
    $height   = (float)($body['height']   ?? 0);
    $age      = (int)($body['age']         ?? 0);
    $gender   = $body['gender']            ?? 'male';

    if (!$tdee || !$weight || !$height || !$age) jsonError('Data tidak lengkap.');

    $ins = $db->prepare(
        'INSERT INTO tdee_log (user_id, tdee, bmr, activity, weight, height, age, gender)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $ins->execute([$userId, $tdee, $bmr, $activity, $weight, $height, $age, $gender]);

    jsonSuccess(['id' => (int)$db->lastInsertId()], 'TDEE berhasil disimpan.');
}

jsonError('Method tidak diizinkan.', 405);
