<?php
// Bật báo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Kết nối đến cơ sở dữ liệu
require 'db_connection.php';

// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Lấy dữ liệu từ yêu cầu
    $userId = $data['id'] ?? null;
    $grant = $data['grant'] ?? null;

    if ($userId && $grant) {
        // Chuẩn bị truy vấn
        $stmt = $conn->prepare("UPDATE users SET grant = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('si', $grant, $userId);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
            }

            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Chuẩn bị truy vấn thất bại']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
}
$conn->close();
?>