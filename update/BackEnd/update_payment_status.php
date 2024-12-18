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

// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Lấy dữ liệu từ yêu cầu
    $appointmentId = $data['id'] ?? null;
    $paymentStatus = $data['payment_status'] ?? null;
    $username = $data['username'] ?? null; // Thêm username từ input
    $servicePrice = $data['servicePrice'] ?? null; // Thêm servicePrice từ input

    if ($appointmentId && $paymentStatus) {
        // Chuẩn bị truy vấn cập nhật trạng thái thanh toán
        $stmt = $conn->prepare("UPDATE appointments SET payment_status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('si', $paymentStatus, $appointmentId);

            if ($stmt->execute()) {
                // Nếu trạng thái là "Hoàn thành", cập nhật amount trong bảng users
                if ($paymentStatus === 'Hoàn thành' && $username && $servicePrice) {
                    $stmt->close();

                    // Lấy số tiền hiện tại của người dùng
                    $query = "SELECT amount FROM users WHERE username = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $stmt->bind_result($currentAmount);
                    if ($stmt->fetch()) {
                        $stmt->close();

                        // Cộng thêm số tiền
                        $newAmount = $currentAmount + $servicePrice;

                        // Cập nhật số tiền
                        $updateQuery = "UPDATE users SET amount = ? WHERE username = ?";
                        $stmt = $conn->prepare($updateQuery);
                        $stmt->bind_param("ds", $newAmount, $username);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true, "message" => "Cập nhật thành công"]);
                        } else {
                            echo json_encode(["success" => false, "message" => "Cập nhật số tiền thất bại", "error" => $stmt->error]);
                        }
                    } else {
                        echo json_encode(["success" => false, "message" => "Người dùng không tồn tại"]);
                    }
                } else {
                    echo json_encode(["success" => true, "message" => "Cập nhật trạng thái thành công"]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Cập nhật trạng thái thất bại', 'error' => $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Chuẩn bị truy vấn thất bại', 'error' => $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
}
$conn->close();
?>