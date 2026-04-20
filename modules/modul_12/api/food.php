<?php
require_once __DIR__ . '/config.php';

$db = getDB();
$userEmail = requireAuth();

// ─── GET ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // 7-day summary
    if (($_GET['action'] ?? '') === 'week') {
        $rows = $db->prepare(
            'SELECT log_date, SUM(calories) AS total_cal
               FROM food_log
              WHERE user_email = ?
                AND log_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
              GROUP BY log_date
              ORDER BY log_date ASC'
        );
        $rows->execute([$userEmail]);
        jsonSuccess($rows->fetchAll());
    }

    // Daily log
    $date = $_GET['date'] ?? date('Y-m-d');
    $rows = $db->prepare(
        'SELECT id, name, calories, meal_type, protein_g, carbs_g, fat_g, recorded_at
           FROM food_log
          WHERE user_email = ? AND log_date = ?
          ORDER BY recorded_at ASC'
    );
    $rows->execute([$userEmail, $date]);
    jsonSuccess($rows->fetchAll());
}

// ─── POST ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true);
    $action = $body['action'] ?? 'save';

    if ($action === 'delete') {
        $id = (int)($body['id'] ?? 0);
        $db->prepare('DELETE FROM food_log WHERE id = ? AND user_email = ?')
           ->execute([$id, $userEmail]);
        jsonSuccess([], 'Log makanan dihapus.');
    }

    if ($action === 'clear') {
        $date = $body['date'] ?? date('Y-m-d');
        $db->prepare('DELETE FROM food_log WHERE user_email = ? AND log_date = ?')
           ->execute([$userEmail, $date]);
        jsonSuccess([], 'Semua log makanan hari ini dihapus.');
    }

    $name      = trim($body['name']       ?? '');
    $calories  = (int)($body['calories']  ?? 0);
    $mealType  = trim($body['meal_type']  ?? '');
    $logDate   = $body['log_date']        ?? date('Y-m-d');

    if (!$name || !$calories || !$mealType) jsonError('Data tidak lengkap.');

    $ins = $db->prepare(
        'INSERT INTO food_log (user_email, name, calories, meal_type, protein_g, carbs_g, fat_g, log_date, user_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)'
    );
    $ins->execute([
        $userEmail, $name, $calories, $mealType, 
        (int)($body['protein_g'] ?? 0), 
        (int)($body['carbs_g'] ?? 0), 
        (int)($body['fat_g'] ?? 0), 
        $logDate
    ]);

    jsonSuccess(['id' => (int)$db->lastInsertId()], 'Makanan berhasil disimpan.');
}

jsonError('Method tidak diizinkan.', 405);