<?php
require_once 'includes/functions.php';

// Lấy tham số lọc & phân trang
$category_id = $_GET['category'] ?? '';
$keyword = trim($_GET['keyword'] ?? '');
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$roast_level = $_GET['roast_level'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$featured = $_GET['featured'] ?? '';

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 9;
$offset = ($page - 1) * $limit;

// Xây dựng câu truy vấn động
$where = ["status = 1"];
$params = [];

if ($category_id) {
    $where[] = "category_id = ?";
    $params[] = $category_id;
}
if ($keyword) {
    $where[] = "name LIKE ?";
    $params[] = "%$keyword%";
}
if ($min_price !== '') {
    $where[] = "COALESCE(sale_price, price) >= ?";
    $params[] = $min_price;
}
if ($max_price !== '') {
    $where[] = "COALESCE(sale_price, price) <= ?";
    $params[] = $max_price;
}
if ($roast_level) {
    $where[] = "roast_level = ?";
    $params[] = $roast_level;
}
if ($featured) {
    $where[] = "is_featured = 1";
}

$where_sql = implode(" AND ", $where);

// Đếm tổng số bản ghi
$stmt_count = $conn->prepare("SELECT COUNT(*) FROM products WHERE $where_sql");
$stmt_count->execute($params);
$total_products = $stmt_count->fetchColumn();
$total_pages = ceil($total_products / $limit);

// Sắp xếp
$order_by = "id DESC";
if ($sort === 'price_asc') $order_by = "COALESCE(sale_price, price) ASC";
if ($sort === 'price_desc') $order_by = "COALESCE(sale_price, price) DESC";
if ($sort === 'bestseller') $order_by = "sold DESC";

// Lấy danh sách sản phẩm phân trang
$sql = "SELECT * FROM products WHERE $where_sql ORDER BY $order_by LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Lấy danh mục để hiển thị sidebar
$categories = $conn->query("SELECT * FROM categories WHERE status = 1")->fetchAll();

$page_title = 'Danh sách sản phẩm - Aroma Coffee';
include 'includes/header.php';
?>

<div class="container my-4">
    <div class="row">
        <!-- Sidebar Lọc -->
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm p-3">
                <h5 class="fw-bold text-cafe mb-3"><i class="bi bi-funnel me-2"></i>Bộ Lọc Tìm Kiếm</h5>
                <form action="products.php" method="GET">
                    <?php if ($keyword): ?>
                        <input type="hidden" name="keyword" value="<?= htmlspecialchars($keyword) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Danh mục</label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="">Tất cả danh mục</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Mức rang</label>
                        <select name="roast_level" class="form-select form-select-sm">
                            <option value="">Tất cả mức rang</option>
                            <option value="Light" <?= $roast_level === 'Light' ? 'selected' : '' ?>>Light</option>
                            <option value="Medium" <?= $roast_level === 'Medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="Dark" <?= $roast_level === 'Dark' ? 'selected' : '' ?>>Dark</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Khoảng giá (VNĐ)</label>
                        <div class="input-group input-group-sm mb-2">
                            <input type="number" class="form-control" name="min_price" placeholder="Từ" value="<?= htmlspecialchars($min_price) ?>">
                            <input type="number" class="form-control" name="max_price" placeholder="Đến" value="<?= htmlspecialchars($max_price) ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-cafe btn-sm w-100">Áp dụng lọc</button>
                    <a href="products.php" class="btn btn-outline-secondary btn-sm w-100 mt-2">Xóa bộ lọc</a>
                </form>
            </div>
        </div>

        <!-- Danh sách sản phẩm chính -->
        <div class="col-md-9">
            <div class="card border-0 shadow-sm p-3 mb-4 d-flex flex-row justify-content-between align-items-center">
                <span class="text-muted small">Hiển thị <?= count($products) ?> / <?= $total_products ?> sản phẩm</span>
                <form action="products.php" method="GET" class="d-flex align-items-center">
                    <?php foreach ($_GET as $key => $val): if ($key !== 'sort' && $key !== 'page'): ?>
                        <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($val) ?>">
                    <?php endif; endforeach; ?>
                    <label class="me-2 small fw-bold text-nowrap">Sắp xếp:</label>
                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                        <option value="bestseller" <?= $sort === 'bestseller' ? 'selected' : '' ?>>Bán chạy nhất</option>
                        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                    </select>
                </form>
            </div>

            <?php if (empty($products)): ?>
                <div class="alert alert-warning text-center py-4">Không tìm thấy sản phẩm phù hợp.</div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($products as $prod): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm border-0">
                                <?php if ($prod['sale_price']): ?>
                                    <span class="badge bg-danger position-absolute m-2">Giảm giá</span>
                                <?php endif; ?>
                                <a href="product-detail.php?id=<?= $prod['id'] ?>">
                                    <img src="<?= htmlspecialchars($prod['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($prod['name']) ?>" style="height: 180px; object-fit: cover;">
                                </a>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fs-6"><a href="product-detail.php?id=<?= $prod['id'] ?>" class="text-decoration-none text-dark fw-bold"><?= htmlspecialchars($prod['name']) ?></a></h5>
                                    <div class="mt-auto">
                                        <div class="mb-2">
                                            <?php if ($prod['sale_price']): ?>
                                                <span class="text-danger fw-bold fs-5"><?= format_currency($prod['sale_price']) ?></span>
                                                <span class="text-muted text-decoration-line-through small ms-2"><?= format_currency($prod['price']) ?></span>
                                            <?php else: ?>
                                                <span class="text-cafe fw-bold fs-5"><?= format_currency($prod['price']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <a href="product-detail.php?id=<?= $prod['id'] ?>" class="btn btn-outline-cafe btn-sm w-100"><i class="bi bi-eye me-1"></i>Xem chi tiết</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Phân trang -->
                <?php if ($total_pages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>