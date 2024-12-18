<?php
// Bật hiển thị lỗi trong môi trường phát triển
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require 'db_connection.php';

try {
    // Chỉ xử lý nếu phương thức là POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        exit;
    }

    // Đọc và giải mã dữ liệu JSON
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($input)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
        exit;
    }

    // Lấy và kiểm tra dữ liệu từ input
    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');
    $role = trim($input['role'] ?? '');

    if (empty($username) || empty($password) || empty($role)) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng điền đầy đủ thông tin!']);
        exit;
    }

    // Xác định quyền dựa trên vai trò
    $grant = match ($role) {
        'admin' => 1,
        'staff' => 2,
        'doctor' => 3,
        default => null,
    };

    if ($grant === null) {
        echo json_encode(['status' => 'error', 'message' => 'Vai trò không hợp lệ!']);
        exit;
    }

    // Mã hóa mật khẩu
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Kiểm tra tài khoản trùng lặp
    $sql_check = "SELECT COUNT(*) FROM users WHERE username = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $stmt_check->bind_result($user_count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($user_count > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username đã tồn tại!']);
        exit;
    }

    // Thêm tài khoản mới vào cơ sở dữ liệu (sử dụng dấu backtick cho cột `grant`)
    $sql_insert = "INSERT INTO users (username, password, role, `grant`) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("sssi", $username, $hashed_password, $role, $grant);

    if ($stmt_insert->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Đăng ký thành công!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi khi thêm tài khoản: ' . $stmt_insert->error]);
    }

    $stmt_insert->close();
    $conn->close();
} catch (Exception $e) {
    // Xử lý các lỗi không mong muốn
    echo json_encode(['status' => 'error', 'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()]);
}
?>