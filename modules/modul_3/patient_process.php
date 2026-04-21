<?php
/**
 * Modul 3 CRUD Process
 * Menangani Create, Update, Delete untuk Pasien
 */
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/config/database3.php';

requireLogin(BASE_URL . '/modules/modul_3/login.php');
startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: patients.php');
    exit;
}

$user = getCurrentUser();
global $db;

$action = $_POST['action'] ?? '';

try {
    if ($action === 'create') {
        $stmt = $db->prepare("INSERT INTO modul3_patients (user_id, name, age, gender, symptoms) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $user['id'],
            $_POST['name'],
            $_POST['age'],
            $_POST['gender'],
            $_POST['symptoms']
        ]);
        setFlash('success', 'Data pasien berhasil ditambahkan.');
    } 
    elseif ($action === 'update') {
        $id = $_POST['id'];
        
        // Ensure user owns this patient
        $stmt = $db->prepare("UPDATE modul3_patients SET name = ?, age = ?, gender = ?, symptoms = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([
            $_POST['name'],
            $_POST['age'],
            $_POST['gender'],
            $_POST['symptoms'],
            $id,
            $user['id']
        ]);
        setFlash('success', 'Data pasien berhasil diperbarui.');
    } 
    elseif ($action === 'delete') {
        $id = $_POST['id'];
        $stmt = $db->prepare("DELETE FROM modul3_patients WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user['id']]);
        setFlash('success', 'Data pasien berhasil dihapus.');
    }
} catch (PDOException $e) {
    setFlash('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
}

header('Location: patients.php');
exit;
