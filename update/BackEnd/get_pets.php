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
$sql = "SELECT * FROM pets ";
$result = $conn->query($sql);

if ($result === FALSE) {
    echo json_encode(['error' => 'Query error: ' . $conn->error]);
    exit();
}

// Lấy dữ liệu
$users = $result->fetch_all(MYSQLI_ASSOC);

// Trả về dữ liệu dưới dạng JSON
echo json_encode($users);

// Đóng kết nối
$conn->close();
?>