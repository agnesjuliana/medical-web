<?php
/**
 * API Endpoint: Scan and Triage (Dummy ML Version)
 * 
 * Receives an image, simulates ML detection, and inserts 
 * into the database (with auto-triage logic).
 */

require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/../config/database.php';

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

// 2. Triage Logic (Patient Only Route)
$status = 'completed_by_ml'; // No longer using pending_doctor_review

// 3. Generate Recommendations
$recommendations = [];
if ($severity === 'Mild') {
    $recommendations = [
        'ingredients' => ['Salicylic Acid (BHA)', 'Azelaic Acid'],
        'advice' => 'Kondisi jerawat ringan. Sangat disarankan untuk menggunakan eksfolian kimia lembut seperti BHA untuk membersihkan pori-pori.',
        'prescription' => 'Pertimbangkan serum Salicylic Acid 2% atau krim Azelaic Acid 10% (bebas)'
    ];
} elseif ($severity === 'Moderate') {
    $recommendations = [
        'ingredients' => ['Benzoyl Peroxide', 'Topical Retinoids', 'Niacinamide'],
        'advice' => 'Kondisi jerawat sedang. Kombinasi anti-bakteri dan percepatan pergantian sel kulit diperlukan.',
        'prescription' => 'Gunakan Benzoyl Peroxide 2.5% - 5% (spot treatment) dan Retinoid topikal di malam hari.'
    ];
} else {
    $recommendations = [
        'ingredients' => ['Isotretinoin Oral', 'Antibiotik Oral', 'Spironolactone'],
        'advice' => 'Kondisi jerawat tingkat parah. Intervensi medis profesional diperlukan untuk mencegah jaringan parut permanen.',
        'prescription' => 'Segera konsultasikan dengan dokter spesialis kulit (Dermatovenerologi) untuk resep obat oral sistemik.'
    ];
}

// 4. Database Insertion
try {
    $db = getModul7DBConnection();
    
    $checkTable = $db->query("SHOW TABLES LIKE 'screening_results'");
    if ($checkTable->rowCount() == 0) {
        require_once __DIR__ . '/init.php';
    }

    $stmt = $db->prepare("
        INSERT INTO screening_results 
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
            'counts' => $counts,
            'recommendations' => $recommendations
        ],
        'triage_status' => $status
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
}
