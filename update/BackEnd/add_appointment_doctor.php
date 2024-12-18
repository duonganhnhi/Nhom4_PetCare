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
    !isset($data['date'], $data['time'], $data['name'], $data['phone'], $data['pets'], $data['doctorName'], $data['finalPrice'], $data['weight'], $data['text'], $data['paymentStatus'], $data['username']) ||
    !is_array($data['pets'])
) {
    die(json_encode(["status" => "error", "message" => "Thiếu hoặc không hợp lệ dữ liệu đầu vào."]));
}

// Trích xuất thông tin
$date = $data['date'];
$time = $data['time'];
$name = $data['name'];
$phone = $data['phone'];
$pets = json_encode($data['pets']); // Encode mảng pets thành JSON
$doctorName = $data['doctorName'];
$finalPrice = $data['finalPrice'];
$weight = $data['weight'];
$text = $data['text'];
$paymentStatus = $data['paymentStatus'];
$username = $data['username'];

// Thực hiện truy vấn INSERT
$sql = "INSERT INTO appointmentsdoc (date, time, name, phone, pets, doctorName, finalPrice, weight, text, payment_status, username)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Lỗi khi chuẩn bị câu lệnh: " . $conn->error);
    }

    $stmt->bind_param("sssssssssss", $date, $time, $name, $phone, $pets, $doctorName, $finalPrice, $weight, $text, $paymentStatus, $username);

    if ($stmt->execute()) {
        // Kiểm tra paymentStatus và cập nhật amount nếu là 'hoàn thành'
        if ($paymentStatus === 'Hoàn thành') {
            // Lấy số tiền người dùng hiện tại
            $query = "SELECT amount FROM users WHERE username = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->bind_result($currentAmount);
            $stmt->fetch();
            $stmt->close();

            // Cộng số tiền đơn vào amount
            $newAmount = $currentAmount + $servicePrice;

            $updateQuery = "UPDATE users SET amount = ? WHERE username = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ds", $newAmount, $username);
            $stmt->execute();
            $stmt->close();
        }

        echo json_encode(["status" => "success"]);
    } else {
        throw new Exception("Lỗi khi thực hiện câu lệnh: " . $stmt->error);
    }

    $stmt->close();
} catch (Exception $e) {
    die(json_encode(["status" => "error", "message" => $e->getMessage()]));
} finally {
    $conn->close();
}
?>