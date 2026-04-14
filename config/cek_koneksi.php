<?php
require_once __DIR__ . '/database.php';  // karena satu folder

// Cek koneksi backbone (dosen)
try {
    $pdo = getDBConnection();
    echo "✅ backbone_medweb: TERHUBUNG<br>";
    
    $users = $pdo->query("SELECT id, name, email FROM users")->fetchAll();
    echo "👥 Jumlah user: " . count($users) . "<br>";
    foreach ($users as $u) {
        echo "— {$u['name']} ({$u['email']})<br>";
    }
} catch (Exception $e) {
    echo "❌ backbone_medweb: GAGAL — " . $e->getMessage() . "<br>";
}

echo "<br>";

// Cek koneksi med_solve_lab (kelompokmu)
try {
    $pdo_app = getAppDBConnection();
    echo "✅ med_solve_lab: TERHUBUNG<br>";
    
    $projects = $pdo_app->query("SELECT id, problem FROM projects")->fetchAll();
    echo "📁 Jumlah project: " . count($projects) . "<br>";
    foreach ($projects as $p) {
        echo "— {$p['problem']}<br>";
    }
} catch (Exception $e) {
    echo "❌ med_solve_lab: GAGAL — " . $e->getMessage() . "<br>";
}