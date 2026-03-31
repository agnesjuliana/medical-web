<?php
/**
 * Delete BMI Record — Modul 0
 * 
 * Deletes a BMI record by ID (only if owned by current user).
 * Expects GET parameter: ?id=<record_id>
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../config/db_modul0.php';

requireLogin();
startSession();

$user = getCurrentUser();
if (!$user) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$recordId = (int) ($_GET['id'] ?? 0);

if ($recordId < 1) {
    setFlash('error', 'ID record tidak valid.');
    header('Location: ' . BASE_URL . '/modules/modul_0/index.php');
    exit;
}

try {
    $db = getModul0Connection();

    // Only delete if the record belongs to the current user
    $stmt = $db->prepare("DELETE FROM bmi_records WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        'id'      => $recordId,
        'user_id' => $user['id'],
    ]);

    if ($stmt->rowCount() > 0) {
        setFlash('success', 'Record BMI berhasil dihapus.');
    } else {
        setFlash('error', 'Record tidak ditemukan atau bukan milik Anda.');
    }
} catch (PDOException $e) {
    error_log("BMI delete error: " . $e->getMessage());
    setFlash('error', 'Gagal menghapus record. Silakan coba lagi.');
}

header('Location: ' . BASE_URL . '/modules/modul_0/index.php');
exit;
