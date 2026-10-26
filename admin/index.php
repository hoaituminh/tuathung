<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$page_title = 'Dashboard Quản Trị - Aroma Coffee';
include __DIR__ . '/includes/header.php';

// Thống kê tổng quan
$total_revenue = $conn->query("SELECT SUM(total_amount) FROM orders WHERE order_status = 'completed'")->fetchColumn() ?? 0;
$total_orders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?? 0;
$total_customers = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn() ?? 0;
$total_products = $conn->query("SELECT COUNT(*) FROM products WHERE status = 1")->fetchColumn() ?? 0;

// Đơn hàng mới nhất
$recent_orders = $conn->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC LIMIT 5")->fetchAll();

// Sản phẩm sắp hết hàng (stock < 10)
$low_stock_products = $conn->query("SELECT * FROM products WHERE stock < 10 AND status = 1 ORDER BY stock ASC LIMIT 5")->fetchAll();

// Dữ liệu doanh thu 7 ngày gần nhất cho Chart.js
$chart_days = [];
$chart_revenue = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_days[] = date('d/m', strtotime($date));
    
    $stmt_rev = $conn->prepare("SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) = ? AND order_status = 'completed'");
    $stmt_rev->execute([$date]);
    $chart_revenue[] = $stmt_rev->fetchColumn() ?? 0;
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 text-cafe fw-bold"><i class="bi bi-speedometer2 me-2"></i>Dashboard Quản Trị</h1>
</div>

<!-- Thống kê Card -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white border-0 shadow-sm p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1 small">Tổng Doanh Thu</h6>
                    <h4 class="fw-bold mb-0"><?= format_currency($total_revenue) ?></h4>
                </div>
                <i class="bi bi-currency-dollar fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white border-0 shadow-sm p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1 small">Tổng Đơn Hàng</h6>
                    <h4 class="fw-bold mb-0"><?= number_format($total_orders) ?></h4>
                </div>
                <i class="bi bi-cart-check fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark border-0 shadow-sm p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1 small">Khách Hàng</h6>
                    <h4 class="fw-bold mb-0"><?= number_format($total_customers) ?></h4>
                </div>
                <i class="bi bi-people fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white border-0 shadow-sm p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1 small">Sản Phẩm</h6>
                    <h4 class="fw-bold mb-0"><?= number_format($total_products) ?></h4>
                </div>
                <i class="bi bi-box-seam fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<!-- Biểu đồ doanh thu Chart.js -->
<div class="card border-0 shadow-sm p-4 mb-4">
    <h5 class="fw-bold text-cafe mb-3"><i class="bi bi-bar-chart-line me-2"></i>Biểu Đồ Doanh Thu 7 Ngày Gần Nhất</h5>
    <canvas id="revenueChart" style="max-height: 300px;"></canvas>
</div>

<div class="row">
    <!-- Đơn hàng mới nhất -->
    <div class="col-md-7 mb-4">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-cafe m-0">Đơn Hàng Mới Nhất</h5>
                <a href="orders.php" class="btn btn-sm btn-outline-cafe">Xem tất cả</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $ord): ?>
                            <tr>
                                <td><a href="order-detail.php?id=<?= $ord['id'] ?>" class="fw-bold text-cafe"><?= e($ord['order_code']) ?></a></td>
                                <td><?= e($ord['full_name']) ?></td>
                                <td><?= format_currency($ord['total_amount']) ?></td>
                                <td><span class="badge bg-secondary"><?= ucfirst($ord['order_status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sản phẩm sắp hết hàng -->
    <div class="col-md-5 mb-4">
        <div class="card border-0 shadow-sm p-3">
            <h5 class="fw-bold text-cafe mb-3">Sản Phẩm Sắp Hết Hàng</h5>
            <ul class="list-group list-group-flush">
                <?php foreach ($low_stock_products as $p): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div class="d-flex align-items-center">
                            <img src="<?= e($p['image']) ?>" style="width: 40px; height: 40px; object-fit: cover;" class="rounded me-2">
                            <span class="small fw-bold"><?= e($p['name']) ?></span>
                        </div>
                        <span class="badge bg-danger">Còn <?= $p['stock'] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_days) ?>,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?= json_encode($chart_revenue) ?>,
            borderColor: '#4A3525',
            backgroundColor: 'rgba(74, 53, 37, 0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>