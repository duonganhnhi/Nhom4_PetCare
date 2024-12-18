<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Kết nối cơ sở dữ liệu
require 'db_connection.php';

// Lấy dữ liệu từ request
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    $id = $data['id'];

    // Chuẩn bị và thực thi câu lệnh SQL
    $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ?");

    if ($stmt) {
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Contact deleted successfully']);
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