<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "ID Analisis tidak diberikan"]);
    exit();
}

$id_analisis = (int)$_GET['id'];
$pdo = getDBConnection();

// 1. Ambil nama file dari DB
$stmt = $pdo->prepare("SELECT foto_rontgen, data_landmark FROM modul_11_sefalometri WHERE id_analisis = ?");
$stmt->execute([$id_analisis]);
$data = $stmt->fetch();

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Data tidak ditemukan di database"]);
    exit();
}

// Jika sudah pernah diproses AI sebelumnya, kembalikan cahce DB agar tidak memberatkan server 
if ($data['data_landmark'] != null && $data['data_landmark'] != "") {
    echo json_encode(["status" => "success", "source" => "database", "landmarks" => json_decode($data['data_landmark'], true)]);
    exit();
}

$file_name = $data['foto_rontgen'];
$file_path = __DIR__ . '/uploads/' . $file_name;

if (!file_exists($file_path)) {
    echo json_encode(["status" => "error", "message" => "File gambar fisik tidak ditemukan di direktori uploads"]);
    exit();
}

// 2. Hubungi Otak Python via cURL Multipart
$cfile = new CURLFile($file_path, mime_content_type($file_path), $file_name);
$postData = array('image' => $cfile);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:5000/predict");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code != 200 || $response === false) {
    echo json_encode(["status" => "error", "message" => "Gagal menghubungi Engine AI Python. Pastikan app.py sedang menyala di terminal!", "detail" => $error]);
    exit();
}

// 3. Tangkap dan Simpan permanen balasan Python ke Database XAMPP
$responseData = json_decode($response, true);
if (isset($responseData['landmarks'])) {
    
    // Konversi array landmarks menjadi string JSON
    $jsonLandmarks = json_encode($responseData['landmarks']);
    
    // Update ke database teman Anda
    $upd = $pdo->prepare("UPDATE modul_11_sefalometri SET data_landmark = ? WHERE id_analisis = ?");
    $upd->execute([$jsonLandmarks, $id_analisis]);
    
    echo json_encode(["status" => "success", "source" => "ai_python", "landmarks" => $responseData['landmarks']]);
} else {
    echo json_encode(["status" => "error", "message" => "Format balasan Python (JSON) tidak valid saat diparsing PHP"]);
}
?>
