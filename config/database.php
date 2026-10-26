<?php
// Bật hiển thị lỗi để bắt bệnh trang trắng
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$db   = 'cafe_shop';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo "<div style='color: red; font-family: sans-serif; padding: 20px; background: #fff3f3; border: 1px solid #ffcdd2; border-radius: 5px; margin: 20px;'>
            <h3 style='margin-top:0;'>Lỗi kết nối cơ sở dữ liệu!</h3>
            <p><strong>Chi tiết lỗi:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            <p><b>Gợi ý:</b> Hãy kiểm tra xem bạn đã tạo database tên là <code>cafe_shop</code> và import file <code>database.sql</code> trong phpMyAdmin chưa nhé.</p>
         </div>";
    exit;
}
?>