<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require 'db_connection.php'; // Bao gồm tập tin cấu hình

// Nhận ID từ yêu cầu
$id = $_GET['id'] ?? null;

if ($id) {
    // Chuẩn bị câu truy vấn lấy mật khẩu
    $query = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Trả về giá trị mật khẩu trực tiếp
        echo json_encode(['status' => 'success', 'password' => $user['password']]);
    } else {
        echo json_encode(['status' => 'error', 'error' => 'Không tìm thấy người dùng']);
    }
} else {
    echo json_encode(['status' => 'error', 'error' => 'ID không hợp lệ']);
}
?>