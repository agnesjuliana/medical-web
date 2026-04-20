<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../core/auth.php';
startSession();
requireLogin();


$user = getCurrentUser();
require_once __DIR__ . '/config_10.php';

header('Content-Type: application/json');

// ambil username login
$username = $user['name']; // GANTI INI

// ambil data dari JS
$data = json_decode(file_get_contents("php://input"), true);
echo json_encode([
    "user" => $user,
    "data" => $data
]);
exit;

// validasi sederhana
if (!$data) {
    echo json_encode(["status" => "error", "message" => "Data tidak valid"]);
    exit;
}

$usia = $data['usia'];
$gender = $data['gender'];
$berat = $data['berat'];
$tinggi = $data['tinggi'];
$aktivitas = $data['aktivitas'];
$kalori = $data['kalori'];

// gunakan prepared statement (WAJIB BIAR AMAN)
$stmt = mysqli_prepare($conn, "INSERT INTO hasil_kalkulator 
(username, usia, gender, berat, tinggi, aktivitas, kalori) 
VALUES (?, ?, ?, ?, ?, ?, ?)");

mysqli_stmt_bind_param($stmt, "sissddi",
    $username,
    $usia,
    $gender,
    $berat,
    $tinggi,
    $aktivitas,
    $kalori
);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode([
    "status" => "error",
    "message" => mysqli_error($conn),
    "data" => $data
]);

}

mysqli_stmt_close($stmt);
?>