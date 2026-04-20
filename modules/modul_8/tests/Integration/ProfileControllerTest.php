<?php
/**
 * Integration: ProfileController via Backend/index.php dispatcher
 *
 * All scenarios: get_profile, save_profile (happy + validation)
 */

require_once __DIR__ . '/IntegrationBase.php';

print_suite_header('get_profile');

// ── TC-P-01: Profile found ───────────────────────────────────────────────────
{
    $t   = new IntegrationBase();
    $pdo = $t->pdo;
    $pdo->enqueueRow([
        'user_id' => 1, 'gender' => 'male', 'birth_date' => '1995-01-01',
        'height_cm' => '175', 'weight_kg' => '70', 'activity_level' => 'active',
        'goal' => 'maintain', 'goal_weight_kg' => null, 'step_goal' => '8000',
        'barriers' => '{stress}', 'daily_calorie_target' => '2200',
        'daily_protein_g' => '165', 'daily_carbs_g' => '220', 'daily_fats_g' => '73',
        'onboarded_at' => '2024-01-01 00:00:00',
    ]);
    $r = $t->call('get_profile');
    assert_true('TC-P-01 success data present', isset($r['data']),           json_encode($r));
    assert_equals('TC-P-01 gender',      'male',    $r['data']['gender']);
    assert_equals('TC-P-01 height_cm',   175.0,     $r['data']['height_cm']);
    assert_equals('TC-P-01 weight_kg',   70.0,      $r['data']['weight_kg']);
    assert_equals('TC-P-01 barriers parsed', ['stress'], $r['data']['barriers']);
    assert_true('TC-P-01 goal_weight_kg null', $r['data']['goal_weight_kg'] === null);
}

// ── TC-P-02: Profile not found → 404 ────────────────────────────────────────
{
    $t   = new IntegrationBase();
    $t->pdo->enqueueEmpty();
    $r = $t->call('get_profile');
    assert_true('TC-P-02 error key present', isset($r['error']), json_encode($r));
    assert_contains('TC-P-02 error message', 'not found', $r['error']);
}

// ── TC-P-03: Barriers with multiple elements ─────────────────────────────────
{
    $t   = new IntegrationBase();
    $t->pdo->enqueueRow([
        'user_id' => 1, 'gender' => 'female', 'birth_date' => '1990-05-15',
        'height_cm' => '165', 'weight_kg' => '60', 'activity_level' => 'beginner',
        'goal' => 'lose', 'goal_weight_kg' => '55', 'step_goal' => '10000',
        'barriers' => '{time,money,motivation}', 'daily_calorie_target' => '1500',
        'daily_protein_g' => '112', 'daily_carbs_g' => '150', 'daily_fats_g' => '50',
        'onboarded_at' => '2024-01-01 00:00:00',
    ]);
    $r = $t->call('get_profile');
    assert_equals('TC-P-03 barriers count', 3, count($r['data']['barriers']));
    assert_equals('TC-P-03 goal_weight_kg cast', 55.0, $r['data']['goal_weight_kg']);
}

// ──────────────────────────────────────────────────────────────────────────────

print_suite_header('save_profile — Happy Path');

function baseBody(string $gender = 'male', string $activity = 'active',
                  string $goal = 'lose'): array {
    return [
        'gender'         => $gender,
        'birth_date'     => date('Y-m-d', strtotime('-25 years')),
        'height_cm'      => 180,
        'weight_kg'      => 80,
        'activity_level' => $activity,
        'goal'           => $goal,
        'step_goal'      => 10000,
        'barriers'       => ['time', 'motivation'],
    ];
}

// ── TC-P-04: Happy path male/active/lose ────────────────────────────────────
{
    $t = new IntegrationBase();
    $r = $t->call('save_profile', body: baseBody());
    assert_true('TC-P-04 saved=true', $r['data']['saved'] ?? false === true, json_encode($r));
    assert_true('TC-P-04 calorie_target present', isset($r['data']['daily_calorie_target']));
    assert_true('TC-P-04 calorie > 0', ($r['data']['daily_calorie_target'] ?? 0) > 0);
}

