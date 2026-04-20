<?php

namespace Backend\Services;

use DateTimeImmutable;

class ProfileService
{
    /**
     * Calculate nutrition targets based on user profile
     */
    public function calculateTargets(array $data): array
    {
        $weight_kg = (float) $data['weight_kg'];
        $height_cm = (float) $data['height_cm'];
        $gender = $data['gender'];
        $activity_level = $data['activity_level'];
        $goal = $data['goal'];
        
        $birth = new DateTimeImmutable($data['birth_date']);
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
        $tdee = $bmr * ($activity_factors[$activity_level] ?? 1.2);

        // Goal adjustment
        $goal_adjustments = [
            'lose' => -500,
            'maintain' => 0,
            'gain' => 500
        ];
        $calorie_target = (int) round($tdee + ($goal_adjustments[$goal] ?? 0));

        // Compute macros: 30% protein, 40% carbs, 30% fats
        $protein_g = (int) round(($calorie_target * 0.30) / 4);
        $carbs_g = (int) round(($calorie_target * 0.40) / 4);
        $fats_g = (int) round(($calorie_target * 0.30) / 9);

        // Compute micros: fiber (30g), sugar (50g max), sodium (2300mg)
        // These are standard recommendations and remain constant across profiles
        $fiber_g = 30;
        $sugar_g = 50;
        $sodium_mg = 2300;

        return [
            'daily_calorie_target' => $calorie_target,
            'daily_protein_g' => $protein_g,
            'daily_carbs_g' => $carbs_g,
            'daily_fats_g' => $fats_g,
            'daily_fiber_g' => $fiber_g,
            'daily_sugar_g' => $sugar_g,
            'daily_sodium_mg' => $sodium_mg
        ];
    }

    /**
     * Format PostgreSQL array string to PHP array
     */
    public function parsePostgresArray(?string $pgArray): array
    {
        if ($pgArray === '{}' || empty($pgArray)) {
            return [];
        }
        $str = trim($pgArray, '{}');
        return array_map('trim', explode(',', $str));
    }

    /**
     * Format PHP array to PostgreSQL array string
     */
    public function formatPostgresArray(array $array): string
    {
        return '{' . implode(',', $array) . '}';
    }
}
