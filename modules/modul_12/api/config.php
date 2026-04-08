<?php
// ============================================================
//  HEALTHEDU — api/config.php
//  ⚙️  Ganti DB_HOST, DB_USER, DB_PASS sesuai server kamu
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // ← ganti sesuai user MySQL kamu
define('DB_PASS', '');              // ← ganti sesuai password MySQL kamu
define('DB_NAME', 'healthedu');

// CORS — izinkan request dari frontend (sesuaikan domain jika perlu)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ─────────────────────────────────────────────
// Koneksi PDO
// ─────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            jsonError('Koneksi database gagal: ' . $e->getMessage(), 500);
        }
    }
    return $pdo;
}

// ─────────────────────────────────────────────
// Helper response
// ─────────────────────────────────────────────
function jsonSuccess(array $data = [], string $msg = 'OK'): void {
    echo json_encode(['success' => true, 'message' => $msg, 'data' => $data]);
    exit();
}

function jsonError(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit();
}

// ─────────────────────────────────────────────
// Auth helper — ambil user_id dari token
// ─────────────────────────────────────────────
function requireAuth(): int {
    $token = '';

    // Cek header Authorization: Bearer <token>
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (str_starts_with($auth, 'Bearer ')) {
        $token = trim(substr($auth, 7));
    }

    // Fallback: cek body JSON
    if (!$token) {
        $body = json_decode(file_get_contents('php://input'), true);
        $token = $body['token'] ?? '';
    }

    if (!$token) {
        jsonError('Tidak terautentikasi. Silakan login terlebih dahulu.', 401);
    }

    $db   = getDB();
    $stmt = $db->prepare('SELECT user_id, expires_at FROM sessions WHERE token = ?');
    $stmt->execute([$token]);
    $row  = $stmt->fetch();

    if (!$row) {
        jsonError('Token tidak valid. Silakan login ulang.', 401);
    }
    if (new DateTime() > new DateTime($row['expires_at'])) {
        jsonError('Sesi telah kedaluwarsa. Silakan login ulang.', 401);
    }

    return (int) $row['user_id'];
}
