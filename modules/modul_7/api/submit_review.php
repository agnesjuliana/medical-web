<?php
/**
 * API Endpoint: Submit Doctor's Review
 */

require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

startSession();
$user = getCurrentUser();

if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized role');
}

$screening_id = $_POST['screening_id'] ?? null;
$doctor_notes = trim($_POST['doctor_notes'] ?? '');

if (!$screening_id || empty($doctor_notes)) {
    // Should probably redirect with flash error, but a simple die for now
    die('Invalid input. Make sure to fill the notes.');
}

try {
    $db = getDBConnection();
    
    $stmt = $db->prepare("
        UPDATE modul7_screenings 
        SET status = 'reviewed_by_doctor', 
            doctor_id = :did, 
            doctor_notes = :notes 
        WHERE id = :id AND status = 'pending_doctor_review'
    ");

    $stmt->execute([
        'did' => $user['id'],
        'notes' => $doctor_notes,
        'id' => $screening_id
    ]);

    // Redirect successfully
    header("Location: " . BASE_URL . "/modules/modul_7/index.php");
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    exit('DB Error: ' . $e->getMessage());
}
