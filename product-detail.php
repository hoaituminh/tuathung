<?php
require_once 'includes/functions.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.status = 1");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('products.php');
}

// Xử lý Xóa đánh giá
if (isset($_GET['action']) && $_GET['action'] === 'delete_review' && is_logged_in()) {
    $stmt_del = $conn->prepare("DELETE FROM reviews WHERE product_id = ? AND user_id = ?");
    $stmt_del->execute([$id, $_SESSION['user_id']]);
    $_SESSION['success'] = 'Đã xóa đánh giá của bạn.';
    redirect("product-detail.php?id=$id");
}

// Xử lý gửi mới hoặc cập nhật đánh giá
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!is_logged_in()) {
        $_SESSION['error'] = 'Vui lòng đăng nhập để đánh giá sản phẩm.';
    } elseif (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $rating = intval($_POST['rating'] ?? 5);
        $comment = trim($_POST['comment'] ?? '');
        $user_id = $_SESSION['user_id'];

        if ($rating < 1 || $rating > 5) {
            $_SESSION['error'] = 'Số sao đánh giá không hợp lệ.';
        } else {
            $stmt_check = $conn->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
            $stmt_check->execute([$id, $user_id]);
            $existing = $stmt_check->fetch();

            if ($existing) {
                $stmt_up = $conn->prepare("UPDATE reviews SET rating = ?, comment = ?, created_at = NOW() WHERE id = ?");
                $stmt_up->execute([$rating, $comment, $existing['id']]);
                $_SESSION['success'] = 'Cập nhật đánh giá của bạn thành công!';
            } else {
                $stmt_in = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
                $stmt_in->execute([$id, $user_id, $rating, $comment]);
                $_SESSION['success'] = 'Cảm ơn bạn đã gửi đánh giá sản phẩm!';
            }
        }
    }
    redirect("product-detail.php?id=$id");
}

// Lấy danh sách toàn bộ đánh giá
$stmt_rev = $conn->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.id DESC");
$stmt_rev->execute([$id]);
$reviews = $stmt_rev->fetchAll();

// Kiểm tra xem user hiện tại đã đánh giá chưa
$my_review = null;
if (is_logged_in()) {
    $stmt_my = $conn->prepare("SELECT * FROM reviews WHERE product_id = ? AND user_id = ?");
    $stmt_my->execute([$id, $_SESSION['user_id']]);
    $my_review = $stmt_my->fetch();
}

$page_title = e($product['name']) . ' - Aroma Coffee';
include 'includes/header.php';
?>

<style>
/* Sửa lỗi che/mất chữ số khi nhập số lớn hơn 10 */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
input[type=number] {
  -moz-appearance: textfield;
}
</style>

