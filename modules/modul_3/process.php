<?php
/**
 * Modul 3 — Process Upload
 */
require_once __DIR__ . '/../../core/auth.php';
requireLogin();
startSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['thorax_image']) && $_FILES['thorax_image']['error'] === UPLOAD_ERR_OK) {
        
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmpPath = $_FILES['thorax_image']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['thorax_image']['name']);
        $destination = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmpPath, $destination)) {
            
            // --- MOCKING MACHINE LEARNING RESULT ---
            // Di tahap ini (sementara sebelum Python disambung), kita acak skor kemungkinan TBC
            // Kita gunakan RNG simpel: 30% kemungkinan TBC Tinggi, 70% Normal
            $rand = mt_rand(1, 100);
            if ($rand <= 30) {
                // Positif/Indikasi Kuat
                $score = mt_rand(75, 98);
                $status = 'TERINDIKASI TBC';
                $color = 'text-red-500';
                $bg = 'bg-red-500'; // For progress bar
                $alertBg = 'bg-red-50'; // For div background
                $border = 'border-red-200';
            } else {
                // Negatif/Indikasi Lemah
                $score = mt_rand(2, 25);
                $status = 'NORMAL / AMAN';
                $color = 'text-green-500';
                $bg = 'bg-green-500';
                $alertBg = 'bg-green-50';
                $border = 'border-green-200';
            }

            // Simpan info terkait ke session untuk ditampilkan di result.php
            $_SESSION['modul3_result'] = [
                'filename' => $fileName,
                'score' => $score,
                'status' => $status,
                'color' => $color,
                'bg' => $bg,
                'alertBg' => $alertBg,
                'border' => $border,
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // Arahkan ke halaman hasil
            header('Location: ' . BASE_URL . '/modules/modul_3/result.php');
            exit;
        } else {
            die('Gagal memindahkan file yang diunggah. Pastikan folder modules/modul_3 memiliki hak akses tulis.');
        }

    } else {
        die('Batal diunggah: Error pada file atau gambar tidak dipilih.');
    }
} else {
    header('Location: ' . BASE_URL . '/modules/modul_3/index.php');
    exit;
}
?>
