<?php
/**
 * Integration: ProgressController via Backend/index.php dispatcher
 *
 * Scenarios:
 *   get_weight_progress  — happy path, missing profile, delta computation,
 *                          range narrower than delta window (reviewer fix)
 *   get_weekly_energy    — exact week bounds (no over-scan), skeleton fill
 *   get_calorie_averages — backward-compat shape
 *   get_progress_summary — combined payload, all three sections present
 */

require_once __DIR__ . '/IntegrationBase.php';

// ── Fixtures ─────────────────────────────────────────────────────────────────

function baseProfile(array $overrides = []): array
{
    return array_merge([
        'weight_kg'            => '80.0',
        'goal_weight_kg'       => '75.0',
        'height_cm'            => '175.0',
        'daily_calorie_target' => '2000',
        'daily_protein_g'      => '150',
        'daily_carbs_g'        => '200',
        'daily_fats_g'         => '60',
    ], $overrides);
}

/**
 * Enqueue a get_weight_progress run:
 *   1. profile fetch
 *   2. weight logs fetchAll  (getProgressSummary — range query, for chart)
 *   3. first_log fetch       (getProgressSummary — getFirstLog)
 *   4. delta logs fetchAll   (getRecentLogs(30) — always 30d, independent of range)
 */
function enqueueWeightProgress(
    MockPdo $pdo,
    array   $chartLogs    = [],
    ?array  $firstLog     = null,
    array   $deltaLogs    = [],
    array   $profileOverrides = []
): void {
    $pdo->enqueueRow(baseProfile($profileOverrides));  // 1. profile
    $pdo->enqueueRows($chartLogs);                     // 2. chart logs (range)
    if ($firstLog !== null) {                          // 3. first log
        $pdo->enqueueRow($firstLog);
    } else {
        $pdo->enqueueEmpty();
    }
    $pdo->enqueueRows($deltaLogs);                     // 4. delta logs (always 30d)
}

/**
 * Enqueue a get_weekly_energy run:
 *   1. getCaloriesByDateRange fetchAll
 */
function enqueueWeeklyEnergy(MockPdo $pdo, array $rows = []): void
{
    $pdo->enqueueRows($rows);
}

/**
 * Enqueue a get_calorie_averages run:
 *   1. getDailyCalories(7)  fetchAll
 *   2. getDailyCalories(30) fetchAll
 */
function enqueueCalorieAverages(MockPdo $pdo, array $rows7d = [], array $rows30d = []): void
{
    $pdo->enqueueRows($rows7d);
    $pdo->enqueueRows($rows30d);
}

/**
 * Enqueue a get_progress_summary run (all sections in order).
 *   1. profile
 *   2. chart logs (range)
 *   3. first log
 *   4. delta logs (30d, independent)
 *   5. weekly energy (getCaloriesByDateRange)
 *   6. avg 7d (getDailyCalories)
 *   7. avg 30d (getDailyCalories)
 */
function enqueueProgressSummary(
    MockPdo $pdo,
    array   $chartLogs    = [],
    ?array  $firstLog     = null,
    array   $deltaLogs    = [],
    array   $weeklyRows   = [],
    array   $rows7d       = [],
    array   $rows30d      = [],
    array   $profileOverrides = []
): void {
    $pdo->enqueueRow(baseProfile($profileOverrides));  // 1. profile
    $pdo->enqueueRows($chartLogs);                     // 2. chart logs
    if ($firstLog !== null) {                          // 3. first log
        $pdo->enqueueRow($firstLog);
    } else {
        $pdo->enqueueEmpty();
    }
    $pdo->enqueueRows($deltaLogs);                     // 4. delta logs (30d)
    $pdo->enqueueRows($weeklyRows);                    // 5. weekly energy
    $pdo->enqueueRows($rows7d);                        // 6. avg 7d
    $pdo->enqueueRows($rows30d);                       // 7. avg 30d
}

// ═════════════════════════════════════════════════════════════════════════════
print_suite_header('get_weight_progress');

