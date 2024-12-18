<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cấu hình tiêu đề CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Kết nối cơ sở dữ liệu
require 'db_connection.php';

// Kiểm tra kết nối
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error]));
}

// Lấy dữ liệu từ request
$data = json_decode(file_get_contents("php://input"), true);

// Kiểm tra dữ liệu đầu vào
if (isset($data['id'], $data['fullName'], $data['phone'], $data['email'], $data['grant'], $data['address'])) {

    // Gán giá trị từ request
    $id = $data['id'];
    $fullName = $data['fullName'];
    $phone = $data['phone'];
    $email = $data['email'];
    $grant = $data['grant'];
    $address = $data['address'];

    // Câu lệnh SQL cập nhật
    $sql = "UPDATE users SET 
                fullname = ?, 
                phone = ?, 
                email = ?, 
                grant = ?, 
                address = ? 
            WHERE id = ?";

    // Chuẩn bị câu lệnh
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssssi", $fullName, $phone, $email, $grant, $address, $id);

        // Thực thi câu lệnh
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Cập nhật thành công."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Không thể cập nhật thông tin nhân viên."]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Lỗi khi chuẩn bị câu lệnh SQL."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Dữ liệu không đầy đủ."]);
}

// Đóng kết nối
$conn->close();
?>
