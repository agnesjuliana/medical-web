<?php
/**
 * Database Migration Script
 * Run: php migrate.php
 */

require_once __DIR__ . '/config/database.php';

try {
    echo "=== Database Migration ===\n\n";
    echo "Connecting to: " . DB_HOST . ":" . DB_PORT . "/" . DB_NAME . "\n";

    $pdo = getDBConnection();
    echo "✓ Connected successfully!\n\n";

    // Read schema
    $schema = file_get_contents(__DIR__ . '/config/schema.sql');

    // Split statements
    $statements = array_filter(
        array_map('trim', explode(';', $schema)),
        fn($stmt) => !empty($stmt)
    );

    echo "Running " . count($statements) . " migration statement(s)...\n";

    foreach ($statements as $idx => $statement) {
        echo "  [" . ($idx + 1) . "] Executing: " . substr($statement, 0, 50) . "...\n";
        $pdo->exec($statement);
    }

    echo "\n✓ Migration completed successfully!\n";
    exit(0);

} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
