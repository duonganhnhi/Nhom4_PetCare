<?php
// Bật báo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Kết nối đến cơ sở dữ liệu
require 'db_connection.php';

// Kiểm tra kết nối
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Kết nối không thành công: " . $conn->connect_error]);
    exit;
}

// Nhận dữ liệu từ POST request
$data = json_decode(file_get_contents("php://input"), true);

// Kiểm tra dữ liệu đầu vào
if (
    !isset($data['id'], $data['petName'], $data['gender'], $data['age'], $data['breed'], $data['weight'], $data['species']) ||
    empty($data['petName']) || empty($data['gender']) || empty($data['breed']) || empty($data['species']) ||
    !is_numeric($data['age']) || !is_numeric($data['weight']) || $data['age'] <= 0 || $data['weight'] <= 0
) {
    echo json_encode(["status" => "error", "message" => "Thiếu hoặc không hợp lệ dữ liệu đầu vào."]);
    exit;
}

$id = $data['id'];
$petName = $data['petName'];
$gender = $data['gender'];
$age = $data['age'];
$breed = $data['breed'];
$weight = $data['weight'];
$species = $data['species'];

// Thực hiện cập nhật thông tin thú cưng
$update_sql = "UPDATE pets SET name = ?, gender = ?, age = ?, breed = ?, weight = ?, species = ? WHERE id = ?";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param("ssisssi", $petName, $gender, $age, $breed, $weight, $species, $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Cập nhật thú cưng thành công."]);
} else {
    echo json_encode(["status" => "error", "message" => "Lỗi khi cập nhật: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>