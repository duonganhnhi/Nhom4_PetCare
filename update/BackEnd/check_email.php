<?php
// Kết nối tới database
header("Access-Control-Allow-Origin: *"); // Cho phép mọi nguồn truy cập (hoặc thay * bằng địa chỉ cụ thể nếu cần)
header("Access-Control-Allow-Methods: POST, GET, OPTIONS"); // Cho phép các phương thức truy cập
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Cho phép các header cần thiết
header("Access-Control-Allow-Credentials: true"); // Nếu bạn cần gửi cookies cùng yêu cầu


require 'db_connection.php'; // Sử dụng kết nối từ db_connection.php

// Nhận dữ liệu từ React
$email = $_POST['email'];

// Chuẩn bị câu truy vấn
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo 'Email exists';
} else {
    echo 'Email not found';
}

// Đóng kết nối
$stmt->close();
$conn->close();
?>