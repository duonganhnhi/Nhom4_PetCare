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
    !isset($data['date'], $data['time'], $data['name'], $data['phone'], $data['pets'], $data['serviceName'], $data['servicePrice'], $data['weight'], $data['paymentMethod'], $data['paymentStatus'], $data['username']) ||
    !is_array($data['pets'])
) {
    die(json_encode(["status" => "error", "message" => "Thiếu hoặc không hợp lệ dữ liệu đầu vào."]));
}

// Trích xuất thông tin
$date = str_replace('/', '-', $data['date']); // Convert 'YYYY/MM/DD' to 'YYYY-MM-DD'
$time = $data['time'];
$name = $data['name'];
$phone = $data['phone'];
$pets = json_encode($data['pets']); // Encode mảng pets thành JSON
$serviceName = $data['serviceName'];
$servicePrice = $data['servicePrice'];
$weight = $data['weight'];
$paymentMethod = $data['paymentMethod'];
$paymentStatus = $data['paymentStatus'];
$username = $data['username'];

// Kiểm tra ngày và giờ hợp lệ
$currentDateTime = new DateTime();
$appointmentDateTime = DateTime::createFromFormat('Y-m-d H:i', "$date $time");

if (!$appointmentDateTime) {
    die(json_encode(["status" => "error", "message" => "Định dạng ngày hoặc giờ không hợp lệ."]));
}

// Kiểm tra nếu đặt trong quá khứ
if ($appointmentDateTime < $currentDateTime) {
    die(json_encode(["status" => "error", "message" => "Ngày hoặc giờ không được trong quá khứ."]));
}

// Kiểm tra nếu ngày hẹn là ngày hiện tại
if ($appointmentDateTime->format('Y-m-d') === $currentDateTime->format('Y-m-d')) {
    if ((int)$appointmentDateTime->format('H') < (int)$currentDateTime->format('H') ||
        ((int)$appointmentDateTime->format('H') === (int)$currentDateTime->format('H') &&
         (int)$appointmentDateTime->format('i') <= (int)$currentDateTime->format('i'))) {
        die(json_encode(["status" => "error", "message" => "Giờ đặt trong ngày hiện tại phải sau giờ hiện tại."]));
    }
}


// Lấy giờ từ thời gian hẹn
$hour = (int) $appointmentDateTime->format('H');

// Kiểm tra giờ có nằm trong khung hợp lệ (7:00 - 21:00)
if ($hour < 7 || $hour >= 21) {
    die(json_encode(["status" => "error", "message" => "Giờ đặt lịch phải từ 7 giờ sáng đến 9 giờ tối."]));
}

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
        // Có lịch trùng lặp
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

    $stmt->bind_param("sssssssssss", $date, $time, $name, $phone, $pets, $serviceName, $servicePrice, $weight, $paymentMethod, $paymentStatus, $username);

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
?>
