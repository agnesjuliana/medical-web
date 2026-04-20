<?php
/**
 * Modul 3 Logout
 */
require_once __DIR__ . '/../../core/session.php';

startSession();
destroySession();

header('Location: ' . BASE_URL . '/modules/modul_3/login.php');
exit;
