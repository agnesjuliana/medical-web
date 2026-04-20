<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "database_10";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    // Jangan kirim JSON di sini karena ini file config umum
    die("Koneksi gagal: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");
?>