<div class="container my-5">
    <!-- Chi tiết sản phẩm -->
    <div class="card border-0 shadow-sm p-4 mb-5 rounded-4 bg-white">
        <div class="row g-4 align-items-center">
            <div class="col-md-5">
                <img src="<?= e($product['image']) ?>" class="img-fluid rounded-4 shadow-sm w-100" style="max-height: 400px; object-fit: cover;" onerror="this.src='https://placehold.co/400x300?text=Product';">
            </div>
            <div class="col-md-7">
                <span class="badge bg-warning text-dark mb-2 px-3 py-2 rounded-pill"><?= e($product['category_name']) ?></span>
                <h2 class="fw-bold text-cafe mb-3"><?= e($product['name']) ?></h2>
                <div class="mb-3">
                    <?php if ($product['sale_price']): ?>
                        <span class="text-danger fw-bold fs-3 me-3"><?= format_currency($product['sale_price']) ?></span>
                        <span class="text-muted text-decoration-line-through fs-5"><?= format_currency($product['price']) ?></span>
                    <?php else: ?>
                        <span class="text-cafe fw-bold fs-3"><?= format_currency($product['price']) ?></span>
                    <?php endif; ?>
                </div>

                <p class="text-muted mb-4"><?= nl2br(e($product['description'])) ?></p>

                <ul class="list-unstyled text-muted small mb-4">
                    <li><strong>Khối lượng:</strong> <?= e($product['weight']) ?></li>
                    <li><strong>Xuất xứ:</strong> <?= e($product['origin']) ?></li>
                    <li><strong>Mức rang:</strong> <?= e($product['roast_level']) ?></li>
                    <li><strong>Tồn kho:</strong> <span class="badge bg-success"><?= $product['stock'] ?> sản phẩm</span></li>
                </ul>

                <!-- BỘ NÚT TĂNG GIẢM SỐ LƯỢNG (Đã mở rộng rộng 140px chuẩn) -->
                <form action="cart.php" method="GET" class="d-flex align-items-center gap-3">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                    <div class="input-group" style="width: 140px;">
                        <button class="btn btn-outline-secondary px-3" type="button" onclick="changeQty(-1)"><i class="bi bi-dash-lg"></i></button>
                        <input type="number" class="form-control text-center fw-bold px-1" id="product_qty" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                        <button class="btn btn-outline-secondary px-3" type="button" onclick="changeQty(1)"><i class="bi bi-plus-lg"></i></button>
                    </div>

                    <button type="submit" class="btn btn-cafe btn-lg px-4 rounded-pill">
                        <i class="bi bi-cart-plus me-2"></i>Thêm vào giỏ hàng
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Khối Đánh giá sản phẩm -->
    <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-cafe m-0"><i class="bi bi-star-fill text-warning me-2"></i>Đánh Giá Sản Phẩm (<?= count($reviews) ?>)</h4>
            
            <?php if (is_logged_in() && !$my_review): ?>
                <button class="btn btn-cafe btn-sm rounded-pill px-3" data-bs-toggle="collapse" data-bs-target="#reviewFormCollapse">
                    <i class="bi bi-pencil-square me-1"></i>Viết đánh giá
                </button>
            <?php endif; ?>
        </div>

        <?php if (is_logged_in() && !$my_review): ?>
            <div class="collapse mb-4" id="reviewFormCollapse">
                <div class="bg-light p-4 rounded-3">
                    <h6 class="fw-bold mb-3">Gửi đánh giá của bạn</h6>
                    <form action="product-detail.php?id=<?= $id ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="submit_review" value="1">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Đánh giá sao</label>
                            <select name="rating" class="form-select w-auto">
                                <option value="5">5 Star - Rất tuyệt vời</option>
                                <option value="4">4 Star - Hài lòng</option>
                                <option value="3">3 Star - Bình thường</option>
                                <option value="2">2 Star - Không hài lòng</option>
                                <option value="1">1 Star - Tệ</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nội dung nhận xét</label>
                            <textarea class="form-control" name="comment" rows="3" required placeholder="Cảm nhận của bạn về chất lượng sản phẩm..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-cafe rounded-pill px-4">Gửi đánh giá</button>
                    </form>
                </div>
            </div>
        <?php elseif (!is_logged_in()): ?>
            <div class="alert alert-warning mb-4">Vui lòng <a href="login.php" class="fw-bold text-dark">đăng nhập</a> để đăng bài đánh giá.</div>
        <?php endif; ?>

        <!-- Danh sách bình luận -->
        <div class="review-list">
            <?php if (empty($reviews)): ?>
                <p class="text-muted">Chưa có đánh giá nào cho sản phẩm này.</p>
            <?php else: ?>
                <?php foreach ($reviews as $rev): 
                    $is_my_rev = (is_logged_in() && $rev['user_id'] == $_SESSION['user_id']);
                ?>
                    <div class="border-bottom pb-3 mb-3 <?= $is_my_rev ? 'bg-light p-3 rounded-3 border-start border-4 border-warning' : '' ?>">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <strong class="text-dark me-2"><?= e($rev['full_name']) ?></strong>
                                <?php if ($is_my_rev): ?>
                                    <span class="badge bg-warning text-dark me-2">Đánh giá của bạn</span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <small class="text-muted me-2"><?= date('d/m/Y H:i', strtotime($rev['created_at'])) ?></small>
                                
                                <?php if ($is_my_rev): ?>
                                    <button class="btn btn-sm btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#editReviewModal">
                                        <i class="bi bi-pencil-fill me-1"></i>Sửa
                                    </button>
                                    <a href="product-detail.php?id=<?= $id ?>&action=delete_review" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="return confirm('Bạn muốn xóa đánh giá này?');">
                                        <i class="bi bi-trash-fill me-1"></i>Xóa
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="text-warning mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star-fill<?= $i <= $rev['rating'] ? '' : '-half opacity-25' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="mb-0 text-dark"><?= e($rev['comment']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL SỬA ĐÁNH GIÁ -->
<?php if ($my_review): ?>
<div class="modal fade" id="editReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-cafe text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Chỉnh Sửa Đánh Giá</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="product-detail.php?id=<?= $id ?>" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="submit_review" value="1">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Chọn lại số sao</label>
                        <select name="rating" class="form-select">
                            <option value="5" <?= $my_review['rating'] == 5 ? 'selected' : '' ?>>5 Star - Rất tuyệt vời</option>
                            <option value="4" <?= $my_review['rating'] == 4 ? 'selected' : '' ?>>4 Star - Hài lòng</option>
                            <option value="3" <?= $my_review['rating'] == 3 ? 'selected' : '' ?>>3 Star - Bình thường</option>
                            <option value="2" <?= $my_review['rating'] == 2 ? 'selected' : '' ?>>2 Star - Không hài lòng</option>
                            <option value="1" <?= $my_review['rating'] == 1 ? 'selected' : '' ?>>1 Star - Tệ</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nội dung nhận xét</label>
                        <textarea class="form-control" name="comment" rows="4" required><?= e($my_review['comment']) ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-cafe">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function changeQty(step) {
    var input = document.getElementById('product_qty');
    var val = parseInt(input.value) + step;
    var min = parseInt(input.min) || 1;
    var max = parseInt(input.max) || 999;
    if (val >= min && val <= max) {
        input.value = val;
    }
}
</script>

<?php include 'includes/footer.php'; ?>