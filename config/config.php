<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Định nghĩa URL gốc của dự án
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/cafe_shop');
}

// Thiết lập múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Luôn gọi file kết nối CSDL ở đây
require_once __DIR__ . '/database.php';
?>