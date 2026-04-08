<?php
// ============================================================
//  HEALTHEDU — api/login.php
//  POST { email, password }
// ============================================================
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method tidak diizinkan.', 405);

$body     = json_decode(file_get_contents('php://input'), true);
$email    = trim($body['email']    ?? '');
$password =       $body['password'] ?? '';

if (!$email || !$password) jsonError('Email dan password harus diisi.');

$db   = getDB();
$stmt = $db->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    jsonError('Email atau password salah.');
}

// Hapus sesi lama user ini (opsional, biar bersih)
$db->prepare('DELETE FROM sessions WHERE user_id = ?')->execute([$user['id']]);

// Buat sesi baru
$token   = bin2hex(random_bytes(32));
$expires = (new DateTime('+30 days'))->format('Y-m-d H:i:s');
$db->prepare('INSERT INTO sessions (user_id, token, expires_at) VALUES (?, ?, ?)')
   ->execute([$user['id'], $token, $expires]);

jsonSuccess([
    'token' => $token,
    'user'  => [
        'id'    => $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
    ],
], 'Login berhasil!');
