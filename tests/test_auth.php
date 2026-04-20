<?php

$url = 'http://127.0.0.1:8000';
$randomStr = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 8);
$email = "test_user_{$randomStr}@example.com";
$password = "Password123!";
$name = "Test User {$randomStr}";

echo "Starting Auth Tests against {$url}...\n";

// Start server
$cmd = sprintf('php -S 127.0.0.1:8000 -t %s > /dev/null 2>&1 & echo $!', escapeshellarg(dirname(__DIR__)));
$pid = trim(shell_exec($cmd));
sleep(2); // Wait for server to start

$cookieFile = sys_get_temp_dir() . '/cookie.txt';
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

function doRequest($path, $postData = null) {
    global $url, $cookieFile;
    $ch = curl_init($url . $path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    if ($postData) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => $response];
}

// 1. Register
echo "1. Testing Registration...\n";
$res = doRequest('/auth/process_register.php', [
    'name' => $name,
    'email' => $email,
    'password' => $password,
    'password_confirm' => $password
]);
if (strpos($res['body'], 'Please sign in') !== false || strpos($res['body'], 'login.php') !== false || strpos($res['body'], 'Account created successfully') !== false) {
    echo "✅ Registration passed\n";
} else {
    echo "❌ Registration failed\n";
    file_put_contents('reg_fail.html', $res['body']);
}

// 2. Login
echo "2. Testing Login...\n";
$res = doRequest('/auth/process_login.php', [
    'email' => $email,
    'password' => $password
]);
if (strpos($res['body'], 'Sign out') !== false || strpos($res['body'], 'logout.php') !== false || strpos($res['body'], $name) !== false) {
    echo "✅ Login passed\n";
} else {
    echo "❌ Login failed\n";
    file_put_contents('login_fail.html', $res['body']);
}

// 3. Logout
echo "3. Testing Logout...\n";
$res = doRequest('/auth/logout.php');
if (strpos($res['body'], 'Sign in') !== false || strpos($res['body'], 'login.php') !== false) {
    echo "✅ Logout passed\n";
} else {
    echo "❌ Logout failed\n";
}

// 4. Invalid Login
echo "4. Testing Invalid Login...\n";
$res = doRequest('/auth/process_login.php', [
    'email' => $email,
    'password' => 'wrongpassword!'
]);
if (strpos($res['body'], 'Invalid email or password') !== false) {
    echo "✅ Invalid Login check passed\n";
} else {
    echo "❌ Invalid Login check failed\n";
}

// Cleanup
echo "Cleaning up server PID: $pid\n";
shell_exec("kill $pid");
echo "🎉 Tests completed.\n";

