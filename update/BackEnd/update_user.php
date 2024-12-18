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

// Kiểm tra dữ liệu đầy đủ
if (isset($data['id'], $data['username'], $data['fullname'], $data['email'], $data['phone'], $data['address'], $data['role'], $data['gender'], $data['dob'])) {
    $id = $data['id'];
    $username = $data['username'];
    $fullname = $data['fullname'];
    $email = $data['email'];
    $phone = $data['phone'];
    $address = $data['address'];
    $role = $data['role'];
    $gender = $data['gender'];
    $dob = $data['dob'];

    // Câu lệnh SQL để cập nhật thông tin người dùng
    $sql = "UPDATE users SET 
                username = ?, 
                fullname = ?, 
                email = ?, 
                phone = ?, 
                address = ?, 
                role = ?, 
                gender = ?, 
                dob = ?
            WHERE id = ?";

    // Sử dụng prepared statements để tránh SQL Injection
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssssssi", $username, $fullname, $email, $phone, $address, $role, $gender, $dob, $id);

        // Thực thi câu lệnh
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Cập nhật thông tin người dùng thành công."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Không thể cập nhật thông tin người dùng."]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Lỗi khi chuẩn bị câu lệnh SQL."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Dữ liệu không đầy đủ."]);
}

$conn->close();
?>
