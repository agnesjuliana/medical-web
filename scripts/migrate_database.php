<?php
/**
 * Database Migration Script
 * Converts schema.sql to PostgreSQL and runs migrations
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

    // PostgreSQL migration schema
    $migrations = [
        // Users table
        "CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",

        // Refresh tokens table
        "CREATE TABLE IF NOT EXISTS refresh_tokens (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            token VARCHAR(255) UNIQUE NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",

        // Password resets table
        "CREATE TABLE IF NOT EXISTS password_resets (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            token VARCHAR(255) UNIQUE NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",

        // Email verifications table
        "CREATE TABLE IF NOT EXISTS email_verifications (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            token VARCHAR(255) UNIQUE NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            verified_at TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",

        // Login attempts table
        "CREATE TABLE IF NOT EXISTS login_attempts (
            id SERIAL PRIMARY KEY,
            email VARCHAR(100) NOT NULL,
            ip_address VARCHAR(45),
            attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            success BOOLEAN DEFAULT FALSE
        )",
    ];

    echo "🔄 Running migrations...\n\n";

    foreach ($migrations as $index => $sql) {
        try {
            $pdo->exec($sql);
            $tableMatch = preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/', $sql, $matches);
            $tableName = $tableMatch ? $matches[1] : "migration " . ($index + 1);
            echo "✓ Created table: $tableName\n";
        } catch (PDOException $e) {
            echo "⚠️  Error on migration " . ($index + 1) . ": " . $e->getMessage() . "\n";
        }
    }

    echo "\n✅ Migration complete!\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
