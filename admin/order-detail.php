<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = 'Đơn hàng không tồn tại.';
    redirect('admin/orders.php');
}

// Xử lý cập nhật trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $new_order_status = $_POST['order_status'] ?? 'pending';
        $new_payment_status = $_POST['payment_status'] ?? 'unpaid';

        // Nếu chuyển sang trạng thái Hủy (cancelled) và trước đó chưa hủy -> Hoàn tồn kho
        if ($new_order_status === 'cancelled' && $order['order_status'] !== 'cancelled') {
            $stmt_det = $conn->prepare("SELECT product_id, quantity FROM order_details WHERE order_id = ?");
            $stmt_det->execute([$id]);
            $details = $stmt_det->fetchAll();

            foreach ($details as $det) {
                $stmt_back = $conn->prepare("UPDATE products SET stock = stock + ?, sold = GREATEST(0, sold - ?) WHERE id = ?");
                $stmt_back->execute([$det['quantity'], $det['quantity'], $det['product_id']]);
            }
        }

        $stmt_up = $conn->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?");
        $stmt_up->execute([$new_order_status, $new_payment_status, $id]);

        $_SESSION['success'] = 'Cập nhật trạng thái đơn hàng thành công!';
        redirect("admin/order-detail.php?id=$id");
    }
}

// Lấy sản phẩm trong đơn
$stmt_items = $conn->prepare("SELECT * FROM order_details WHERE order_id = ?");
$stmt_items->execute([$id]);
$items = $stmt_items->fetchAll();

$page_title = 'Chi tiết đơn hàng #' . $order['order_code'] . ' - Admin';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 text-cafe fw-bold"><i class="bi bi-receipt me-2"></i>Đơn Hàng #<?= e($order['order_code']) ?></h1>
    <a href="orders.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Quay lại danh sách</a>
</div>

<div class="row">
    <!-- Cập nhật trạng thái -->
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm p-4">
            <h5 class="fw-bold text-cafe mb-3">Xử Lý Đơn Hàng</h5>
            <form action="order-detail.php?id=<?= $id ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <input type="hidden" name="update_status" value="1">

                <div class="mb-3">
                    <label class="form-label fw-bold">Trạng thái đơn hàng</label>
                    <select name="order_status" class="form-select">
                        <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>Chờ xác nhận</option>
                        <option value="confirmed" <?= $order['order_status'] === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                        <option value="shipping" <?= $order['order_status'] === 'shipping' ? 'selected' : '' ?>>Đang giao hàng</option>
                        <option value="completed" <?= $order['order_status'] === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                        <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>Hủy đơn (Hoàn kho)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Trạng thái thanh toán</label>
                    <select name="payment_status" class="form-select">
                        <option value="unpaid" <?= $order['payment_status'] === 'unpaid' ? 'selected' : '' ?>>Chưa thanh toán</option>
                        <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-cafe w-100 fw-bold">Lưu thay đổi</button>
            </form>
        </div>
    </div>

    <!-- Thông tin người nhận và danh sách sản phẩm -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-bold text-cafe mb-3">Thông Tin Người Nhận</h5>
            <p class="mb-1"><strong>Tài khoản đặt:</strong> <?= e($order['full_name']) ?> (<?= e($order['email']) ?>)</p>
            <p class="mb-1"><strong>Người nhận:</strong> <?= e($order['receiver_name']) ?> (<?= e($order['receiver_phone']) ?>)</p>
            <p class="mb-1"><strong>Địa chỉ giao:</strong> <?= e($order['shipping_address']) ?></p>
            <p class="mb-1"><strong>Hình thức TT:</strong> <?= $order['payment_method'] === 'COD' ? 'Thanh toán khi nhận hàng (COD)' : 'Chuyển khoản' ?></p>
            <?php if ($order['note']): ?>
                <p class="mb-0"><strong>Ghi chú:</strong> <?= e($order['note']) ?></p>
            <?php endif; ?>
        </div>

        <div class="card border-0 shadow-sm p-4">
            <h5 class="fw-bold text-cafe mb-3">Sản Phẩm Trong Đơn</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><strong><?= e($item['product_name']) ?></strong></td>
                                <td><?= format_currency($item['price']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td class="fw-bold text-cafe"><?= format_currency($item['price'] * $item['quantity']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <hr>
            <div class="text-end">
                <p class="mb-1">Tạm tính: <strong><?= format_currency($order['total_amount'] - $order['shipping_fee'] + $order['discount_amount']) ?></strong></p>
                <?php if ($order['discount_amount'] > 0): ?>
                    <p class="mb-1 text-danger">Giảm giá: <strong>-<?= format_currency($order['discount_amount']) ?></strong></p>
                <?php endif; ?>
                <p class="mb-1">Phí vận chuyển: <strong><?= format_currency($order['shipping_fee']) ?></strong></p>
                <h4 class="fw-bold text-danger mt-2">Tổng cộng: <?= format_currency($order['total_amount']) ?></h4>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>