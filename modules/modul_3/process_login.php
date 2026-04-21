<?php
/**
 * Modul 3 Process Login
 * 
 * Handles POST from login form specifically for Modul 3.
 * Validates credentials and creates session, then redirects to Modul 3.
 */

require_once __DIR__ . '/../../core/session.php';
require_once __DIR__ . '/../../core/validator.php';
require_once __DIR__ . '/../../config/database.php';

startSession();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/modules/modul_3/login.php');
    exit;
}

$db = getDBConnection();

// Validate input
$validator = new Validator($_POST);
$validator
    ->required('email', 'Email')
    ->email('email', 'Email')
    ->required('password', 'Password');

if ($validator->fails()) {
    $firstError = array_values($validator->errors())[0];
    setFlash('error', $firstError);
    setFlash('old_email', $_POST['email'] ?? '');
    header('Location: ' . BASE_URL . '/modules/modul_3/login.php');
    exit;
}

$email    = trim($_POST['email']);
$password = $_POST['password'];

// Find user by email
$stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    setFlash('error', 'Invalid email or password.');
    setFlash('old_email', $email);
    header('Location: ' . BASE_URL . '/modules/modul_3/login.php');
    exit;
}

// Login success — set session
setUser($user);
header('Location: ' . BASE_URL . '/modules/modul_3/index.php');
exit;
