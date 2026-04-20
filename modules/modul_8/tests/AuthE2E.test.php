<?php
/**
 * E2E Auth Test for Register and Login
 * Validates the core authentication flow before entering Modul 8.
 */

$baseUrl = 'http://localhost:8000';

echo "===========================================\n";
echo "1. Testing Registration\n";
echo "===========================================\n";

$email = 'e2e_test_' . time() . '@example.com';
$password = 'password123';
$name = 'E2E Test User';

$registerData = http_build_query([
    'name' => $name,
    'email' => $email,
    'password' => $password,
    'confirm_password' => $password,
]);

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => $registerData,
        'ignore_errors' => true,
    ],
];

$context  = stream_context_create($options);
$response = file_get_contents($baseUrl . '/auth/process_register.php', false, $context);

$headers = $http_response_header ?? [];
$isRedirect = false;
foreach ($headers as $header) {
    if (strpos($header, 'Location: login.php') !== false || strpos($header, 'Location: /auth/login.php') !== false) {
        $isRedirect = true;
    }
}

if ($isRedirect) {
    echo "✅ Registration successful. Redirected to login.\n";
} else {
    echo "❌ Registration failed.\n";
    echo "Response: $response\n";
    print_r($headers);
    exit(1);
}

echo "\n===========================================\n";
echo "2. Testing Login\n";
echo "===========================================\n";

$loginData = http_build_query([
    'email' => $email,
    'password' => $password,
]);

$options['http']['content'] = $loginData;
$context  = stream_context_create($options);
$response = file_get_contents($baseUrl . '/auth/process_login.php', false, $context);

$headers = $http_response_header ?? [];
$isRedirect = false;
$sessionCookie = null;

foreach ($headers as $header) {
    if (strpos($header, 'Location:') !== false && (strpos($header, 'index.php') !== false || strpos($header, 'dashboard') !== false || strpos($header, '../index.php') !== false)) {
        $isRedirect = true;
    }
    if (stripos($header, 'Set-Cookie: PHPSESSID=') !== false) {
        $sessionCookie = true;
    }
}

if ($isRedirect) {
    echo "✅ Login successful. Redirected to dashboard.\n";
    if ($sessionCookie) {
        echo "✅ Session cookie (PHPSESSID) received successfully.\n";
    } else {
        echo "❌ No session cookie received.\n";
        exit(1);
    }
} else {
    echo "❌ Login failed.\n";
    echo "Response: $response\n";
    print_r($headers);
    exit(1);
}

echo "\n✅ PHP Auth E2E Test Passed!\n";
