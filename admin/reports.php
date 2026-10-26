<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Xuất báo cáo CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=bao_cao_doanh_thu_' . date('Ymd') . '.csv');
    
    $output = fopen('php://output', 'w');
    // Ghi UTF-8 BOM để Excel không bị lỗi phông chữ tiếng Việt
    fputs($output, "\xEF\xBB\xBF");
    fputcsv($output, ['Mã đơn hàng', 'Khách hàng', 'Tổng tiền (VNĐ)', 'Trạng thái', 'Ngày đặt']);

    $stmt_exp = $conn->query("SELECT o.order_code, u.full_name, o.total_amount, o.order_status, o.created_at FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC");
    while ($row = $stmt_exp->fetch()) {
        fputcsv($output, [$row['order_code'], $row['full_name'], $row['total_amount'], $row['order_status'], $row['created_at']]);
    }
    fclose($output);
    exit();
}

// Thống kê theo tháng hiện tại
$current_month = date('m');
$current_year = date('Y');

$stmt_month = $conn->prepare("SELECT SUM(total_amount) FROM orders WHERE MONTH(created_at) = ? AND YEAR(created_at) = ? AND order_status = 'completed'");
$stmt_month->execute([$current_month, $current_year]);
$month_revenue = $stmt_month->fetchColumn() ?? 0;

// Top 5 sản phẩm bán chạy nhất
$top_products = $conn->query("SELECT name, sold, price, image FROM products ORDER BY sold DESC LIMIT 5")->fetchAll();

$page_title = 'Báo cáo thống kê - Aroma Coffee Admin';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h2 class="fw-bold text-dark m-0"><i class="bi bi-bar-chart-line-fill text-warning me-2"></i>Báo Cáo Thống Kê</h2>
    <a href="reports.php?export=csv" class="btn btn-success fw-bold"><i class="bi bi-file-earmark-excel me-1"></i> Xuất Báo Cáo Excel (CSV)</a>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card card-custom p-4 bg-primary text-white">
            <h5><i class="bi bi-calendar-check me-2"></i>Doanh Thu Tháng <?= $current_month ?>/<?= $current_year ?></h5>
            <h2 class="fw-bold mb-0 mt-2"><?= format_currency($month_revenue) ?></h2>
        </div>
    </div>
</div>

<div class="card card-custom p-4">
    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-trophy-fill text-warning me-2"></i>Top 5 Sản Phẩm Bán Chạy Nhất</h5>
    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead class="table-light">
                <tr>
                    <th>Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Đơn giá</th>
                    <th>Đã bán</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_products as $tp): ?>
                    <tr>
                        <td><img src="<?= e($tp['image']) ?>" class="img-preview-box" onerror="this.src='https://placehold.co/100x100?text=Error';"></td>
                        <td><strong><?= e($tp['name']) ?></strong></td>
                        <td><?= format_currency($tp['price']) ?></td>
                        <td><span class="badge bg-success fs-6"><?= $tp['sold'] ?> sản phẩm</span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>