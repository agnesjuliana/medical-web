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
     */
    public function getWeightProgress(int $userId): void
    {
        $range   = (int) ($_GET['range'] ?? 90);
        $profile = $this->profiles->getByUserId($userId);

        if (!$profile) {
            $this->jsonError('Profile not found', 404);
        }

        $this->jsonSuccess($this->buildWeightProgress($userId, $range, $profile));
    }

    /**
     * GET ?action=get_weekly_energy[&offset=0]
     * offset=0 → this week, offset=1 → last week, etc.
     */
    public function getWeeklyEnergy(int $userId): void
    {
        $offset = (int) ($_GET['offset'] ?? 0);
        $this->jsonSuccess($this->buildWeeklyEnergy($userId, $offset));
    }

    /**
     * GET ?action=get_progress_summary
     * One-shot payload: weight progress (90d) + weekly energy (offset 0) + calorie averages.
     */
    public function getProgressSummaryPayload(int $userId): void
    {
        $profile = $this->profiles->getByUserId($userId);
        if (!$profile) {
            $this->jsonError('Profile not found', 404);
        }

        $this->jsonSuccess([
            'weight_progress'  => $this->buildWeightProgress($userId, 90, $profile),
            'weekly_energy'    => $this->buildWeeklyEnergy($userId, 0),
            'calorie_averages' => $this->buildCalorieAverages($userId),
        ]);
    }

    /**
     * GET ?action=get_calorie_averages
     */
    public function getCalorieAverages(int $userId): void
    {
        $this->jsonSuccess($this->buildCalorieAverages($userId));
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function buildWeightProgress(int $userId, int $range, array $profile): array
    {
        $currentWeight = (float) $profile['weight_kg'];
        $goalWeight    = $profile['goal_weight_kg'] ? (float) $profile['goal_weight_kg'] : null;

        $summary     = $this->weights->getProgressSummary($userId, $range);
        $logs        = $summary['logs'];
        $firstLog    = $summary['first_log'];
        $startWeight = $firstLog ? (float) $firstLog['weight_kg'] : $currentWeight;

        $chartData = [];
        foreach ($logs as $log) {
            $chartData[] = [
                'day'    => date('D', strtotime($log['log_date'])),
                'date'   => $log['log_date'],
                'weight' => $log['weight_kg'],
            ];
        }

        // Delta source is always 30 days regardless of chart range,
        // so narrow ranges (e.g. range=7) don't silently zero out 30d delta.
        $deltaLogs = $this->weights->getRecentLogs($userId, 30);

        $deltas = [3 => 0.0, 7 => 0.0, 30 => 0.0];
        foreach ([3, 7, 30] as $window) {
            $cutoff     = date('Y-m-d', strtotime("-{$window} days"));
            $windowLogs = array_values(array_filter($deltaLogs, fn($l) => $l['log_date'] >= $cutoff));

            if (\count($windowLogs) >= 2) {
                $first           = (float) $windowLogs[0]['weight_kg'];
                $last            = (float) end($windowLogs)['weight_kg'];
                $deltas[$window] = round($last - $first, 1);
            }
        }

        $goalProgress = 0.0;
        if ($goalWeight !== null && $startWeight !== $goalWeight) {
            $direction    = $goalWeight > $startWeight ? 1 : -1;
            $numerator    = ($currentWeight - $startWeight) * $direction;
            $denominator  = abs($goalWeight - $startWeight);
            $goalProgress = max(0, min(100, round(($numerator / $denominator) * 100, 1)));
        }

        return [
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
            'height_cm' => (float) $profile['height_cm'],
            'bmi'       => $this->calcBmi($currentWeight, (float) $profile['height_cm']),
        ];
    }

    private function buildWeeklyEnergy(int $userId, int $offset): array
    {
        $monday = date('Y-m-d', strtotime("monday this week -{$offset} week"));
        $sunday = date('Y-m-d', strtotime("sunday this week -{$offset} week"));

        $rows = $this->meals->getCaloriesByDateRange($userId, $monday, $sunday);

        $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $result   = [];
        for ($i = 0; $i < 7; $i++) {
            $date          = date('Y-m-d', strtotime("{$monday} +{$i} days"));
            $result[$date] = [
                'day'          => $dayNames[$i],
                'date'         => $date,
                'consumed_cal' => 0,
            ];
        }

        foreach ($rows as $row) {
            if (isset($result[$row['log_date']])) {
                $result[$row['log_date']]['consumed_cal'] = $row['calories'];
            }
        }

        $days          = array_values($result);
        $totalConsumed = array_sum(array_column($days, 'consumed_cal'));

        return [
            'week_start'     => $monday,
            'week_end'       => $sunday,
            'days'           => $days,
            'total_consumed' => $totalConsumed,
        ];
    }

    private function buildCalorieAverages(int $userId): array
    {
        $logs7d  = $this->meals->getDailyCalories($userId, 7);
        $logs30d = $this->meals->getDailyCalories($userId, 30);

        $avg7d  = \count($logs7d)  > 0 ? (int) round(array_sum(array_column($logs7d,  'calories')) / \count($logs7d))  : null;
        $avg30d = \count($logs30d) > 0 ? (int) round(array_sum(array_column($logs30d, 'calories')) / \count($logs30d)) : null;

        return [
            'avg_7d'  => $avg7d,
            'avg_30d' => $avg30d,
            'logs_7d' => $logs7d,
        ];
    }

    private function calcBmi(float $weightKg, float $heightCm): float
    {
        if ($heightCm <= 0) return 0.0;
        $heightM = $heightCm / 100;
        return round($weightKg / ($heightM * $heightM), 1);
    }
}
