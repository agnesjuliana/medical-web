<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pdo = getDBConnection();

    // 1. Tangkap data teks dari form
    $nama = $_POST['nama_pasien'];
    $nik = $_POST['nik'];
    $usia = $_POST['usia'];
    $jenis_kelamin = $_POST['jenis_kelamin'];

    // 2. Tangkap file foto rontgen
    $foto = $_FILES['foto_rontgen'];
    
    // Validasi jika tidak ada foto yang terkirim (Mencegah Kode Error 4)
    if ($foto['error'] != 0) {
        echo "<script>alert('Terjadi kesalahan saat membaca file gambar. Silakan coba pilih ulang fotonya.'); window.location.href='index.php';</script>";
        exit();
    }

    $nama_file_asli = $foto['name'];
    $tmp_file = $foto['tmp_name'];
    
    // Ganti nama file agar unik (mencegah bentrok jika nama file sama)
    $nama_file_baru = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "", $nama_file_asli);
    
    // Tentukan alamat folder utama
    $folder_direktori = __DIR__ . '/uploads/';

    // CEK OTOMATIS: Buat folder uploads jika belum ada
    if (!is_dir($folder_direktori)) {
        mkdir($folder_direktori, 0777, true);
    }

    $folder_tujuan = $folder_direktori . $nama_file_baru;

    try {
        // 3. Pindahkan foto dari memori sementara ke folder uploads/
        if (move_uploaded_file($tmp_file, $folder_tujuan)) {
            
            // 4. Simpan biodata ke tabel modul_11_pasien
            $sql_pasien = "INSERT INTO modul_11_pasien (nama_pasien, nik, usia, jenis_kelamin) VALUES (?, ?, ?, ?)";
            $stmt_pasien = $pdo->prepare($sql_pasien);
            $stmt_pasien->execute([$nama, $nik, $usia, $jenis_kelamin]);
            
            // Ambil ID pasien yang baru saja dibuat
            $id_pasien_baru = $pdo->lastInsertId();

            // 5. Simpan nama foto rontgen ke tabel modul_11_sefalometri
            $sql_foto = "INSERT INTO modul_11_sefalometri (id_pasien, foto_rontgen) VALUES (?, ?)";
            $stmt_foto = $pdo->prepare($sql_foto);
            $stmt_foto->execute([$id_pasien_baru, $nama_file_baru]);

            // Ambil ID analisis untuk dilempar ke halaman result
            $id_analisis = $pdo->lastInsertId();

            // 6. Alihkan otomatis ke halaman UI Hasil Diagnosis
            header("Location: result.php?id=" . $id_analisis);
            exit();

        } else {
            echo "<script>alert('Gagal mengunggah gambar fisik ke server.'); window.location.href='index.php';</script>";
        }
    } catch (PDOException $e) {
        die("Error Database: " . $e->getMessage());
    }
} else {
    // Jika file diakses langsung tanpa lewat form
    header("Location: index.php");
    exit();
}
?>