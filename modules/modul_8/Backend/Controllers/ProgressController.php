<?php

namespace Backend\Controllers;

use Backend\Core\Controller;
use Backend\Repositories\WeightRepository;
use Backend\Repositories\MealRepository;
use Backend\Repositories\ProfileRepository;

class ProgressController extends Controller
{
    private WeightRepository  $weights;
    private MealRepository    $meals;
    private ProfileRepository $profiles;

    public function __construct(
        WeightRepository  $weights,
        MealRepository    $meals,
        ProfileRepository $profiles
    ) {
        $this->weights  = $weights;
        $this->meals    = $meals;
        $this->profiles = $profiles;
    }

    /**
     * GET ?action=get_weight_progress[&range=90]
     * Returns current weight, goal, start weight, logs for chart,
     * and delta changes for 3/7/30 day windows.
     */
    public function getWeightProgress(int $userId): void
    {
        $range   = (int) ($_GET['range'] ?? 90);
        $profile = $this->profiles->getByUserId($userId);

        if (!$profile) {
            $this->jsonError('Profile not found', 404);
        }

        $currentWeight = (float) $profile['weight_kg'];
        $goalWeight    = $profile['goal_weight_kg'] ? (float) $profile['goal_weight_kg'] : null;

        // Fetch historical logs for chart
        $logs     = $this->weights->getRecentLogs($userId, $range);
        $firstLog = $this->weights->getFirstLog($userId);
        $startWeight = $firstLog ? (float) $firstLog['weight_kg'] : $currentWeight;

        // Build chart data — label by "Mon", "Tue" etc. for last 7 entries when range<=90
        $chartData = [];
        foreach ($logs as $log) {
            $chartData[] = [
                'day'    => date('D', strtotime($log['log_date'])), // e.g. "Mon"
                'date'   => $log['log_date'],
                'weight' => $log['weight_kg'],
            ];
        }

        // Compute deltas
        $deltas = [];
        foreach ([3, 7, 30] as $window) {
            $windowLogs = $this->weights->getRecentLogs($userId, $window);
            if (count($windowLogs) >= 2) {
                $first = (float) $windowLogs[0]['weight_kg'];
                $last  = (float) end($windowLogs)['weight_kg'];
                $diff  = round($last - $first, 1);
                $deltas[$window] = $diff;
            } else {
                $deltas[$window] = 0.0;
            }
        }

        // Goal progress
        $goalProgress = 0.0;
        if ($goalWeight !== null && $startWeight !== $goalWeight) {
            $totalChange   = $startWeight - $goalWeight;
            $currentChange = $startWeight - $currentWeight;
            $goalProgress  = $totalChange > 0
                ? max(0, min(100, round(($currentChange / $totalChange) * 100, 1)))
                : 0.0;
        }

        $this->jsonSuccess([
            'current_weight' => $currentWeight,
            'start_weight'   => $startWeight,
            'goal_weight'    => $goalWeight,
            'goal_progress'  => $goalProgress,
            'logs'           => $chartData,
            'deltas'         => [
                '3d'  => $deltas[3],
                '7d'  => $deltas[7],
                '30d' => $deltas[30],
            ],
            // BMI
            'height_cm' => (float) $profile['height_cm'],
            'bmi'       => $this->calcBmi((float) $profile['weight_kg'], (float) $profile['height_cm']),
        ]);
    }

    /**
     * GET ?action=get_weekly_energy[&offset=0]
     * offset=0 → this week, offset=1 → last week, etc.
     * Returns per-day {day, consumed_cal} for that ISO week.
     */
    public function getWeeklyEnergy(int $userId): void
    {
        $offset    = (int) ($_GET['offset'] ?? 0);
        // Start of the target week (Monday)
        $monday    = date('Y-m-d', strtotime("monday this week -{$offset} week"));
        $sunday    = date('Y-m-d', strtotime("sunday this week -{$offset} week"));

        $stmt = $this->meals->getDailyCalories($userId, 7 + ($offset * 7));

        // Build a full week skeleton then fill
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $result = [];
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime($monday . " +{$i} days"));
            $result[$date] = [
                'day'          => $days[$i],
                'date'         => $date,
                'consumed_cal' => 0,
            ];
        }

        foreach ($stmt as $row) {
            if (isset($result[$row['log_date']])) {
                $result[$row['log_date']]['consumed_cal'] = $row['calories'];
            }
        }

        $rows = array_values($result);
        $totalConsumed = array_sum(array_column($rows, 'consumed_cal'));

        $this->jsonSuccess([
            'week_start'     => $monday,
            'week_end'       => $sunday,
            'days'           => $rows,
            'total_consumed' => $totalConsumed,
        ]);
    }

    /**
     * GET ?action=get_calorie_averages
     * Returns average daily calories over 7d, 30d.
     */
    public function getCalorieAverages(int $userId): void
    {
        $logs7d  = $this->meals->getDailyCalories($userId, 7);
        $logs30d = $this->meals->getDailyCalories($userId, 30);

        $avg7d  = count($logs7d)  > 0 ? (int) round(array_sum(array_column($logs7d,  'calories')) / count($logs7d))  : null;
        $avg30d = count($logs30d) > 0 ? (int) round(array_sum(array_column($logs30d, 'calories')) / count($logs30d)) : null;

        $this->jsonSuccess([
            'avg_7d'  => $avg7d,
            'avg_30d' => $avg30d,
            'logs_7d' => $logs7d,
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function calcBmi(float $weightKg, float $heightCm): float
    {
        if ($heightCm <= 0) return 0.0;
        $heightM = $heightCm / 100;
        return round($weightKg / ($heightM * $heightM), 1);
    }
}
