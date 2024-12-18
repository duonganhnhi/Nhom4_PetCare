<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require 'db_connection.php';

// Giả định rằng bạn đã xác thực người dùng và có ID người dùng trong phiên làm việc  
session_start();
$userId = $_SESSION['user_id']; // Lấy user ID từ session  

$data = json_decode(file_get_contents('php://input'), true);

// Kiểm tra xem userId có tồn tại không  
if (!$userId) {
    echo json_encode(['error' => 'User ID not found']);
    exit;
}

// Chỉ cập nhật các trường có giá trị hợp lệ  
$updates = [];
$params = [];

if (!empty($data['password'])) {
    $updates[] = "password = ?";
    // Hash the password before storing  
    $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
}
if (!empty($data['email'])) {
    $updates[] = "email = ?";
    $params[] = $data['email'];
}
if (!empty($data['phone'])) {
    $updates[] = "phone = ?";
    $params[] = $data['phone'];
}
if (!empty($data['address'])) {
    $updates[] = "address = ?";
    $params[] = $data['address'];
}

// Nếu không có trường nào để cập nhật  
if (empty($updates)) {
    echo json_encode(['error' => 'No fields to update']);
    exit;
}

$params[] = $userId;
$sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode(['status' => 'success']);
?>