<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Tắt hiển thị lỗi trong môi trường sản xuất
ini_set('log_errors', 1); // Bật ghi lỗi vào tệp log
ini_set('error_log', 'path/to/error.log'); // Đường dẫn tệp log lỗi

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Bao gồm tệp kết nối cơ sở dữ liệu
require 'db_connection.php';

try {
    // Lấy dữ liệu POST từ client (React)
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("Dữ liệu đầu vào không hợp lệ.");
    }

    // Lấy dữ liệu từ yêu cầu
    $username = $conn->real_escape_string($data['username']); // VARCHAR
    $email = $conn->real_escape_string($data['email']);       // VARCHAR
    $phoneNumber = $conn->real_escape_string($data['phoneNumber']); // VARCHAR
    $address = $conn->real_escape_string($data['address']);    // TEXT
    $password = isset($data['password']) ? $conn->real_escape_string($data['password']) : ''; // VARCHAR (Optional)

    // Xây dựng câu lệnh SQL để cập nhật thông tin người dùng
    $sql = "UPDATE users SET ";
    $fields = [];

    if (!empty($email)) {
        $fields[] = "email='$email'";
    }
    if (!empty($phoneNumber)) {
        $fields[] = "phone='$phoneNumber'";
    }
    if (!empty($address)) {
        if (strlen($address) > 65535) { // TEXT có giới hạn là 65,535 ký tự
            throw new Exception("Address vượt quá giới hạn của kiểu TEXT.");
        }
        $fields[] = "address='$address'";
    }
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $fields[] = "password='$hashedPassword'";
    }

    if (empty($fields)) {
        throw new Exception("Không có trường nào để cập nhật.");
    }

    $sql .= implode(", ", $fields);
    $sql .= " WHERE username='$username'";

    // Thực hiện câu lệnh SQL và kiểm tra kết quả
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Profile updated successfully!"]);
    } else {
        throw new Exception("Lỗi cơ sở dữ liệu: " . $conn->error);
    }
} catch (Exception $e) {
    // Gửi phản hồi lỗi dưới dạng JSON
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
} finally {
    // Đóng kết nối cơ sở dữ liệu
    $conn->close();
}
?>