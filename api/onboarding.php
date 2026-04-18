<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/auth.php';

// Mock user for local development if not strictly logged in
startSession();
$userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : 1; 

// Read JSON payload
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$gender = $data['gender'] ?? 'male';
$activity_level = $data['activity_level'] ?? 'beginner';
$height_cm = isset($data['height_cm']) ? (float)$data['height_cm'] : 0;
$weight_kg = isset($data['weight_kg']) ? (float)$data['weight_kg'] : 0;
$birth_date = $data['birth_date'] ?? date('Y-m-d');
$goal = $data['goal'] ?? 'maintain';

// Calculate Age
$dob = new DateTime($birth_date);
$now = new DateTime();
$age = $now->diff($dob)->y;

// Validate basic inputs
if ($height_cm <= 0 || $weight_kg <= 0 || $age < 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid physical metrics']);
    exit;
}

// 1. Calculate BMR (Mifflin-St Jeor)
$bmr = (10 * $weight_kg) + (6.25 * $height_cm) - (5 * $age);
if ($gender === 'male') {
    $bmr += 5;
} else {
    $bmr -= 161;
}

// 2. Calculate TDEE (Total Daily Energy Expenditure) based on activity modifier
$activity_modifiers = [
    'beginner' => 1.2,     // 0-2 times / week
    'active'   => 1.55,    // 3-5 times / week
    'athlete'  => 1.725    // 6+ times / week
];
$modifier = $activity_modifiers[$activity_level] ?? 1.2;
$tdee = $bmr * $modifier;

// 3. Apply Goal Modifier
$calorie_target = $tdee;
if ($goal === 'lose') {
    $calorie_target -= 500;
} else if ($goal === 'gain') {
    $calorie_target += 500;
}
$calorie_target = (int)round($calorie_target);

// Database Interaction
try {
    $pdo = getDBConnection();
    
    // Check if table exists, create if not (safe fallback for initial dev)
    $sql = "
    CREATE TABLE IF NOT EXISTS user_health_profiles (
        id SERIAL PRIMARY KEY,
        user_id INTEGER NOT NULL UNIQUE,
        gender VARCHAR(10) NOT NULL,
        activity_level VARCHAR(20) NOT NULL,
        height_cm NUMERIC NOT NULL,
        weight_kg NUMERIC NOT NULL,
        birth_date DATE NOT NULL,
        goal VARCHAR(20) NOT NULL,
        daily_calorie_target INTEGER NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    
    // UPSERT Logic
    $stmt = $pdo->prepare("SELECT id FROM user_health_profiles WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $updateQuery = "UPDATE user_health_profiles SET 
            gender = :gender, 
            activity_level = :activity_level, 
            height_cm = :height_cm, 
            weight_kg = :weight_kg, 
            birth_date = :birth_date, 
            goal = :goal, 
            daily_calorie_target = :daily_calorie_target,
            updated_at = CURRENT_TIMESTAMP
            WHERE user_id = :user_id";
        $stmt = $pdo->prepare($updateQuery);
    } else {
        $insertQuery = "INSERT INTO user_health_profiles 
            (user_id, gender, activity_level, height_cm, weight_kg, birth_date, goal, daily_calorie_target) 
            VALUES (:user_id, :gender, :activity_level, :height_cm, :weight_kg, :birth_date, :goal, :daily_calorie_target)";
        $stmt = $pdo->prepare($insertQuery);
    }
    
    $stmt->execute([
        'user_id' => $userId,
        'gender' => $gender,
        'activity_level' => $activity_level,
        'height_cm' => $height_cm,
        'weight_kg' => $weight_kg,
        'birth_date' => $birth_date,
        'goal' => $goal,
        'daily_calorie_target' => $calorie_target
    ]);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => [
            'age' => $age,
            'bmr' => round($bmr),
            'tdee' => round($tdee),
            'daily_calorie_target' => $calorie_target
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    // Ignore db fail for frontend testing flow by mocking success anyway if connection fails
    // This allows UI dev to proceed without local Postgres setup blocks
    echo json_encode([
        'success' => true,
        'warning' => 'DB Connection failed, but calculation returned.',
        'data' => [
            'age' => $age,
            'bmr' => round($bmr),
            'tdee' => round($tdee),
            'daily_calorie_target' => $calorie_target
        ],
        'error_detail' => $e->getMessage()
    ]);
}
