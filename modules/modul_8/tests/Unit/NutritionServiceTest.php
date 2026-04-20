<?php
/**
 * Unit tests for Backend\Services\NutritionService
 *
 * Tests: computeHealthScore (perfect, partial, extreme, zero targets)
 */

// TestKernel loaded by runner.php

spl_autoload_register(function (string $class): void {
    $base = __DIR__ . '/../../Backend/';
    $rel  = str_replace(['Backend\\', '\\'], ['', '/'], $class) . '.php';
    if (file_exists($base . $rel)) require $base . $rel;
});

use Backend\Services\NutritionService;

print_suite_header('NutritionService::computeHealthScore');

$svc = new NutritionService();

$targets = ['calories' => 2000, 'protein_g' => 150, 'carbs_g' => 200, 'fats_g' => 60];

// ── TC-NS-01: Perfect intake → score = 100 ──────────────────────────────────
{
    $r = $svc->computeHealthScore($targets, array_merge($targets, ['water_ml' => 0, 'fiber_g' => 0]));
    assert_equals('TC-NS-01 perfect score = 100', 100, $r['score']);
    assert_equals('TC-NS-01 cal_dev = 0',          0,   $r['cal_dev']);
    assert_equals('TC-NS-01 macro_dev = 0',        0,   $r['macro_dev']);
}

// ── TC-NS-02: Slight under (−10%) → score ≥ 90 ─────────────────────────────
{
    $consumed = ['calories' => 1800, 'protein_g' => 135, 'carbs_g' => 180, 'fats_g' => 54];
    $r = $svc->computeHealthScore($targets, $consumed);
    assert_true('TC-NS-02 score ≥ 90 for 10% under', $r['score'] >= 90,
        "got {$r['score']}");
    assert_true('TC-NS-02 cal_dev ≈ 10', abs($r['cal_dev'] - 10.0) < 0.5,
        "got {$r['cal_dev']}");
}

// ── TC-NS-03: Severe over (500%) → score clamped at 0 or very low ───────────
{
    $consumed = ['calories' => 10000, 'protein_g' => 750, 'carbs_g' => 1000, 'fats_g' => 300];
    $r = $svc->computeHealthScore($targets, $consumed);
    assert_true('TC-NS-03 score clamped ≥ 0',    $r['score'] >= 0,   "got {$r['score']}");
    assert_true('TC-NS-03 score ≤ 100',           $r['score'] <= 100, "got {$r['score']}");
    assert_true('TC-NS-03 score low (<50)',        $r['score'] < 50,   "got {$r['score']}");
}

// ── TC-NS-04: Zero consumed → large deviations, score clamped ───────────────
{
    $consumed = ['calories' => 0, 'protein_g' => 0, 'carbs_g' => 0, 'fats_g' => 0];
    $r = $svc->computeHealthScore($targets, $consumed);
    assert_true('TC-NS-04 score ≥ 0',   $r['score'] >= 0,   "got {$r['score']}");
    assert_true('TC-NS-04 score ≤ 100', $r['score'] <= 100, "got {$r['score']}");
    assert_equals('TC-NS-04 cal_dev = 100%', 100.0, $r['cal_dev']);
}

// ── TC-NS-05: Zero targets (guard against div-by-zero) ──────────────────────
{
    $zeroTargets  = ['calories' => 0, 'protein_g' => 0, 'carbs_g' => 0, 'fats_g' => 0];
    $consumed     = ['calories' => 0, 'protein_g' => 0, 'carbs_g' => 0, 'fats_g' => 0];
    $r = $svc->computeHealthScore($zeroTargets, $consumed);
    assert_true('TC-NS-05 no crash on zero targets', true);
    assert_true('TC-NS-05 score within 0-100', $r['score'] >= 0 && $r['score'] <= 100,
        "got {$r['score']}");
}

// ── TC-NS-06: Only calorie deviation, macros perfect ────────────────────────
{
    $consumed = ['calories' => 2500, 'protein_g' => 150, 'carbs_g' => 200, 'fats_g' => 60];
    $r = $svc->computeHealthScore($targets, $consumed);
    // calDev = 25%, macro_dev = 0 → score = 100 - min(30, 12.5) - 0 = 87.5 → 87
    assert_true('TC-NS-06 macro_dev = 0', $r['macro_dev'] == 0.0, "got {$r['macro_dev']}");
    assert_true('TC-NS-06 score = 100 - 12.5 = 87', $r['score'] === 87, "got {$r['score']}");
}

// ── TC-NS-07: calDev cap: >60% deviation only penalises 30 pts ──────────────
{
    // 200% calorie over-consumption → calDev=200, min(30, 100)=30 → max penalty for cal
    $consumed = ['calories' => 6000, 'protein_g' => 150, 'carbs_g' => 200, 'fats_g' => 60];
    $r = $svc->computeHealthScore($targets, $consumed);
    // calDev = 200%, macroDev = 0 → score = 100 - 30 - 0 = 70
    assert_equals('TC-NS-07 score capped deduction = 70', 70, $r['score']);
}

// ── TC-NS-08: Both cal and macro max penalty → score = 40 ───────────────────
{
    $consumed = ['calories' => 6000, 'protein_g' => 750, 'carbs_g' => 1000, 'fats_g' => 300];
    $r = $svc->computeHealthScore($targets, $consumed);
    assert_equals('TC-NS-08 both max penalties → 40', 40, $r['score']);
}

// ── TC-NS-09: score is an integer ───────────────────────────────────────────
{
    $consumed = ['calories' => 1900, 'protein_g' => 140, 'carbs_g' => 190, 'fats_g' => 55];
    $r = $svc->computeHealthScore($targets, $consumed);
    assert_true('TC-NS-09 score is int', is_int($r['score']), gettype($r['score']));
}
