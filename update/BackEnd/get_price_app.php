<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "your_database_name";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Truy vấn để lấy dữ liệu
$sql = "SELECT serviceName, servicePrice, payment_status FROM appointments";
$result = $conn->query($sql);

$revenueMap = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['payment_status'] === 'Hoàn thành') {
            $serviceName = $row['serviceName'] ?: 'Unknown Service';
            $servicePrice = floatval($row['servicePrice']);

            if (!isset($revenueMap[$serviceName])) {
                $revenueMap[$serviceName] = 0;
            }

            $revenueMap[$serviceName] += $servicePrice;
        }
    }
}

// Trả dữ liệu về dưới dạng JSON
echo json_encode($revenueMap);

$conn->close();
?>