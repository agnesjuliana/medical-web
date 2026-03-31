<?php
/**
 * Session Manager
 * 
 * Centralized session handling with secure defaults.
 * Include this file at the top of any page that needs session access.
 */

/**
 * Start session with secure configuration
 */
function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.use_only_cookies', '1');

        session_start();
    }
}

/**
 * Set user data in session after successful login
 * 
 * @param array $user User data from database
 */
function setUser(array $user): void
{
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['logged_in']  = true;
    $_SESSION['login_time'] = time();

    // Regenerate session ID to prevent fixation
    session_regenerate_id(true);
}

/**
 * Get current session user data
 * 
 * @return array|null User data or null if not logged in
 */
function getSessionUser(): ?array
{
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        return null;
    }

    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
    ];
}

/**
 * Destroy session and clear all data
 */
function destroySession(): void
{
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}

/**
 * Set a flash message (persists for one page load)
 * 
 * @param string $key   Flash key (e.g. 'success', 'error')
 * @param string $value Flash message
 */
function setFlash(string $key, string $value): void
{
    $_SESSION['flash'][$key] = $value;
}

/**
 * Get and clear a flash message
 * 
 * @param string $key Flash key
 * @return string|null
 */
function getFlash(string $key): ?string
{
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}