// TC-WP-01: Happy path — response shape
{
    $t = new IntegrationBase();
    enqueueWeightProgress($t->pdo);
    $r = $t->call('get_weight_progress');
    assert_true('TC-WP-01 data present', isset($r['data']));
    assert_key('TC-WP-01 current_weight', 'current_weight', $r['data']);
    assert_key('TC-WP-01 start_weight',   'start_weight',   $r['data']);
    assert_key('TC-WP-01 goal_weight',    'goal_weight',    $r['data']);
    assert_key('TC-WP-01 goal_progress',  'goal_progress',  $r['data']);
    assert_key('TC-WP-01 logs',           'logs',           $r['data']);
    assert_key('TC-WP-01 deltas',         'deltas',         $r['data']);
    assert_key('TC-WP-01 height_cm',      'height_cm',      $r['data']);
    assert_key('TC-WP-01 bmi',            'bmi',            $r['data']);
}

// TC-WP-02: Delta keys present
{
    $t = new IntegrationBase();
    enqueueWeightProgress($t->pdo);
    $r      = $t->call('get_weight_progress');
    $deltas = $r['data']['deltas'] ?? [];
    assert_key('TC-WP-02 delta 3d',  '3d',  $deltas);
    assert_key('TC-WP-02 delta 7d',  '7d',  $deltas);
    assert_key('TC-WP-02 delta 30d', '30d', $deltas);
}

// TC-WP-03: Delta computed correctly from independent delta dataset
{
    $t         = new IntegrationBase();
    $deltaLogs = [
        ['log_date' => date('Y-m-d', strtotime('-6 days')), 'weight_kg' => '82.0'],
        ['log_date' => date('Y-m-d', strtotime('-3 days')), 'weight_kg' => '81.0'],
        ['log_date' => date('Y-m-d', strtotime('-1 day')),  'weight_kg' => '80.5'],
    ];
    enqueueWeightProgress(
        $t->pdo,
        $deltaLogs,
        ['log_date' => date('Y-m-d', strtotime('-90 days')), 'weight_kg' => '85.0'],
        $deltaLogs
    );
    $r = $t->call('get_weight_progress');
    assert_equals('TC-WP-03 7d delta', -1.5, $r['data']['deltas']['7d']);
    assert_equals('TC-WP-03 3d delta', -0.5, $r['data']['deltas']['3d']);
}

// TC-WP-04: Exactly 4 DB queries (profile + chart_logs + first_log + delta_logs)
{
    $t = new IntegrationBase();
    enqueueWeightProgress($t->pdo);
    $t->call('get_weight_progress');
    assert_equals('TC-WP-04 query count', 4, count($t->pdo->queries));
}

// TC-WP-05: Missing profile returns error
{
    $t = new IntegrationBase();
    $t->pdo->enqueueEmpty();
    $r = $t->call('get_weight_progress');
    assert_true('TC-WP-05 error on missing profile', isset($r['error']));
}

// TC-WP-06: start_weight falls back to current when no first log
{
    $t = new IntegrationBase();
    $t->pdo->enqueueRow(baseProfile());
    $t->pdo->enqueueRows([]);   // no chart logs
    $t->pdo->enqueueEmpty();    // no first log
    $t->pdo->enqueueRows([]);   // no delta logs
    $r = $t->call('get_weight_progress');
    assert_equals('TC-WP-06 start_weight fallback', 80.0, $r['data']['start_weight']);
}

// TC-WP-07: Narrow range (7d) does not corrupt 30d delta — reviewer regression fix
// Chart logs only cover 7 days; delta logs cover 30 days and show real movement.
{
    $t         = new IntegrationBase();
    $chartLogs = [
        ['log_date' => date('Y-m-d', strtotime('-3 days')), 'weight_kg' => '81.0'],
        ['log_date' => date('Y-m-d', strtotime('-1 day')),  'weight_kg' => '80.5'],
    ];
    $deltaLogs = [
        ['log_date' => date('Y-m-d', strtotime('-29 days')), 'weight_kg' => '84.0'],
        ['log_date' => date('Y-m-d', strtotime('-15 days')), 'weight_kg' => '82.0'],
        ['log_date' => date('Y-m-d', strtotime('-3 days')),  'weight_kg' => '81.0'],
        ['log_date' => date('Y-m-d', strtotime('-1 day')),   'weight_kg' => '80.5'],
    ];
    enqueueWeightProgress($t->pdo, $chartLogs, null, $deltaLogs);
    $r = $t->call('get_weight_progress', ['range' => '7']);
    // Chart should only have 2 entries (7d range)
    assert_equals('TC-WP-07 chart logs scoped to range', 2, count($r['data']['logs']));
    // 30d delta must reflect 30-day history from deltaLogs, not the narrow chart range
    assert_equals('TC-WP-07 30d delta uses full history', -3.5, $r['data']['deltas']['30d']);
    // 7d delta also correct
    assert_equals('TC-WP-07 7d delta correct', -0.5, $r['data']['deltas']['7d']);
}

