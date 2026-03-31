<?php
/**
 * Auth Helper
 * 
 * Authentication utilities for checking login state
 * and protecting pages. Depends on core/session.php.
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Check if current user is logged in
 * 
 * @return bool
 */
function isLoggedIn(): bool
{
    startSession();
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Require login — redirect to login page if not authenticated
 * 
 * @param string $redirect URL to redirect to (relative to project root)
 */
function requireLogin(string $redirect = ''): void
{
    if ($redirect === '') $redirect = BASE_URL . '/auth/login.php';
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

/**
 * Require guest — redirect to dashboard if already logged in
 * 
 * @param string $redirect URL to redirect to
 */
function requireGuest(string $redirect = ''): void
{
    if ($redirect === '') $redirect = BASE_URL . '/index.php';
    if (isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

/**
 * Get current authenticated user data from session
 * 
 * @return array|null
 */
function getCurrentUser(): ?array
{
    startSession();
    return getSessionUser();
}

/**
 * Get current user's display initials (for avatar)
 * 
 * @return string
 */
function getUserInitials(): string
{
    $user = getCurrentUser();
    if (!$user) return '?';

    $parts = explode(' ', trim($user['name']));
    $initials = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) {
        $initials .= strtoupper(substr(end($parts), 0, 1));
    }
    return $initials;
}
