<?php
// ============================================================
//  HEALTHEDU — api/logout.php
//  POST { token }
// ============================================================
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method tidak diizinkan.', 405);

$body  = json_decode(file_get_contents('php://input'), true);
$token = $body['token'] ?? '';

if ($token) {
    getDB()->prepare('DELETE FROM sessions WHERE token = ?')->execute([$token]);
}

jsonSuccess([], 'Logout berhasil.');
