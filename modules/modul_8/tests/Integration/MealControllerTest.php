<?php
/**
 * Integration: MealController via Backend/index.php dispatcher
 *
 * Scenarios: list_meals, log_meal, delete_meal, list_saved_foods,
 *            log_water, log_weight
 */

require_once __DIR__ . '/IntegrationBase.php';

// ──────────────────────────────────────────────────────────────────────────────
print_suite_header('list_meals');

// TC-M-01: Returns meals for a date
{
    $t   = new IntegrationBase();
    $t->pdo->enqueueRows([
        ['id' => 1, 'meal_type' => 'breakfast', 'name' => 'Oats', 'calories' => '300',
         'protein_g' => '10', 'carbs_g' => '55', 'fats_g' => '5',
         'fiber_g' => '4', 'sugar_g' => '2', 'sodium_mg' => '100',
         'serving_size' => '80', 'photo_url' => null, 'source' => 'manual',
         'ai_confidence' => null, 'saved_food_id' => null,
         'created_at' => '2024-06-01 08:00:00'],
    ]);
    $r = $t->call('list_meals', ['date' => '2024-06-01']);
    assert_true('TC-M-01 data is array', is_array($r['data'] ?? null), json_encode($r));
    assert_equals('TC-M-01 one meal returned', 1, count($r['data']));
    assert_equals('TC-M-01 meal name', 'Oats', $r['data'][0]['name']);
    assert_true('TC-M-01 calories is int', is_int($r['data'][0]['calories']));
}

// TC-M-02: Invalid date format → 422
{
    $t = new IntegrationBase();
    $r = $t->call('list_meals', ['date' => '06-01-2024']);
    assert_true('TC-M-02 error on bad date', isset($r['error']), json_encode($r));
}

// TC-M-03: Empty result for a date
{
    $t = new IntegrationBase();
    $t->pdo->enqueueRows([]);
    $r = $t->call('list_meals', ['date' => '2024-06-01']);
    assert_equals('TC-M-03 empty meals array', 0, count($r['data'] ?? []));
}

// ──────────────────────────────────────────────────────────────────────────────
print_suite_header('log_meal');

function mealBody(array $overrides = []): array {
    return array_merge([
        'meal_type'  => 'lunch',
        'name'       => 'Chicken Rice',
        'log_date'   => date('Y-m-d'),
        'calories'   => 550,
        'protein_g'  => 35,
        'carbs_g'    => 60,
        'fats_g'     => 12,
        'fiber_g'    => 3,
        'sugar_g'    => 2,
        'sodium_mg'  => 400,
        'source'     => 'manual',
    ], $overrides);
}

// TC-M-04: Happy path
{
    $t = new IntegrationBase();
    $t->pdo->enqueueRow(['id' => '42', 'created_at' => '2024-06-01 12:00:00']);
    $r = $t->call('log_meal', body: mealBody());
    assert_true('TC-M-04 data.id present', isset($r['data']['id']), json_encode($r));
    assert_equals('TC-M-04 id is 42', 42, $r['data']['id']);
}

// TC-M-05: ai_scan source requires ai_confidence
{
    $t = new IntegrationBase();
    $r = $t->call('log_meal', body: mealBody(['source' => 'ai_scan']));
    assert_true('TC-M-05 error missing ai_confidence', isset($r['error']), json_encode($r));
    assert_contains('TC-M-05 error text', 'ai_confidence', $r['error']);
}

// TC-M-06: ai_scan with confidence present → success
{
    $t = new IntegrationBase();
    $t->pdo->enqueueRow(['id' => '7', 'created_at' => '2024-06-01 13:00:00']);
    $r = $t->call('log_meal', body: mealBody(['source' => 'ai_scan', 'ai_confidence' => 0.87]));
    assert_true('TC-M-06 ai_scan success', isset($r['data']['id']), json_encode($r));
}

// TC-M-07: Invalid meal_type
{
    $t = new IntegrationBase();
    $r = $t->call('log_meal', body: mealBody(['meal_type' => 'midnight_snack']));
    assert_true('TC-M-07 error invalid meal_type', isset($r['error']), json_encode($r));
}

// TC-M-08: Empty name
{
    $t = new IntegrationBase();
    $r = $t->call('log_meal', body: mealBody(['name' => '']));
    assert_true('TC-M-08 error on empty name', isset($r['error']), json_encode($r));
}

// TC-M-09: Invalid log_date format
{
    $t = new IntegrationBase();
    $r = $t->call('log_meal', body: mealBody(['log_date' => '01/06/2024']));
    assert_true('TC-M-09 error on bad log_date', isset($r['error']), json_encode($r));
}

// ──────────────────────────────────────────────────────────────────────────────
print_suite_header('delete_meal');

