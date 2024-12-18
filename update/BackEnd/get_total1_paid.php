<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Content-Type: application/json');

require 'db_connection.php'; // Bao gồm file kết nối đến database

// Kết nối đến database
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

// Thực hiện truy vấn để tính tổng tiền với trạng thái payment_status là "paid"
$sql = "SELECT SUM(servicePrice) AS total_paid FROM appointments WHERE payment_status = 'Hoàn thành'";
$result = $conn->query($sql);

if ($result === FALSE) {
    echo json_encode(['error' => 'Query error: ' . $conn->error]);
    exit();
}

// Lấy kết quả
$row = $result->fetch_assoc();
$total_paid = $row['total_paid'] ?? 0; // Nếu không có kết quả thì trả về 0

// Trả về tổng tiền dưới dạng JSON
echo json_encode(['total_paid' => $total_paid]);

// Đóng kết nối
$conn->close();
?>