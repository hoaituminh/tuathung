<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Xử lý Xóa mã giảm giá
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = 'Đã xóa mã giảm giá.';
    redirect('admin/coupons.php');
}

// Xử lý Thêm mã giảm giá mới
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $discount_type = $_POST['discount_type'] ?? 'percent';
        $discount_value = floatval($_POST['discount_value'] ?? 0);
        $min_order_amount = floatval($_POST['min_order_amount'] ?? 0);
        $start_date = $_POST['start_date'] ?? date('Y-m-d');
        $end_date = $_POST['end_date'] ?? date('Y-m-d', strtotime('+30 days'));

        $stmt = $conn->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$code, $discount_type, $discount_value, $min_order_amount, $start_date, $end_date]);
        $_SESSION['success'] = 'Thêm mã giảm giá mới thành công!';
        redirect('admin/coupons.php');
    }
}

$coupons = $conn->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();

$page_title = 'Quản lý mã giảm giá - Aroma Coffee Admin';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h2 class="fw-bold text-dark m-0"><i class="bi bi-ticket-perforated-fill text-warning me-2"></i>Quản Lý Mã Giảm Giá</h2>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card card-custom p-4">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-plus-circle-fill me-2 text-warning"></i>Tạo Mã Giảm Giá</h5>
            <form action="coupons.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Mã Coupon <span class="text-danger">*</span></label>
                    <input type="text" class="form-control text-uppercase fw-bold" name="code" required placeholder="VD: CAFE2026">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Loại giảm giá</label>
                    <select name="discount_type" class="form-select">
                        <option value="percent">Phần trăm (%)</option>
                        <option value="fixed">Số tiền cố định (VNĐ)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Giá trị giảm <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="discount_value" required placeholder="VD: 10 hoặc 50000">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Đơn tối thiểu (VNĐ)</label>
                    <input type="number" class="form-control" name="min_order_amount" value="0">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ngày bắt đầu</label>
                    <input type="date" class="form-control" name="start_date" value="<?= date('Y-m-d') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ngày kết thúc</label>
                    <input type="date" class="form-control" name="end_date" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                </div>

                <button type="submit" class="btn btn-cafe w-100 py-2 mt-2"><i class="bi bi-floppy-fill me-1"></i> Tạo mã</button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card card-custom p-4">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-list-stars me-2 text-warning"></i>Danh Sách Mã Giảm Giá</h5>
            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã</th>
                            <th>Mức giảm</th>
                            <th>Đơn tối thiểu</th>
                            <th>Hạn dùng</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $c): ?>
                            <tr>
                                <td><strong class="text-cafe fs-6"><?= e($c['code']) ?></strong></td>
                                <td>
                                    <span class="badge bg-danger">
                                        <?= $c['discount_type'] === 'percent' ? $c['discount_value'] . '%' : format_currency($c['discount_value']) ?>
                                    </span>
                                </td>
                                <td><?= format_currency($c['min_order_amount']) ?></td>
                                <td><small><?= date('d/m/Y', strtotime($c['start_date'])) ?> - <?= date('d/m/Y', strtotime($c['end_date'])) ?></small></td>
                                <td>
                                    <a href="coupons.php?action=delete&id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xóa mã này?');"><i class="bi bi-trash-fill"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>