<?php
// ============================================================
//  HEALTHEDU — api/food.php
//  GET  ?token=xxx[&date=YYYY-MM-DD]  → ambil log makanan
//  POST { token, name, calories, meal_type, protein_g, carbs_g, fat_g } → simpan
//  POST { token, action:'delete', id }   → hapus satu
//  POST { token, action:'clear'[, date]} → hapus semua hari ini
//  GET  ?token=xxx&action=week          → 7-day calorie summary
// ============================================================
require_once __DIR__ . '/config.php';

$db = getDB();

// ─── GET ────────────────────────────────────────────────────
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

    // 7-day summary untuk bar chart
    if (($_GET['action'] ?? '') === 'week') {
        $rows = $db->prepare(
            'SELECT log_date, SUM(calories) AS total_cal
               FROM food_log
              WHERE user_id = ?
                AND log_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
              GROUP BY log_date
              ORDER BY log_date ASC'
        );
        $rows->execute([$userId]);
        jsonSuccess($rows->fetchAll());
    }

    // Log harian
    $date = $_GET['date'] ?? date('Y-m-d');
    $rows = $db->prepare(
        'SELECT id, name, calories, meal_type, protein_g, carbs_g, fat_g, recorded_at
           FROM food_log
          WHERE user_id = ? AND log_date = ?
          ORDER BY recorded_at ASC'
    );
    $rows->execute([$userId, $date]);
    jsonSuccess($rows->fetchAll());
}

// ─── POST ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = requireAuth();
    $body   = json_decode(file_get_contents('php://input'), true);
    $action = $body['action'] ?? 'save';

    if ($action === 'delete') {
        $id = (int)($body['id'] ?? 0);
        if (!$id) jsonError('ID tidak valid.');
        $db->prepare('DELETE FROM food_log WHERE id = ? AND user_id = ?')
           ->execute([$id, $userId]);
        jsonSuccess([], 'Log makanan dihapus.');
    }

    if ($action === 'clear') {
        $date = $body['date'] ?? date('Y-m-d');
        $db->prepare('DELETE FROM food_log WHERE user_id = ? AND log_date = ?')
           ->execute([$userId, $date]);
        jsonSuccess([], 'Semua log makanan hari ini dihapus.');
    }

    // Default: simpan
    $name      = trim($body['name']       ?? '');
    $calories  = (int)($body['calories']  ?? 0);
    $mealType  = trim($body['meal_type']  ?? '');
    $proteinG  = (int)($body['protein_g'] ?? 0);
    $carbsG    = (int)($body['carbs_g']   ?? 0);
    $fatG      = (int)($body['fat_g']     ?? 0);
    $logDate   = $body['log_date']         ?? date('Y-m-d');

    if (!$name || !$calories || !$mealType) jsonError('Data tidak lengkap.');

    $ins = $db->prepare(
        'INSERT INTO food_log (user_id, name, calories, meal_type, protein_g, carbs_g, fat_g, log_date)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $ins->execute([$userId, $name, $calories, $mealType, $proteinG, $carbsG, $fatG, $logDate]);

    jsonSuccess(['id' => (int)$db->lastInsertId()], 'Makanan berhasil disimpan.');
}

jsonError('Method tidak diizinkan.', 405);
