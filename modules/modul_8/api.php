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
            $image_b64 = $body['image_b64'] ?? '';
            if (empty($image_b64)) {
                json_error('image_b64 required', 422);
            }
            if (!preg_match('/^data:image\/[a-z]+;base64,/', $image_b64)) {
                json_error('Invalid image encoding', 422);
            }
            $rawB64 = preg_replace('/^data:image\/[a-z]+;base64,/', '', $image_b64);
            if (base64_decode($rawB64, true) === false) {
                json_error('Invalid image encoding', 422);
            }
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT scan_count FROM m8_ai_scan_quota WHERE user_id = ? AND log_date = CURRENT_DATE FOR UPDATE");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $currentCount = $row ? (int)$row['scan_count'] : 0;
            if ($currentCount >= 20) {
                $pdo->rollBack();
                json_error('Daily AI scan limit reached (20/day)', 429);
            }
            $stmt = $pdo->prepare("INSERT INTO m8_ai_scan_quota (user_id, log_date, scan_count) VALUES (?, CURRENT_DATE, 1) ON CONFLICT (user_id, log_date) DO UPDATE SET scan_count = m8_ai_scan_quota.scan_count + 1, updated_at = NOW()");
            $stmt->execute([$userId]);
            $pdo->commit();
            
            $apiKey = getenv('ANTHROPIC_API_KEY') ?: getenv('GEMINI_API_KEY');
            if (empty($apiKey)) {
                json_success([
                    'items' => [[
                        'name' => 'Nasi Goreng Ayam (Mock AI)',
                        'estimated_grams' => 250,
                        'calories' => 450,
                        'protein_g' => 20,
                        'carbs_g' => 50,
                        'fats_g' => 15,
                        'confidence' => 0.98
                    ]],
                    'notes' => 'Mock AI details for requested image'
                ]);
            } else {
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
                                    'media_type' => 'image/jpeg',
                                    'data'       => $rawB64,
                                ],
                            ],
                            [
                                'type' => 'text',
                                'text' => 'Analyze this food photo and return JSON with this exact schema: {"items":[{"name":"string","estimated_grams":number,"calories":number,"protein_g":number,"carbs_g":number,"fats_g":number,"confidence":number}],"notes":"string"}. confidence is 0.0–1.0. Return ONLY valid JSON.',
                            ],
                        ],
                    ]],
                ];
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
                $anthropicData = json_decode($response, true);
                $text = $anthropicData['content'][0]['text'] ?? '';
                $prediction = json_decode($text, true);
                if ($prediction === null || !isset($prediction['items'])) {
                    json_error('AI could not parse the food image. Please log manually.', 422);
                }
                json_success($prediction);
            }
            break;

        case 'get_ai_quota':
            $stmt = $pdo->prepare("SELECT scan_count FROM m8_ai_scan_quota WHERE user_id = ? AND log_date = CURRENT_DATE");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $scan_count = $row ? (int)$row['scan_count'] : 0;
            json_success(['used' => $scan_count, 'limit' => 20, 'remaining' => max(0, 20 - $scan_count)]);
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
