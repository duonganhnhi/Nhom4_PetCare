<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require 'db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Kiểm tra trạng thái thanh toán của lịch hẹn
        $checkStmt = $pdo->prepare("SELECT payment_status FROM appointments WHERE id = ?");
        $checkStmt->execute([$data['id']]);
        $appointment = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($appointment) {
            $paymentStatus = $appointment['payment_status']; // Trạng thái thanh toán hiện tại

            // Chỉ hủy nếu trạng thái là "Chờ xác nhận", "Đã xác nhận" hoặc "Chờ thực hiện"
            $allowedStatuses = ['Chờ xác nhận', 'Đã xác nhận', 'Chờ thực hiện'];
            if (in_array($paymentStatus, $allowedStatuses)) {
                $updateStmt = $pdo->prepare("UPDATE appointments SET payment_status = 'Huỷ' WHERE id = ?");
                $updateStmt->execute([$data['id']]);

                // Lấy trạng thái sau khi cập nhật
                $updatedStmt = $pdo->prepare("SELECT payment_status FROM appointments WHERE id = ?");
                $updatedStmt->execute([$data['id']]);
                $updatedAppointment = $updatedStmt->fetch(PDO::FETCH_ASSOC);

                echo json_encode([
                    'status' => 'success',
                    'paymentStatus' => $updatedAppointment['payment_status']
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'error' => 'Không thể hủy, trạng thái thanh toán không hợp lệ.',
                    'paymentStatus' => $paymentStatus
                ]);
            }
        } else {
            echo json_encode(['error' => 'Lịch hẹn không tồn tại.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Thiếu ID lịch hẹn.']);
}
?>
