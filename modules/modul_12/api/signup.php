<?php
// ============================================================
//  HEALTHEDU — api/signup.php
//  POST { name, email, password }
// ============================================================
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method tidak diizinkan.', 405);

$body = json_decode(file_get_contents('php://input'), true);

$name     = trim($body['name']     ?? '');
$email    = trim($body['email']    ?? '');
$password =       $body['password'] ?? '';

// Validasi
if (!$name)                       jsonError('Nama lengkap harus diisi.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonError('Format email tidak valid.');
if (strlen($password) < 8)        jsonError('Password minimal 8 karakter.');

$db = getDB();

// Cek email sudah terdaftar
$chk = $db->prepare('SELECT id FROM users WHERE email = ?');
$chk->execute([$email]);
if ($chk->fetch()) jsonError('Email sudah terdaftar. Silakan login.');

// Simpan user
$hash = password_hash($password, PASSWORD_BCRYPT);
$ins  = $db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
$ins->execute([$name, $email, $hash]);
$userId = (int) $db->lastInsertId();

// Buat sesi
$token   = bin2hex(random_bytes(32));
$expires = (new DateTime('+30 days'))->format('Y-m-d H:i:s');
$db->prepare('INSERT INTO sessions (user_id, token, expires_at) VALUES (?, ?, ?)')
   ->execute([$userId, $token, $expires]);

jsonSuccess([
    'token' => $token,
    'user'  => ['id' => $userId, 'name' => $name, 'email' => $email],
], 'Akun berhasil dibuat!');
