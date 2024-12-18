<?php
header('Content-Type: application/json');

// Lấy dữ liệu JSON gửi từ phía client
$inputData = json_decode(file_get_contents('php://input'), true);

// Kiểm tra nếu dữ liệu có hợp lệ
if (isset($inputData['username'])) {
    $username = $inputData['username'];

    // Xử lý dữ liệu nếu cần
    // Ví dụ: ghi vào cơ sở dữ liệu, gửi email, v.v.

    // Trả về kết quả thành công
    echo json_encode(['status' => 'success', 'message' => 'Username đã được gửi']);
} else {
    // Nếu không có username, trả về lỗi
    echo json_encode(['status' => 'error', 'error' => 'Username không hợp lệ']);
}
?>