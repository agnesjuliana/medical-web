<?php
/**
 * runner.php — Modul 8 Backend Test Suite
 *
 * Usage: php modules/modul_8/tests/runner.php
 *
 * Structure:
 *   Unit/       — pure class tests, no dispatcher, no DB
 *   Integration/— tests via Backend/index.php with MockPdo
 */

define('UNIT_TESTING', true);

// ── Global stubs required by Backend/index.php ────────────────────────────────
if (!function_exists('requireLogin'))    { function requireLogin(): void {} }
if (!function_exists('startSession'))    { function startSession(): void {} }
if (!function_exists('getDBConnection')) { function getDBConnection(): void {} }
if (!function_exists('getCurrentUser'))  { function getCurrentUser(): array { return []; } }

// ── TestKernel must load first (stream wrapper + MockPdo + assert helpers) ────
require_once __DIR__ . '/TestKernel.php';

$suites = [
    // ── Unit ──────────────────────────────────────────────────────────────────
    'Unit/ProfileServiceTest.php',
    'Unit/NutritionServiceTest.php',
    //'Unit/AiScanServiceTest.php',

    // ── Integration ───────────────────────────────────────────────────────────
    'Integration/IntegrationBase.php',          // shared base, no tests itself
    'Integration/ProfileControllerTest.php',
    'Integration/MealControllerTest.php',
    'Integration/DashboardControllerTest.php',
    'Integration/AiControllerTest.php',
    'Integration/ProgressControllerTest.php',
];

foreach ($suites as $file) {
    $path = __DIR__ . '/' . $file;
    if (!file_exists($path)) {
        echo "\033[33m  [SKIP] $file — not found\033[0m\n";
        continue;
    }
    require_once $path;
}

// ── Final summary ─────────────────────────────────────────────────────────────
print_summary();
exit(get_exit_code());
