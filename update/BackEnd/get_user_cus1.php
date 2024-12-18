// Giả sử bạn đang sử dụng MySQL và PDO
<?php
// Kết nối cơ sở dữ liệu
include 'db_connection.php';

if (isset($_GET['username'])) {
    $username = $_GET['username'];

    // Truy vấn dữ liệu theo username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode($user);
    } else {
        echo json_encode(['error' => 'Không tìm thấy người dùng']);
    }
} else {
    echo json_encode(['error' => 'Thiếu thông tin tên người dùng']);
}
?>