<?php
/**
 * Integration: DashboardController via Backend/index.php dispatcher
 *
 * Scenarios: get_dashboard (happy, missing profile, invalid date, score clamping),
 *            get_health_scores
 */

require_once __DIR__ . '/IntegrationBase.php';

// ── Helpers ──────────────────────────────────────────────────────────────────

function enqueueFullDashboard(MockPdo $pdo, array $profileOverrides = [],
                               array $mealOverrides = [],
                               array $waterOverrides = []): void {
    // 1. Profile
    $pdo->enqueueRow(array_merge([
        'daily_calorie_target' => '2000',
        'daily_protein_g'      => '150',
        'daily_carbs_g'        => '200',
        'daily_fats_g'         => '60',
    ], $profileOverrides));

    // 2. Summary (Meal aggregates + Water total combined)
    $pdo->enqueueRow(array_merge([
        'calories'  => '1500',
        'protein_g' => '120',
        'carbs_g'   => '160',
        'fats_g'    => '45',
        'fiber_g'   => '10',
        'sugar_g'   => '0',
        'sodium_mg' => '0',
        'water_ml'  => '1500',
    ], $mealOverrides, $waterOverrides));

    // 3. Recent meals (fetchAll)
    $pdo->enqueueRows([]);
}

// ──────────────────────────────────────────────────────────────────────────────
print_suite_header('get_dashboard');

// TC-D-01: Happy path — all fields present
{
    $t = new IntegrationBase();
    enqueueFullDashboard($t->pdo);
    $r = $t->call('get_dashboard', ['date' => date('Y-m-d')]);
    assert_true('TC-D-01 data present', isset($r['data']), json_encode($r));
    assert_key('TC-D-01 date key',         'date',         $r['data']);
    assert_key('TC-D-01 targets key',      'targets',      $r['data']);
    assert_key('TC-D-01 consumed key',     'consumed',     $r['data']);
    assert_key('TC-D-01 remaining key',    'remaining',    $r['data']);
    assert_key('TC-D-01 health_score key', 'health_score', $r['data']);
    assert_key('TC-D-01 recent_meals key', 'recent_meals', $r['data']);
}

// TC-D-02: Remaining calories computed correctly
{
    $t = new IntegrationBase();
    enqueueFullDashboard($t->pdo,
        ['daily_calorie_target' => '2000'],
        ['calories' => '1200']
    );
    $r = $t->call('get_dashboard', ['date' => date('Y-m-d')]);
    assert_equals('TC-D-02 remaining calories', 800, $r['data']['remaining']['calories']);
}

// TC-D-03: Profile not found → 404
{
    $t = new IntegrationBase();
    $t->pdo->enqueueEmpty(); // no profile row
    $r = $t->call('get_dashboard', ['date' => date('Y-m-d')]);
    assert_true('TC-D-03 error on missing profile', isset($r['error']), json_encode($r));
    assert_contains('TC-D-03 mentions profile', 'profile', strtolower($r['error']));
}

// TC-D-04: Invalid date format → 422
{
    $t = new IntegrationBase();
    $r = $t->call('get_dashboard', ['date' => '2024/06/01']);
    assert_true('TC-D-04 error on bad date', isset($r['error']), json_encode($r));
}

// TC-D-05: Health score clamped to 0 (extreme over-consumption)
{
    $t = new IntegrationBase();
    enqueueFullDashboard($t->pdo,
        ['daily_calorie_target' => '2000', 'daily_protein_g' => '150',
         'daily_carbs_g' => '200', 'daily_fats_g' => '60'],
        ['calories' => '10000', 'protein_g' => '750',
         'carbs_g' => '1000', 'fats_g' => '300', 'fiber_g' => '0']
    );
    $r     = $t->call('get_dashboard', ['date' => date('Y-m-d')]);
    $score = $r['data']['health_score'] ?? -1;
    assert_true('TC-D-05 score ≥ 0',    $score >= 0,   "got $score");
    assert_true('TC-D-05 score ≤ 100',  $score <= 100, "got $score");
    assert_true('TC-D-05 score < 50',   $score < 50,   "got $score");
}

// TC-D-06: Health score = 100 on perfect consumption
{
    $t = new IntegrationBase();
    enqueueFullDashboard($t->pdo,
        ['daily_calorie_target' => '2000', 'daily_protein_g' => '150',
         'daily_carbs_g' => '200', 'daily_fats_g' => '60'],
        ['calories' => '2000', 'protein_g' => '150',
         'carbs_g' => '200', 'fats_g' => '60', 'fiber_g' => '25']
    );
    $r = $t->call('get_dashboard', ['date' => date('Y-m-d')]);
    assert_equals('TC-D-06 perfect score = 100', 100, $r['data']['health_score']);
}

// TC-D-07: Water consumed included in response
{
    $t = new IntegrationBase();
    enqueueFullDashboard($t->pdo, [], [], ['water_ml' => '2500']);
    
    $r = $t->call('get_dashboard', ['date' => date('Y-m-d')]);
    assert_equals('TC-D-07 water_ml = 2500', 2500, $r['data']['consumed']['water_ml']);
}

// TC-D-08: Default date (no GET param) uses today
{
    $t = new IntegrationBase();
    enqueueFullDashboard($t->pdo);
    $r = $t->call('get_dashboard'); // no date param
    assert_equals('TC-D-08 date defaults to today', date('Y-m-d'), $r['data']['date'] ?? '');
}

// ──────────────────────────────────────────────────────────────────────────────
print_suite_header('get_health_scores');

// TC-D-09: Returns history rows
{
    $t = new IntegrationBase();
    $t->pdo->enqueueRows([
        ['log_date' => '2024-06-01', 'score' => '85', 'calorie_deviation_pct' => '5.00',
         'macro_deviation_pct' => '8.00', 'computed_at' => '2024-06-01 23:59:00'],
        ['log_date' => '2024-05-31', 'score' => '72', 'calorie_deviation_pct' => '12.50',
         'macro_deviation_pct' => '15.00', 'computed_at' => '2024-05-31 23:59:00'],
    ]);
    $r = $t->call('get_health_scores', ['days' => '7']);
    assert_equals('TC-D-09 two rows returned', 2, count($r['data'] ?? []));
    assert_equals('TC-D-09 first score', 85, $r['data'][0]['score']);
}

// TC-D-10: Empty history
{
    $t = new IntegrationBase();
    $t->pdo->enqueueRows([]);
    $r = $t->call('get_health_scores', ['days' => '7']);
    assert_equals('TC-D-10 empty history', 0, count($r['data'] ?? []));
}

// TC-D-11: days param clamped (e.g., 9999 → max 30 rows queried)
{
    $t = new IntegrationBase();
    $t->pdo->enqueueRows([]);
    $r = $t->call('get_health_scores', ['days' => '9999']);
    assert_true('TC-D-11 no crash on large days param', isset($r['data']));
}
