<?php
// Bật báo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Kết nối đến cơ sở dữ liệu
require 'db_connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Kiểm tra kết nối
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Kết nối không thành công: " . $conn->connect_error]));
}

// Nhận dữ liệu từ POST request
$data = json_decode(file_get_contents("php://input"), true);

// Trích xuất thông tin từ dữ liệu nhận được
$date = isset($data['date']) ? $data['date'] : null;
$time = isset($data['time']) ? $data['time'] : null;
$name = isset($data['name']) ? $data['name'] : null;
$phone = isset($data['phone']) ? $data['phone'] : null;
$pets = isset($data['pets']) ? json_encode($data['pets']) : null; // Encode mảng pets thành JSON
$services = isset($data['services']) ? $data['services'] : []; // Mảng dịch vụ đã chọn
$weight = isset($data['weight']) ? $data['weight'] : null;
$paymentMethod = isset($data['paymentMethod']) ? $data['paymentMethod'] : null;
$paymentStatus = isset($data['paymentStatus']) ? $data['paymentStatus'] : "Chưa thanh toán";
$username = isset($data['username']) ? $data['username'] : null;

// Kiểm tra nếu username là null thì dừng lại
if ($username === null) {
    die(json_encode(["status" => "error", "message" => "Username không được để trống."]));
}

// Tính tổng giá dịch vụ dựa trên trọng lượng
$totalPrice = 0;
$serviceNames = [];
foreach ($services as $service) {
    if (isset($service['price']) && isset($service['name'])) {
        $prices = explode(',', $service['price']);
        $serviceNames[] = $service['name']; // Lưu tên dịch vụ
        switch ($weight) {
            case '5kg':
                $totalPrice += isset($prices[0]) ? (int)$prices[0] : 0;
                break;
            case '6-10kg':
                $totalPrice += isset($prices[1]) ? (int)$prices[1] : 0;
                break;
            case '11-20kg':
                $totalPrice += isset($prices[2]) ? (int)$prices[2] : 0;
                break;
        }
    }
}

// Ghép danh sách tên các dịch vụ thành chuỗi
$serviceNamesString = implode(', ', $serviceNames);

// Kiểm tra trùng lặp ngày và giờ
$check_sql = "SELECT * FROM appointments 
              WHERE date = ? 
                AND ABS(TIME_TO_SEC(TIMEDIFF(time, ?))) < 600 
                AND payment_status != 'Hoàn thành'";

try {
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        throw new Exception("Lỗi khi chuẩn bị câu lệnh kiểm tra: " . $conn->error);
    }

    $check_stmt->bind_param("ss", $date, $time);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        die(json_encode(["status" => "error", "message" => "Đã có lịch đặt trùng trong vòng 10 phút. Vui lòng chọn thời gian khác."]));
    }

    $check_stmt->close();
} catch (Exception $e) {
    die(json_encode(["status" => "error", "message" => $e->getMessage()]));
}

// Thực hiện thêm lịch hẹn mới
$insert_sql = "INSERT INTO appointments (date, time, name, phone, pets, serviceName, servicePrice, weight, paymentMethod, payment_status, username)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

try {
    $stmt = $conn->prepare($insert_sql);
    if (!$stmt) {
        throw new Exception("Lỗi khi chuẩn bị câu lệnh thêm: " . $conn->error);
    }

    $stmt->bind_param("sssssssssss", $date, $time, $name, $phone, $pets, $serviceNamesString, $totalPrice, $weight, $paymentMethod, $paymentStatus, $username);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Đặt lịch thành công."]);
    } else {
        throw new Exception("Lỗi khi thực thi câu lệnh thêm: " . $stmt->error);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
} finally {
    $conn->close();
}
