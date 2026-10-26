<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Xóa đánh giá
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = 'Đã xóa đánh giá.';
    redirect('admin/reviews.php');
}

$reviews = $conn->query("SELECT r.*, p.name as product_name, u.full_name FROM reviews r JOIN products p ON r.product_id = p.id JOIN users u ON r.user_id = u.id ORDER BY r.id DESC")->fetchAll();

$page_title = 'Quản lý đánh giá - Aroma Coffee Admin';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h2 class="fw-bold text-dark m-0"><i class="bi bi-star-fill text-warning me-2"></i>Quản Lý Đánh Giá</h2>
</div>

<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead class="table-light">
                <tr>
                    <th>Sản phẩm</th>
                    <th>Khách hàng</th>
                    <th>Số sao</th>
                    <th>Nội dung bình luận</th>
                    <th>Ngày đánh giá</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $r): ?>
                    <tr>
                        <td><strong><?= e($r['product_name']) ?></strong></td>
                        <td><?= e($r['full_name']) ?></td>
                        <td>
                            <span class="text-warning fw-bold"><?= $r['rating'] ?> <i class="bi bi-star-fill"></i></span>
                        </td>
                        <td><?= e($r['comment']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                        <td>
                            <a href="reviews.php?action=delete&id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa đánh giá này?');"><i class="bi bi-trash-fill"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>