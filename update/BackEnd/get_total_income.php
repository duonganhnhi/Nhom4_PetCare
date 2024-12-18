<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require 'db_connection.php';

// Kết nối đến database
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

// Tính tổng thu nhập khám (appointmentsdoc với payment_status là "paid")
$sqlConsultation = "SELECT SUM(finalPrice) AS consultationIncome FROM appointmentsdoc WHERE payment_status = 'Hoàn thành'";
$resultConsultation = $conn->query($sqlConsultation);
$consultationIncome = $resultConsultation->fetch_assoc()['consultationIncome'] ?? 0;

// Tính tổng thu nhập spa (appointments với payment_status là "paid")
$sqlSpa = "SELECT SUM(servicePrice) AS spaIncome FROM appointments WHERE payment_status = 'Hoàn thành'";
$resultSpa = $conn->query($sqlSpa);
$spaIncome = $resultSpa->fetch_assoc()['spaIncome'] ?? 0;

// Tính tổng thu nhập
$totalIncome = $consultationIncome + $spaIncome;

// Trả về kết quả dưới dạng JSON
echo json_encode([
    'consultationIncome' => $consultationIncome,
    'spaIncome' => $spaIncome,
    'totalIncome' => $totalIncome
]);

// Đóng kết nối
$conn->close();
?>