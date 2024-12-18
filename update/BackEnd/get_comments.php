<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Kết nối cơ sở dữ liệu
require 'db_connection.php';

// Lấy ID blog từ query string
$blog_id = isset($_GET['blog_id']) ? $_GET['blog_id'] : '';

// Truy vấn cơ sở dữ liệu để lấy bình luận
$sql = "SELECT * FROM comments WHERE blog_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $blog_id);
$stmt->execute();
$result = $stmt->get_result();

// Chuyển đổi kết quả thành mảng
$comments = $result->fetch_all(MYSQLI_ASSOC);

// Đóng kết nối
$stmt->close();
$conn->close();

// Trả về dữ liệu dưới dạng JSON
echo json_encode($comments);
?>