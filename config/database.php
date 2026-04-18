<?php
/**
 * Database Configuration
 *
 * Centralized database connection using PDO.
 * Credentials are loaded from .env in the project root.
 * This file is shared across all modules.
 */

// Load .env from project root
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        $_ENV[trim($key)] = $value;
        putenv(trim($key) . '=' . $value);
    }
}

define('BASE_URL', isset($_ENV['BASE_URL']) ? $_ENV['BASE_URL'] : '/medical-web');

// Parse DATABASE_URL if available (postgres://user:pass@host:port/dbname?params)
$_dbUrl = $_ENV['DATABASE_URL'] ?? null;
if ($_dbUrl) {
    $p = parse_url($_dbUrl);
    define('DB_DRIVER',  $p['scheme'] === 'postgres' ? 'pgsql' : $p['scheme']);
    define('DB_HOST',    $p['host']);
    define('DB_PORT',    $p['port'] ?? 5432);
    define('DB_NAME',    ltrim($p['path'] ?? '/postgres', '/'));
    define('DB_USER',    $p['user'] ?? '');
    define('DB_PASS',    $p['pass'] ?? '');
    // Parse sslmode from query string
    parse_str($p['query'] ?? '', $_dbQuery);
    define('DB_SSLMODE', $_dbQuery['sslmode'] ?? 'require');
} else {
    define('DB_DRIVER',  'pgsql');
    define('DB_HOST',    $_ENV['DB_HOST'] ?? 'localhost');
    define('DB_PORT',    $_ENV['DB_PORT'] ?? 5432);
    define('DB_NAME',    $_ENV['DB_NAME'] ?? 'postgres');
    define('DB_USER',    $_ENV['DB_USER'] ?? '');
    define('DB_PASS',    $_ENV['DB_PASS'] ?? '');
    define('DB_SSLMODE', $_ENV['DB_SSLMODE'] ?? 'require');
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
        $dsn = DB_DRIVER . ":host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=" . DB_SSLMODE;

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
