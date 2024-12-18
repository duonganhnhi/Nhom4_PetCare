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

// Nhận tên bác sĩ từ tham số GET
$doctorName = isset($_GET['doctorName']) ? $_GET['doctorName'] : '';

// Câu truy vấn SELECT để lấy danh sách các cuộc hẹn của bác sĩ cụ thể
$sql = "SELECT id, name, phone, date, time, pets, doctorName, finalPrice, weight, text, payment_status, username
        FROM appointmentsdoc
        WHERE doctorName = ?";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Lỗi khi chuẩn bị câu lệnh: " . $conn->error);
    }

    // Gán giá trị cho tham số trong câu truy vấn
    $stmt->bind_param("s", $doctorName);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $appointmentsdoc = [];

        while ($row = $result->fetch_assoc()) {
            // Chuyển đổi payment_status thành paymentStatus
            $row['paymentStatus'] = $row['payment_status'];
            unset($row['payment_status']); // Xóa cột payment_status

            $appointmentsdoc[] = $row;
        }

        // Trả về kết quả dưới dạng JSON
        echo json_encode($appointmentsdoc);
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