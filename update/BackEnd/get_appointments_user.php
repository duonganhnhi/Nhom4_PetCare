<?php
// Bật báo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Kết nối đến cơ sở dữ liệu
require 'db_connection.php';

// Kiểm tra kết nối
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Kết nối không thành công: " . $conn->connect_error]);
    exit;
}

// Lấy dữ liệu từ query string (GET request)
if (isset($_GET['username'])) {
    $username = $conn->real_escape_string($_GET['username']); // Tránh SQL Injection

    // Câu truy vấn SELECT để lấy danh sách các cuộc hẹn
    $sql = "SELECT * FROM appointments WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username); // Sử dụng prepared statement
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $appointments = [];

        // Duyệt qua từng hàng kết quả và lưu vào mảng $appointments
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }

        // Trả về kết quả dưới dạng JSON
        echo json_encode($appointments);
    } else {
        echo json_encode([]); // Không có kết quả
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Missing 'username' parameter"]);
}

// Đóng kết nối
$conn->close();
?>