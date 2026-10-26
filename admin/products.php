<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Xử lý Xóa sản phẩm
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $prod_id = intval($_GET['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$prod_id]);
    $_SESSION['success'] = 'Đã xóa sản phẩm.';
    redirect('admin/products.php');
}

// Lấy danh sách sản phẩm
$products = $conn->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();

$page_title = 'Quản lý sản phẩm - Aroma Coffee Admin';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 text-cafe fw-bold"><i class="bi bi-box-seam me-2"></i>Quản Lý Sản Phẩm</h1>
    <a href="product-edit.php" class="btn btn-cafe"><i class="bi bi-plus-lg me-1"></i>Thêm Sản Phẩm Mới</a>
</div>

<div class="card border-0 shadow-sm p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <th>Tồn kho</th>
                    <th>Đã bán</th>
                    <th>Nổi bật</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><img src="<?= e($p['image']) ?>" style="width: 50px; height: 50px; object-fit: cover;" class="rounded"></td>
                        <td><strong><?= e($p['name']) ?></strong></td>
                        <td><span class="badge bg-secondary"><?= e($p['category_name']) ?></span></td>
                        <td>
                            <?php if ($p['sale_price']): ?>
                                <span class="text-danger fw-bold"><?= format_currency($p['sale_price']) ?></span><br>
                                <small class="text-muted text-decoration-line-through"><?= format_currency($p['price']) ?></small>
                            <?php else: ?>
                                <span class="fw-bold text-cafe"><?= format_currency($p['price']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-<?= $p['stock'] > 5 ? 'success' : 'danger' ?>"><?= $p['stock'] ?></span></td>
                        <td><?= $p['sold'] ?></td>
                        <td><?= $p['is_featured'] ? '<i class="bi bi-star-fill text-warning"></i>' : '<i class="bi bi-star text-muted"></i>' ?></td>
                        <td>
                            <span class="badge bg-<?= $p['status'] ? 'success' : 'secondary' ?>">
                                <?= $p['status'] ? 'Đang bán' : 'Ẩn' ?>
                            </span>
                        </td>
                        <td>
                            <a href="product-edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                            <a href="products.php?action=delete&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?');"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>