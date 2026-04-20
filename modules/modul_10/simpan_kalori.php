<?php
require_once __DIR__ . '/../../core/auth.php';
requireLogin();
startSession();

require_once __DIR__ . '/config_10.php';

$user = getCurrentUser();

$username = $user['name'];

$data = json_decode(file_get_contents("php://input"), true);

// DEBUG masuk gak datanya
if (!$data) {
    echo json_encode(["status"=>"error","msg"=>"DATA KOSONG"]);
    exit;
}

$usia = $data['usia'];
$gender = $data['gender'];
$berat = $data['berat'];
$tinggi = $data['tinggi'];
$aktivitas = $data['aktivitas'];
$kalori = $data['kalori'] ?? 0;

// QUERY
$query = "INSERT INTO hasil_kalkulator 
(username, usia, gender, berat, tinggi, aktivitas, kalori)
VALUES ('$username','$usia','$gender','$berat','$tinggi','$aktivitas','$kalori')";

if (mysqli_query($conn, $query)) {
    echo json_encode(["status"=>"success"]);
} else {
    echo json_encode([
        "status"=>"error",
        "msg"=>mysqli_error($conn)
    ]);
}