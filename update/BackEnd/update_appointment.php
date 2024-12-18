<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require 'db_connection.php';

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Lấy dữ liệu từ request
$data = json_decode(file_get_contents("php://input"), true);

// Kiểm tra xem dữ liệu có đầy đủ không
if (isset($data['id'], $data['name'], $data['phone'], $data['weight'], $data['serviceName'], $data['date'], $data['time'], $data['paymentMethod'], $data['servicePrice'])) {

    // Cập nhật dữ liệu
    $id = $data['id'];
    $name = $data['name'];
    $phone = $data['phone'];
    $weight = $data['weight'];
    $serviceName = $data['serviceName'];
    $date = $data['date'];
    $time = $data['time'];
    $paymentMethod = $data['paymentMethod'];
    $servicePrice = $data['servicePrice'];

    // Câu lệnh SQL để cập nhật
    $sql = "UPDATE appointments SET 
                name = ?, 
                phone = ?, 
                weight = ?, 
                serviceName = ?, 
                date = ?, 
                time = ?, 
                paymentMethod = ?, 
                servicePrice = ? 
            WHERE id = ?";

    // Sử dụng prepared statements để tránh SQL Injection
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssssssi", $name, $phone, $weight, $serviceName, $date, $time, $paymentMethod, $servicePrice, $id);

        // Thực thi câu lệnh
        if ($stmt->execute()) {
            // Nếu thành công, trả về JSON thông báo thành công
            echo json_encode(["status" => "success", "message" => "Cập nhật thành công."]);
        } else {
            // Nếu có lỗi khi thực thi câu lệnh
            echo json_encode(["status" => "error", "message" => "Không thể cập nhật lịch khám."]);
        }

        $stmt->close();
    } else {
        // Lỗi chuẩn bị câu lệnh SQL
        echo json_encode(["status" => "error", "message" => "Lỗi khi chuẩn bị câu lệnh SQL."]);
    }
} else {
    // Thiếu dữ liệu đầu vào
    echo json_encode(["status" => "error", "message" => "Dữ liệu không đầy đủ."]);
}

$conn->close();
?>