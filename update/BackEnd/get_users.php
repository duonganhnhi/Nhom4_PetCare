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

// Lấy tham số tìm kiếm từ GET request
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Tạo truy vấn SQL với điều kiện tìm kiếm
$sql = "SELECT * FROM users WHERE role = 'customer'";

if ($search) {
    // Tìm kiếm trong các trường: username, fullname, phone, email, address, sex
    $sql .= " AND (username LIKE '%$search%' OR fullname LIKE '%$search%' OR phone LIKE '%$search%' OR email LIKE '%$search%' OR address LIKE '%$search%' OR gender LIKE '%$search%')";
}

$result = $conn->query($sql);

if ($result === FALSE) {
    echo json_encode(['error' => 'Query error: ' . $conn->error]);
    exit();
}

// Lấy dữ liệu
$users = $result->fetch_all(MYSQLI_ASSOC);

// Kiểm tra nếu không có dữ liệu
if (empty($users)) {
    echo json_encode(['message' => 'Không có dữ liệu']);
    exit();
}

// Trả về dữ liệu dưới dạng JSON
echo json_encode($users);

// Đóng kết nối
$conn->close();
?>
