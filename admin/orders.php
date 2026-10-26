<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Lọc trạng thái
$status_filter = $_GET['status'] ?? '';
$where = "1=1";
$params = [];

if ($status_filter) {
    $where .= " AND o.order_status = ?";
    $params[] = $status_filter;
}

$stmt = $conn->prepare("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id WHERE $where ORDER BY o.id DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();

$page_title = 'Quản lý đơn hàng - Aroma Coffee Admin';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 text-cafe fw-bold"><i class="bi bi-receipt me-2"></i>Quản Lý Đơn Hàng</h1>
</div>

<!-- Bộ lọc trạng thái -->
<div class="mb-3 d-flex gap-2 flex-wrap">
    <a href="orders.php" class="btn btn-sm btn-<?= $status_filter === '' ? 'cafe' : 'outline-secondary' ?>">Tất cả</a>
    <a href="orders.php?status=pending" class="btn btn-sm btn-<?= $status_filter === 'pending' ? 'warning' : 'outline-warning text-dark' ?>">Chờ xác nhận</a>
    <a href="orders.php?status=confirmed" class="btn btn-sm btn-<?= $status_filter === 'confirmed' ? 'info' : 'outline-info' ?>">Đã xác nhận</a>
    <a href="orders.php?status=shipping" class="btn btn-sm btn-<?= $status_filter === 'shipping' ? 'primary' : 'outline-primary' ?>">Đang giao</a>
    <a href="orders.php?status=completed" class="btn btn-sm btn-<?= $status_filter === 'completed' ? 'success' : 'outline-success' ?>">Hoàn thành</a>
    <a href="orders.php?status=cancelled" class="btn btn-sm btn-<?= $status_filter === 'cancelled' ? 'danger' : 'outline-danger' ?>">Đã hủy</a>
</div>

<div class="card border-0 shadow-sm p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Mã đơn hàng</th>
                    <th>Khách hàng</th>
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
                        <td><strong class="text-cafe"><?= e($ord['order_code']) ?></strong></td>
                        <td><?= e($ord['full_name']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($ord['created_at'])) ?></td>
                        <td class="fw-bold text-danger"><?= format_currency($ord['total_amount']) ?></td>
                        <td>
                            <span class="badge bg-<?= $ord['payment_status'] === 'paid' ? 'success' : 'secondary' ?>">
                                <?= $ord['payment_status'] === 'paid' ? 'Đã TT' : 'Chưa TT' ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                                $status_labels = [
                                    'pending' => ['Chờ xác nhận', 'warning text-dark'],
                                    'confirmed' => ['Đã xác nhận', 'info'],
                                    'shipping' => ['Đang giao', 'primary'],
                                    'completed' => ['Hoàn thành', 'success'],
                                    'cancelled' => ['Đã hủy', 'danger']
                                ];
                                $st = $status_labels[$ord['order_status']] ?? ['Không rõ', 'secondary'];
                            ?>
                            <span class="badge bg-<?= $st[1] ?>"><?= $st[0] ?></span>
                        </td>
                        <td>
                            <a href="order-detail.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-cafe"><i class="bi bi-eye me-1"></i>Chi tiết / Đổi trạng thái</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>