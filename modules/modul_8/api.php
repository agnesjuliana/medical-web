<?php
/**
 * Modul 8 API Entry Point
 * 
 * Handles authentication, request parsing, and routing for all Modul 8 actions.
 */

// 1. Require Dependencies
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/session.php';

// 2. Set JSON Response Headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// 3. Authentication Guard
// This function will redirect to login page if not authenticated
requireLogin();

// 4. Extract User Identity
$userId = (int) $_SESSION['user_id'];

// 5. Parse Action Parameter
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if (empty($action)) {
    http_response_code(400);
    echo json_encode(['error' => 'action required']);
    exit;
}

// 6. Parse JSON Request Body
$body = json_decode(file_get_contents('php://input'), true) ?? [];

// 7. Database Connection
$pdo = getDBConnection();

// 8. Define JSON Helper Functions
/**
 * Output success JSON response and exit
 */
function json_success($data, int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['data' => $data]);
    exit;
}

/**
 * Output error JSON response and exit
 */
function json_error(string $message, int $code = 400): never
{
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

// 9. Build Action Router Switch
try {
    switch ($action) {
        // 10. Define Skeleton Routes
        case 'get_profile':
        case 'save_profile':
        case 'get_dashboard':
        case 'list_meals':
        case 'log_meal':
        case 'delete_meal':
        case 'list_saved_foods':
        case 'save_food':
        case 'delete_saved_food':
        case 'log_water':
        case 'list_weight_logs':
        case 'log_weight':
        case 'get_health_scores':
        case 'ai_scan_food':
        case 'get_ai_quota':
            // To be implemented in future tasks
            json_success(['message' => 'Action ' . $action . ' is not yet implemented']);
            break;

        // 11. Default Route Fallback
        default:
            json_error('Unknown action', 404);
            break;
    }
} catch (PDOException $e) {
    // Prevent leaking raw SQL errors
    json_error('Database error', 500);
}
