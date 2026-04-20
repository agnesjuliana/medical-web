<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/config/database3.php'; // Path diperbaiki!

requireLogin(BASE_URL . '/modules/modul_3/login.php');
startSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['thorax_image'])) {
    $user = getCurrentUser();
    global $db;
    
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileName = time() . '_' . basename($_FILES['thorax_image']['name']);
    $destination = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['thorax_image']['tmp_name'], $destination)) {
        // AI Logic Mocking
        $score = mt_rand(1, 100); 
        $status = ($score > 50) ? 'TERINDIKASI TBC' : 'NORMAL / AMAN';

        // Cek patient_id
        $patient_id = !empty($_POST['patient_id']) ? $_POST['patient_id'] : null;

        // Simpan ke DB
        if ($db) {
            $stmt = $db->prepare("INSERT INTO modul3_history (user_id, patient_id, filename, confidence_score, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user['id'], $patient_id, $fileName, $score, $status]);
            
            // Simpan ID yang baru diinsert ke session untuk referensi cetak (print)
            $last_id = $db->lastInsertId();
        }

        $_SESSION['modul3_result'] = [
            'history_id' => $last_id ?? null,
            'filename' => $fileName,
            'score' => $score,
            'status' => $status
        ];

        header('Location: result.php');
        exit;
    }
}
header('Location: index.php');