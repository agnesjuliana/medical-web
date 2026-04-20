<?php
session_start();
require_once __DIR__ . '/../../core/auth.php';
requireLogin();
require_once __DIR__ . '/config_10.php';

header('Content-Type: application/json');

// Ambil data user dari session/auth
$user = getCurrentUser(); 
$username = $user['name']; // Ini akan bernilai 'nadzifa' sesuai data kamu

// Gunakan mysqli_real_escape_string biar aman dari karakter aneh
$username_safe = mysqli_real_escape_string($conn, $username);

$sql = "SELECT usia, gender, berat, tinggi, aktivitas, kalori, waktu 
        FROM hasil_kalkulator 
        WHERE username='$username_safe' 
        ORDER BY waktu DESC";

$result = mysqli_query($conn, $sql);
$data = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Format waktu biar enak dibaca: 20 Mei 2024
        $row['waktu'] = date('d M Y, H:i', strtotime($row['waktu']));
        $data[] = $row;
    }
}

echo json_encode(["status" => "success", "data" => $data]);
exit;