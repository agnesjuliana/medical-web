<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

// SETUP FOOD DATABASE DUMMY JIKA BELUM ADA
$create = $conn->query("CREATE TABLE IF NOT EXISTS food_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    folic FLOAT NOT NULL,
    iron FLOAT NOT NULL
)");
if (!$create) {
    echo json_encode(['status' => 'error', 'message' => 'Create Table Error: ' . $conn->error]);
    exit;
}

$res = $conn->query("SELECT COUNT(*) as count FROM food_items");
if (!$res) {
    echo json_encode(['status' => 'error', 'message' => 'Select Error: ' . $conn->error]);
    exit;
}
$row = $res->fetch_assoc();
if ($row['count'] == 0) {
    $conn->query("INSERT INTO food_items (name, folic, iron) VALUES 
    ('Hati Ayam (100g)', 588, 9.0), ('Hati Sapi (100g)', 250, 8.0), ('Daging Sapi (100g)', 12, 2.8),
    ('Kuning Telur Ayam (1 Btr)', 146, 2.7), ('Telur Ayam Utuh (1 Btr)', 45, 1.2), ('Kerang (100g)', 16, 21.0),
    ('Bayam Masak (100g)', 170, 3.1), ('Daun Kelor (100g)', 40, 7.0), ('Kacang Hijau (100g)', 625, 6.7),
    ('Susu Bumil (1 Gelas)', 400, 1.0), ('Tablet Tambah Darah (1 Tab)', 400, 60)");
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? '';

if ($method === 'POST') {
    if ($action === 'add_child') {
        $name = $_POST['name'] ?? '';
        $type = $_POST['type'] ?? '';
        $stmt = $conn->prepare("INSERT INTO children (user_id, name, type) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $name, $type);
        if ($stmt->execute()) echo json_encode(['status' => 'success', 'id' => $stmt->insert_id]);
        else echo json_encode(['status' => 'error', 'message' => 'Gagal menambah profil']);
    }
    
    // CRUD STUNTING
    elseif ($action === 'simpan_stunting') {
        $child_id = $_POST['child_id'];
        $month = $_POST['month'];
        $bb = $_POST['bb'] !== '' ? $_POST['bb'] : null;
        $tb = $_POST['tb'] !== '' ? $_POST['tb'] : null;
        $lk = $_POST['lk'] !== '' ? $_POST['lk'] : null;

        $stmt = $conn->prepare("INSERT INTO stunting_data (child_id, month, bb, tb, lk) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE bb=VALUES(bb), tb=VALUES(tb), lk=VALUES(lk)");
        $stmt->bind_param("iiddd", $child_id, $month, $bb, $tb, $lk);
        if($stmt->execute()) echo json_encode(['status'=>'success']);
        else echo json_encode(['status'=>'error', 'message'=>$conn->error]);
    }
    elseif ($action === 'hapus_bulan_stunting') {
        $child_id = $_POST['child_id'];
        $month = $_POST['month'];
        $stmt = $conn->prepare("DELETE FROM stunting_data WHERE child_id = ? AND month = ?");
        $stmt->bind_param("ii", $child_id, $month);
        if ($stmt->execute()) echo json_encode(['status'=>'success']);
        else echo json_encode(['status'=>'error', 'message'=>$conn->error]);
    }
    elseif ($action === 'hapus_stunting') {
        $child_id = $_POST['child_id'];
        $stmt = $conn->prepare("DELETE FROM stunting_data WHERE child_id = ?");
        $stmt->bind_param("i", $child_id);
        $stmt->execute();
        echo json_encode(['status'=>'success']);
    }
    
    // CRUD REMINDER (BARIS TERPISAH)
    elseif ($action === 'simpan_reminder') {
        $child_id = $_POST['child_id'];
        $tgl_patokan = $_POST['tgl'];
        $items = json_decode($_POST['items'], true); // Array of tasks
        
        // 1. Hapus data lama untuk anak ini biar bersih
        $stmt = $conn->prepare("DELETE FROM reminder_data WHERE child_id = ?");
        $stmt->bind_param("i", $child_id);
        $stmt->execute();

        // 2. Insert baris baru satu-satu per tugas
        $stmt = $conn->prepare("INSERT INTO reminder_data (child_id, tgl_patokan, tugas, start_date_str, end_date_str, start_date_obj, end_date_obj, planned_date) VALUES (?, ?, ?, ?, ?, ?, ?, NULL)");
        
        foreach($items as $it) {
            $stmt->bind_param("issssss", $child_id, $tgl_patokan, $it['tugas'], $it['startDateStr'], $it['endDateStr'], $it['startDateObj'], $it['endDateObj']);
            $stmt->execute();
        }
        echo json_encode(['status'=>'success']);
    }

    elseif ($action === 'update_rencana_reminder') {
        $child_id = $_POST['child_id'];
        $tugas = $_POST['tugas'];
        $planned_date = $_POST['planned_date'];

        $stmt = $conn->prepare("UPDATE reminder_data SET planned_date = ? WHERE child_id = ? AND tugas = ?");
        $stmt->bind_param("sis", $planned_date, $child_id, $tugas);
        if($stmt->execute()) echo json_encode(['status'=>'success']);
        else echo json_encode(['status'=>'error', 'message'=>$conn->error]);
    }
    
    
    // CRUD NUTRISI
    elseif ($action === 'simpan_nutrisi') {
        $child_id = $_POST['child_id'];
        $record_date = $_POST['record_date'];
        $food_index = $_POST['food_index']; 
        $name = $_POST['name'];
        $folic = $_POST['folic'];
        $iron = $_POST['iron'];
        $editing_id = $_POST['editing_id'] ?? null;
        
        if ($editing_id && $editing_id != 'null' && $editing_id != '') {
            $stmt = $conn->prepare("UPDATE nutrition_history SET food_index=?, name=?, folic=?, iron=? WHERE id=?");
            $stmt->bind_param("isddi", $food_index, $name, $folic, $iron, $editing_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO nutrition_history (child_id, record_date, food_index, name, folic, iron) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isisdd", $child_id, $record_date, $food_index, $name, $folic, $iron);
        }
        if($stmt->execute()) echo json_encode(['status'=>'success']);
        else echo json_encode(['status'=>'error', 'message'=>$conn->error]);
    }
    elseif ($action === 'hapus_nutrisi') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM nutrition_history WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(['status'=>'success']);
    }
    elseif ($action === 'hapus_hari_nutrisi') {
        $child_id = $_POST['child_id'];
        $date = $_POST['record_date'];
        $stmt = $conn->prepare("DELETE FROM nutrition_history WHERE child_id = ? AND record_date = ?");
        $stmt->bind_param("is", $child_id, $date);
        $stmt->execute();
        echo json_encode(['status'=>'success']);
    }
}
elseif ($method === 'GET') {
    if ($action === 'get_foods') {
        $res = $conn->query("SELECT * FROM food_items");
        $foods = [];
        while($r = $res->fetch_assoc()) $foods[] = $r;
        echo json_encode(['status'=>'success', 'data'=>$foods]);
    }
    elseif ($action === 'get_stunting') {
        $child_id = $_GET['child_id'];
        $stmt = $conn->prepare("SELECT * FROM stunting_data WHERE child_id = ?");
        $stmt->bind_param("i", $child_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        while($r = $res->fetch_assoc()) $data[] = $r;
        echo json_encode(['status'=>'success', 'data'=>$data]);
    }
    elseif ($action === 'get_nutrisi') {
        $child_id = $_GET['child_id'];
        $stmt = $conn->prepare("SELECT * FROM nutrition_history WHERE child_id = ? ORDER BY record_date DESC");
        $stmt->bind_param("i", $child_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        while($r = $res->fetch_assoc()) $data[] = $r;
        echo json_encode(['status'=>'success', 'data'=>$data]);
    }
    elseif ($action === 'get_reminder') {
        $child_id = $_GET['child_id'];
        $stmt = $conn->prepare("SELECT * FROM reminder_data WHERE child_id = ?");
        $stmt->bind_param("i", $child_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $items = [];
        $tgl_patokan = "";
        
        while($row = $res->fetch_assoc()) {
            $tgl_patokan = $row['tgl_patokan'];
            $items[] = [
                'tugas' => $row['tugas'],
                'startDateStr' => $row['start_date_str'],
                'endDateStr' => $row['end_date_str'],
                'startDateObj' => $row['start_date_obj'],
                'endDateObj' => $row['end_date_obj'],
                'userPlannedDate' => $row['planned_date']
            ];
        }

        if (count($items) > 0) {
            echo json_encode(['status'=>'success', 'data'=>['tgl'=>$tgl_patokan, 'items'=>$items]]);
        } else {
            echo json_encode(['status'=>'success', 'data'=>null]);
        }
    }
}
?>
