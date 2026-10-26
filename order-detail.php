<?php
require_once 'includes/functions.php';
require_login();

$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = 'Đơn hàng không tồn tại.';
    redirect('orders.php');
}

// Xử lý hủy đơn hàng (chỉ khi đang 'pending')
if (isset($_GET['action']) && $_GET['action'] === 'cancel' && $order['order_status'] === 'pending') {
    try {
        $conn->beginTransaction();

        // Cập nhật trạng thái đơn hàng thành cancelled
        $stmt_up = $conn->prepare("UPDATE orders SET order_status = 'cancelled' WHERE id = ?");
        $stmt_up->execute([$id]);

        // Hoàn lại tồn kho và giảm số lượng bán
        $stmt_det = $conn->prepare("SELECT product_id, quantity FROM order_details WHERE order_id = ?");
        $stmt_det->execute([$id]);
        $details = $stmt_det->fetchAll();

        foreach ($details as $det) {
            $stmt_back = $conn->prepare("UPDATE products SET stock = stock + ?, sold = GREATEST(0, sold - ?) WHERE id = ?");
            $stmt_back->execute([$det['quantity'], $det['quantity'], $det['product_id']]);
        }

        $conn->commit();
        $_SESSION['success'] = 'Đã hủy đơn hàng thành công và hoàn trả tồn kho.';
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = 'Không thể hủy đơn hàng: ' . $e->getMessage();
    }
    redirect("order-detail.php?id=$id");
}

// Lấy chi tiết sản phẩm trong đơn
$stmt_items = $conn->prepare("SELECT * FROM order_details WHERE order_id = ?");
$stmt_items->execute([$id]);
$items = $stmt_items->fetchAll();

$page_title = 'Chi tiết đơn hàng #' . $order['order_code'] . ' - Aroma Coffee';
include 'includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-cafe mb-0"><i class="bi bi-receipt me-2"></i>Chi Tiết Đơn Hàng #<?= e($order['order_code']) ?></h2>
        <a href="orders.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Quay lại danh sách</a>
    </div>

    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-bold text-cafe mb-3">Danh Sách Sản Phẩm</h5>
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
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-bold text-cafe mb-3">Thông Tin Đơn Hàng</h5>
                <p class="mb-2"><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                <p class="mb-2"><strong>Người nhận:</strong> <?= e($order['receiver_name']) ?></p>
                <p class="mb-2"><strong>Số điện thoại:</strong> <?= e($order['receiver_phone']) ?></p>
                <p class="mb-2"><strong>Địa chỉ:</strong> <?= e($order['shipping_address']) ?></p>
                <?php if ($order['note']): ?>
                    <p class="mb-2"><strong>Ghi chú:</strong> <?= e($order['note']) ?></p>
                <?php endif; ?>
                <p class="mb-2"><strong>Thanh toán:</strong> <?= $order['payment_method'] === 'COD' ? 'Thanh toán khi nhận hàng (COD)' : 'Chuyển khoản' ?></p>
                <p class="mb-3"><strong>Trạng thái:</strong> 
                    <span class="badge bg-secondary"><?= ucfirst($order['order_status']) ?></span>
                </p>

                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span>Tạm tính:</span>
                    <span><?= format_currency($order['total_amount'] - $order['shipping_fee'] + $order['discount_amount']) ?></span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span>Giảm giá:</span>
                        <span>-<?= format_currency($order['discount_amount']) ?></span>
                    </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between mb-3">
                    <span>Phí ship:</span>
                    <span><?= format_currency($order['shipping_fee']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold fs-5">Tổng tiền:</span>
                    <span class="fw-bold fs-4 text-danger"><?= format_currency($order['total_amount']) ?></span>
                </div>

                <?php if ($order['order_status'] === 'pending'): ?>
                    <a href="order-detail.php?id=<?= $id ?>&action=cancel" class="btn btn-outline-danger w-100" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?');">
                        <i class="bi bi-x-circle me-1"></i>Hủy Đơn Hàng
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>