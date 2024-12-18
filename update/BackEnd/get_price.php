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

// Câu truy vấn SELECT tổng hợp tổng doanh thu theo tháng, năm và từng dịch vụ
$sql = "SELECT 
            MONTH(date) AS month, 
            YEAR(date) AS year, 
            serviceName, 
            SUM(servicePrice) AS total_revenue
        FROM appointments 
        WHERE payment_status = 'Hoàn thành'
        GROUP BY YEAR(date), MONTH(date), serviceName
        ORDER BY year ASC, month ASC, serviceName ASC";

$result = $conn->query($sql);

if ($result === false) {
    die(json_encode(["status" => "error", "message" => "Lỗi khi thực thi truy vấn: " . $conn->error]));
}

if ($result->num_rows > 0) {
    $summary = [];

    // Duyệt qua từng hàng kết quả và lưu vào mảng $summary
    while ($row = $result->fetch_assoc()) {
        $summary[] = [
            'month' => (int) $row['month'], // Ép kiểu để đảm bảo đúng định dạng JSON
            'year' => (int) $row['year'],
            'service_name' => $row['serviceName'],
            'total_revenue' => (float) $row['total_revenue'],
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