<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Kết nối cơ sở dữ liệu
require 'db_connection.php';

// Truy vấn dữ liệu từ bảng contacts
$sql = "SELECT * FROM contacts";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Lưu dữ liệu vào mảng
    $contacts = array();
    while ($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }
    echo json_encode($contacts);
} else {
    echo json_encode(array('message' => 'No contacts found.'));
}

$conn->close();
?>