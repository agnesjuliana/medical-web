<?php
require_once __DIR__ . '/../../core/auth.php';
startSession();
requireLogin();


$user = getCurrentUser();
echo json_encode($user);
exit;
require_once __DIR__ . '/config_10.php';

header('Content-Type: application/json');


$username = $user['name']; // GANTI INI
$sql = "SELECT usia, gender, berat, tinggi, aktivitas, kalori, waktu 
        FROM hasil_kalkulator 
        WHERE username='$username' 
        ORDER BY waktu DESC";

$result = mysqli_query($conn, $sql);

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>
