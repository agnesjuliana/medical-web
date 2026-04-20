<?php

namespace Backend\Repositories;

use PDO;

class MealRepository
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getByDate(int $userId, string $date): array
    {
        $stmt = $this->pdo->prepare('
            SELECT id, meal_type, name, calories, protein_g, carbs_g, fats_g,
                   fiber_g, sugar_g, sodium_mg, serving_size, photo_url, source,
                   ai_confidence, saved_food_id, created_at
            FROM m8_meals
            WHERE user_id = ? AND log_date = ?
            ORDER BY created_at ASC
        ');
        $stmt->execute([$userId, $date]);
        $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($meals as &$meal) {
            $meal['calories']      = (int)   $meal['calories'];
            $meal['protein_g']     = (float) $meal['protein_g'];
            $meal['carbs_g']       = (float) $meal['carbs_g'];
            $meal['fats_g']        = (float) $meal['fats_g'];
            $meal['fiber_g']       = (float) $meal['fiber_g'];
            $meal['sugar_g']       = (float) $meal['sugar_g'];
            $meal['sodium_mg']     = (float) $meal['sodium_mg'];
            $meal['serving_size']  = $meal['serving_size'] !== null ? (float) $meal['serving_size'] : null;
            $meal['ai_confidence'] = $meal['ai_confidence'] !== null ? (float) $meal['ai_confidence'] : null;
            $meal['saved_food_id'] = $meal['saved_food_id'] !== null ? (int) $meal['saved_food_id'] : null;
        }
        unset($meal);

        return $meals;
    }

    public function getAggregatesByDate(int $userId, string $date): array
    {
        $stmt = $this->pdo->prepare('
            SELECT
                COALESCE(SUM(calories), 0) as calories,
                COALESCE(SUM(protein_g), 0) as protein_g,
                COALESCE(SUM(carbs_g), 0) as carbs_g,
                COALESCE(SUM(fats_g), 0) as fats_g,
                COALESCE(SUM(fiber_g), 0) as fiber_g,
                COALESCE(SUM(sugar_g), 0) as sugar_g,
                COALESCE(SUM(sodium_mg), 0) as sodium_mg
            FROM m8_meals
            WHERE user_id = ? AND log_date = ?
        ');
        $stmt->execute([$userId, $date]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRecentByDate(int $userId, string $date, int $limit = 5): array
    {
        $stmt = $this->pdo->prepare('
            SELECT id, meal_type, name, calories, protein_g, carbs_g, fats_g,
                   photo_url, source, created_at
            FROM m8_meals
            WHERE user_id = ? AND log_date = ?
            ORDER BY created_at DESC
            LIMIT ?
        ');
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $date);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($meals as &$meal) {
            $meal['calories']  = (int)   $meal['calories'];
            $meal['protein_g'] = (float) $meal['protein_g'];
            $meal['carbs_g']   = (float) $meal['carbs_g'];
            $meal['fats_g']    = (float) $meal['fats_g'];
        }
        unset($meal);

        return $meals;
    }

    public function insert(array $data): array
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO m8_meals (
                user_id, log_date, meal_type, name, calories, protein_g,
                carbs_g, fats_g, fiber_g, sugar_g, sodium_mg,
                serving_size, photo_url, source, ai_confidence, saved_food_id,
                created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            RETURNING id, created_at
        ');
        $stmt->execute([
            $data['user_id'], $data['log_date'], $data['meal_type'], $data['name'],
            $data['calories'], $data['protein_g'], $data['carbs_g'], $data['fats_g'],
            $data['fiber_g'], $data['sugar_g'], $data['sodium_mg'],
            $data['serving_size'], $data['photo_url'], $data['source'],
            $data['ai_confidence'], $data['saved_food_id'],
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete(int $id, int $userId): int
    {
        $stmt = $this->pdo->prepare('DELETE FROM m8_meals WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount();
    }

    public function listSavedFoods(int $userId): array
    {
        $stmt = $this->pdo->prepare('
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
            $food['calories']     = (int)   $food['calories'];
            $food['protein_g']    = (float) $food['protein_g'];
            $food['carbs_g']      = (float) $food['carbs_g'];
            $food['fats_g']       = (float) $food['fats_g'];
            $food['fiber_g']      = (float) $food['fiber_g'];
            $food['sugar_g']      = (float) $food['sugar_g'];
            $food['sodium_mg']    = (float) $food['sodium_mg'];
            $food['serving_size'] = $food['serving_size'] !== null ? (float) $food['serving_size'] : null;
        }
        unset($food);

        return $foods;
    }

    /**
     * Get daily consumed calories for the past N days.
     * Returns array of { log_date, calories } rows.
     * Uses parameterized query to prevent SQL injection.
     */
    public function getDailyCalories(int $userId, int $days = 7): array
    {
        $stmt = $this->pdo->prepare('
            SELECT log_date,
                   COALESCE(SUM(calories), 0) AS calories
            FROM m8_meals
            WHERE user_id = ? AND log_date >= CURRENT_DATE - (? || \' days\')::INTERVAL
            GROUP BY log_date
            ORDER BY log_date ASC
        ');
        $stmt->execute([$userId, $days]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['calories'] = (int) $r['calories'];
        }
        unset($r);
        return $rows;
    }

    /**
     * List saved foods for a user with optional search by name.
     * Supports pagination via limit and offset.
     */
    public function listSavedFoodsWithSearch(int $userId, ?string $searchQuery = null, int $limit = 20, int $offset = 0): array
    {
        $query = '
            SELECT id, name, brand, calories, protein_g, carbs_g, fats_g,
                   fiber_g, sugar_g, sodium_mg, serving_size, serving_unit,
                   barcode, source, created_at
            FROM m8_saved_foods
            WHERE user_id = ?
        ';
        $params = [$userId];

        // Safely add search filter using parameterized ILIKE
        if ($searchQuery !== null && !empty($searchQuery)) {
            $query .= ' AND name ILIKE ?';
            // Escape wildcard characters and add wildcards for partial matching
            $escapedQuery = addcslashes($searchQuery, '\\%_');
            $params[] = '%' . $escapedQuery . '%';
        }

        $query .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($foods as &$food) {
            $food['calories']     = (int)   $food['calories'];
            $food['protein_g']    = (float) $food['protein_g'];
            $food['carbs_g']      = (float) $food['carbs_g'];
            $food['fats_g']       = (float) $food['fats_g'];
            $food['fiber_g']      = (float) $food['fiber_g'];
            $food['sugar_g']      = (float) $food['sugar_g'];
            $food['sodium_mg']    = (float) $food['sodium_mg'];
            $food['serving_size'] = $food['serving_size'] !== null ? (float) $food['serving_size'] : null;
        }
        unset($food);

        return $foods;
    }

    /**
     * Insert a new saved food entry.
     */
    public function insertSavedFood(array $data): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO m8_saved_foods (
                user_id, name, brand, calories, protein_g, carbs_g, fats_g,
                fiber_g, sugar_g, sodium_mg, serving_size, serving_unit,
                barcode, source, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            RETURNING id
        ');
        $stmt->execute([
            $data['user_id'], $data['name'], $data['brand'] ?? null,
            $data['calories'], $data['protein_g'] ?? 0, $data['carbs_g'] ?? 0,
            $data['fats_g'] ?? 0, $data['fiber_g'] ?? 0, $data['sugar_g'] ?? 0,
            $data['sodium_mg'] ?? 0, $data['serving_size'] ?? null, $data['serving_unit'] ?? null,
            $data['barcode'] ?? null, $data['source'] ?? 'manual'
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['id'];
    }

    /**
     * Delete a saved food entry by ID and verify ownership.
     * Returns 1 if deleted, 0 if not found or unauthorized.
     */
    public function deleteSavedFood(int $foodId, int $userId): int
    {
        $stmt = $this->pdo->prepare('
            DELETE FROM m8_saved_foods
            WHERE id = ? AND user_id = ?
        ');
        $stmt->execute([$foodId, $userId]);
        return $stmt->rowCount();
    }

    /**
     * Get a saved food by ID and verify ownership (for IDOR check).
     */
    public function getSavedFoodByIdAndUser(int $foodId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT id, user_id
            FROM m8_saved_foods
            WHERE id = ? AND user_id = ?
        ');
        $stmt->execute([$foodId, $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
