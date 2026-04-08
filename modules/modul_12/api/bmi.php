<?php
// ============================================================
//  HEALTHEDU — api/bmi.php
//  POST  { token, bmi, category, weight, height, age, gender }  → simpan
//  GET   ?token=xxx                                              → ambil riwayat
//  POST  { token, action:'delete', id }                         → hapus satu
//  POST  { token, action:'clear' }                              → hapus semua
// ============================================================
require_once __DIR__ . '/config.php';

$db = getDB();

// ─── GET: ambil riwayat ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $token = $_GET['token'] ?? '';
    if (!$token) jsonError('Token diperlukan.', 401);

    // resolve token → user_id
    $s = $db->prepare('SELECT user_id, expires_at FROM sessions WHERE token = ?');
    $s->execute([$token]);
    $sess = $s->fetch();
    if (!$sess || new DateTime() > new DateTime($sess['expires_at'])) {
        jsonError('Sesi tidak valid.', 401);
    }
    $userId = (int) $sess['user_id'];

    $rows = $db->prepare(
        'SELECT id, bmi, category, weight, height, age, gender, recorded_at
           FROM bmi_log
          WHERE user_id = ?
          ORDER BY recorded_at DESC
          LIMIT 15'
    );
    $rows->execute([$userId]);
    jsonSuccess($rows->fetchAll());
}

// ─── POST: simpan / hapus ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = requireAuth();
    $body   = json_decode(file_get_contents('php://input'), true);
    $action = $body['action'] ?? 'save';

    if ($action === 'delete') {
        $id = (int)($body['id'] ?? 0);
        if (!$id) jsonError('ID tidak valid.');
        $db->prepare('DELETE FROM bmi_log WHERE id = ? AND user_id = ?')
           ->execute([$id, $userId]);
        jsonSuccess([], 'Riwayat BMI dihapus.');
    }

    if ($action === 'clear') {
        $db->prepare('DELETE FROM bmi_log WHERE user_id = ?')->execute([$userId]);
        jsonSuccess([], 'Semua riwayat BMI dihapus.');
    }

    // Default: simpan
    $bmi      = (float)($body['bmi']      ?? 0);
    $category = trim($body['category']    ?? '');
    $weight   = (float)($body['weight']   ?? 0);
    $height   = (float)($body['height']   ?? 0);
    $age      = (int)($body['age']         ?? 0);
    $gender   = $body['gender']            ?? 'male';

    if (!$bmi || !$category || !$weight || !$height || !$age) jsonError('Data tidak lengkap.');
    if (!in_array($gender, ['male','female'])) $gender = 'male';

    // Batasi 15 entri per user
    $count = (int) $db->query("SELECT COUNT(*) FROM bmi_log WHERE user_id = $userId")->fetchColumn();
    if ($count >= 15) {
        // Hapus yang paling lama
        $db->prepare(
            'DELETE FROM bmi_log WHERE user_id = ? ORDER BY recorded_at ASC LIMIT 1'
        )->execute([$userId]);
    }

    $ins = $db->prepare(
        'INSERT INTO bmi_log (user_id, bmi, category, weight, height, age, gender)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $ins->execute([$userId, $bmi, $category, $weight, $height, $age, $gender]);

    jsonSuccess(['id' => (int)$db->lastInsertId()], 'BMI berhasil disimpan.');
}

jsonError('Method tidak diizinkan.', 405);
