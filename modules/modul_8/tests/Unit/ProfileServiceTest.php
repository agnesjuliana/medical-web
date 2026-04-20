<?php
/**
 * Unit tests for Backend\Services\ProfileService
 *
 * Tests: calculateTargets, parsePostgresArray, formatPostgresArray
 */

// TestKernel loaded by runner.php

// Autoload the service directly (no framework needed)
spl_autoload_register(function (string $class): void {
    $base = __DIR__ . '/../../Backend/';
    $rel  = str_replace(['Backend\\', '\\'], ['', '/'], $class) . '.php';
    if (file_exists($base . $rel)) require $base . $rel;
});

use Backend\Services\ProfileService;

print_suite_header('ProfileService::calculateTargets');

$svc = new ProfileService();

// ── Helper ──────────────────────────────────────────────────────────────────

function makeBirthDate(int $yearsAgo): string {
    return date('Y-m-d', strtotime("-$yearsAgo years"));
}

function calcExpected(float $weight, float $height, int $age, string $gender,
                      string $activity, string $goal): array {
    $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age);
    $bmr += ($gender === 'male') ? 5 : -161;
    $factors = ['beginner' => 1.375, 'active' => 1.55, 'athlete' => 1.725];
    $tdee  = $bmr * ($factors[$activity] ?? 1.2);
    $adj   = ['lose' => -500, 'maintain' => 0, 'gain' => 500];
    $cal   = (int) round($tdee + ($adj[$goal] ?? 0));
    return [
        'daily_calorie_target' => $cal,
        'daily_protein_g'      => (int) round(($cal * 0.30) / 4),
        'daily_carbs_g'        => (int) round(($cal * 0.40) / 4),
        'daily_fats_g'         => (int) round(($cal * 0.30) / 9),
    ];
}

// ── TC-PS-01: Male, 25y, 80kg, 180cm, active, lose ─────────────────────────
{
    $expected = calcExpected(80, 180, 25, 'male', 'active', 'lose');
    $result   = $svc->calculateTargets([
        'weight_kg' => 80, 'height_cm' => 180, 'gender' => 'male',
        'activity_level' => 'active', 'goal' => 'lose',
        'birth_date' => makeBirthDate(25),
    ]);
    assert_equals('TC-PS-01 calorie target (male, active, lose)', $expected['daily_calorie_target'], $result['daily_calorie_target']);
    assert_equals('TC-PS-01 protein_g', $expected['daily_protein_g'], $result['daily_protein_g']);
    assert_equals('TC-PS-01 carbs_g',   $expected['daily_carbs_g'],   $result['daily_carbs_g']);
    assert_equals('TC-PS-01 fats_g',    $expected['daily_fats_g'],    $result['daily_fats_g']);
}

// ── TC-PS-02: Female, 30y, 60kg, 165cm, beginner, maintain ─────────────────
{
    $expected = calcExpected(60, 165, 30, 'female', 'beginner', 'maintain');
    $result   = $svc->calculateTargets([
        'weight_kg' => 60, 'height_cm' => 165, 'gender' => 'female',
        'activity_level' => 'beginner', 'goal' => 'maintain',
        'birth_date' => makeBirthDate(30),
    ]);
    assert_equals('TC-PS-02 calorie target (female, beginner, maintain)', $expected['daily_calorie_target'], $result['daily_calorie_target']);
    assert_equals('TC-PS-02 protein_g', $expected['daily_protein_g'], $result['daily_protein_g']);
}

// ── TC-PS-03: Male, 40y, 100kg, 190cm, athlete, gain ───────────────────────
{
    $expected = calcExpected(100, 190, 40, 'male', 'athlete', 'gain');
    $result   = $svc->calculateTargets([
        'weight_kg' => 100, 'height_cm' => 190, 'gender' => 'male',
        'activity_level' => 'athlete', 'goal' => 'gain',
        'birth_date' => makeBirthDate(40),
    ]);
    assert_equals('TC-PS-03 calorie target (male, athlete, gain)', $expected['daily_calorie_target'], $result['daily_calorie_target']);
    assert_true('TC-PS-03 calorie > 3000 for bulk', $result['daily_calorie_target'] > 3000,
        "Got {$result['daily_calorie_target']}");
}

// ── TC-PS-04: Female athlete — calorie should be positive ───────────────────
{
    $result = $svc->calculateTargets([
        'weight_kg' => 55, 'height_cm' => 160, 'gender' => 'female',
        'activity_level' => 'athlete', 'goal' => 'lose',
        'birth_date' => makeBirthDate(22),
    ]);
    assert_true('TC-PS-04 calorie > 0', $result['daily_calorie_target'] > 0,
        "Got {$result['daily_calorie_target']}");
    assert_true('TC-PS-04 protein > 0', $result['daily_protein_g'] > 0);
}

// ── TC-PS-05: Macro distribution sums roughly to calorie target ─────────────
{
    $result = $svc->calculateTargets([
        'weight_kg' => 75, 'height_cm' => 175, 'gender' => 'male',
        'activity_level' => 'active', 'goal' => 'maintain',
        'birth_date' => makeBirthDate(28),
    ]);
    $macroCalories = ($result['daily_protein_g'] * 4)
                   + ($result['daily_carbs_g'] * 4)
                   + ($result['daily_fats_g'] * 9);
    $diff = abs($macroCalories - $result['daily_calorie_target']);
    assert_true('TC-PS-05 macros within ±50 kcal of target', $diff <= 50,
        "diff={$diff}, macros={$macroCalories}, target={$result['daily_calorie_target']}");
}

// ──────────────────────────────────────────────────────────────────────────────

print_suite_header('ProfileService::parsePostgresArray & formatPostgresArray');

// TC-PS-06: Parse normal array
$parsed = $svc->parsePostgresArray('{time,motivation}');
assert_equals('TC-PS-06 count', 2, count($parsed));
assert_equals('TC-PS-06 first',  'time',       $parsed[0]);
assert_equals('TC-PS-06 second', 'motivation', $parsed[1]);

// TC-PS-07: Parse empty array literal
$parsed = $svc->parsePostgresArray('{}');
assert_equals('TC-PS-07 empty array', 0, count($parsed));

// TC-PS-08: Parse null / empty string
$parsed = $svc->parsePostgresArray(null);
assert_equals('TC-PS-08 null => empty array', 0, count($parsed));

// TC-PS-09: Format array back
$formatted = $svc->formatPostgresArray(['stress', 'money', 'time']);
assert_equals('TC-PS-09 format', '{stress,money,time}', $formatted);

// TC-PS-10: Format empty array
$formatted = $svc->formatPostgresArray([]);
assert_equals('TC-PS-10 format empty', '{}', $formatted);

// TC-PS-11: Round-trip parse then format
$original  = '{a,b,c}';
$rt        = $svc->formatPostgresArray($svc->parsePostgresArray($original));
assert_equals('TC-PS-11 round-trip', $original, $rt);
