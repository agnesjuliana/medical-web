<?php
/**
 * Unit tests for Backend\Services\AiScanService
 *
 * Tests: parseImageUri, sanitizePrediction, getDailyLimit
 * (callGemini is excluded — it makes real HTTP calls and should be integration-tested)
 */

// TestKernel loaded by runner.php

spl_autoload_register(function (string $class): void {
    $base = __DIR__ . '/../../Backend/';
    $rel  = str_replace(['Backend\\', '\\'], ['', '/'], $class) . '.php';
    if (file_exists($base . $rel)) require $base . $rel;
});

use Backend\Services\AiScanService;

print_suite_header('AiScanService::getDailyLimit');

$svc = new AiScanService();

assert_equals('TC-AI-01 daily limit = 20', 20, $svc->getDailyLimit());

// ──────────────────────────────────────────────────────────────────────────────

print_suite_header('AiScanService::parseImageUri');

// TC-AI-02: Valid JPEG URI
{
    $rawBytes = random_bytes(16);
    $b64      = base64_encode($rawBytes);
    $uri      = "data:image/jpeg;base64,$b64";
    $result   = $svc->parseImageUri($uri);
    assert_equals('TC-AI-02 media_type jpeg', 'image/jpeg', $result['media_type']);
    assert_equals('TC-AI-02 raw_b64 extracted', $b64, $result['raw_b64']);
}

// TC-AI-03: Valid PNG URI
{
    $b64  = base64_encode('fakepngdata');
    $uri  = "data:image/png;base64,$b64";
    $r    = $svc->parseImageUri($uri);
    assert_equals('TC-AI-03 media_type png', 'image/png', $r['media_type']);
}

// TC-AI-04: Missing data:image/ prefix → InvalidArgumentException
{
    $threw = false;
    try {
        $svc->parseImageUri('not-a-data-uri');
    } catch (\InvalidArgumentException $e) {
        $threw = true;
    }
    assert_true('TC-AI-04 throws on missing data:image/ prefix', $threw);
}

// TC-AI-05: Plain text (not base64) → InvalidArgumentException
{
    $threw = false;
    try {
        $svc->parseImageUri('data:image/jpeg;base64,!!!notbase64!!!');
    } catch (\InvalidArgumentException $e) {
        $threw = true;
    }
    assert_true('TC-AI-05 throws on invalid base64', $threw);
}

// TC-AI-06: Empty string → exception
{
    $threw = false;
    try {
        $svc->parseImageUri('');
    } catch (\InvalidArgumentException $e) {
        $threw = true;
    }
    assert_true('TC-AI-06 throws on empty string', $threw);
}

// TC-AI-07: data:image/webp variant
{
    $b64  = base64_encode('fakewebpdata');
    $uri  = "data:image/webp;base64,$b64";
    $r    = $svc->parseImageUri($uri);
    assert_equals('TC-AI-07 media_type webp', 'image/webp', $r['media_type']);
}

// ──────────────────────────────────────────────────────────────────────────────

print_suite_header('AiScanService::sanitizePrediction');

// TC-AI-08: Valid full prediction
{
    $input = [
        'items' => [
            ['name' => 'Rice', 'estimated_grams' => 150, 'calories' => 200,
             'protein_g' => 4.0, 'carbs_g' => 44.0, 'fats_g' => 0.5, 'confidence' => 0.92],
            ['name' => 'Egg', 'estimated_grams' => 50, 'calories' => 78,
             'protein_g' => 6.3, 'carbs_g' => 0.6, 'fats_g' => 5.3, 'confidence' => 0.88],
        ],
        'notes' => 'Typical breakfast',
    ];
    $result = $svc->sanitizePrediction($input);
    assert_equals('TC-AI-08 items count', 2, count($result['items']));
    assert_equals('TC-AI-08 rice calories', 200, $result['items'][0]['calories']);
    assert_equals('TC-AI-08 rice confidence rounded', 0.92, $result['items'][0]['confidence']);
    assert_true('TC-AI-08 notes preserved', isset($result['notes']));
}

// TC-AI-09: Item missing required field is silently dropped
{
    $input = [
        'items' => [
            ['name' => 'Apple', 'calories' => 95, 'protein_g' => 0.5,
             'carbs_g' => 25.0, 'fats_g' => 0.3, 'confidence' => 0.9],
            // Missing 'confidence' → should be dropped
            ['name' => 'Banana', 'calories' => 105, 'protein_g' => 1.3, 'carbs_g' => 27.0, 'fats_g' => 0.4],
        ],
        'notes' => '',
    ];
    $result = $svc->sanitizePrediction($input);
    assert_equals('TC-AI-09 invalid item dropped → 1 item', 1, count($result['items']));
    assert_equals('TC-AI-09 valid item kept', 'Apple', $result['items'][0]['name']);
}

// TC-AI-10: Missing 'items' key → UnexpectedValueException
{
    $threw = false;
    try {
        $svc->sanitizePrediction(['notes' => 'no items key']);
    } catch (\UnexpectedValueException $e) {
        $threw = true;
    }
    assert_true('TC-AI-10 throws when items key missing', $threw);
}

// TC-AI-11: 'items' is not array → UnexpectedValueException
{
    $threw = false;
    try {
        $svc->sanitizePrediction(['items' => 'not-an-array']);
    } catch (\UnexpectedValueException $e) {
        $threw = true;
    }
    assert_true('TC-AI-11 throws when items is not array', $threw);
}

// TC-AI-12: All items invalid → empty items list returned
{
    $input  = ['items' => [['bad' => 'data'], ['also' => 'bad']], 'notes' => ''];
    $result = $svc->sanitizePrediction($input);
    assert_equals('TC-AI-12 all invalid items filtered to []', 0, count($result['items']));
}

// TC-AI-13: estimated_grams optional — absent means null
{
    $input = [
        'items' => [[
            'name' => 'Unknown food', 'calories' => 200, 'protein_g' => 10,
            'carbs_g' => 30, 'fats_g' => 5, 'confidence' => 0.5,
            // no estimated_grams
        ]],
        'notes' => '',
    ];
    $result = $svc->sanitizePrediction($input);
    assert_true('TC-AI-13 estimated_grams is null when absent',
        $result['items'][0]['estimated_grams'] === null);
}

// TC-AI-14: Numeric values are cast correctly
{
    $input = [
        'items' => [[
            'name' => 'Test', 'estimated_grams' => '100', 'calories' => '350',
            'protein_g' => '12.567', 'carbs_g' => '45.123', 'fats_g' => '8.999',
            'confidence' => '0.777',
        ]],
        'notes' => '',
    ];
    $result = $svc->sanitizePrediction($input);
    $item = $result['items'][0];
    assert_true('TC-AI-14 calories is int',          is_int($item['calories']),          gettype($item['calories']));
    assert_true('TC-AI-14 protein_g rounded to 1dp', $item['protein_g'] === 12.6,        "got {$item['protein_g']}");
    assert_true('TC-AI-14 carbs_g rounded',          $item['carbs_g'] === 45.1,          "got {$item['carbs_g']}");
    assert_true('TC-AI-14 confidence rounded to 2dp', $item['confidence'] === 0.78,       "got {$item['confidence']}");
}

// TC-AI-15: Markdown code-fence stripping (via callGemini — tested at service level via regex)
{
    // We test the regex used in callGemini by replicating it
    $fenced   = '```json' . "\n" . '{"items":[],"notes":"ok"}' . "\n" . '```';
    $stripped = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $fenced);
    assert_equals('TC-AI-15 strip markdown fences', '{"items":[],"notes":"ok"}', $stripped);
}
