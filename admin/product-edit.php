<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = intval($_GET['id'] ?? 0);
$product = null;
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
}

// Xử lý Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $category_id = intval($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $sale_price = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
        $weight = trim($_POST['weight'] ?? '500g');
        $origin = trim($_POST['origin'] ?? 'Việt Nam');
        $roast_level = trim($_POST['roast_level'] ?? 'Medium');
        $stock = intval($_POST['stock'] ?? 0);
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $status = intval($_POST['status'] ?? 1);
        $image_url = trim($_POST['image_url'] ?? '');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        // Xử lý link ngoài vs upload file
        $image_path = $_POST['current_image'] ?? 'https://placehold.co/400x300?text=Product';
        if (!empty($image_url)) {
            $image_path = $image_url;
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = upload_file($_FILES['image'], __DIR__ . '/../uploads/products');
            if ($upload['success']) {
                $image_path = BASE_URL . '/uploads/products/' . $upload['filename'];
            }
        }

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE products SET category_id=?, name=?, slug=?, description=?, price=?, sale_price=?, image=?, weight=?, origin=?, roast_level=?, stock=?, is_featured=?, status=? WHERE id=?");
            $stmt->execute([$category_id, $name, $slug, $description, $price, $sale_price, $image_path, $weight, $origin, $roast_level, $stock, $is_featured, $status, $id]);
            $_SESSION['success'] = 'Cập nhật sản phẩm thành công!';
        } else {
            $stmt = $conn->prepare("INSERT INTO products (category_id, name, slug, description, price, sale_price, image, weight, origin, roast_level, stock, is_featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $name, $slug, $description, $price, $sale_price, $image_path, $weight, $origin, $roast_level, $stock, $is_featured, $status]);
            $_SESSION['success'] = 'Thêm sản phẩm mới thành công!';
        }
        redirect('admin/products.php');
    }
}

$categories = $conn->query("SELECT * FROM categories WHERE status = 1")->fetchAll();

$page_title = ($id > 0 ? 'Sửa' : 'Thêm') . ' sản phẩm - Aroma Coffee Admin';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h2 class="fw-bold text-dark m-0"><i class="bi bi-box-seam-fill text-warning me-2"></i><?= $id > 0 ? 'Sửa Sản Phẩm' : 'Thêm Sản Phẩm Mới' ?></h2>
    <a href="products.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Quay lại danh sách</a>
</div>

<div class="card card-custom p-4 col-md-10 mx-auto">
    <form action="product-edit.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <input type="hidden" name="current_image" value="<?= e($product['image'] ?? '') ?>">

        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" required value="<?= e($product['name'] ?? '') ?>" placeholder="VD: Cà phê hạt Arabica Cầu Đất">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Mô tả sản phẩm</label>
                    <textarea class="form-control" name="description" rows="5"><?= e($product['description'] ?? '') ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Giá bán gốc (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="price" required value="<?= $product['price'] ?? 0 ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Giá khuyến mãi (VNĐ)</label>
                        <input type="number" class="form-control" name="sale_price" value="<?= $product['sale_price'] ?? '' ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Khối lượng / Quy cách</label>
                        <input type="text" class="form-control" name="weight" value="<?= e($product['weight'] ?? '500g') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Xuất xứ</label>
                        <input type="text" class="form-control" name="origin" value="<?= e($product['origin'] ?? 'Việt Nam') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Mức rang</label>
                        <select name="roast_level" class="form-select">
                            <option value="Light" <?= ($product['roast_level'] ?? '') === 'Light' ? 'selected' : '' ?>>Light</option>
                            <option value="Medium" <?= ($product['roast_level'] ?? 'Medium') === 'Medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="Dark" <?= ($product['roast_level'] ?? '') === 'Dark' ? 'selected' : '' ?>>Dark</option>
                            <option value="N/A" <?= ($product['roast_level'] ?? '') === 'N/A' ? 'selected' : '' ?>>N/A (Dụng cụ/Trà)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Danh mục <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Số lượng tồn kho <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="stock" required value="<?= $product['stock'] ?? 100 ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="bi bi-link-45deg me-1"></i>URL Hình ảnh (Link ngoài)</label>
                    <input type="url" class="form-control" name="image_url" placeholder="https://domain.com/image.jpg" value="<?= (isset($product['image']) && str_starts_with($product['image'], 'http')) ? e($product['image']) : '' ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="bi bi-upload me-1"></i>Hoặc chọn tệp từ máy</label>
                    <input type="file" class="form-control" name="image" accept="image/*">
                </div>

                <?php if (!empty($product['image'])): ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold d-block">Ảnh hiện tại:</label>
                        <img src="<?= e($product['image']) ?>" class="img-preview-box" onerror="this.src='https://placehold.co/100x100?text=No+Image';">
                    </div>
                <?php endif; ?>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" <?= !empty($product['is_featured']) ? 'checked' : '' ?>>
                    <label class="form-check-label fw-semibold" for="is_featured">Sản phẩm nổi bật</label>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="1" <?= ($product['status'] ?? 1) == 1 ? 'selected' : '' ?>>Đang bán</option>
                        <option value="0" <?= ($product['status'] ?? 1) == 0 ? 'selected' : '' ?>>Ẩn sản phẩm</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-cafe w-100 py-2 fw-bold mt-2"><i class="bi bi-floppy-fill me-1"></i> <?= $id > 0 ? 'Lưu cập nhật' : 'Thêm sản phẩm' ?></button>
            </div>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>