<?php
require_once 'includes/functions.php';
require_login();

// Xử lý thêm/xóa yêu thích
if (isset($_GET['action']) && isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    if ($_GET['action'] === 'add') {
        $stmt = $conn->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
    } elseif ($_GET['action'] === 'remove') {
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
    }
    redirect('wishlist.php');
}

$stmt = $conn->prepare("SELECT p.* FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$wishlist_items = $stmt->fetchAll();

$page_title = 'Sản phẩm yêu thích - Aroma Coffee';
include 'includes/header.php';
?>

<div class="container my-4">
    <h2 class="fw-bold text-cafe mb-4"><i class="bi bi-heart-fill text-danger me-2"></i>Sản Phẩm Yêu Thích</h2>

    <?php if (empty($wishlist_items)): ?>
        <div class="alert alert-info">Bạn chưa có sản phẩm yêu thích nào.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($wishlist_items as $prod): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <a href="product-detail.php?id=<?= $prod['id'] ?>">
                            <img src="<?= htmlspecialchars($prod['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($prod['name']) ?>" style="height: 180px; object-fit: cover;">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fs-6"><a href="product-detail.php?id=<?= $prod['id'] ?>" class="text-decoration-none text-dark fw-bold"><?= htmlspecialchars($prod['name']) ?></a></h5>
                            <div class="mt-auto">
                                <span class="text-cafe fw-bold fs-5 mb-2 d-block"><?= format_currency($prod['sale_price'] ?? $prod['price']) ?></span>
                                <div class="d-flex gap-2">
                                    <a href="product-detail.php?id=<?= $prod['id'] ?>" class="btn btn-outline-cafe btn-sm w-100">Chi tiết</a>
                                    <a href="wishlist.php?action=remove&product_id=<?= $prod['id'] ?>" class="btn btn-outline-danger btn-sm" title="Xóa"><i class="bi bi-trash"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>