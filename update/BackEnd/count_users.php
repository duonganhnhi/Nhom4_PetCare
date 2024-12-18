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

// Truy vấn đếm số tài khoản của khách hàng
$sql = "SELECT COUNT(*) AS total_customers FROM users WHERE role = 'customer'";
$result = $conn->query($sql);

if ($result === FALSE) {
    echo json_encode(['error' => 'Query error: ' . $conn->error]);
    exit();
}

// Lấy dữ liệu
$row = $result->fetch_assoc();
echo json_encode(['total_customers' => $row['total_customers']]);

// Đóng kết nối
$conn->close();
?>