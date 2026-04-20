<?php
/**
 * Modul 8 API Entry Point
 *
 * Thin dispatcher: bootstraps dependencies and routes actions to controllers.
 * All business logic lives in Backend/{Controllers,Services,Repositories}.
 */

// ─── 1. Bootstrap ──────────────────────────────────────────────────────────

if (!defined('UNIT_TESTING')) {
    require_once __DIR__ . '/../../../config/database.php';
    require_once __DIR__ . '/../../../core/auth.php';
    require_once __DIR__ . '/../../../core/session.php';
}

if (!headers_sent() && !defined('UNIT_TESTING')) {
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
}

if (!defined('UNIT_TESTING')) {
    requireLogin();
}

// ─── 2. Autoloader ─────────────────────────────────────────────────────────

spl_autoload_register(function (string $class): void {
    $prefix  = 'Backend\\';
    $baseDir = __DIR__ . '/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// ─── 3. Request Inputs ─────────────────────────────────────────────────────

$userId = (int) ($_SESSION['user_id'] ?? 0);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if (empty($action)) {
    http_response_code(400);
    echo json_encode(['error' => 'action required']);
    exit;
}

if (!isset($body)) {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
}

if (!isset($pdo)) {
    $pdo = getDBConnection();
}

// ─── 4. Route → Controller ─────────────────────────────────────────────────

use Backend\Controllers\ProfileController;
use Backend\Controllers\MealController;
use Backend\Controllers\DashboardController;
use Backend\Controllers\AiController;
use Backend\Repositories\ProfileRepository;
use Backend\Repositories\MealRepository;
use Backend\Repositories\WaterRepository;
use Backend\Repositories\WeightRepository;
use Backend\Repositories\HealthScoreRepository;
use Backend\Repositories\AiScanRepository;
use Backend\Services\ProfileService;
use Backend\Services\NutritionService;
use Backend\Services\DailyHealthScoreService;
use Backend\Services\AiScanService;

try {
    switch ($action) {

        // ── Profile ──────────────────────────────────────────────────────
        case 'get_profile':
        case 'save_profile':
            $controller = new ProfileController(
                new ProfileRepository($pdo),
                new ProfileService(),
                new DailyHealthScoreService(
                    new MealRepository($pdo),
                    new WaterRepository($pdo),
                    new ProfileRepository($pdo),
                    new HealthScoreRepository($pdo),
                    new NutritionService()
                )
            );
            $action === 'get_profile'
                ? $controller->getProfile($userId)
                : $controller->saveProfile($userId);
            break;

        // ── Dashboard & Health Scores ─────────────────────────────────────
        case 'get_dashboard':
        case 'get_health_scores':
            $controller = new DashboardController(
                new ProfileRepository($pdo),
                new MealRepository($pdo),
                new HealthScoreRepository($pdo),
                new NutritionService()
            );
            $action === 'get_dashboard'
                ? $controller->getDashboard($userId)
                : $controller->getHealthScores($userId);
            break;

        // ── Meals, Water, Weight ──────────────────────────────────────────
        case 'list_meals':
        case 'log_meal':
        case 'delete_meal':
        case 'list_saved_foods':
        case 'save_food':
        case 'delete_saved_food':
        case 'log_water':
        case 'log_weight':
        case 'list_weight_logs':
            $controller = new MealController(
                new MealRepository($pdo),
                new WaterRepository($pdo),
                new WeightRepository($pdo),
                new DailyHealthScoreService(
                    new MealRepository($pdo),
                    new WaterRepository($pdo),
                    new ProfileRepository($pdo),
                    new HealthScoreRepository($pdo),
                    new NutritionService()
                )
            );
            match ($action) {
                'list_meals'       => $controller->listMeals($userId),
                'log_meal'         => $controller->logMeal($userId),
                'delete_meal'      => $controller->deleteMeal($userId),
                'list_saved_foods' => $controller->listSavedFoods($userId),
                'save_food'        => $controller->saveSavedFood($userId),
                'delete_saved_food'=> $controller->deleteSavedFood($userId),
                'log_water'        => $controller->logWater($userId),
                'log_weight'       => $controller->logWeight($userId),
                'list_weight_logs' => $controller->listWeightLogs($userId),
            };
            break;

        // ── AI Food Scanning ──────────────────────────────────────────────
        case 'get_ai_quota':
        case 'ai_scan_food':
            $controller = new AiController(
                new AiScanRepository($pdo),
                new AiScanService()
            );
            $action === 'get_ai_quota'
                ? $controller->getQuota($userId)
                : $controller->scanFood($userId);
            break;

        // ── Auth & User Info ──────────────────────────────────────────────
        case 'get_user_info':
        case 'logout':
        case 'delete_account':
            $controller = new \Backend\Controllers\UserController($pdo);
            match ($action) {
                'get_user_info'  => $controller->getUserInfo(),
                'logout'         => $controller->logout(),
                'delete_account' => $controller->deleteAccount($userId),
            };
            break;

        // ── Progress ──────────────────────────────────────────────────────
        case 'get_weight_progress':
        case 'get_weekly_energy':
        case 'get_calorie_averages':
        case 'get_progress_summary':
            $controller = new \Backend\Controllers\ProgressController(
                new \Backend\Repositories\WeightRepository($pdo),
                new \Backend\Repositories\MealRepository($pdo),
                new \Backend\Repositories\ProfileRepository($pdo)
            );
            match ($action) {
                'get_weight_progress'  => $controller->getWeightProgress($userId),
                'get_weekly_energy'    => $controller->getWeeklyEnergy($userId),
                'get_calorie_averages' => $controller->getCalorieAverages($userId),
                'get_progress_summary' => $controller->getProgressSummaryPayload($userId),
            };
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Unknown action']);
    }

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
