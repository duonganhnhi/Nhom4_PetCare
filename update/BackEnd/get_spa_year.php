<?php
// Bật báo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Kết nối đến cơ sở dữ liệu
require 'db_connection.php';

// Kiểm tra kết nối
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Kết nối không thành công: " . $conn->connect_error]));
}

// Câu truy vấn SELECT tổng hợp tổng số đơn đặt và tổng giá tiền theo từng dịch vụ và từng năm
$sql = "SELECT 
            serviceName, 
            YEAR(date) AS year, 
            COUNT(*) AS total_orders, 
            SUM(servicePrice) AS total_revenue
        FROM appointments 
        WHERE payment_status = 'Hoàn thành'
        GROUP BY serviceName, YEAR(date)
        ORDER BY year ASC, serviceName ASC";

$result = $conn->query($sql);

if ($result === false) {
    die(json_encode(["status" => "error", "message" => "Lỗi khi thực thi truy vấn: " . $conn->error]));
}

if ($result->num_rows > 0) {
    $summary = [];

    // Duyệt qua từng hàng kết quả và lưu vào mảng $summary
    while ($row = $result->fetch_assoc()) {
        $summary[] = [
            'serviceName' => $row['serviceName'],
            'year' => $row['year'],
            'total_orders' => $row['total_orders'],
            'total_revenue' => $row['total_revenue'],
        ];
    }

    // Trả về kết quả dưới dạng JSON
    echo json_encode($summary);
} else {
    echo json_encode([]);
}

// Đóng kết nối
$conn->close();
?>