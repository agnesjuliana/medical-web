<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $pass = $_POST['pass'] ?? '';

        if (empty($name) || empty($email) || empty($pass)) {
            echo json_encode(['status' => 'error', 'message' => 'Semua form wajib diisi!']);
            exit;
        }

        // Cek email dobel
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email sudah terdaftar, silakan Login.']);
            exit;
        }
        $stmt->close();

        // Register user baru
        $stmt = $conn->prepare("INSERT INTO users (name, email, pass) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $pass);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Akun berhasil didaftarkan! silakan Log In.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal mendaftar database error.']);
        }
        $stmt->close();
    }
    else if ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $pass = $_POST['pass'] ?? '';

        if (empty($email) || empty($pass)) {
            echo json_encode(['status' => 'error', 'message' => 'Isi email dan password!']);
            exit;
        }

        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ? AND pass = ?");
        $stmt->bind_param("ss", $email, $pass);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['growlife_user_id'] = $user['id'];
            $_SESSION['motherName'] = $user['name'];
            echo json_encode(['status' => 'success', 'name' => $user['name']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Username atau Password salah!']);
        }
        $stmt->close();
    }
    else if ($action === 'logout') {
        session_destroy();
        echo json_encode(['status' => 'success']);
    }
}
?>
