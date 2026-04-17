<?php
/**
 * Clear Database Script
 * Drops all tables from the PostgreSQL database
 */

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

// Get database connection
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDBConnection();

    // Get all table names
    $stmt = $pdo->query("
        SELECT tablename FROM pg_tables
        WHERE schemaname = 'public'
    ");

    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        echo "No tables found in the database.\n";
        exit(0);
    }

    echo "Found " . count($tables) . " table(s) to drop:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    echo "\n";

    // Drop all tables with CASCADE to handle foreign keys
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE \"$table\" CASCADE");
        echo "✓ Dropped table: $table\n";
    }

    echo "\n✅ All tables dropped successfully!\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
