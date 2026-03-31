<?php
/**
 * Logout
 * 
 * Destroys session and redirects to login.
 */

require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../config/database.php';

startSession();
destroySession();

header('Location: ' . BASE_URL . '/auth/login.php');
exit;
