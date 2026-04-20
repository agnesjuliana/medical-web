<?php

namespace Backend\Controllers;

use Backend\Core\Controller;
use Backend\Repositories\ProfileRepository;
use Backend\Services\ProfileService;
use Backend\Repositories\MealRepository;
use Backend\Repositories\HealthScoreRepository;
use Backend\Services\NutritionService;
use DateTimeImmutable;

class ProfileController extends Controller
{
    private ProfileRepository $repository;
    private ProfileService $service;
    private MealRepository $meals;
    private HealthScoreRepository $scores;
    private NutritionService $nutrition;

    public function __construct(
        ProfileRepository $repository,
        ProfileService $service,
        MealRepository $meals,
        HealthScoreRepository $scores,
        NutritionService $nutrition
    ) {
        $this->repository = $repository;
        $this->service = $service;
        $this->meals = $meals;
        $this->scores = $scores;
        $this->nutrition = $nutrition;
    }

    public function getProfile(int $userId): void
    {
        $profile = $this->repository->getByUserId($userId);

        if (!$profile) {
            $this->jsonError('Profile not found', 404);
        }

        // Convert numeric fields with explicit type casting
        $profile['height_cm'] = (float) $profile['height_cm'];
        $profile['weight_kg'] = (float) $profile['weight_kg'];
        $profile['goal_weight_kg'] = $profile['goal_weight_kg'] ? (float) $profile['goal_weight_kg'] : null;
        $profile['daily_calorie_target'] = (int) $profile['daily_calorie_target'];
        $profile['daily_protein_g'] = (int) $profile['daily_protein_g'];
        $profile['daily_carbs_g'] = (int) $profile['daily_carbs_g'];
        $profile['daily_fats_g'] = (int) $profile['daily_fats_g'];
        $profile['daily_fiber_g'] = (int) $profile['daily_fiber_g'];
        $profile['daily_sugar_g'] = (int) $profile['daily_sugar_g'];
        $profile['daily_sodium_mg'] = (int) $profile['daily_sodium_mg'];
        $profile['step_goal'] = (int) $profile['step_goal'];

        // Parse barriers
        $profile['barriers'] = $this->service->parsePostgresArray($profile['barriers']);

        $this->jsonSuccess($profile);
    }

    public function saveProfile(int $userId): void
    {
        $body = $this->getRequestBody();

        // Basic Validation (re-using logic from api.php)
        $gender = $body['gender'] ?? '';
        $birth_date = $body['birth_date'] ?? '';
        $height_cm = $body['height_cm'] ?? null;
        $weight_kg = $body['weight_kg'] ?? null;
        $activity_level = $body['activity_level'] ?? '';
        $goal = $body['goal'] ?? '';
        $goal_weight_kg = $body['goal_weight_kg'] ?? null;
        $step_goal = $body['step_goal'] ?? 10000;
        $barriers = $body['barriers'] ?? [];

        if (!in_array($gender, ['male', 'female'])) {
            $this->jsonError('Invalid field: gender', 422);
        }

        $birth = DateTimeImmutable::createFromFormat('Y-m-d', $birth_date);
        if (!$birth || $birth->format('Y-m-d') !== $birth_date || $birth > new DateTimeImmutable('today')) {
            $this->jsonError('Invalid field: birth_date', 422);
        }

        // Validate age is between 10-120 years
        $age = $birth->diff(new DateTimeImmutable('today'))->y;
        if ($age < 10 || $age > 120) {
            $this->jsonError('Age must be between 10 and 120 years', 422);
        }

        if (!is_numeric($height_cm) || $height_cm <= 0) $this->jsonError('Invalid field: height_cm', 422);
        if (!is_numeric($weight_kg) || $weight_kg <= 0) $this->jsonError('Invalid field: weight_kg', 422);
        if (!in_array($activity_level, ['beginner', 'active', 'athlete'])) $this->jsonError('Invalid field: activity_level', 422);
        if (!in_array($goal, ['lose', 'maintain', 'gain'])) $this->jsonError('Invalid field: goal', 422);

        // Calculate Targets via Service
        $targets = $this->service->calculateTargets([
            'weight_kg' => $weight_kg,
            'height_cm' => $height_cm,
            'gender' => $gender,
            'activity_level' => $activity_level,
            'goal' => $goal,
            'birth_date' => $birth_date
        ]);

        // Prepare data for Repository
        $data = array_merge($body, $targets, [
            'user_id' => $userId,
            'barriers' => $this->service->formatPostgresArray($barriers),
            'goal_weight_kg' => $goal_weight_kg !== null ? (float) $goal_weight_kg : null,
            'step_goal' => (int) $step_goal
        ]);

        $success = $this->repository->upsert($data);

        if ($success) {
            $this->recomputeAndPersistDailyScore($userId, date('Y-m-d'));
            $this->jsonSuccess(['saved' => true, 'daily_calorie_target' => $targets['daily_calorie_target']], 200);
        } else {
            $this->jsonError('Failed to save profile', 500);
        }
    }

    private function recomputeAndPersistDailyScore(int $userId, string $date): void
    {
        $profile = $this->repository->getByUserId($userId);
        if (!$profile) {
            return;
        }

        $summary = $this->meals->getDashboardSummaryByDate($userId, $date);

        $targets = [
            'calories'  => (int) $profile['daily_calorie_target'],
            'protein_g' => (int) $profile['daily_protein_g'],
            'carbs_g'   => (int) $profile['daily_carbs_g'],
            'fats_g'    => (int) $profile['daily_fats_g'],
        ];

        $consumed = [
            'calories'  => (int) $summary['meal_totals']['calories'],
            'protein_g' => (float) $summary['meal_totals']['protein_g'],
            'carbs_g'   => (float) $summary['meal_totals']['carbs_g'],
            'fats_g'    => (float) $summary['meal_totals']['fats_g'],
            'water_ml'  => (int) $summary['water_ml'],
        ];

        $score = $this->nutrition->computeHealthScore($targets, $consumed);
        $this->scores->upsert($userId, $date, $score['score'], $score['cal_dev'], $score['macro_dev']);
    }
}