// ── TC-P-05: Happy path female/beginner/maintain ────────────────────────────
{
    $t = new IntegrationBase();
    $r = $t->call('save_profile', body: baseBody('female', 'beginner', 'maintain'));
    assert_true('TC-P-05 saved=true', $r['data']['saved'] ?? false === true, json_encode($r));
}

// ── TC-P-06: Barriers formatted as Postgres array in DB params ───────────────
{
    $t   = new IntegrationBase();
    $r   = $t->call('save_profile', body: baseBody());
    $params = null;
    foreach ($t->pdo->queries as $q) {
        if (str_contains($q['sql'], 'INSERT INTO m8_user_profiles')) {
            $params = array_values($q['params']);
            break;
        }
    }
    // barriers is param index 9 in upsert
    assert_equals('TC-P-06 barriers formatted as postgres', '{time,motivation}', $params[9] ?? null);
}

// ──────────────────────────────────────────────────────────────────────────────

print_suite_header('save_profile — Validation');

// ── TC-P-07: Invalid gender ──────────────────────────────────────────────────
{
    $t = new IntegrationBase();
    $body = array_merge(baseBody(), ['gender' => 'other']);
    $r = $t->call('save_profile', body: $body);
    assert_true('TC-P-07 error on invalid gender', isset($r['error']), json_encode($r));
    assert_contains('TC-P-07 error mentions gender', 'gender', $r['error']);
}

// ── TC-P-08: Future birth_date ───────────────────────────────────────────────
{
    $t = new IntegrationBase();
    $body = array_merge(baseBody(), ['birth_date' => date('Y-m-d', strtotime('+1 year'))]);
    $r = $t->call('save_profile', body: $body);
    assert_true('TC-P-08 error on future birthdate', isset($r['error']), json_encode($r));
    assert_contains('TC-P-08 mentions birth_date', 'birth_date', $r['error']);
}

// ── TC-P-09: Invalid birth_date format ──────────────────────────────────────
{
    $t = new IntegrationBase();
    $body = array_merge(baseBody(), ['birth_date' => '25-01-1999']);
    $r = $t->call('save_profile', body: $body);
    assert_true('TC-P-09 error on malformed date', isset($r['error']), json_encode($r));
}

// ── TC-P-10: Negative height ─────────────────────────────────────────────────
{
    $t = new IntegrationBase();
    $body = array_merge(baseBody(), ['height_cm' => -10]);
    $r = $t->call('save_profile', body: $body);
    assert_true('TC-P-10 error on negative height', isset($r['error']), json_encode($r));
    assert_contains('TC-P-10 mentions height_cm', 'height_cm', $r['error']);
}

// ── TC-P-11: Zero weight ─────────────────────────────────────────────────────
{
    $t = new IntegrationBase();
    $body = array_merge(baseBody(), ['weight_kg' => 0]);
    $r = $t->call('save_profile', body: $body);
    assert_true('TC-P-11 error on zero weight', isset($r['error']), json_encode($r));
    assert_contains('TC-P-11 mentions weight_kg', 'weight_kg', $r['error']);
}

// ── TC-P-12: Invalid activity_level ─────────────────────────────────────────
{
    $t = new IntegrationBase();
    $body = array_merge(baseBody(), ['activity_level' => 'couch_potato']);
    $r = $t->call('save_profile', body: $body);
    assert_true('TC-P-12 error on bad activity', isset($r['error']), json_encode($r));
}

// ── TC-P-13: Invalid goal ────────────────────────────────────────────────────
{
    $t = new IntegrationBase();
    $body = array_merge(baseBody(), ['goal' => 'bulk_hard']);
    $r = $t->call('save_profile', body: $body);
    assert_true('TC-P-13 error on bad goal', isset($r['error']), json_encode($r));
}

// ── TC-P-14: Missing gender entirely ────────────────────────────────────────
{
    $t = new IntegrationBase();
    $body = baseBody();
    unset($body['gender']);
    $r = $t->call('save_profile', body: $body);
    assert_true('TC-P-14 error on missing gender', isset($r['error']), json_encode($r));
}

// ── TC-P-15: Missing birth_date ──────────────────────────────────────────────
{
    $t = new IntegrationBase();
    $body = baseBody();
    unset($body['birth_date']);
    $r = $t->call('save_profile', body: $body);
    assert_true('TC-P-15 error on missing birth_date', isset($r['error']), json_encode($r));
}
