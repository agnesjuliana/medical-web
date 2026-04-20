<?php

namespace Backend\Services;

use Backend\Repositories\MealRepository;
use Backend\Repositories\WaterRepository;
use Backend\Repositories\ProfileRepository;
use Backend\Repositories\HealthScoreRepository;

class DailyHealthScoreService
{
    private MealRepository $meals;
    private WaterRepository $water;
    private ProfileRepository $profiles;
    private HealthScoreRepository $scores;
    private NutritionService $nutrition;

    public function __construct(
        MealRepository $meals,
        WaterRepository $water,
        ProfileRepository $profiles,
        HealthScoreRepository $scores,
        NutritionService $nutrition
    ) {
        $this->meals = $meals;
        $this->water = $water;
        $this->profiles = $profiles;
        $this->scores = $scores;
        $this->nutrition = $nutrition;
    }

    public function recomputeAndPersist(int $userId, string $date): void
    {
        $profile = $this->profiles->getByUserId($userId);
        if (!$profile) {
            return;
        }

        $mealTotals = $this->meals->getAggregatesByDate($userId, $date);
        $waterMl = $this->water->getTotalByDate($userId, $date);

        $targets = [
            'calories'  => (int) $profile['daily_calorie_target'],
            'protein_g' => (int) $profile['daily_protein_g'],
            'carbs_g'   => (int) $profile['daily_carbs_g'],
            'fats_g'    => (int) $profile['daily_fats_g'],
        ];

        $consumed = [
            'calories'  => (int) $mealTotals['calories'],
            'protein_g' => (float) $mealTotals['protein_g'],
            'carbs_g'   => (float) $mealTotals['carbs_g'],
            'fats_g'    => (float) $mealTotals['fats_g'],
            'water_ml'  => (int) $waterMl,
        ];

        $score = $this->nutrition->computeHealthScore($targets, $consumed);
        $this->scores->upsert($userId, $date, $score['score'], $score['cal_dev'], $score['macro_dev']);
    }
}
