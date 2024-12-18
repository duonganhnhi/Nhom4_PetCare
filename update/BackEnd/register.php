<?php
// Set headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Import database connection
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ yêu cầu POST
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $role = 'customer'; // Mặc định là customer

    // Kiểm tra xem có trường nào bị trống không
    if (empty($username) || empty($password) || empty($fullname) || empty($email) || empty($phone) || empty($address)) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng điền đầy đủ thông tin!']);
        exit();
    }

    // Kiểm tra định dạng email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Email không hợp lệ!']);
        exit();
    }

    // Kiểm tra định dạng số điện thoại (10-11 chữ số)
    if (!preg_match('/^\d{10,11}$/', $phone)) {
        echo json_encode(['status' => 'error', 'message' => 'Số điện thoại không hợp lệ!']);
        exit();
    }

    // Mã hóa mật khẩu
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Kiểm tra trùng lặp username, email hoặc số điện thoại
    $sql_check = "SELECT * FROM users WHERE username = ? OR email = ? OR phone = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("sss", $username, $email, $phone);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['username'] === $username) {
            echo json_encode(['status' => 'error', 'message' => 'Tên đăng nhập đã tồn tại!']);
        } elseif ($row['email'] === $email) {
            echo json_encode(['status' => 'error', 'message' => 'Email đã tồn tại!']);
        } elseif ($row['phone'] === $phone) {
            echo json_encode(['status' => 'error', 'message' => 'Số điện thoại đã tồn tại!']);
        }
        $stmt_check->close();
        exit();
    }
    $stmt_check->close();

    // Thêm người dùng mới vào cơ sở dữ liệu
    $stmt = $conn->prepare("INSERT INTO users (username, password, fullname, email, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $username, $hashed_password, $fullname, $email, $phone, $address, $role);

    if ($stmt->execute()) {
        // Lấy ID người dùng vừa được tạo
        $userId = $stmt->insert_id;

        echo json_encode([
            'status' => 'success',
            'message' => 'Đăng ký thành công!',
            'userId' => $userId,
            'username' => $username,
            'role' => $role,
            'grant' => 'default_grant', // Thông tin mặc định cho grant (nếu cần)
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Đã xảy ra lỗi: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Phương thức yêu cầu không hợp lệ']);
}
?>
