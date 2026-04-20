<?php
require_once 'config_10.php';

if (!$conn) {
    die("❌ Config error - cek config_10.php");
}

echo "1. Koneksi OK<br>";

// Cek database exists
$db_check = mysqli_query($conn, "SELECT DATABASE() as db");
$row = mysqli_fetch_assoc($db_check);
echo "2. Database aktif: " . $row['db'] . "<br>";

// Cek table
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'hasil_kalkulator'");
if (mysqli_num_rows($table_check) > 0) {
    echo "✅ Table hasil_kalkulator OK<br>";
    
    // Show structure
    $desc = mysqli_query($conn, "DESCRIBE hasil_kalkulator");
    echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
    while($row = mysqli_fetch_assoc($desc)) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "❌ Table hasil_kalkulator TIDAK ADA!<br>";
    echo "Jalankan database_10.sql<br>";
}

// Test INSERT
echo "<h3>TEST INSERT:</h3>";
$username = "debug_" . time();
$usia = 40;
$gender = "female";
$berat = 60;
$tinggi = 156;
$aktivitas = 1.375;
$kalori = 1534;

$stmt = mysqli_prepare($conn, "INSERT INTO hasil_kalkulator (username, usia, gender, berat, tinggi, aktivitas, kalori) VALUES (?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sissddi", $username, $usia, $gender, $berat, $tinggi, $aktivitas, $kalori);

if (mysqli_stmt_execute($stmt)) {
    $id = mysqli_insert_id($conn);
    echo "✅ INSERT SUKSES! ID: $id<br>";
} else {
    echo "❌ INSERT GAGAL: " . mysqli_stmt_error($stmt) . "<br>";
}

mysqli_stmt_close($stmt);

// Show last records
$res = mysqli_query($conn, "SELECT * FROM hasil_kalkulator ORDER BY id DESC LIMIT 3");
echo "<h3>DATA TERAKHIR:</h3>";
while($row = mysqli_fetch_assoc($res)) {
    echo "ID {$row['id']}: {$row['username']} - {$row['kalori']} kkal<br>";
}
?>