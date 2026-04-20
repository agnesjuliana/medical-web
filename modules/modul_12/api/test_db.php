<?php
require_once __DIR__ . '/config.php';

try {
    $db = getDB();
    $email = requireAuth(); // This will test if your SSO session is active
    
    $stmt = $db->prepare("INSERT INTO bmi_log (user_email, bmi, category, weight, height, age, gender) VALUES (?, 22.5, 'Normal', 70, 175, 25, 'male')");
    $stmt->execute([$email]);
    
    echo "Success! Logged in as: " . $email . "<br>";
    echo "Test record inserted into healthedu.bmi_log.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}