<?php
require_once 'includes/functions.php';
require_login();

$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$page_title = 'Lịch sử đơn hàng - Aroma Coffee';
include 'includes/header.php';
?>

<div class="container my-4">
    <h2 class="fw-bold text-cafe mb-4"><i class="bi bi-box-seam me-2"></i>Đơn Hàng Của Tôi</h2>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">Bạn chưa có đơn hàng nào.</div>
    <?php else: ?>
        <div class="card border-0 shadow-sm p-3">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $ord): ?>
                            <tr>
                                <td><span class="fw-bold text-cafe"><?= e($ord['order_code']) ?></span></td>
                                <td><?= date('d/m/Y H:i', strtotime($ord['created_at'])) ?></td>
                                <td class="fw-bold text-danger"><?= format_currency($ord['total_amount']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $ord['payment_status'] === 'paid' ? 'success' : 'warning text-dark' ?>">
                                        <?= $ord['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        $status_labels = [
                                            'pending' => ['Chờ xác nhận', 'warning text-dark'],
                                            'confirmed' => ['Đã xác nhận', 'info'],
                                            'shipping' => ['Đang giao hàng', 'primary'],
                                            'completed' => ['Hoàn thành', 'success'],
                                            'cancelled' => ['Đã hủy', 'danger']
                                        ];
                                        $st = $status_labels[$ord['order_status']] ?? ['Không rõ', 'secondary'];
                                    ?>
                                    <span class="badge bg-<?= $st[1] ?>"><?= $st[0] ?></span>
                                </td>
                                <td>
                                    <a href="order-detail.php?id=<?= $ord['id'] ?>" class="btn btn-outline-cafe btn-sm"><i class="bi bi-eye"></i> Chi tiết</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>