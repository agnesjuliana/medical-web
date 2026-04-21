<?php
/**
 * Modul 3 Logout
 */
// 1. Sertakan auth.php supaya BASE_URL dikenali
require_once __DIR__ . '/../../core/auth.php'; 
require_once __DIR__ . '/../../core/session.php';

startSession();
destroySession();

// 2. Redirect balik ke login page Modul 3
// Kita pakai BASE_URL yang sekarang sudah aman dipanggil
header('Location: ' . BASE_URL . '/modules/modul_3/login.php');
exit;