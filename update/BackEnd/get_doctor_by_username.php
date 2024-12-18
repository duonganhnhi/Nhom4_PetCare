<?php
// Bật báo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Kết nối đến cơ sở dữ liệu
require 'db_connection.php';

// Kiểm tra kết nối
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Kết nối không thành công: " . $conn->connect_error]));
}

$username = isset($_GET['username']) ? $_GET['username'] : '';
$query = $mysqli->prepare('SELECT doctorName FROM doctors WHERE username = ?');
$query->bind_param('s', $username);
$query->execute();
$query->bind_result($doctorName);
$query->fetch();

$response = array('doctorName' => $doctorName);
echo json_encode($response);

$query->close();
$mysqli->close();
?>