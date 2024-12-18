<?php
// Kết nối tới database
header("Access-Control-Allow-Origin: *"); // Cho phép mọi nguồn truy cập (hoặc thay * bằng địa chỉ cụ thể nếu cần)
header("Access-Control-Allow-Methods: POST, GET, OPTIONS"); // Cho phép các phương thức truy cập
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Cho phép các header cần thiết
header("Access-Control-Allow-Credentials: true"); // Nếu bạn cần gửi cookies cùng yêu cầu

require 'db_connection.php'; // Sử dụng kết nối từ db_connection.php

// Nhận dữ liệu từ React
$email = $_POST['email'];
$newPassword = $_POST['newPassword']; // Không mã hóa mật khẩu

// Chuẩn bị câu truy vấn
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $newPassword, $email);

if ($stmt->execute()) {
    echo 'Password reset successful!';
} else {
    echo 'Password reset failed.';
}

// Đóng kết nối
$stmt->close();
$conn->close();
?>