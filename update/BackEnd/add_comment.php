<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Kết nối cơ sở dữ liệu
require 'db_connection.php';

// Lấy dữ liệu từ POST request
$name = $_POST['name'] ?? '';
$comment = $_POST['comment'] ?? '';
$blog_id = $_POST['blog_id'] ?? '';

// Kiểm tra dữ liệu
if ($name && $comment && $blog_id) {
    $sql = "INSERT INTO comments (name, comment, blog_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $name, $comment, $blog_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi khi thêm bình luận.']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu dữ liệu.']);
}

$conn->close();
?>