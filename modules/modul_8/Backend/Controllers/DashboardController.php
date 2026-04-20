<?php

namespace Backend\Controllers;

use Backend\Core\Controller;
use Backend\Repositories\ProfileRepository;
use Backend\Repositories\MealRepository;
use Backend\Repositories\WaterRepository;
use Backend\Repositories\HealthScoreRepository;
use Backend\Services\NutritionService;

class DashboardController extends Controller
{
    private ProfileRepository     $profiles;
    private MealRepository        $meals;
    private WaterRepository       $water;
    private HealthScoreRepository $scores;
    private NutritionService      $nutrition;

    public function __construct(
        ProfileRepository     $profiles,
        MealRepository        $meals,
        WaterRepository       $water,
        HealthScoreRepository $scores,
        NutritionService      $nutrition
    ) {
        $this->profiles  = $profiles;
        $this->meals     = $meals;
        $this->water     = $water;
        $this->scores    = $scores;
        $this->nutrition = $nutrition;
    }

    public function getDashboard(int $userId): void
    {
        $date = $_GET['date'] ?? date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->jsonError('Invalid date', 422);
        }

        // Load profile targets
        $profile = $this->profiles->getByUserId($userId);
        if (!$profile) {
            $this->jsonError('Profile not found. Complete onboarding first.', 404);
        }

        $targets = [
            'calories'  => (int) $profile['daily_calorie_target'],
            'protein_g' => (int) $profile['daily_protein_g'],
            'carbs_g'   => (int) $profile['daily_carbs_g'],
            'fats_g'    => (int) $profile['daily_fats_g'],
            'fiber_g'   => (int) $profile['daily_fiber_g'],
            'sugar_g'   => (int) $profile['daily_sugar_g'],
            'sodium_mg' => (int) $profile['daily_sodium_mg'],
            'step_goal' => (int) $profile['step_goal'],
        ];

        // Aggregate meal data
        $mealTotals   = $this->meals->getAggregatesByDate($userId, $date);
        $waterTotal   = $this->water->getTotalByDate($userId, $date);
        $recentMeals  = $this->meals->getRecentByDate($userId, $date);

        $consumed = [
            'calories'  => (int)   $mealTotals['calories'],
            'protein_g' => (float) $mealTotals['protein_g'],
            'carbs_g'   => (float) $mealTotals['carbs_g'],
            'fats_g'    => (float) $mealTotals['fats_g'],
            'fiber_g'   => (float) $mealTotals['fiber_g'],
            'sugar_g'   => (float) ($mealTotals['sugar_g'] ?? 0),
            'sodium_mg' => (float) ($mealTotals['sodium_mg'] ?? 0),
            'water_ml'  => $waterTotal,
        ];

        $remaining = [
            'calories'  => $targets['calories']  - $consumed['calories'],
            'protein_g' => $targets['protein_g'] - $consumed['protein_g'],
            'carbs_g'   => $targets['carbs_g']   - $consumed['carbs_g'],
            'fats_g'    => $targets['fats_g']    - $consumed['fats_g'],
        ];

        // Compute & persist health score
        $scoreData = $this->nutrition->computeHealthScore($targets, $consumed);
        $this->scores->upsert($userId, $date, $scoreData['score'], $scoreData['cal_dev'], $scoreData['macro_dev']);

        $this->jsonSuccess([
            'date'         => $date,
            'targets'      => $targets,
            'consumed'     => $consumed,
            'remaining'    => $remaining,
            'recent_meals' => $recentMeals,
            'health_score' => $scoreData['score'],
        ]);
    }

    public function getHealthScores(int $userId): void
    {
        $days = (int) ($_GET['days'] ?? 7);
        $days = max(1, min(30, $days));

        $this->jsonSuccess($this->scores->getRecent($userId, $days));
    }
}
