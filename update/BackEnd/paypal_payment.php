<?php
// paypal_payment.php

// Định nghĩa thông tin CLIENT_ID và SECRET
define('PAYPAL_CLIENT_ID', 'AfUKYdjskg7QFJHWSUAAnnEPqhV9FF5GfBszBaCGy_zz5FoFiwdecr2n_L36GAMt1yN5DqiITb8QgIxw'); // Thay bằng CLIENT_ID của bạn
define('PAYPAL_SECRET', 'YOUR_PAYPAL_SECRET'); // Thay bằng SECRET của bạn
define('PAYPAL_BASE_URL', 'https://api-m.sandbox.paypal.com'); // Dùng URL này cho sandbox; thay bằng 'https://api-m.paypal.com' nếu dùng môi trường live.

// Cấu hình CORS để cho phép truy cập từ ứng dụng React
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Hàm lấy Access Token từ PayPal
function getAccessToken()
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_BASE_URL . "/v1/oauth2/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ":" . PAYPAL_SECRET);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Accept: application/json",
        "Accept-Language: en_US"
    ));

    $result = curl_exec($ch);
    curl_close($ch);

    if (empty($result)) {
        return null;
    } else {
        $json = json_decode($result);
        return $json->access_token;
    }
}

// Hàm tạo thanh toán trên PayPal
function createPayment($amount, $currency, $returnUrl, $cancelUrl)
{
    $accessToken = getAccessToken();
    if (!$accessToken) {
        return null;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_BASE_URL . "/v1/payments/payment");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "Authorization: Bearer $accessToken"
    ));

    $postData = json_encode(array(
        "intent" => "sale",
        "payer" => array(
            "payment_method" => "paypal"
        ),
        "transactions" => array(
            array(
                "amount" => array(
                    "total" => $amount,
                    "currency" => $currency
                ),
                "description" => "Payment for service"
            )
        ),
        "redirect_urls" => array(
            "return_url" => $returnUrl,
            "cancel_url" => $cancelUrl
        )
    ));

    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    $result = curl_exec($ch);
    curl_close($ch);

    if (empty($result)) {
        return null;
    } else {
        return json_decode($result);
    }
}

// Xử lý yêu cầu POST từ ứng dụng React
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Đọc dữ liệu từ yêu cầu
    $data = json_decode(file_get_contents('php://input'), true);
    $amount = $data['amount'];
    $currency = $data['currency'];
    $returnUrl = $data['returnUrl'];
    $cancelUrl = $data['cancelUrl'];

    // Tạo thanh toán
    $payment = createPayment($amount, $currency, $returnUrl, $cancelUrl);

    if ($payment) {
        // Trả về phản hồi JSON cho ứng dụng React
        echo json_encode($payment);
    } else {
        // Trả về lỗi nếu không tạo được thanh toán
        echo json_encode(array('error' => 'Unable to create payment.'));
    }
}
?>