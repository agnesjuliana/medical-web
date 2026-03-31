<?php
/**
 * Process Register
 * 
 * Handles POST from register form.
 * Validates input, hashes password, inserts user.
 */

require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../core/validator.php';
require_once __DIR__ . '/../config/database.php';

startSession();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/auth/register.php');
    exit;
}

$db = getDBConnection();

// Validate input
$validator = new Validator($_POST, $db);
$validator
    ->required('name', 'Full name')
    ->maxLength('name', 100, 'Full name')
    ->required('email', 'Email')
    ->email('email', 'Email')
    ->unique('email', 'users', 'email', 'Email')
    ->required('password', 'Password')
    ->minLength('password', 6, 'Password')
    ->required('password_confirm', 'Password confirmation')
    ->match('password', 'password_confirm', 'Password confirmation');

if ($validator->fails()) {
    $_SESSION['validation_errors'] = $validator->errors();
    $_SESSION['old_input'] = [
        'name'  => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
    ];
    header('Location: ' . BASE_URL . '/auth/register.php');
    exit;
}

// Sanitize and hash
$name     = trim($_POST['name']);
$email    = trim($_POST['email']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Insert user
try {
    $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
    $stmt->execute([
        'name'     => $name,
        'email'    => $email,
        'password' => $password,
    ]);

    setFlash('success', 'Account created successfully! Please sign in.');
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;

} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    setFlash('error', 'An error occurred. Please try again.');
    $_SESSION['old_input'] = [
        'name'  => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
    ];
    header('Location: ' . BASE_URL . '/auth/register.php');
    exit;
}
