<?php
/**
 * PHP Built-in Server Router
 *
 * Strips the /medical-web prefix so the server can be run
 * from inside the project directory.
 *
 * Usage: php -S localhost:8000 router.php
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Strip /medical-web prefix
$uri = preg_replace('#^/medical-web#', '', $uri);
if ($uri === '') $uri = '/';

$file = __DIR__ . $uri;

// Directory → look for index.php
if (is_dir($file)) {
    $file = rtrim($file, '/') . '/index.php';
}

if (file_exists($file)) {
    if (!str_ends_with($file, '.php')) {
        // Serve static assets manually with correct MIME type
        $mimes = [
            'js'   => 'application/javascript',
            'css'  => 'text/css',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'svg'  => 'image/svg+xml',
            'ico'  => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2'=> 'font/woff2',
            'json' => 'application/json',
        ];
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime = $mimes[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime);
        readfile($file);
        return true;
    }
    require $file;
} else {
    http_response_code(404);
    echo "404 — File not found: $uri";
}
