<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Aroma Coffee' ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Style -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Top Bar -->
    <div class="bg-dark text-white py-1 small d-none d-md-block">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-telephone-fill me-1"></i> Hotline: 090 123 4567 | 
                <i class="bi bi-envelope-fill ms-2 me-1"></i> support@aromacoffee.vn
            </div>
            <div>
                <span>Miễn phí vận chuyển cho đơn hàng từ 300.000đ</span>
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-cafe fs-4" href="<?= BASE_URL ?>/index.php">
                <i class="bi bi-cup-hot-fill text-warning me-1"></i> Aroma Coffee
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php">Trang chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php">Sản phẩm</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/news.php">Tin tức</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/contact.php">Liên hệ</a></li>
                </ul>

                <form class="d-flex me-3" action="<?= BASE_URL ?>/products.php" method="GET">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm cà phê..." value="<?= e($_GET['search'] ?? '') ?>">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>

                <div class="d-flex align-items-center">
                    <a href="<?= BASE_URL ?>/cart.php" class="btn btn-outline-cafe btn-sm me-2 position-relative">
                        <i class="bi bi-cart3"></i> Giỏ hàng
                        <?php 
                        if (is_logged_in()) {
                            // SỬA: COUNT(id) để đếm số mặt hàng khác nhau trong giỏ
                            $stmt_c_count = $conn->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
                            $stmt_c_count->execute([$_SESSION['user_id']]);
                            $cart_count = $stmt_c_count->fetchColumn() ?? 0;
                            if ($cart_count > 0) {
                                echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">' . $cart_count . '</span>';
                            }
                        }
                        ?>
                    </a>

                    <?php if (is_logged_in()): ?>
                        <div class="dropdown">
                            <button class="btn btn-cafe btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i> <?= e($_SESSION['user_name']) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <?php if (is_admin()): ?>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/index.php"><i class="bi bi-speedometer2 me-2"></i>Trang quản trị</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile.php"><i class="bi bi-person me-2"></i>Tài khoản của tôi</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/orders.php"><i class="bi bi-box-seam me-2"></i>Đơn hàng của tôi</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/login.php" class="btn btn-cafe btn-sm me-1">Đăng nhập</a>
                        <a href="<?= BASE_URL ?>/register.php" class="btn btn-outline-cafe btn-sm">Đăng ký</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Toast Notification Container Client (Tự động ẩn sau 3.5s) -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <?php if (isset($_SESSION['success'])): ?>
            <div id="flashToast" class="toast align-items-center text-white bg-success border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3500">
                <div class="d-flex">
                    <div class="toast-body fs-6">
                        <i class="bi bi-check-circle-fill me-2"></i><?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div id="flashToast" class="toast align-items-center text-white bg-danger border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3500">
                <div class="d-flex">
                    <div class="toast-body fs-6">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var toastEl = document.getElementById('flashToast');
        if (toastEl) {
            var toast = new bootstrap.Toast(toastEl, { delay: 3500 });
            toast.show();
        }
    });
    </script>