<?php
require_once __DIR__ . '/../../core/auth.php';
requireLogin();
startSession();

$user = getCurrentUser();
require_once __DIR__ . '/../../config_modul10.php';

$username = $user['username'];

// ambil data dari JS
$data = json_decode(file_get_contents("php://input"), true);

$usia = $data['usia'];
$gender = $data['gender'];
$berat = $data['berat'];
$tinggi = $data['tinggi'];
$aktivitas = $data['aktivitas'];
$kalori = $data['kalori'];

// simpan ke database
$query = "INSERT INTO hasil_kalkulator 
(username, usia, gender, berat, tinggi, aktivitas, kalori)
VALUES ('$username','$usia','$gender','$berat','$tinggi','$aktivitas','$kalori')";

mysqli_query($conn, $query);

// response ke JS
echo json_encode(["status" => "success"]);
?>
