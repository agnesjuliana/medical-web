<?php
// config.php
// Konfigurasi Database untuk Modul GrowLife - Kelompok 2
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "db_growlife_kel2";

$conn = new mysqli($host, $user, $pass, $db_name);

if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}
?>
