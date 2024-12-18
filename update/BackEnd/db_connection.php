<?php
$servername = "127.0.0.1"; // Địa chỉ máy chủ MySQL
$username = "root";         // Tên người dùng MySQL (thường là 'root' với Laragon)
$password = "";             // Mật khẩu của MySQL (thường để trống với Laragon)
$dbname = "petcare";    // Tên cơ sở dữ liệu bạn đã tạo

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
// Kết nối thành công
?>