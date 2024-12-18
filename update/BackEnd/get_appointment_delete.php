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

// Câu truy vấn SELECT để lấy danh sách các cuộc hẹn
$sql = "SELECT id, name, phone, date, time, pets, serviceName, servicePrice, weight, paymentMethod, payment_status, username FROM appointments WHERE payment_status = 'Huỷ'order by date,time ASC";
$result = $conn->query($sql);

if ($result === false) {
    die(json_encode(["status" => "error", "message" => "Lỗi khi thực thi truy vấn: " . $conn->error]));
}

if ($result->num_rows > 0) {
    $appointments = [];

    // Duyệt qua từng hàng kết quả và lưu vào mảng $appointments
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }

    // Trả về kết quả dưới dạng JSON
    echo json_encode($appointments);
} else {
    echo json_encode([]);
}

// Đóng kết nối
$conn->close();
?>