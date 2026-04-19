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
        // Task 2: User Profile & Dashboard Endpoints
        case 'get_profile':
            $stmt = $pdo->prepare('
                SELECT user_id, gender, birth_date, height_cm, weight_kg,
                       activity_level, goal, goal_weight_kg, step_goal,
                       barriers, daily_calorie_target, daily_protein_g,
                       daily_carbs_g, daily_fats_g, onboarded_at
                FROM m8_user_profiles
                WHERE user_id = ?
            ');
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                json_error('Profile not found', 404);
            }

            // Convert numeric fields to proper types
            $row['height_cm'] = (float) $row['height_cm'];
            $row['weight_kg'] = (float) $row['weight_kg'];
            $row['goal_weight_kg'] = $row['goal_weight_kg'] ? (float) $row['goal_weight_kg'] : null;
            $row['daily_calorie_target'] = (int) $row['daily_calorie_target'];
            $row['daily_protein_g'] = (int) $row['daily_protein_g'];
            $row['daily_carbs_g'] = (int) $row['daily_carbs_g'];
            $row['daily_fats_g'] = (int) $row['daily_fats_g'];
            $row['step_goal'] = (int) $row['step_goal'];

            // Parse PostgreSQL TEXT[] into PHP array
            // Note: This parser is safe because save_profile enforces that barrier items
            // cannot contain {, }, ", \, or , characters (checked via strpbrk).
            if ($row['barriers'] === '{}' || empty($row['barriers'])) {
                $row['barriers'] = [];
            } else {
                $barriers_str = trim($row['barriers'], '{}');
                $row['barriers'] = array_map('trim', explode(',', $barriers_str));
            }

            json_success($row);
            break;

        case 'save_profile':
            // Validate input
            $gender = $body['gender'] ?? '';
            $birth_date = $body['birth_date'] ?? '';
            $height_cm = $body['height_cm'] ?? null;
            $weight_kg = $body['weight_kg'] ?? null;
            $activity_level = $body['activity_level'] ?? '';
            $goal = $body['goal'] ?? '';
            $goal_weight_kg = $body['goal_weight_kg'] ?? null;
            $step_goal = $body['step_goal'] ?? 10000;
            $barriers = $body['barriers'] ?? [];

            // Validation rules
            if (!in_array($gender, ['male', 'female'])) {
                json_error('Invalid field: gender', 422);
            }

            // Validate birth_date: proper calendar date, not future
            $birth = DateTimeImmutable::createFromFormat('Y-m-d', $birth_date);
            $birthErrors = DateTimeImmutable::getLastErrors();
            $warningCount = is_array($birthErrors) ? $birthErrors['warning_count'] : 0;
            $errorCount = is_array($birthErrors) ? $birthErrors['error_count'] : 0;

            if (
                !$birth ||
                $warningCount > 0 ||
                $errorCount > 0 ||
                $birth->format('Y-m-d') !== $birth_date ||
                $birth > new DateTimeImmutable('today')
            ) {
                json_error('Invalid field: birth_date', 422);
            }

            if (!is_numeric($height_cm) || $height_cm <= 0) {
                json_error('Invalid field: height_cm', 422);
            }
            if (!is_numeric($weight_kg) || $weight_kg <= 0) {
                json_error('Invalid field: weight_kg', 422);
            }
            if (!in_array($activity_level, ['beginner', 'active', 'athlete'])) {
                json_error('Invalid field: activity_level', 422);
            }
            if (!in_array($goal, ['lose', 'maintain', 'gain'])) {
                json_error('Invalid field: goal', 422);
            }
            if ($goal_weight_kg !== null && (!is_numeric($goal_weight_kg) || $goal_weight_kg <= 0)) {
                json_error('Invalid field: goal_weight_kg', 422);
            }
            if (!is_int($step_goal) && !ctype_digit((string)$step_goal)) {
                json_error('Invalid field: step_goal', 422);
            }

            // Validate barriers: must be array of strings with safe characters
            if (!is_array($barriers)) {
                json_error('Invalid field: barriers', 422);
            }
            foreach ($barriers as $item) {
                if (!is_string($item)) {
                    json_error('Invalid field: barriers', 422);
                }
                if (strpbrk($item, "{},\"\\") !== false) {
                    json_error('Invalid field: barriers', 422);
                }
            }

            // Normalize values
            $height_cm = (float) $height_cm;
            $weight_kg = (float) $weight_kg;
            $goal_weight_kg = $goal_weight_kg !== null ? (float) $goal_weight_kg : null;
            $step_goal = (int) $step_goal;

            // Compute TDEE + macros
            $age = $birth->diff(new DateTimeImmutable('today'))->y;

            // BMR (Mifflin-St Jeor)
            $bmr = (10 * $weight_kg) + (6.25 * $height_cm) - (5 * $age);
            $bmr += ($gender === 'male') ? 5 : -161;

            // Activity factor
            $activity_factors = [
                'beginner' => 1.375,
                'active' => 1.55,
                'athlete' => 1.725
            ];
            $tdee = $bmr * $activity_factors[$activity_level];

            // Goal adjustment
            $goal_adjustments = [
                'lose' => -500,
                'maintain' => 0,
                'gain' => 500
            ];
            $calorie_target = (int) round($tdee + $goal_adjustments[$goal]);

            // Compute macros
            $protein_g = (int) round(($calorie_target * 0.30) / 4);
            $carbs_g = (int) round(($calorie_target * 0.40) / 4);
            $fats_g = (int) round(($calorie_target * 0.30) / 9);

            // Convert barriers array to PostgreSQL array literal
            $barriers_str = '{' . implode(',', $barriers) . '}';

            // UPSERT profile
            $stmt = $pdo->prepare('
                INSERT INTO m8_user_profiles
                    (user_id, gender, birth_date, height_cm, weight_kg,
                     activity_level, goal, goal_weight_kg, step_goal, barriers,
                     daily_calorie_target, daily_protein_g, daily_carbs_g,
                     daily_fats_g, onboarded_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ON CONFLICT (user_id) DO UPDATE SET
                    gender               = EXCLUDED.gender,
                    birth_date           = EXCLUDED.birth_date,
                    height_cm            = EXCLUDED.height_cm,
                    weight_kg            = EXCLUDED.weight_kg,
                    activity_level       = EXCLUDED.activity_level,
                    goal                 = EXCLUDED.goal,
                    goal_weight_kg       = EXCLUDED.goal_weight_kg,
                    step_goal            = EXCLUDED.step_goal,
                    barriers             = EXCLUDED.barriers,
                    daily_calorie_target = EXCLUDED.daily_calorie_target,
                    daily_protein_g      = EXCLUDED.daily_protein_g,
                    daily_carbs_g        = EXCLUDED.daily_carbs_g,
                    daily_fats_g         = EXCLUDED.daily_fats_g,
                    onboarded_at         = COALESCE(m8_user_profiles.onboarded_at, NOW()),
                    updated_at           = NOW()
            ');
            $stmt->execute([
                $userId, $gender, $birth_date, $height_cm, $weight_kg,
                $activity_level, $goal, $goal_weight_kg, $step_goal, $barriers_str,
                $calorie_target, $protein_g, $carbs_g, $fats_g
            ]);

            json_success(['saved' => true, 'daily_calorie_target' => $calorie_target], 200);
            break;

        case 'get_dashboard':
            $date = $_GET['date'] ?? date('Y-m-d');

            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                json_error('Invalid date', 422);
            }

            // Load profile targets
            $stmt = $pdo->prepare('
                SELECT daily_calorie_target, daily_protein_g, daily_carbs_g, daily_fats_g
                FROM m8_user_profiles
                WHERE user_id = ?
            ');
            $stmt->execute([$userId]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$profile) {
                json_error('Profile not found. Complete onboarding first.', 404);
            }

            // Query meal aggregates for date
            $stmt = $pdo->prepare('
                SELECT
                    COALESCE(SUM(calories), 0) as calories,
                    COALESCE(SUM(protein_g), 0) as protein_g,
                    COALESCE(SUM(carbs_g), 0) as carbs_g,
                    COALESCE(SUM(fats_g), 0) as fats_g,
                    COALESCE(SUM(fiber_g), 0) as fiber_g
                FROM m8_meals
                WHERE user_id = ? AND log_date = ?
            ');
            $stmt->execute([$userId, $date]);
            $meal_totals = $stmt->fetch(PDO::FETCH_ASSOC);

            // Query water total for date
            $stmt = $pdo->prepare('
                SELECT COALESCE(SUM(amount_ml), 0) as water_ml
                FROM m8_water_logs
                WHERE user_id = ? AND log_date = ?
            ');
            $stmt->execute([$userId, $date]);
            $water_result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Query recent meals (last 5)
            $stmt = $pdo->prepare('
                SELECT id, meal_type, name, calories, protein_g, carbs_g, fats_g,
                       photo_url, source, created_at
                FROM m8_meals
                WHERE user_id = ? AND log_date = ?
                ORDER BY created_at DESC
                LIMIT 5
            ');
            $stmt->execute([$userId, $date]);
            $recent_meals = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convert numeric values
            foreach ($recent_meals as &$meal) {
                $meal['calories'] = (int) $meal['calories'];
                $meal['protein_g'] = (float) $meal['protein_g'];
                $meal['carbs_g'] = (float) $meal['carbs_g'];
                $meal['fats_g'] = (float) $meal['fats_g'];
            }
            unset($meal);

            // Compute remaining values
            $targets = [
                'calories' => (int) $profile['daily_calorie_target'],
                'protein_g' => (int) $profile['daily_protein_g'],
                'carbs_g' => (int) $profile['daily_carbs_g'],
                'fats_g' => (int) $profile['daily_fats_g']
            ];

            $consumed = [
                'calories' => (int) $meal_totals['calories'],
                'protein_g' => (float) $meal_totals['protein_g'],
                'carbs_g' => (float) $meal_totals['carbs_g'],
                'fats_g' => (float) $meal_totals['fats_g'],
                'fiber_g' => (float) $meal_totals['fiber_g'],
                'water_ml' => (int) $water_result['water_ml']
            ];

            $remaining = [
                'calories' => $targets['calories'] - $consumed['calories'],
                'protein_g' => $targets['protein_g'] - $consumed['protein_g'],
                'carbs_g' => $targets['carbs_g'] - $consumed['carbs_g'],
                'fats_g' => $targets['fats_g'] - $consumed['fats_g']
            ];

            // --- Health Score Computation ---
            // Compute calorie deviation percentage
            $calDev = round(
                abs($consumed['calories'] - $targets['calories'])
                / max($targets['calories'], 1) * 100,
                2
            );

            // Compute macro deviation percentage (average of protein, carbs, fats)
            $proteinDev = abs($consumed['protein_g'] - $targets['protein_g'])
                          / max($targets['protein_g'], 1) * 100;
            $carbsDev   = abs($consumed['carbs_g'] - $targets['carbs_g'])
                          / max($targets['carbs_g'], 1) * 100;
            $fatsDev    = abs($consumed['fats_g'] - $targets['fats_g'])
                          / max($targets['fats_g'], 1) * 100;
            $macroDev   = round(($proteinDev + $carbsDev + $fatsDev) / 3, 2);

            // Compute score: start at 100, penalize deviations, clamp 0-100
            $healthScore = 100 - min(30, $calDev * 0.5) - min(30, $macroDev * 0.5);
            $healthScore = (int) max(0, min(100, $healthScore));

            // Upsert health score into m8_daily_health_scores
            $stmt = $pdo->prepare('
                INSERT INTO m8_daily_health_scores
                    (user_id, log_date, score, calorie_deviation_pct, macro_deviation_pct)
                VALUES (?, ?, ?, ?, ?)
                ON CONFLICT (user_id, log_date) DO UPDATE SET
                    score                 = EXCLUDED.score,
                    calorie_deviation_pct = EXCLUDED.calorie_deviation_pct,
                    macro_deviation_pct   = EXCLUDED.macro_deviation_pct,
                    computed_at           = NOW(),
                    updated_at            = NOW()
            ');
            $stmt->execute([$userId, $date, $healthScore, $calDev, $macroDev]);

            json_success([
                'date' => $date,
                'targets' => $targets,
                'consumed' => $consumed,
                'remaining' => $remaining,
                'recent_meals' => $recent_meals,
                'health_score' => $healthScore
            ]);
            break;

        // 10. Define Skeleton Routes
        case 'list_meals':
            $date = $_GET['date'] ?? date('Y-m-d');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                json_error('Invalid date format', 422);
            }

            $stmt = $pdo->prepare('
                SELECT id, meal_type, name, calories, protein_g, carbs_g, fats_g,
                       fiber_g, sugar_g, sodium_mg, serving_size, photo_url, source,
                       ai_confidence, saved_food_id, created_at
                FROM m8_meals
                WHERE user_id = ? AND log_date = ?
                ORDER BY created_at ASC
            ');
            $stmt->execute([$userId, $date]);
            $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Type conversion
            foreach ($meals as &$meal) {
                $meal['calories'] = (int) $meal['calories'];
                $meal['protein_g'] = (float) $meal['protein_g'];
                $meal['carbs_g'] = (float) $meal['carbs_g'];
                $meal['fats_g'] = (float) $meal['fats_g'];
                $meal['fiber_g'] = (float) $meal['fiber_g'];
                $meal['sugar_g'] = (float) $meal['sugar_g'];
                $meal['sodium_mg'] = (float) $meal['sodium_mg'];
                $meal['serving_size'] = $meal['serving_size'] ? (float) $meal['serving_size'] : null;
                $meal['ai_confidence'] = $meal['ai_confidence'] !== null ? (float) $meal['ai_confidence'] : null;
                $meal['saved_food_id'] = $meal['saved_food_id'] !== null ? (int) $meal['saved_food_id'] : null;
            }
            unset($meal);

            json_success($meals);
            break;

        case 'log_meal':
            $meal_type = $body['meal_type'] ?? '';
            $name = $body['name'] ?? '';
            $log_date = $body['log_date'] ?? date('Y-m-d');
            $calories = $body['calories'] ?? 0;
            $protein = $body['protein_g'] ?? 0;
            $carbs = $body['carbs_g'] ?? 0;
            $fats = $body['fats_g'] ?? 0;
            $fiber = $body['fiber_g'] ?? 0;
            $sugar = $body['sugar_g'] ?? 0;
            $sodium = $body['sodium_mg'] ?? 0;
            $serving_size = $body['serving_size'] ?? null;
            $photo_url = $body['photo_url'] ?? null;
            $source = $body['source'] ?? 'manual';
            $ai_confidence = isset($body['ai_confidence']) ? (float) $body['ai_confidence'] : null;
            $saved_food_id = isset($body['saved_food_id']) ? (int) $body['saved_food_id'] : null;

            if (!in_array($meal_type, ['breakfast', 'lunch', 'dinner', 'snack'])) {
                json_error('Invalid meal_type', 422);
            }
            if (empty($name)) {
                json_error('Name is required', 422);
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $log_date)) {
                json_error('Invalid log_date', 422);
            }
            if ($source === 'ai_scan' && $ai_confidence === null) {
                json_error('ai_confidence is required when source is ai_scan', 422);
            }

            $stmt = $pdo->prepare('
                INSERT INTO m8_meals (
                    user_id, log_date, meal_type, name, calories, protein_g,
                    carbs_g, fats_g, fiber_g, sugar_g, sodium_mg,
                    serving_size, photo_url, source, ai_confidence, saved_food_id,
                    created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                RETURNING id, created_at
            ');
            $stmt->execute([
                $userId, $log_date, $meal_type, $name, $calories, $protein,
                $carbs, $fats, $fiber, $sugar, $sodium,
                $serving_size, $photo_url, $source, $ai_confidence, $saved_food_id
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            json_success([
                'id' => (int) $row['id'],
                'created_at' => $row['created_at']
            ], 201);
            break;

        case 'delete_meal':
            $id = $body['id'] ?? null;
            if (!$id) {
                json_error('ID required', 400);
            }

            $stmt = $pdo->prepare('DELETE FROM m8_meals WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $userId]);

            if ($stmt->rowCount() === 0) {
                json_error('Meal not found or unauthorized', 404);
            }

            json_success(['deleted' => true]);
            break;

        case 'list_saved_foods':
            $stmt = $pdo->prepare('
                SELECT id, name, brand, calories, protein_g, carbs_g, fats_g,
                       fiber_g, sugar_g, sodium_mg, serving_size, serving_unit,
                       barcode, source, created_at
                FROM m8_saved_foods
                WHERE user_id = ?
                ORDER BY created_at DESC
            ');
            $stmt->execute([$userId]);
            $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($foods as &$food) {
                $food['calories'] = (int) $food['calories'];
                $food['protein_g'] = (float) $food['protein_g'];
                $food['carbs_g'] = (float) $food['carbs_g'];
                $food['fats_g'] = (float) $food['fats_g'];
                $food['fiber_g'] = (float) $food['fiber_g'];
                $food['sugar_g'] = (float) $food['sugar_g'];
                $food['sodium_mg'] = (float) $food['sodium_mg'];
                $food['serving_size'] = $food['serving_size'] ? (float) $food['serving_size'] : null;
            }
            unset($food);

            json_success($foods);
            break;

        case 'log_water':
            $amount = $body['amount_ml'] ?? 0;
            $log_date = $body['log_date'] ?? date('Y-m-d');

            if ($amount <= 0) {
                json_error('Amount must be positive', 422);
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $log_date)) {
                json_error('Invalid log_date', 422);
            }

            $stmt = $pdo->prepare('
                INSERT INTO m8_water_logs (user_id, log_date, amount_ml, logged_at, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW(), NOW())
                RETURNING id
            ');
            $stmt->execute([$userId, $log_date, $amount]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            json_success(['id' => (int) $row['id']], 201);
            break;

        case 'log_weight':
            $weight = $body['weight_kg'] ?? 0;
            $log_date = $body['log_date'] ?? date('Y-m-d');

            if ($weight <= 0) {
                json_error('Weight must be positive', 422);
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $log_date)) {
                json_error('Invalid log_date', 422);
            }

            $pdo->beginTransaction();
            try {
                // 1. Log to history
                $stmt = $pdo->prepare('
                    INSERT INTO m8_weight_logs (user_id, weight_kg, log_date, created_at, updated_at)
                    VALUES (?, ?, ?, NOW(), NOW())
                    RETURNING id
                ');
                $stmt->execute([$userId, $weight, $log_date]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                // 2. Update profile snapshot
                $stmt = $pdo->prepare('
                    UPDATE m8_user_profiles
                    SET weight_kg = ?, updated_at = NOW()
                    WHERE user_id = ?
                ');
                $stmt->execute([$weight, $userId]);

                $pdo->commit();
                json_success(['id' => (int) $row['id']], 201);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'get_ai_quota':
            $stmt = $pdo->prepare('
                SELECT scan_count
                FROM m8_ai_scan_quota
                WHERE user_id = ? AND log_date = CURRENT_DATE
            ');
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $scanCount = $row ? (int) $row['scan_count'] : 0;

            json_success([
                'used'      => $scanCount,
                'limit'     => 20,
                'remaining' => max(0, 20 - $scanCount)
            ]);
            break;

        case 'ai_scan_food':
            // 3a. Validate image_b64
            $image_b64 = $body['image_b64'] ?? '';
            if (empty($image_b64)) {
                json_error('image_b64 required', 422);
            }

            // Validate it's a data URI
            if (strpos($image_b64, 'data:image/') !== 0) {
                json_error('image_b64 must be a data:image/ URI', 422);
            }

            // Extract raw base64 data — regex tolerates optional params (e.g. ;charset=utf-8)
            $rawB64 = preg_replace('/^data:image\/[a-z]+(?:;[^,]+)*;base64,/', '', $image_b64);
            if (base64_decode($rawB64, true) === false) {
                json_error('Invalid image encoding', 422);
            }

            // Extract the MIME type for the API call (e.g., "image/jpeg")
            preg_match('/^data:(image\/[a-z]+)(?:;[^,]+)*;base64,/', $image_b64, $mimeMatch);
            $mediaType = $mimeMatch[1] ?? 'image/jpeg';

            // 3b. Rate limit check (atomic with row locking)
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('
                SELECT scan_count
                FROM m8_ai_scan_quota
                WHERE user_id = ? AND log_date = CURRENT_DATE
                FOR UPDATE
            ');
            $stmt->execute([$userId]);
            $quotaRow = $stmt->fetch(PDO::FETCH_ASSOC);

            $currentCount = $quotaRow ? (int) $quotaRow['scan_count'] : 0;

            if ($currentCount >= 20) {
                $pdo->rollBack();
                json_error('Daily AI scan limit reached (20/day)', 429);
            }

            // 3c. UPSERT quota (increment by 1)
            $stmt = $pdo->prepare('
                INSERT INTO m8_ai_scan_quota (user_id, log_date, scan_count)
                VALUES (?, CURRENT_DATE, 1)
                ON CONFLICT (user_id, log_date) DO UPDATE
                    SET scan_count = m8_ai_scan_quota.scan_count + 1,
                        updated_at = NOW()
            ');
            $stmt->execute([$userId]);
            $pdo->commit();

            // 3d. Read API key
            $apiKey = getenv('ANTHROPIC_API_KEY');
            if (empty($apiKey)) {
                json_error('AI service not configured', 503);
            }

            // 3e. Build the Anthropic Messages API payload
            $payload = [
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 1024,
                'system'     => 'You are a food nutrition expert. When given a food photo, identify all visible food items and estimate their nutritional content. Always respond with valid JSON only — no prose, no markdown code fences.',
                'messages'   => [[
                    'role'    => 'user',
                    'content' => [
                        [
                            'type'   => 'image',
                            'source' => [
                                'type'       => 'base64',
                                'media_type' => $mediaType,
                                'data'       => $rawB64,
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => 'Analyze this food photo and return JSON with this exact schema: {"items":[{"name":"string","estimated_grams":number,"calories":number,"protein_g":number,"carbs_g":number,"fats_g":number,"confidence":number}],"notes":"string"}. confidence is 0.0-1.0. Return ONLY valid JSON.',
                        ],
                    ],
                ]],
            ];

            // 3f. Send cURL request
            $ch = curl_init('https://api.anthropic.com/v1/messages');
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'x-api-key: ' . $apiKey,
                    'anthropic-version: 2023-06-01',
                ],
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false || $httpCode !== 200) {
                json_error('AI service error, please try again or log manually', 502);
            }

            // 3g. Parse the Anthropic response
            $anthropicData = json_decode($response, true);
            $text = $anthropicData['content'][0]['text'] ?? '';

            // Strip markdown code fences if Claude wraps its JSON response in them
            $text = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $text);

            $prediction = json_decode($text, true);
            if ($prediction === null || !isset($prediction['items'])) {
                json_error('AI could not parse the food image. Please log manually.', 422);
            }

            // 3h. Validate each item — skip malformed items instead of rejecting all
            $validItems = [];
            foreach ($prediction['items'] as $item) {
                if (
                    !isset($item['name']) || !is_string($item['name']) ||
                    !isset($item['calories']) || !is_numeric($item['calories']) ||
                    !isset($item['protein_g']) || !is_numeric($item['protein_g']) ||
                    !isset($item['carbs_g']) || !is_numeric($item['carbs_g']) ||
                    !isset($item['fats_g']) || !is_numeric($item['fats_g']) ||
                    !isset($item['confidence']) || !is_numeric($item['confidence'])
                ) {
                    continue; // Skip this malformed item
                }
                $validItems[] = [
                    'name'            => $item['name'],
                    'estimated_grams' => isset($item['estimated_grams']) && is_numeric($item['estimated_grams'])
                                         ? (float) $item['estimated_grams']
                                         : null,
                    'calories'        => (int) $item['calories'],
                    'protein_g'       => round((float) $item['protein_g'], 1),
                    'carbs_g'         => round((float) $item['carbs_g'], 1),
                    'fats_g'          => round((float) $item['fats_g'], 1),
                    'confidence'      => round((float) $item['confidence'], 2),
                ];
            }

            $prediction['items'] = $validItems;

            json_success($prediction);
            break;

        case 'get_health_scores':
            $days = (int) ($_GET['days'] ?? 7);
            // Clamp between 1 and 30
            $days = max(1, min(30, $days));

            $stmt = $pdo->prepare('
                SELECT log_date, score, calorie_deviation_pct,
                       macro_deviation_pct, computed_at
                FROM m8_daily_health_scores
                WHERE user_id = ?
                ORDER BY log_date DESC
                LIMIT ?
            ');
            // Explicit PARAM_INT binding — avoids SQL type errors when emulated prepares are off
            $stmt->bindValue(1, $userId, PDO::PARAM_INT);
            $stmt->bindValue(2, $days, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Type conversion
            foreach ($rows as &$row) {
                $row['score'] = (int) $row['score'];
                $row['calorie_deviation_pct'] = round((float) $row['calorie_deviation_pct'], 2);
                $row['macro_deviation_pct'] = round((float) $row['macro_deviation_pct'], 2);
            }
            unset($row);

            json_success($rows);
            break;

        case 'save_food':
        case 'delete_saved_food':
        case 'list_weight_logs':
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
