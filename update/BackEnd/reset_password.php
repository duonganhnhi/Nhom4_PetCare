<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connection.php';

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Kết nối cơ sở dữ liệu không thành công: ' . $conn->connect_error]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['email'])) {
    $email = trim($data['email']);

    // Kiểm tra xem email có tồn tại trong cơ sở dữ liệu không
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Không thể chuẩn bị câu lệnh: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $verificationCode = rand(100000, 999999);

        $stmtReset = $conn->prepare("INSERT INTO password_resets (email, code) VALUES (?, ?)");
        if (!$stmtReset) {
            echo json_encode(['success' => false, 'message' => 'Không thể chuẩn bị câu lệnh lưu mã xác thực: ' . $conn->error]);
            exit;
        }
        $stmtReset->bind_param('si', $email, $verificationCode);
        $stmtReset->execute();

        $subject = "Mã xác thực đặt lại mật khẩu";
        $message = "Mã xác thực của bạn là: $verificationCode";
        $headers = "From: no-reply@yourdomain.com";

        if (mail($email, $subject, $message, $headers)) {
            echo json_encode(['success' => true, 'message' => 'Mã xác thực đã được gửi đến email của bạn.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gửi email thất bại.']);
        }

        $stmtReset->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Email không tồn tại.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu đầu vào không hợp lệ.']);
}
?>