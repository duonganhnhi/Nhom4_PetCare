<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require 'db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['username']) && isset($data['password']) && isset($data['role'])) {
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, email, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_BCRYPT), // Mã hóa mật khẩu
            $data['role'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null
        ]);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Missing required fields']);
}
?>