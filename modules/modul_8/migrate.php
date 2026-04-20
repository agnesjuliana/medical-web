<?php
/**
 * Migration Runner — Modul 8 Calorie Tracker
 *
 * Usage: php migrate.php
 *
 * Reads schema.sql dan menjalankan CREATE TABLE statements
 * via database.php configuration.
 */

require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = getDBConnection();

    // Baca schema.sql
    $schemaPath = __DIR__ . '/schema.sql';
    if (!file_exists($schemaPath)) {
        die("❌ Error: schema.sql not found at $schemaPath\n");
    }

    $sql = file_get_contents($schemaPath);

    // Remove SQL comments (both -- and /* */)
    $sql = preg_replace('/--[^\n]*/', '', $sql);  // Remove -- comments
    $sql = preg_replace('#/\*.*?\*/#s', '', $sql); // Remove /* */ comments

    // Split by semicolon
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($stmt) => !empty($stmt)
    );

    $count = 0;
    foreach ($statements as $statement) {
        try {
            $pdo->exec($statement);
            $count++;
            echo "✅ Executed statement $count\n";
        } catch (PDOException $e) {
            // Check if it's just "already exists" warning
            if (str_contains($e->getMessage(), 'already exists')) {
                echo "ℹ️  Statement $count (already exists)\n";
            } else {
                echo "❌ Statement $count error: " . $e->getMessage() . "\n";
                exit(1);
            }
        }
    }

    echo "\n✅ Migration completed: $count statements executed\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
