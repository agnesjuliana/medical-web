<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "ID Analisis tidak diberikan"]);
    exit();
}

$id_analisis = (int)$_GET['id'];
$conf = isset($_GET['conf']) ? (int)$_GET['conf'] : 30; // Bawaan slider
$overlap = isset($_GET['overlap']) ? (int)$_GET['overlap'] : 50;

$pdo = getDBConnection();

// 1. Ambil nama file dari DB
$stmt = $pdo->prepare("SELECT foto_rontgen FROM modul_11_sefalometri WHERE id_analisis = ?");
$stmt->execute([$id_analisis]);
$data = $stmt->fetch();

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Data tidak ditemukan di database"]);
    exit();
}

$file_name = $data['foto_rontgen'];
$file_path = __DIR__ . '/uploads/' . $file_name;

if (!file_exists($file_path)) {
    echo json_encode(["status" => "error", "message" => "File gambar fisik tidak ditemukan di direktori uploads"]);
    exit();
}

// 2. Hubungi Superkomputer Roboflow (Amerika Serikat) via cURL Base64
$image_data = file_get_contents($file_path);
$base64_image = base64_encode($image_data);

// Suntikkan Variabel Slider Langsung ke Otak Roboflow!
$api_url = "https://detect.roboflow.com/reappciona-train-2ihu8/3?api_key=85m4iA2oYXKKT63LkMM6&confidence=" . $conf . "&overlap=" . $overlap;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $base64_image);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/x-www-form-urlencoded"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Timeout lebih singkat karena Roboflow sangat cepat

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code != 200 || $response === false) {
    echo json_encode(["status" => "error", "message" => "Gagal menghubungi Server Roboflow Cloud!", "detail" => $response ? $response : $error]);
    exit();
}

// 3. Tangkap dan Rapikan Format Balasan Roboflow agar cocok dengan Web
$responseData = json_decode($response, true);
if (isset($responseData['predictions'])) {
    
    $results = [];
    foreach ($responseData['predictions'] as $idx => $pred) {
        // Roboflow mengembalikan x, y sebagai titik pusat balok bounding box / keypoint
        $results[] = [
            "id" => isset($pred['class_id']) ? $pred['class_id'] : $idx,
            "label" => isset($pred['class']) ? $pred['class'] : 'Titik ' . $idx,
            "x" => (float)$pred['x'],
            "y" => (float)$pred['y']
        ];
    }

    // Konversi array landmarks rapi menjadi string JSON
    $jsonLandmarks = json_encode($results);
    
    // Update ke database XAMPP
    $upd = $pdo->prepare("UPDATE modul_11_sefalometri SET data_landmark = ? WHERE id_analisis = ?");
    $upd->execute([$jsonLandmarks, $id_analisis]);
    
    echo json_encode(["status" => "success", "source" => "roboflow_ai", "landmarks" => $results]);
} else {
    echo json_encode(["status" => "error", "message" => "Format balasan Roboflow tidak dapat dikenali.", "raw" => $response]);
}
?>
