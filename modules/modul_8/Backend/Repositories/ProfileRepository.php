<?php

namespace Backend\Repositories;

use PDO;

class ProfileRepository
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getByUserId(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT user_id, gender, birth_date, height_cm, weight_kg,
                   activity_level, goal, goal_weight_kg, step_goal,
                   barriers, daily_calorie_target, daily_protein_g,
                   daily_carbs_g, daily_fats_g, daily_fiber_g, daily_sugar_g,
                   daily_sodium_mg, onboarded_at
            FROM m8_user_profiles
            WHERE user_id = ?
        ');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function upsert(array $data): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO m8_user_profiles
                (user_id, gender, birth_date, height_cm, weight_kg,
                 activity_level, goal, goal_weight_kg, step_goal, barriers,
                 daily_calorie_target, daily_protein_g, daily_carbs_g,
                 daily_fats_g, daily_fiber_g, daily_sugar_g, daily_sodium_mg,
                 onboarded_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
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
                daily_fiber_g        = EXCLUDED.daily_fiber_g,
                daily_sugar_g        = EXCLUDED.daily_sugar_g,
                daily_sodium_mg      = EXCLUDED.daily_sodium_mg,
                onboarded_at         = COALESCE(m8_user_profiles.onboarded_at, NOW()),
                updated_at           = NOW()
        ');

        return $stmt->execute([
            $data['user_id'], $data['gender'], $data['birth_date'], $data['height_cm'], $data['weight_kg'],
            $data['activity_level'], $data['goal'], $data['goal_weight_kg'], $data['step_goal'], $data['barriers'],
            $data['daily_calorie_target'], $data['daily_protein_g'], $data['daily_carbs_g'], $data['daily_fats_g'],
            $data['daily_fiber_g'], $data['daily_sugar_g'], $data['daily_sodium_mg']
        ]);
    }
}
