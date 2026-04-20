<?php
// ============================================================
//  HEALTHEDU — api/config.php (SSO BRIDGE VERSION)
// ============================================================

// 1. Link to the parent application's core logic for session checks
require_once __DIR__ . '/../../../core/auth.php';

// 2. Set headers for AJAX communication
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * 3. Module-Specific Database Connection
 * Points to 'healthedu' instead of 'backbone_medweb'
 */
function getDB(): PDO {
    static $modulePdo = null;

    if ($modulePdo === null) {
        // Use your local XAMPP credentials
        $host = 'localhost';
        $db   = 'healthedu'; // THE CORE FIX: Point here
        $user = 'root';
        $pass = ''; 
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $modulePdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Module DB Connection failed']);
            exit;
        }
    }

    return $modulePdo;
}

// 4. Standard Response Helpers
function jsonSuccess(array $data = [], string $msg = 'OK'): void {
    echo json_encode(['success' => true, 'message' => $msg, 'data' => $data]);
    exit();
}

function jsonError(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit();
}

// 5. NEW SSO Auth Helper
function requireSSO(): string {
    // This still works because it uses the parent session internally
    if (!isLoggedIn()) {
        jsonError('Tidak terautentikasi. Silakan login via MedWeb.', 401);
    }
    
    $user = getCurrentUser(); 
    return $user['email'];    
}

function requireAuth(): string {
    return requireSSO();
}