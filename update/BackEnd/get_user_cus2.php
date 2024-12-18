<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require 'db_connection.php'; // Bao gồm tập tin cấu hình

// Tạo kết nối MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

// Thực hiện truy vấn
$sql = "SELECT * FROM users WHERE role = 'customer'";
$result = $conn->query($sql);

if ($result === FALSE) {
    echo json_encode(['error' => 'Query error: ' . $conn->error]);
    exit();
}

// Xử lý kết quả
$users = [];
while ($user = $result->fetch_assoc()) {
    // Tính toán level dựa trên amount
    if ($user['amount'] >= 5000000) {
        $user['level'] = 'Premium';
    } elseif ($user['amount'] >= 1000000) {
        $user['level'] = 'Gold';
    } else {
        $user['level'] = 'Standard';
    }
    // Thêm thông tin vào danh sách người dùng
    $users[] = $user;
}

// Trả về dữ liệu dưới dạng JSON

echo json_encode($users);


// Đóng kết nối
$conn->close();
?>