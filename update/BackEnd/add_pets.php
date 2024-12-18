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
    die(json_encode(["status" => "error", "message" => "Kết nối không thành công: " . $conn->connect_error]));
}

// Nhận dữ liệu từ POST request
$data = json_decode(file_get_contents("php://input"), true);

// Kiểm tra dữ liệu đầu vào
if (
    !isset($data['nameUser'], $data['petName'], $data['gender'], $data['age'], $data['breed'], $data['weight'], $data['species']) ||
    empty($data['nameUser']) || empty($data['petName']) || empty($data['gender']) || empty($data['breed']) || empty($data['species']) ||
    !is_numeric($data['age']) || !is_numeric($data['weight']) || $data['age'] <= 0 || $data['weight'] <= 0
) {
    die(json_encode(["status" => "error", "message" => "Thiếu hoặc không hợp lệ dữ liệu đầu vào."]));
}

// Trích xuất thông tin
$nameUser = $data['nameUser'];
$petName = $data['petName'];
$gender = $data['gender'];
$age = $data['age'];
$breed = $data['breed'];
$weight = $data['weight'];
$species = $data['species'];

// Kiểm tra xem tài khoản người dùng có tồn tại hay không
$check_user_sql = "SELECT * FROM users WHERE username = ?";
$check_user_stmt = $conn->prepare($check_user_sql);
$check_user_stmt->bind_param("s", $nameUser);
$check_user_stmt->execute();
$user_result = $check_user_stmt->get_result();

if ($user_result->num_rows === 0) {
    die(json_encode(["status" => "error", "message" => "Tài khoản người dùng không tồn tại."]));
}

// Kiểm tra xem thú cưng đã tồn tại cho người dùng này chưa
$check_pet_sql = "SELECT * FROM pets WHERE nameUser = ? AND name = ?";
$check_pet_stmt = $conn->prepare($check_pet_sql);
$check_pet_stmt->bind_param("ss", $nameUser, $petName);
$check_pet_stmt->execute();
$pet_result = $check_pet_stmt->get_result();

if ($pet_result->num_rows > 0) {
    die(json_encode(["status" => "error", "message" => "Thú cưng với tên này đã tồn tại cho tài khoản này."]));
}

// Thực hiện thêm thú cưng mới
$insert_sql = "INSERT INTO pets (nameUser, name, gender, age, breed, weight, species)
               VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($insert_sql);
$stmt->bind_param("sssssss", $nameUser, $petName, $gender, $age, $breed, $weight, $species);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Thêm thú cưng thành công."]);
} else {
    echo json_encode(["status" => "error", "message" => "Lỗi khi thực thi câu lệnh thêm: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>