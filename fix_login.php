<?php
require_once 'includes/functions.php';

$new_pass = '123456';
$hashed = password_hash($new_pass, PASSWORD_DEFAULT);
$emails = ['admin@cafe.com', 'khach1@cafe.com', 'khach2@cafe.com'];

try {
    // Cập nhật mật khẩu mới cho toàn bộ tài khoản mẫu
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    foreach ($emails as $email) {
        $stmt->execute([$hashed, $email]);
    }
    
    echo "<div style='font-family: Arial; padding: 30px; text-align: center;'>
            <h2 style='color: green;'>✅ Cập nhật mật khẩu thành công cho tất cả tài khoản mẫu!</h2>
            <p>Mật khẩu chung cho các tài khoản: <b>123456</b></p>
            <ul style='list-style: none; padding: 0;'>
                <li>Admin: <code>admin@cafe.com</code> / <code>123456</code></li>
                <li>Khách 1: <code>khach1@cafe.com</code> / <code>123456</code></li>
                <li>Khách 2: <code>khach2@cafe.com</code> / <code>123456</code></li>
            </ul>
            <br>
            <a href='login.php' style='background: #4A3525; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Đến trang đăng nhập ngay</a>
          </div>";
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>