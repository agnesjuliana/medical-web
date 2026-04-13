<?php
/**
 * Database Configuration
 *
 * Centralized database connection using PDO.
 * Credentials are read from environment variables first, then from a .env
 * file in the project root (if present), and finally fall back to the
 * defaults shown below.  Copy .env.example to .env and fill in your own
 * values — never commit .env to version control.
 */

// ---------------------------------------------------------------------------
// Load .env file (simple key=value parser, no external library required)
// Only the keys listed in $_envAllowed are imported to prevent unexpected
// environment variable injection from a malicious or corrupt .env file.
// ---------------------------------------------------------------------------
$_envAllowed = ['APP_BASE_URL', 'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_CHARSET'];
$_envFile    = dirname(__DIR__) . '/.env';

if (file_exists($_envFile)) {
    $lines = file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments and blank-ish lines
        if (str_starts_with(ltrim($line), '#')) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }
        $key   = trim($parts[0]);
        $value = trim($parts[1]);

        // Only process whitelisted, non-empty keys
        if ($key === '' || !in_array($key, $_envAllowed, true)) {
            continue;
        }

        // Strip optional surrounding quotes
        if (
            strlen($value) >= 2 &&
            (($value[0] === '"' && $value[-1] === '"') ||
             ($value[0] === "'" && $value[-1] === "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        // Don't override a variable that was already set in the environment
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}
unset($_envAllowed, $_envFile, $lines, $line, $parts, $key, $value);

// ---------------------------------------------------------------------------
// Resolve configuration — environment variables override built-in defaults
// ---------------------------------------------------------------------------

// Base URL — update this if the project moves to a different subdirectory
define('BASE_URL', getenv('APP_BASE_URL') !== false ? getenv('APP_BASE_URL') : '/medical-web');

define('DB_HOST',    getenv('DB_HOST')    !== false ? getenv('DB_HOST')    : 'localhost');
define('DB_PORT',    getenv('DB_PORT')    !== false ? getenv('DB_PORT')    : '3306');
define('DB_NAME',    getenv('DB_NAME')    !== false ? getenv('DB_NAME')    : 'backbone_medweb');
define('DB_USER',    getenv('DB_USER')    !== false ? getenv('DB_USER')    : 'root');
define('DB_PASS',    getenv('DB_PASS')    !== false ? getenv('DB_PASS')    : '');
define('DB_CHARSET', getenv('DB_CHARSET') !== false ? getenv('DB_CHARSET') : 'utf8mb4');

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
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

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
