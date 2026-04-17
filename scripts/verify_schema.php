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
$tables = $pdo->query('SELECT tablename FROM pg_tables WHERE schemaname = \'public\' ORDER BY tablename')->fetchAll(PDO::FETCH_COLUMN);

echo "📊 Database Schema:\n\n";
foreach ($tables as $table) {
    $cols = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = '$table' ORDER BY ordinal_position")->fetchAll(PDO::FETCH_ASSOC);
    echo "  📋 $table\n";
    foreach ($cols as $col) {
        echo "     - {$col['column_name']} ({$col['data_type']})\n";
    }
    echo "\n";
}
