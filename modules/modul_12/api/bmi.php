<?php
require_once __DIR__ . '/config.php';

$db = getDB();
// Get email from the shared MedWeb session
$userEmail = requireAuth(); 

// ─── GET: ambil riwayat ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // No more token manual check; requireAuth() handles it via session
    $rows = $db->prepare(
        'SELECT id, bmi, category, weight, height, age, gender, recorded_at
           FROM bmi_log
          WHERE user_email = ?
          ORDER BY recorded_at DESC
          LIMIT 15'
    );
    $rows->execute([$userEmail]);
    jsonSuccess($rows->fetchAll());
}

// ─── POST: simpan / hapus ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true);
    $action = $body['action'] ?? 'save';

    if ($action === 'delete') {
        $id = (int)($body['id'] ?? 0);
        if (!$id) jsonError('ID tidak valid.');
        $db->prepare('DELETE FROM bmi_log WHERE id = ? AND user_email = ?')
           ->execute([$id, $userEmail]);
        jsonSuccess([], 'Riwayat BMI dihapus.');
    }

    if ($action === 'clear') {
        $db->prepare('DELETE FROM bmi_log WHERE user_email = ?')->execute([$userEmail]);
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

    // Limit 15 entries using email
    $stmtCount = $db->prepare("SELECT COUNT(*) FROM bmi_log WHERE user_email = ?");
    $stmtCount->execute([$userEmail]);
    $count = (int)$stmtCount->fetchColumn();
    
    if ($count >= 15) {
        $db->prepare('DELETE FROM bmi_log WHERE user_email = ? ORDER BY recorded_at ASC LIMIT 1')
           ->execute([$userEmail]);
    }

    $ins = $db->prepare(
        'INSERT INTO bmi_log (user_email, bmi, category, weight, height, age, gender, user_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, 0)'
    );
    $ins->execute([$userEmail, $bmi, $category, $weight, $height, $age, $gender]);

    jsonSuccess(['id' => (int)$db->lastInsertId()], 'BMI berhasil disimpan.');
}

jsonError('Method tidak diizinkan.', 405);