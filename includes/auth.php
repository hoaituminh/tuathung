<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Kiểm tra xem người dùng đã đăng nhập chưa
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Kiểm tra xem người dùng hiện tại có phải là admin không
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Yêu cầu đăng nhập (dùng cho trang cá nhân, giỏ hàng, thanh toán...)
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['error'] = 'Vui lòng đăng nhập để tiếp tục.';
        redirect('login.php');
    }
}

/**
 * Yêu cầu quyền admin (dùng cho khu vực quản trị)
 */
function require_admin() {
    if (!is_logged_in() || !is_admin()) {
        $_SESSION['error'] = 'Bạn không có quyền truy cập khu vực này.';
        redirect('login.php');
    }
}

/**
 * Lấy thông tin user hiện tại
 */
function get_current_user_info($conn) {
    if (!is_logged_in()) return null;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
?>