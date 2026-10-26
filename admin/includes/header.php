<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin(); // Kiểm tra quyền quản trị viên
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Admin Dashboard - Aroma Coffee' ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --cafe-dark: #2C1D11;
            --cafe-brown: #4A3525;
            --cafe-accent: #8C6D53;
            --cafe-gold: #D4A373;
            --cafe-cream: #FAEDCD;
            --cafe-bg: #F4F1EA;
        }
        body {
            background-color: var(--cafe-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--cafe-dark) 0%, #1A110B 100%);
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar .brand-title {
            color: var(--cafe-gold);
            font-size: 1.35rem;
            letter-spacing: 0.5px;
        }
        .sidebar .nav-link {
            color: #C5B8AC;
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 4px;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #FFF;
            background-color: var(--cafe-accent);
            transform: translateX(4px);
        }
        .main-content {
            padding: 28px 32px;
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            background-color: #FFF;
        }
        .table-custom {
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        .table-custom tbody tr {
            background-color: #FFF;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            transition: transform 0.15s ease;
        }
        .table-custom tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .btn-cafe {
            background-color: var(--cafe-brown);
            color: #FFF;
            border: none;
            font-weight: 600;
        }
        .btn-cafe:hover {
            background-color: var(--cafe-dark);
            color: #FFF;
        }
        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .img-preview-box {
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #E9E2D0;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse p-3">
            <a href="<?= BASE_URL ?>/admin/index.php" class="text-decoration-none brand-title fw-bold d-flex align-items-center mb-4 ps-2 pt-2">
                <i class="bi bi-cup-hot-fill text-warning me-2 fs-3"></i> Aroma Admin
            </a>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="<?= BASE_URL ?>/admin/index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li class="nav-item"><a href="<?= BASE_URL ?>/admin/categories.php" class="nav-link"><i class="bi bi-folder-fill me-2"></i>Danh mục</a></li>
                <li class="nav-item"><a href="<?= BASE_URL ?>/admin/products.php" class="nav-link"><i class="bi bi-box-seam-fill me-2"></i>Sản phẩm</a></li>
                <li class="nav-item"><a href="<?= BASE_URL ?>/admin/orders.php" class="nav-link"><i class="bi bi-receipt-cutoff me-2"></i>Đơn hàng</a></li>
                <li class="nav-item"><a href="<?= BASE_URL ?>/admin/users.php" class="nav-link"><i class="bi bi-people-fill me-2"></i>Khách hàng</a></li>
                <li class="nav-item"><a href="<?= BASE_URL ?>/admin/coupons.php" class="nav-link"><i class="bi bi-ticket-perforated-fill me-2"></i>Mã giảm giá</a></li>
                <li class="nav-item"><a href="<?= BASE_URL ?>/admin/reviews.php" class="nav-link"><i class="bi bi-star-fill me-2"></i>Đánh giá</a></li>
                <li class="nav-item"><a href="<?= BASE_URL ?>/admin/news.php" class="nav-link"><i class="bi bi-newspaper me-2"></i>Tin tức</a></li>
                <li class="nav-item"><a href="<?= BASE_URL ?>/admin/contacts.php" class="nav-link"><i class="bi bi-envelope-paper-fill me-2"></i>Liên hệ</a></li>
                <li class="nav-item"><a href="<?= BASE_URL ?>/admin/reports.php" class="nav-link"><i class="bi bi-bar-chart-line-fill me-2"></i>Thống kê</a></li>
                <hr class="text-secondary my-3">
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php" target="_blank" class="nav-link text-warning"><i class="bi bi-globe me-2"></i>Xem Website <i class="bi bi-box-arrow-up-right small ms-1"></i></a></li>
                <li class="nav-item"><a href="<?= BASE_URL ?>/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
            </ul>
        </nav>

        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 main-content">

            <!-- Toast Notification Container (Admin tự ẩn sau 3.5s) -->
            <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
                <?php if (isset($_SESSION['success'])): ?>
                    <div id="adminToast" class="toast align-items-center text-white bg-success border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3500">
                        <div class="d-flex">
                            <div class="toast-body fs-6">
                                <i class="bi bi-check-circle-fill me-2"></i><?= $_SESSION['success']; unset($_SESSION['success']); ?>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div id="adminToast" class="toast align-items-center text-white bg-danger border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3500">
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
                var toastEl = document.getElementById('adminToast');
                if (toastEl) {
                    var toast = new bootstrap.Toast(toastEl, { delay: 3500 });
                    toast.show();
                }
            });
            </script>