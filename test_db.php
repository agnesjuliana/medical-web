<?php
$dsn = "pgsql:host=127.0.0.1;port=59359;dbname=postgres;sslmode=disable";
$user = "postgres"; // username can be anything according to tunnel
$pass = ""; // password can be none

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Connected successfully to tunnel!\n";
    
    // Create the user_profiles table if not exists
    $sql = "
    CREATE TABLE IF NOT EXISTS user_health_profiles (
        id SERIAL PRIMARY KEY,
        user_id INTEGER NOT NULL,
        gender VARCHAR(10) NOT NULL,
        activity_level VARCHAR(20) NOT NULL,
        height_cm NUMERIC NOT NULL,
        weight_kg NUMERIC NOT NULL,
        birth_date DATE NOT NULL,
        goal VARCHAR(20) NOT NULL,
        daily_calorie_target INTEGER NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Table user_health_profiles created/verified successfully.\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
