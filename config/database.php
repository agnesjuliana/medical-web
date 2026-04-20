<?php
/**
 * Database Configuration
 *
 * Centralized database connection using PDO.
 * PostgreSQL connection via Prisma (credentials from .env).
 * This file is shared across all modules.
 */

// Load environment variables
require_once __DIR__ . '/env.php';

// Base URL — for local dev use '/', for production adjust as needed
define('BASE_URL', getenv('BASE_URL') ?: '');

// Parse PostgreSQL URL from environment (.env file)
$databaseUrl = getenv('DATABASE_URL')
    ?: getenv('POSTGRES_URL');

if ($databaseUrl) {
    // Parse PostgreSQL connection string
    // Format: postgres://user:password@host:port/database?sslmode=require
    $parsed = parse_url($databaseUrl);

    define('DB_HOST', $parsed['host'] ?? 'localhost');
    define('DB_PORT', $parsed['port'] ?? 5432);
    define('DB_NAME', ltrim($parsed['path'] ?? '/postgres', '/'));
    define('DB_USER', urldecode($parsed['user'] ?? 'postgres'));
    define('DB_PASS', urldecode($parsed['pass'] ?? ''));

    // Extract SSL mode from query string
    $queryStr = $parsed['query'] ?? '';
    parse_str($queryStr, $queryParams);
    define('DB_SSL_MODE', $queryParams['sslmode'] ?? 'require');
} else {
    // Fallback defaults — set DATABASE_URL in .env to override
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_PORT', (int)(getenv('DB_PORT') ?: 5432));
    define('DB_NAME', getenv('DB_NAME') ?: 'postgres');
    define('DB_USER', getenv('DB_USER') ?: 'postgres');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('DB_SSL_MODE', getenv('DB_SSLMODE') ?: 'require');
}

/**
 * Get PDO database connection
 * 
 * @return PDO
 * @throws PDOException
 */
function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=" . DB_SSL_MODE;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }

    return $pdo;
}
