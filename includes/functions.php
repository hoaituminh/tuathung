<?php
/**
 * Các hàm tiện ích dùng chung cho toàn bộ website
 */

// Luôn gọi file config trước tiên để khởi tạo session, BASE_URL và $conn
require_once __DIR__ . '/../config/config.php';

/**
 * Hàm rút gọn escape HTML chống XSS
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Làm sạch dữ liệu đầu vào chống XSS
 */
function sanitize($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Chuyển hướng trang an toàn
 */
function redirect($url) {
    if (!headers_sent()) {
        header("Location: " . rtrim(BASE_URL, '/') . '/' . ltrim($url, '/'));
    } else {
        echo "<script>window.location.href='" . rtrim(BASE_URL, '/') . '/' . ltrim($url, '/') . "';</script>";
    }
    exit();
}

/**
 * Định dạng tiền tệ VNĐ
 */
function format_currency($amount) {
    return number_format($amount ?? 0, 0, ',', '.') . 'đ';
}

/**
 * Tạo CSRF Token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Kiểm tra CSRF Token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/**
 * Xử lý upload file an toàn (Hỗ trợ tốt JPG, PNG, WEBP)
 */
function upload_file($file, $target_dir) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Không có file được tải lên hoặc lỗi upload.'];
    }

    // Các MIME type hợp lệ cho ảnh
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/x-png'];
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $file_type = $finfo->file($file['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        return ['success' => false, 'message' => 'Chỉ chấp nhận file định dạng ảnh (JPG, PNG, WEBP).'];
    }

    if ($file['size'] > 5 * 1024 * 1024) { // Tăng giới hạn lên 5MB cho thoải mái
        return ['success' => false, 'message' => 'Dung lượng file không được vượt quá 5MB.'];
    }

    // Lấy phần mở rộng chuẩn từ tên file gốc
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
        $extension = 'png'; // Mặc định nếu không khớp
    }

    $new_filename = uniqid('img_', true) . '.' . $extension;
    
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $target_path = rtrim($target_dir, '/') . '/' . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['success' => true, 'filename' => $new_filename];
    }

    return ['success' => false, 'message' => 'Lỗi khi di chuyển file tải lên thư mục đích.'];
}

// Nạp file auth để đồng bộ toàn bộ hàm xác thực người dùng (bao gồm require_admin)
require_once __DIR__ . '/auth.php';
?>