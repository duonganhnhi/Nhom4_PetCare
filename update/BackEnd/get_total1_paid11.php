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

// Lấy tham số start_date và end_date từ URL
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Kiểm tra tính hợp lệ của các tham số
if (!$start_date || !$end_date) {
    echo json_encode(['error' => 'Invalid or missing parameters']);
    exit();
}

// Chuẩn bị truy vấn với điều kiện thời gian
$sql = "SELECT SUM(servicePrice) AS total_paid 
        FROM appointments 
        WHERE payment_status = 'Hoàn thành' AND appointment_date BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Statement preparation failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

// Kiểm tra và xử lý kết quả
$row = $result->fetch_assoc();
$total_paid = $row['total_paid'] ?? 0;

// Trả về tổng tiền dưới dạng JSON
echo json_encode(['total_paid' => $total_paid]);

// Đóng kết nối
$stmt->close();
$conn->close();
?>
