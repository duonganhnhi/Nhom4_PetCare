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

// Kiểm tra tham số truyền vào từ URL
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Kiểm tra nếu thiếu ngày
if (!$start_date || !$end_date) {
    echo json_encode(["status" => "error", "message" => "Thiếu thông tin ngày bắt đầu hoặc kết thúc"]);
    exit;
}

// Câu truy vấn SQL lọc theo khoảng ngày
$sql = "SELECT 
            serviceName, 
            COUNT(*) AS total_orders, 
            SUM(servicePrice) AS total_revenue
        FROM appointments 
        WHERE payment_status = 'Hoàn thành' 
            AND date BETWEEN ? AND ?
        GROUP BY serviceName
        ORDER BY serviceName ASC";

// Chuẩn bị và thực thi truy vấn
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    echo json_encode(["status" => "error", "message" => "Lỗi truy vấn: " . $conn->error]);
    exit;
}

$summary = [];
while ($row = $result->fetch_assoc()) {
    $summary[] = [
        'serviceName' => $row['serviceName'],
        'total_orders' => $row['total_orders'],
        'total_revenue' => $row['total_revenue'],
    ];
}

// Trả về kết quả JSON
echo json_encode($summary);

// Đóng kết nối
$stmt->close();
$conn->close();
?>
