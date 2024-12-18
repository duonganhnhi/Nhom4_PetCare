<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Kết nối cơ sở dữ liệu
require 'db_connection.php';

// Lấy dữ liệu từ request
$data = $_POST;

if (isset($data['name']) && isset($data['email']) && isset($data['subject']) && isset($data['message'])) {
    $name = $data['name'];
    $email = $data['email'];
    $subject = $data['subject'];
    $message = $data['message'];

    // Chuẩn bị và thực thi câu lệnh SQL
    $stmt = $conn->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("ssss", $name, $email, $subject, $message);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            error_log("Error executing query: " . $stmt->error); // Ghi lỗi vào log
            echo json_encode(['status' => 'error', 'message' => 'Failed to execute query: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        error_log("Error preparing statement: " . $conn->error); // Ghi lỗi vào log
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement: ' . $conn->error]);
    }

    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
}
?>