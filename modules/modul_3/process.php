<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/config/database3.php';

requireLogin(BASE_URL . '/modules/modul_3/login.php');
startSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['thorax_image'])) {
    $user = getCurrentUser();
    global $db;
    
    // 1. Pastikan folder uploads ada dan writable
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileInfo = $_FILES['thorax_image'];
    $fileName = time() . '_' . str_replace(' ', '_', basename($fileInfo['name']));
    $destination = $uploadDir . $fileName;

    // 2. Cek apakah ada error upload dari PHP
    if ($fileInfo['error'] !== UPLOAD_ERR_OK) {
        die("Upload gagal dengan error code: " . $fileInfo['error']);
    }

    // 3. Proses Pindah File
    if (move_uploaded_file($fileInfo['tmp_name'], $destination)) {
        // AI Logic Mocking
        $score = mt_rand(1, 100); 
        $status = ($score > 50) ? 'TERINDIKASI TBC' : 'NORMAL / AMAN';

        // Ambil patient_id dari form
        $patient_id = !empty($_POST['patient_id']) ? $_POST['patient_id'] : null;

        // 4. Simpan ke DB
        try {
            $stmt = $db->prepare("INSERT INTO modul3_history (user_id, patient_id, filename, confidence_score, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user['id'], $patient_id, $fileName, $score, $status]);
            $last_id = $db->lastInsertId();

            $_SESSION['modul3_result'] = [
                'history_id' => $last_id,
                'filename' => $fileName,
                'score' => $score,
                'status' => $status
            ];

            header('Location: result.php');
            exit;
        } catch (PDOException $e) {
            // Hapus file jika gagal simpan DB biar gak nyampah
            unlink($destination);
            die("Gagal simpan database: " . $e->getMessage());
        }
    } else {
        die("Gagal memindahkan file. Cek izin folder uploads!");
    }
}
header('Location: index.php');