<?php
require_once 'includes/functions.php';
require_login();

$order_code = $_GET['code'] ?? '';
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_code = ? AND user_id = ?");
$stmt->execute([$order_code, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    redirect('index.php');
}

$page_title = 'Đặt hàng thành công - Aroma Coffee';
include 'includes/header.php';
?>

<div class="container my-5 text-center">
    <div class="card border-0 shadow-sm p-5 col-md-8 mx-auto">
        <div class="mb-4 text-success">
            <i class="bi bi-check-circle-fill display-1"></i>
        </div>
        <h2 class="fw-bold text-cafe mb-3">Đặt Hàng Thành Công!</h2>
        <p class="text-muted mb-4">Cảm ơn bạn đã đặt hàng tại Aroma Coffee. Đơn hàng của bạn đã được ghi nhận và đang chờ xử lý.</p>
        
        <div class="bg-light p-3 rounded mb-4 text-start">
            <p class="mb-1"><strong>Mã đơn hàng:</strong> <span class="text-danger fw-bold"><?= e($order['order_code']) ?></span></p>
            <p class="mb-1"><strong>Người nhận:</strong> <?= e($order['receiver_name']) ?> (<?= e($order['receiver_phone']) ?>)</p>
            <p class="mb-1"><strong>Địa chỉ giao:</strong> <?= e($order['shipping_address']) ?></p>
            <p class="mb-0"><strong>Tổng tiền:</strong> <span class="text-danger fw-bold"><?= format_currency($order['total_amount']) ?></span></p>
        </div>

        <div class="d-flex justify-content-center gap-3">
            <a href="orders.php" class="btn btn-outline-cafe">Xem lịch sử đơn hàng</a>
            <a href="index.php" class="btn btn-cafe">Về trang chủ</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>