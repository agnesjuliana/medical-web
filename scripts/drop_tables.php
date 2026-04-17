<?php
// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $envLines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $value = trim($value, '"\'');
            putenv("$key=$value");
        }
    }
}

require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$tables = ['refresh_tokens', 'password_resets', 'email_verifications', 'login_attempts'];

foreach ($tables as $table) {
    $pdo->exec("DROP TABLE IF EXISTS \"$table\" CASCADE");
    echo "✓ Dropped table: $table\n";
}

echo "\n✅ Done!\n";