// ═════════════════════════════════════════════════════════════════════════════
print_suite_header('get_weekly_energy');

// TC-WE-01: Response shape
{
    $t = new IntegrationBase();
    enqueueWeeklyEnergy($t->pdo);
    $r = $t->call('get_weekly_energy');
    assert_true('TC-WE-01 data present', isset($r['data']));
    assert_key('TC-WE-01 week_start',     'week_start',     $r['data']);
    assert_key('TC-WE-01 week_end',       'week_end',       $r['data']);
    assert_key('TC-WE-01 days',           'days',           $r['data']);
    assert_key('TC-WE-01 total_consumed', 'total_consumed', $r['data']);
}

// TC-WE-02: Exactly 7 days returned
{
    $t = new IntegrationBase();
    enqueueWeeklyEnergy($t->pdo);
    $r = $t->call('get_weekly_energy');
    assert_equals('TC-WE-02 7 days', 7, count($r['data']['days']));
}

// TC-WE-03: Only 1 DB query (exact BETWEEN, not rolling over-scan)
{
    $t = new IntegrationBase();
    enqueueWeeklyEnergy($t->pdo);
    $t->call('get_weekly_energy');
    assert_equals('TC-WE-03 single query', 1, count($t->pdo->queries));
}

// TC-WE-04: Query uses exact Monday-Sunday params (BETWEEN, not rolling window)
{
    $t = new IntegrationBase();
    enqueueWeeklyEnergy($t->pdo);
    $t->call('get_weekly_energy', ['offset' => '0']);
    $params = $t->pdo->queries[0]['params'] ?? [];
    $monday = date('Y-m-d', strtotime('monday this week'));
    $sunday = date('Y-m-d', strtotime('sunday this week'));
    assert_equals('TC-WE-04 monday param', $monday, $params[1] ?? null);
    assert_equals('TC-WE-04 sunday param', $sunday, $params[2] ?? null);
}

// TC-WE-05: Missing days filled with 0
{
    $t = new IntegrationBase();
    enqueueWeeklyEnergy($t->pdo, []);
    $r   = $t->call('get_weekly_energy');
    $sum = array_sum(array_column($r['data']['days'], 'consumed_cal'));
    assert_equals('TC-WE-05 zero fill', 0, $sum);
    assert_equals('TC-WE-05 total_consumed zero', 0, $r['data']['total_consumed']);
}

// TC-WE-06: DB rows fill skeleton correctly
{
    $t      = new IntegrationBase();
    $monday = date('Y-m-d', strtotime('monday this week'));
    enqueueWeeklyEnergy($t->pdo, [
        ['log_date' => $monday, 'calories' => 1800],
    ]);
    $r = $t->call('get_weekly_energy');
    assert_equals('TC-WE-06 monday calories', 1800, $r['data']['days'][0]['consumed_cal']);
    assert_equals('TC-WE-06 total_consumed',  1800, $r['data']['total_consumed']);
}

// ═════════════════════════════════════════════════════════════════════════════
print_suite_header('get_calorie_averages');

// TC-CA-01: Response shape backward-compatible
{
    $t = new IntegrationBase();
    enqueueCalorieAverages($t->pdo);
    $r = $t->call('get_calorie_averages');
    assert_true('TC-CA-01 data present', isset($r['data']));
    assert_key('TC-CA-01 avg_7d',  'avg_7d',  $r['data']);
    assert_key('TC-CA-01 avg_30d', 'avg_30d', $r['data']);
    assert_key('TC-CA-01 logs_7d', 'logs_7d', $r['data']);
}

