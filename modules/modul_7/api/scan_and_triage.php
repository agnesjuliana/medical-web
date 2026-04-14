<?php
/**
 * API Endpoint: Scan and Triage (Dummy ML Version)
 * 
 * Receives an image, simulates ML detection, and inserts 
 * into the database (with auto-triage logic).
 */

require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

startSession();
$user = getCurrentUser();

// Default values for ML Mock
$severities = ['Mild', 'Moderate', 'Severe'];
// Weighted randomness: 40% Mild, 40% Moderate, 20% Severe
$rng = rand(1, 100);
if ($rng <= 40) {
    $severityIndex = 0;
} elseif ($rng <= 80) {
    $severityIndex = 1;
} else {
    $severityIndex = 2;
}

$severity = $severities[$severityIndex];

// Randomize acne counts based on severity
if ($severity === 'Mild') {
    $counts = ['papule' => rand(0, 3), 'pustule' => rand(0, 1), 'blackhead' => rand(0, 5)];
} elseif ($severity === 'Moderate') {
    $counts = ['papule' => rand(3, 8), 'pustule' => rand(1, 4), 'blackhead' => rand(2, 8)];
} else {
    $counts = ['papule' => rand(8, 20), 'pustule' => rand(5, 15), 'blackhead' => rand(5, 15)];
}

// 1. In a real scenario, we'd handle the file upload here. 
// For this mock, we just use a placeholder image path.
$imagePath = 'assets/uploaded_mock_' . time() . '.jpg'; 

// 2. Triage Logic
$status = ($severity === 'Severe') ? 'pending_doctor_review' : 'completed_by_ml';

// 3. Database Insertion
try {
    $db = getDBConnection();
    
    // Check if table exists
    $checkTable = $db->query("SHOW TABLES LIKE 'modul7_screenings'");
    if ($checkTable->rowCount() == 0) {
        // Automatically throw an error to run init.php if it doesn't exist
        throw new Exception("Database table not initialized. Please run init.php");
    }

    $stmt = $db->prepare("
        INSERT INTO modul7_screenings 
        (patient_id, image_path, ml_severity_level, ml_papule_count, ml_pustule_count, ml_blackhead_count, status)
        VALUES (:pid, :img, :sev, :pap, :pus, :bla, :status)
    ");

    $stmt->execute([
        'pid' => $user['id'],
        'img' => $imagePath,
        'sev' => $severity,
        'pap' => $counts['papule'],
        'pus' => $counts['pustule'],
        'bla' => $counts['blackhead'],
        'status' => $status
    ]);

    // Construct response
    $response = [
        'success' => true,
        'result' => [
            'severity' => $severity,
            'counts' => $counts
        ],
        'triage_status' => $status
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
}