// TC-M-10: Happy path
{
    $t = new IntegrationBase();
    $t->pdo->enqueueRows([['id' => 5, 'user_id' => 1, 'log_date' => '2024-06-01']]);
    $t->pdo->rowCount = 1;
    $r = $t->call('delete_meal', body: ['id' => 5]);
    assert_true('TC-M-10 deleted=true', $r['data']['deleted'] ?? false, json_encode($r));
}

// TC-M-11: Not found / unauthorized → 404
{
    $t = new IntegrationBase();
    $t->pdo->rowCount = 0;
    $r = $t->call('delete_meal', body: ['id' => 999]);
    assert_true('TC-M-11 error on not found', isset($r['error']), json_encode($r));
    assert_contains('TC-M-11 mentions not found', 'not found', strtolower($r['error']));
}

// TC-M-12: Missing ID → 400
{
    $t = new IntegrationBase();
    $r = $t->call('delete_meal', body: []);
    assert_true('TC-M-12 error missing id', isset($r['error']), json_encode($r));
}

// ──────────────────────────────────────────────────────────────────────────────
print_suite_header('list_saved_foods');

// TC-M-13: Returns list
{
    $t = new IntegrationBase();
    $t->pdo->enqueueRows([
        ['id' => 1, 'name' => 'Brown Rice', 'brand' => 'Generic', 'calories' => '200',
         'protein_g' => '4', 'carbs_g' => '43', 'fats_g' => '1', 'fiber_g' => '2',
         'sugar_g' => '0', 'sodium_mg' => '5', 'serving_size' => '100',
         'serving_unit' => 'g', 'barcode' => null, 'source' => 'manual',
         'created_at' => '2024-01-01 00:00:00'],
    ]);
    $r = $t->call('list_saved_foods');
    assert_equals('TC-M-13 one saved food', 1, count($r['data'] ?? []));
    assert_equals('TC-M-13 name', 'Brown Rice', $r['data'][0]['name']);
}

// ──────────────────────────────────────────────────────────────────────────────
print_suite_header('log_water');

// TC-M-14: Happy path
{
    $t = new IntegrationBase();
    $t->pdo->enqueueRow(['id' => '3']);
    $r = $t->call('log_water', body: ['amount_ml' => 250, 'log_date' => date('Y-m-d')]);
    assert_true('TC-M-14 id returned', isset($r['data']['id']), json_encode($r));
    assert_equals('TC-M-14 id=3', 3, $r['data']['id']);
}

// TC-M-15: Zero amount → 422
{
    $t = new IntegrationBase();
    $r = $t->call('log_water', body: ['amount_ml' => 0, 'log_date' => date('Y-m-d')]);
    assert_true('TC-M-15 error on 0 amount', isset($r['error']), json_encode($r));
}

// TC-M-16: Negative amount → 422
{
    $t = new IntegrationBase();
    $r = $t->call('log_water', body: ['amount_ml' => -100, 'log_date' => date('Y-m-d')]);
    assert_true('TC-M-16 error on negative amount', isset($r['error']), json_encode($r));
}

// TC-M-17: Invalid log_date → 422
{
    $t = new IntegrationBase();
    $r = $t->call('log_water', body: ['amount_ml' => 500, 'log_date' => 'yesterday']);
    assert_true('TC-M-17 error on invalid date', isset($r['error']), json_encode($r));
}

// ──────────────────────────────────────────────────────────────────────────────
print_suite_header('log_weight');

// TC-M-18: Happy path
{
    $t = new IntegrationBase();
    $t->pdo->enqueueRow(['id' => '10']);
    $r = $t->call('log_weight', body: ['weight_kg' => 79.5, 'log_date' => date('Y-m-d')]);
    assert_true('TC-M-18 id returned', isset($r['data']['id']), json_encode($r));
}

// TC-M-19: Zero weight → 422
{
    $t = new IntegrationBase();
    $r = $t->call('log_weight', body: ['weight_kg' => 0, 'log_date' => date('Y-m-d')]);
    assert_true('TC-M-19 error on 0 weight', isset($r['error']), json_encode($r));
}

// TC-M-20: Negative weight → 422
{
    $t = new IntegrationBase();
    $r = $t->call('log_weight', body: ['weight_kg' => -5, 'log_date' => date('Y-m-d')]);
    assert_true('TC-M-20 error on negative weight', isset($r['error']), json_encode($r));
}

// TC-M-21: Invalid date → 422
{
    $t = new IntegrationBase();
    $r = $t->call('log_weight', body: ['weight_kg' => 75, 'log_date' => '2024/06/01']);
    assert_true('TC-M-21 error on invalid date', isset($r['error']), json_encode($r));
}
