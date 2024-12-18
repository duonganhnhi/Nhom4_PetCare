<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require 'db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['username'])) {
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Kiểm tra nếu username tồn tại trong database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$data['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Chuẩn bị câu lệnh SQL
            $sql = "UPDATE users SET ";
            $params = [];

            // Only update provided fields
            if (isset($data['fullname']) && $data['fullname'] !== '') {
                $sql .= "fullname = ?, ";
                $params[] = $data['fullname'];
            }
            if (isset($data['email']) && $data['email'] !== '') {
                $sql .= "email = ?, ";
                $params[] = $data['email'];
            }
            if (isset($data['phone']) && $data['phone'] !== '') {
                $sql .= "phone = ?, ";
                $params[] = $data['phone'];
            }
            if (isset($data['address']) && $data['address'] !== '') {
                $sql .= "address = ?, ";
                $params[] = $data['address'];
            }
            if (isset($data['gender']) && $data['gender'] !== '') {
                $sql .= "gender = ?, ";
                $params[] = $data['gender'];
            }
            if (isset($data['dob']) && $data['dob'] !== '') {
                $sql .= "dob = ?, ";
                $params[] = $data['dob'];
            }

            // Check for new password
            if (!empty($data['password'])) {
                $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
                $sql .= "password = ?, ";
                $params[] = $hashedPassword;
            }

            // Remove trailing comma and space
            $sql = rtrim($sql, ", ");

            // Finalize the query to update the user with the username
            $sql .= " WHERE username = ?";
            $params[] = $data['username'];

            // Execute the update query
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            echo json_encode(['status' => 'success', 'message' => 'Cập nhật thành công!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Username không tồn tại']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu trường username trong yêu cầu']);
}
?>