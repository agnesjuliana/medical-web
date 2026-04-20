<?php

namespace Backend\Controllers;

use Backend\Core\Controller;
use Backend\Repositories\MealRepository;
use Backend\Repositories\WaterRepository;
use Backend\Repositories\WeightRepository;

class MealController extends Controller
{
    private MealRepository  $meals;
    private WaterRepository $water;
    private WeightRepository $weight;

    public function __construct(
        MealRepository  $meals,
        WaterRepository $water,
        WeightRepository $weight
    ) {
        $this->meals  = $meals;
        $this->water  = $water;
        $this->weight = $weight;
    }

    public function listMeals(int $userId): void
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->jsonError('Invalid date format', 422);
        }

        // Add pagination support with sane limits
        $limit = max(1, min(50, (int) ($_GET['limit'] ?? 20)));
        $offset = max(0, (int) ($_GET['offset'] ?? 0));

        $this->jsonSuccess($this->meals->getByDate($userId, $date, $limit, $offset));
    }

    public function logMeal(int $userId): void
    {
        $body = $this->getRequestBody();

        $meal_type    = $body['meal_type']    ?? '';
        $name         = $body['name']         ?? '';
        $log_date     = $body['log_date']     ?? date('Y-m-d');
        $source       = $body['source']       ?? 'manual';
        $ai_confidence = isset($body['ai_confidence']) ? (float) $body['ai_confidence'] : null;
        $saved_food_id = isset($body['saved_food_id']) ? (int) $body['saved_food_id'] : null;

        if (!in_array($meal_type, ['breakfast', 'lunch', 'dinner', 'snack'], true)) {
            $this->jsonError('Invalid meal_type', 422);
        }
        if (empty($name)) {
            $this->jsonError('Name is required', 422);
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $log_date)) {
            $this->jsonError('Invalid log_date', 422);
        }

        // Validate log_date strictly and ensure not in the future
        $logDateObj = \DateTimeImmutable::createFromFormat('Y-m-d', $log_date);
        if ($logDateObj === false || $logDateObj->format('Y-m-d') !== $log_date) {
            $this->jsonError('Invalid log_date', 422);
        }
        if ($logDateObj > new \DateTimeImmutable('today')) {
            $this->jsonError('log_date cannot be in the future', 422);
        }

        // Validate source enum strictly
        $validSources = ['manual', 'saved', 'database', 'barcode', 'ai_scan'];
        if (!in_array($source, $validSources, true)) {
            $this->jsonError('Invalid source. Must be one of: manual, saved, database, barcode, ai_scan', 422);
        }

        // Validate: source = saved requires saved_food_id
        if ($source === 'saved' && $saved_food_id === null) {
            $this->jsonError('saved_food_id is required when source is saved', 422);
        }

        // Validate: source = ai_scan requires ai_confidence in range 0..1
        if ($source === 'ai_scan') {
            if ($ai_confidence === null) {
                $this->jsonError('ai_confidence is required when source is ai_scan', 422);
            }
            if ($ai_confidence < 0 || $ai_confidence > 1) {
                $this->jsonError('ai_confidence must be between 0 and 1', 422);
            }
        }

        // IDOR Check: If saved_food_id is provided, verify ownership
        if ($saved_food_id !== null) {
            $ownedFood = $this->meals->getSavedFoodByIdAndUser($saved_food_id, $userId);
            if (!$ownedFood) {
                $this->jsonError('Saved food not found or unauthorized', 404);
            }
        }

        $row = $this->meals->insert([
            'user_id'       => $userId,
            'log_date'      => $log_date,
            'meal_type'     => $meal_type,
            'name'          => $name,
            'calories'      => $body['calories']    ?? 0,
            'protein_g'     => $body['protein_g']   ?? 0,
            'carbs_g'       => $body['carbs_g']     ?? 0,
            'fats_g'        => $body['fats_g']      ?? 0,
            'fiber_g'       => $body['fiber_g']     ?? 0,
            'sugar_g'       => $body['sugar_g']     ?? 0,
            'sodium_mg'     => $body['sodium_mg']   ?? 0,
            'serving_size'  => $body['serving_size'] ?? null,
            'photo_url'     => $body['photo_url']   ?? null,
            'source'        => $source,
            'ai_confidence' => $ai_confidence,
            'saved_food_id' => $saved_food_id,
        ]);

        $this->jsonSuccess(['id' => (int) $row['id'], 'created_at' => $row['created_at']], 201);
    }

    public function deleteMeal(int $userId): void
    {
        $body = $this->getRequestBody();
        $id   = $body['id'] ?? null;

        if (!$id) {
            $this->jsonError('ID required', 400);
        }

        if ($this->meals->delete((int) $id, $userId) === 0) {
            $this->jsonError('Meal not found or unauthorized', 404);
        }

        $this->jsonSuccess(['deleted' => true]);
    }

    public function listSavedFoods(int $userId): void
    {
        $searchQuery = $_GET['q'] ?? null;
        $limit = (int) ($_GET['limit'] ?? 20);
        $offset = (int) ($_GET['offset'] ?? 0);

        // Ensure sane limits
        $limit = max(1, min(50, $limit));
        $offset = max(0, $offset);

        $foods = $this->meals->listSavedFoodsWithSearch($userId, $searchQuery, $limit, $offset);
        $this->jsonSuccess($foods);
    }

    public function saveSavedFood(int $userId): void
    {
        $body = $this->getRequestBody();

        // Validate required fields
        $name = $body['name'] ?? '';
        $calories = $body['calories'] ?? null;

        if (empty($name)) {
            $this->jsonError('Name is required', 422);
        }
        if ($calories === null || !is_numeric($calories) || $calories < 0) {
            $this->jsonError('Calories is required and must be >= 0', 422);
        }

        // Validate source if provided
        $source = $body['source'] ?? 'manual';
        $validSources = ['manual', 'database', 'barcode'];
        if (!in_array($source, $validSources, true)) {
            $this->jsonError('Invalid source. Must be one of: manual, database, barcode', 422);
        }

        $data = [
            'user_id' => $userId,
            'name' => $name,
            'brand' => $body['brand'] ?? null,
            'calories' => (int) $calories,
            'protein_g' => $body['protein_g'] ?? 0,
            'carbs_g' => $body['carbs_g'] ?? 0,
            'fats_g' => $body['fats_g'] ?? 0,
            'fiber_g' => $body['fiber_g'] ?? 0,
            'sugar_g' => $body['sugar_g'] ?? 0,
            'sodium_mg' => $body['sodium_mg'] ?? 0,
            'serving_size' => $body['serving_size'] ?? null,
            'serving_unit' => $body['serving_unit'] ?? null,
            'barcode' => $body['barcode'] ?? null,
            'source' => $source,
        ];

        $id = $this->meals->insertSavedFood($data);
        $this->jsonSuccess(['id' => $id], 201);
    }

    public function deleteSavedFood(int $userId): void
    {
        $body = $this->getRequestBody();
        $id = $body['id'] ?? null;

        if (!$id) {
            $this->jsonError('ID required', 400);
        }

        if ($this->meals->deleteSavedFood((int) $id, $userId) === 0) {
            $this->jsonError('Saved food not found or unauthorized', 404);
        }

        $this->jsonSuccess(['deleted' => true]);
    }

    public function listWeightLogs(int $userId): void
    {
        $range = $_GET['range'] ?? '90d';

        // Validate range parameter
        $validRanges = ['90d', '6m', '1y', 'all'];
        if (!in_array($range, $validRanges, true)) {
            $this->jsonError('Invalid range. Must be one of: 90d, 6m, 1y, all', 422);
        }

        $logs = $this->weight->getLogsByRange($userId, $range);
        $this->jsonSuccess($logs);
    }

    public function logWater(int $userId): void
    {
        $body     = $this->getRequestBody();
        $amount   = $body['amount_ml'] ?? 0;
        $log_date = $body['log_date']  ?? date('Y-m-d');

        // Enforce bounded range: 50-5000 ml
        if ($amount < 50 || $amount > 5000) {
            $this->jsonError('amount_ml must be between 50 and 5000', 422);
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $log_date)) {
            $this->jsonError('Invalid log_date', 422);
        }

        $id = $this->water->insert($userId, $log_date, (int) $amount);
        $total = $this->water->getTotalByDate($userId, $log_date);
        $this->jsonSuccess(['id' => $id, 'amount_ml' => (int) $amount, 'water_ml' => $total], 201);
    }

    public function logWeight(int $userId): void
    {
        $body     = $this->getRequestBody();
        $weight   = $body['weight_kg'] ?? 0;
        $log_date = $body['log_date']  ?? date('Y-m-d');
        $note     = isset($body['note']) ? trim((string) $body['note']) : null;

        if ($weight <= 0) {
            $this->jsonError('Weight must be positive', 422);
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $log_date)) {
            $this->jsonError('Invalid log_date', 422);
        }

        // Validate note max length (500 characters)
        if ($note !== null && mb_strlen($note) > 500) {
            $this->jsonError('note max length is 500 characters', 422);
        }

        $id = $this->weight->insertAndUpdateProfile($userId, (float) $weight, $log_date, $note);
        $this->jsonSuccess(['id' => $id], 201);
    }
}
