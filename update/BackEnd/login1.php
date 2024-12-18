<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
 
require 'db_connection.php';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']); // Mật khẩu chưa mã hóa từ Frontend
 
    // Truy vấn lấy thông tin người dùng
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
 
 
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
 
        // Kiểm tra hash từ Frontend và DB
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user['role'];
 
            $amount = $user['amount'];
            $grant = $user['grant'];
 
            $level = ($amount >= 5000000) ? 'Premium' : (($amount >= 1000000) ? 'Gold' : 'Standard');
 
            echo json_encode([
                'status' => 'success',
                'role' => $user['role'],
                'grant' => $grant,
                'amount' => $amount,
                'level' => $level
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
    }
 
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
 