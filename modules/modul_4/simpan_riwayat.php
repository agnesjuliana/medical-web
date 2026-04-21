<?php
// 1. Panggil sistem Auth bawaan kelas
require_once __DIR__ . '/../../core/auth.php';

// 2. Pastikan yang mengakses sudah login
requireLogin();
$user = getCurrentUser();

// Pastikan index ['id'] sesuai dengan nama kolom ID di tabel users kelasmu
$id_user = $user['id'];

// 3. Konfigurasi Database (Hanya untuk calorie-care)
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "calorie-care";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "pesan" => "Gagal konek ke database: " . $conn->connect_error]);
    exit;
}

// 4. Tangkap data dari JavaScript Kalkulator (dari fetch)
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['kalori'])) {
    // Amankan data sebelum masuk query
    $tujuan = $conn->real_escape_string($data['tujuan']);
    $aktivitas = $conn->real_escape_string($data['aktivitas']);
    $durasi = (int) $data['durasi'];
    $kalori = (int) $data['kalori'];

    // 5. Simpan ke database MySQL
    $sql = "INSERT INTO riwayat_kalkulasi (id_user, tujuan, aktivitas, durasi, kalori_terbakar) 
            VALUES ('$id_user', '$tujuan', '$aktivitas', $durasi, $kalori)";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "pesan" => "Query Error: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "pesan" => "Data tidak lengkap"]);
}

$conn->close();
?>