// TC-CA-02: avg_7d computed correctly
{
    $t = new IntegrationBase();
    enqueueCalorieAverages($t->pdo, [
        ['log_date' => date('Y-m-d', strtotime('-1 day')),  'calories' => 2000],
        ['log_date' => date('Y-m-d', strtotime('-2 days')), 'calories' => 1800],
    ]);
    $r = $t->call('get_calorie_averages');
    assert_equals('TC-CA-02 avg_7d', 1900, $r['data']['avg_7d']);
}

// TC-CA-03: null when no logs
{
    $t = new IntegrationBase();
    enqueueCalorieAverages($t->pdo, [], []);
    $r = $t->call('get_calorie_averages');
    assert_true('TC-CA-03 avg_7d null when empty',  $r['data']['avg_7d']  === null);
    assert_true('TC-CA-03 avg_30d null when empty', $r['data']['avg_30d'] === null);
}

// ═════════════════════════════════════════════════════════════════════════════
print_suite_header('get_progress_summary');

// TC-PS-01: Response contains all three sections
{
    $t = new IntegrationBase();
    enqueueProgressSummary($t->pdo);
    $r = $t->call('get_progress_summary');
    assert_true('TC-PS-01 data present', isset($r['data']));
    assert_key('TC-PS-01 weight_progress',  'weight_progress',  $r['data']);
    assert_key('TC-PS-01 weekly_energy',    'weekly_energy',    $r['data']);
    assert_key('TC-PS-01 calorie_averages', 'calorie_averages', $r['data']);
}

// TC-PS-02: Weight progress section has correct keys
{
    $t  = new IntegrationBase();
    enqueueProgressSummary($t->pdo);
    $r  = $t->call('get_progress_summary');
    $wp = $r['data']['weight_progress'] ?? [];
    assert_key('TC-PS-02 current_weight', 'current_weight', $wp);
    assert_key('TC-PS-02 deltas',         'deltas',         $wp);
    assert_key('TC-PS-02 logs',           'logs',           $wp);
}

// TC-PS-03: Weekly energy section has correct keys
{
    $t  = new IntegrationBase();
    enqueueProgressSummary($t->pdo);
    $r  = $t->call('get_progress_summary');
    $we = $r['data']['weekly_energy'] ?? [];
    assert_key('TC-PS-03 week_start',     'week_start',     $we);
    assert_key('TC-PS-03 days',           'days',           $we);
    assert_key('TC-PS-03 total_consumed', 'total_consumed', $we);
}

// TC-PS-04: Calorie averages section has correct keys
{
    $t   = new IntegrationBase();
    enqueueProgressSummary($t->pdo);
    $r   = $t->call('get_progress_summary');
    $avg = $r['data']['calorie_averages'] ?? [];
    assert_key('TC-PS-04 avg_7d',  'avg_7d',  $avg);
    assert_key('TC-PS-04 avg_30d', 'avg_30d', $avg);
}

// TC-PS-05: Missing profile returns error
{
    $t = new IntegrationBase();
    $t->pdo->enqueueEmpty();
    $r = $t->call('get_progress_summary');
    assert_true('TC-PS-05 error on missing profile', isset($r['error']));
}

// TC-PS-06: Delta values correct in combined payload
{
    $t         = new IntegrationBase();
    $deltaLogs = [
        ['log_date' => date('Y-m-d', strtotime('-6 days')), 'weight_kg' => '82.0'],
        ['log_date' => date('Y-m-d', strtotime('-1 day')),  'weight_kg' => '80.0'],
    ];
    enqueueProgressSummary($t->pdo, $deltaLogs, null, $deltaLogs);
    $r = $t->call('get_progress_summary');
    assert_equals('TC-PS-06 7d delta', -2.0, $r['data']['weight_progress']['deltas']['7d']);
}

// TC-PS-07: Weekly energy returns exactly 7 days in summary payload
{
    $t = new IntegrationBase();
    enqueueProgressSummary($t->pdo);
    $r = $t->call('get_progress_summary');
    assert_equals('TC-PS-07 7 days', 7, count($r['data']['weekly_energy']['days']));
}
