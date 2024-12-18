<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require 'db_connection.php';

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Kết nối không thành công: " . $conn->connect_error]));
}

// Nhận dữ liệu từ POST request
$data = json_decode(file_get_contents("php://input"), true);

// Kiểm tra dữ liệu đầu vào
if (
    !isset($data['id']) || !isset($data['date']) || !isset($data['time']) ||
    !isset($data['name']) || !isset($data['phone']) || !isset($data['pets']) ||
    !isset($data['doctorName']) || !isset($data['finalPrice']) || !isset($data['weight']) ||
    !isset($data['text']) || !isset($data['paymentStatus']) || !isset($data['username'])
) {
    die(json_encode(["status" => "error", "message" => "Thiếu dữ liệu đầu vào"]));
}

// Trích xuất thông tin
$id = $data['id'];
$date = $data['date'];
$time = $data['time'];
$name = $data['name'];
$phone = $data['phone'];
$pets = is_array($data['pets']) ? json_encode($data['pets']) : $data['pets'];
$doctorName = $data['doctorName'];
$finalPrice = $data['finalPrice'];
$weight = $data['weight'];
$text = $data['text'];
$paymentStatus = $data['paymentStatus'];
$username = $data['username'];

// Thực hiện truy vấn UPDATE
$sql = "UPDATE appointmentsdoc SET 
        date = ?, 
        time = ?, 
        name = ?, 
        phone = ?, 
        pets = ?, 
        doctorName = ?, 
        finalPrice = ?, 
        weight = ?, 
        text = ?, 
        payment_status = ?, 
        username = ? 
        WHERE id = ?";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Lỗi khi chuẩn bị câu lệnh: " . $conn->error);
    }

    $stmt->bind_param(
        "sssssssssssi",
        $date,
        $time,
        $name,
        $phone,
        $pets,
        $doctorName,
        $finalPrice,
        $weight,
        $text,
        $paymentStatus,
        $username,
        $id
    );

    if ($stmt->execute()) {
        if ($paymentStatus === 'Hoàn thành') {
            // Xử lý cập nhật amount như cũ
            $query = "SELECT amount FROM users WHERE username = ?";
            $stmt2 = $conn->prepare($query);
            $stmt2->bind_param("s", $username);
            $stmt2->execute();
            $result = $stmt2->get_result();
            $row = $result->fetch_assoc();
            $currentAmount = $row['amount'];

            $newAmount = $currentAmount + floatval($finalPrice);

            $updateQuery = "UPDATE users SET amount = ? WHERE username = ?";
            $stmt3 = $conn->prepare($updateQuery);
            $stmt3->bind_param("ds", $newAmount, $username);
            $stmt3->execute();
        }

        echo json_encode(["status" => "success"]);
    } else {
        throw new Exception("Lỗi khi thực hiện câu lệnh: " . $stmt->error);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
} finally {
    if (isset($stmt))
        $stmt->close();
    if (isset($stmt2))
        $stmt2->close();
    if (isset($stmt3))
        $stmt3->close();
    $conn->close();
}
?>