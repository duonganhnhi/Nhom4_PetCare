<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
require_once 'db_connection.php';

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['email']) && isset($data['verificationCode']) && isset($data['newPassword'])) {
    $email = $data['email'];
    $verificationCode = $data['verificationCode'];
    $newPassword = password_hash($data['newPassword'], PASSWORD_DEFAULT); // Hash mật khẩu mới  

    // Kiểm tra mã xác thực  
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE email = ? AND code = ?");
    $stmt->bind_param('si', $email, $verificationCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Cập nhật mật khẩu mới  
        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $updateStmt->bind_param('ss', $newPassword, $email);

        if ($updateStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Mật khẩu đã được đặt lại thành công.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể cập nhật mật khẩu: ' . $conn->error]);
        }

        $updateStmt->close();
        // Xóa mã xác thực sau khi sử dụng  
        $deleteStmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $deleteStmt->bind_param('s', $email);
        $deleteStmt->execute();
        $deleteStmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Mã xác thực không đúng hoặc đã hết hạn.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu đầu vào không hợp lệ.']);
}
?>