<?php
// modules/modul_3/config/database3.php

$host = 'localhost';
$db_name = 'medical_web3'; 
$username = 'root';        
$password = '';            

try {
    $db = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}