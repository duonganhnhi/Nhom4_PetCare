<?php
// Bật báo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
// Allow specific HTTP methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Kết nối đến cơ sở dữ liệu
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Respond to preflight request
    http_response_code(200);
    exit();
}

// Kiểm tra kết nối
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Kết nối không thành công: " . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Xử lý yêu cầu tạo lịch hẹn mới
    $data = json_decode(file_get_contents('php://input'), true);

    // Kiểm tra đầu vào
    $required_fields = ['name', 'phone', 'date', 'time', 'weight', 'services', 'paymentMethod', 'username'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            echo json_encode(["status" => "error", "message" => "Thiếu trường dữ liệu: $field"]);
            exit;
        }
    }

    // Lấy dữ liệu từ yêu cầu
    $name = $conn->real_escape_string($data['name']);
    $phone = $conn->real_escape_string($data['phone']);
    $date = $conn->real_escape_string($data['date']);
    $time = $conn->real_escape_string($data['time']);
    $weight = $conn->real_escape_string($data['weight']);
    $services = $data['services']; // Đây là mảng các dịch vụ
    $paymentMethod = $conn->real_escape_string($data['paymentMethod']);
    $username = $conn->real_escape_string($data['username']);

    // Tính tổng giá dịch vụ dựa trên trọng lượng
    $totalPrice = 0;
    foreach ($services as $service) {
        $prices = explode(',', $service['data_price']); // Chia các mức giá thành mảng
        if ($weight === '5') {
            $totalPrice += (float)$prices[0];
        } elseif ($weight === '6-10') {
            $totalPrice += (float)$prices[1];
        } elseif ($weight === '11-20') {
            $totalPrice += (float)$prices[2];
        }
    }

    // Tạo câu truy vấn INSERT
    $sql = "INSERT INTO appointments (name, phone, date, time, weight, servicePrice, paymentMethod, username) 
            VALUES ('$name', '$phone', '$date', '$time', '$weight', $totalPrice, '$paymentMethod', '$username')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Lịch hẹn được tạo thành công"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Lỗi khi tạo lịch hẹn: " . $conn->error]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Xử lý yêu cầu lấy danh sách lịch hẹn
    $sql = "SELECT id, name, phone, date, time, pets, serviceName, servicePrice, weight, paymentMethod, payment_status, username FROM appointments";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $appointments = [];

        // Duyệt qua từng hàng kết quả và lưu vào mảng $appointments
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }

        // Trả về kết quả dưới dạng JSON
        echo json_encode($appointments);
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Phương thức không được hỗ trợ"]);
}

// Đóng kết nối
$conn->close();
?>
