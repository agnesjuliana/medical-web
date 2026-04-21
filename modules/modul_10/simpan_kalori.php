<?php
session_start();
// 1. Pastikan path ke auth.php benar agar bisa tahu siapa yang login
require_once __DIR__ . '/../../core/auth.php'; 
requireLogin(); 
require_once __DIR__ . '/config_10.php';

header('Content-Type: application/json');

// 2. Ambil username dari session nadzifa
$user = getCurrentUser();
$username = $user['name']; 

// 3. Ambil data JSON dari JavaScript
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// 4. Pastikan nama variabel di sini sama dengan yang dikirim JS (Bindo)
$usia = isset($data['usia']) ? intval($data['usia']) : 0;
$berat = isset($data['berat']) ? floatval($data['berat']) : 0;
$tinggi = isset($data['tinggi']) ? floatval($data['tinggi']) : 0;
$gender = $data['gender'] ?? 'male';
$aktivitas = isset($data['aktivitas']) ? floatval($data['aktivitas']) : 1.2;

// 5. Rumus Hitung (Agar tidak 0)
if ($gender === 'male') {
    $bmr = 88.362 + (13.397 * $berat) + (4.799 * $tinggi) - (5.677 * $usia);
} else {
    $bmr = 447.593 + (9.247 * $berat) + (3.098 * $tinggi) - (4.330 * $usia);
}
$kalori = round($bmr * $aktivitas);

// 6. Simpan ke Database (Pakai $username yang dari session, bukan guest)
$sql = "INSERT INTO hasil_kalkulator (username, usia, gender, berat, tinggi, aktivitas, kalori) 
        VALUES ('$username', '$usia', '$gender', '$berat', '$tinggi', '$aktivitas', '$kalori')";

if (mysqli_query($conn, $sql)) {
    echo json_encode(["status" => "success", "kalori" => $kalori]);
} else {
    echo json_encode(["status" => "error", "msg" => mysqli_error($conn)]);
}