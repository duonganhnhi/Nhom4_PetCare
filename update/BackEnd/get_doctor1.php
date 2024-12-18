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
$sql = "SELECT * FROM appointmentsdoc WHERE doctorName= 'Dr. Nguyễn Ngọc Mai'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $appointmentsdoc = [];

    // Duyệt qua từng hàng kết quả và lưu vào mảng $appointmentsdoc
    while ($row = $result->fetch_assoc()) {
        $appointmentsdoc[] = $row;
    }

    // Trả về kết quả dưới dạng JSON
    echo json_encode($appointmentsdoc);
} else {
    echo json_encode([]);
}

// Đóng kết nối
$conn->close();
?>