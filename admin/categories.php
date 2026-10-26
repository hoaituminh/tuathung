<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Xử lý Xóa danh mục
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $cat_id = intval($_GET['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$cat_id]);
    $_SESSION['success'] = 'Đã xóa danh mục thành công.';
    redirect('admin/categories.php');
}

// Xử lý Thêm / Sửa danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = intval($_POST['status'] ?? 1);
        $image_url = trim($_POST['image_url'] ?? '');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $image_path = $_POST['current_image'] ?? 'https://placehold.co/400x300?text=Category';
        
        if (!empty($image_url)) {
            $image_path = $image_url;
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = upload_file($_FILES['image'], __DIR__ . '/../uploads/categories');
            if ($upload['success']) {
                $image_path = BASE_URL . '/uploads/categories/' . $upload['filename'];
            }
        }

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, image = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $description, $image_path, $status, $id]);
            $_SESSION['success'] = 'Đã cập nhật danh mục thành công!';
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, slug, description, image, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $description, $image_path, $status]);
            $_SESSION['success'] = 'Thêm danh mục mới thành công!';
        }
        redirect('admin/categories.php');
    }
}

// Lấy danh sách danh mục
$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();

// Lấy danh mục đang cần sửa
$edit_cat = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit') {
    $edit_id = intval($_GET['id'] ?? 0);
    $stmt_e = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt_e->execute([$edit_id]);
    $edit_cat = $stmt_e->fetch();
}

$page_title = 'Quản lý danh mục - Aroma Coffee Admin';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h2 class="fw-bold text-dark m-0"><i class="bi bi-folder-fill text-warning me-2"></i>Quản Lý Danh Mục</h2>
</div>

<div class="row">
    <!-- Form Thêm / Sửa -->
    <div class="col-md-5 mb-4">
        <div class="card card-custom p-4">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-<?= $edit_cat ? 'pencil-square' : 'plus-circle-fill' ?> me-2 text-warning"></i>
                <?= $edit_cat ? 'Cập Nhật Danh Mục' : 'Thêm Danh Mục Mới' ?>
            </h5>
            <form action="categories.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <input type="hidden" name="id" value="<?= $edit_cat['id'] ?? 0 ?>">
                <input type="hidden" name="current_image" value="<?= e($edit_cat['image'] ?? '') ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" required value="<?= e($edit_cat['name'] ?? '') ?>" placeholder="VD: Cà phê hạt">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Mô tả</label>
                    <textarea class="form-control" name="description" rows="3" placeholder="Mô tả ngắn gọn..."><?= e($edit_cat['description'] ?? '') ?></textarea>
                </div>

                <!-- Ô dán Link ngoài có xem trước tự động (Live Preview) -->
                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="bi bi-link-45deg me-1"></i>URL Hình ảnh (Link ngoài)</label>
                    <input type="url" class="form-control" id="cat_image_url" name="image_url" placeholder="https://domain.com/image.jpg" value="<?= (isset($edit_cat['image']) && str_starts_with($edit_cat['image'], 'http')) ? e($edit_cat['image']) : '' ?>" oninput="previewImage(this.value)">
                    <small class="text-muted">Dán link ảnh trực tiếp vào đây.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="bi bi-upload me-1"></i>Hoặc chọn tệp từ máy</label>
                    <input type="file" class="form-control" name="image" accept="image/*">
                </div>

                <!-- Khung Xem Trước Ảnh -->
                <div class="mb-3">
                    <label class="form-label fw-semibold d-block">Xem trước ảnh:</label>
                    <img id="cat_preview_img" src="<?= e($edit_cat['image'] ?? 'https://placehold.co/100x100?text=Preview') ?>" class="img-preview-box" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;" onerror="this.src='https://placehold.co/100x100?text=L%E1%BB%97i+Ảnh';">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="1" <?= ($edit_cat['status'] ?? 1) == 1 ? 'selected' : '' ?>>Hiển thị</option>
                        <option value="0" <?= ($edit_cat['status'] ?? 1) == 0 ? 'selected' : '' ?>>Ẩn</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-cafe w-100 py-2 mt-2">
                    <i class="bi bi-floppy-fill me-1"></i> <?= $edit_cat ? 'Lưu cập nhật' : 'Thêm danh mục' ?>
                </button>
                <?php if ($edit_cat): ?>
                    <a href="categories.php" class="btn btn-outline-secondary w-100 mt-2">Hủy bỏ</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Bảng danh sách -->
    <div class="col-md-7">
        <div class="card card-custom p-4">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-list-stars me-2 text-warning"></i>Danh Sách Danh Mục</h5>
            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 70px;">Ảnh</th>
                            <th>Tên danh mục</th>
                            <th>Slug</th>
                            <th>Trạng thái</th>
                            <th style="width: 130px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td>
                                    <img src="<?= e($cat['image']) ?>" class="img-preview-box" onerror="this.src='https://placehold.co/100x100?text=Error';">
                                </td>
                                <td><strong><?= e($cat['name']) ?></strong></td>
                                <td><code class="text-muted"><?= e($cat['slug']) ?></code></td>
                                <td>
                                    <span class="badge bg-<?= $cat['status'] ? 'success' : 'secondary' ?> badge-status">
                                        <?= $cat['status'] ? 'Hiển thị' : 'Ẩn' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="categories.php?action=edit&id=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil-fill"></i></a>
                                    <a href="categories.php?action=delete&id=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xóa danh mục này?');"><i class="bi bi-trash-fill"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(url) {
    var img = document.getElementById('cat_preview_img');
    if (url.trim() !== '') {
        img.src = url;
    } else {
        img.src = '<?= e($edit_cat['image'] ?? 'https://placehold.co/100x100?text=Preview') ?>';
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>