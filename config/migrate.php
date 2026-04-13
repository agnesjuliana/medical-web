<?php
/**
 * Database Migration Script
 * Run this script to migrate the schema to PostgreSQL
 */

require_once __DIR__ . '/database.php';

try {
    $pdo = getDBConnection();

    // Read the schema file
    $schema = file_get_contents(__DIR__ . '/schema.sql');

    // Split by semicolon to handle multiple statements
    $statements = array_filter(
        array_map('trim', explode(';', $schema)),
        fn($stmt) => !empty($stmt)
    );

    echo "Running migration...\n";

    foreach ($statements as $statement) {
        echo "Executing: " . substr($statement, 0, 50) . "...\n";
        $pdo->exec($statement);
    }

    echo "✓ Migration completed successfully!\n";

} catch (PDOException $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
