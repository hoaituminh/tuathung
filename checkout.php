<?php
require_once 'includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];
$user = get_current_user_info($conn);

// Lấy giỏ hàng
$stmt_cart = $conn->prepare("SELECT c.*, p.name, p.price, p.sale_price, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt_cart->execute([$user_id]);
$cart_items = $stmt_cart->fetchAll();

if (empty($cart_items)) {
    $_SESSION['error'] = 'Giỏ hàng của bạn đang trống.';
    redirect('cart.php');
}

$subtotal = 0;
foreach ($cart_items as $item) {
    $price = $item['sale_price'] ?? $item['price'];
    $subtotal += $price * $item['quantity'];
}

$discount_amount = 0;
$coupon_id = null;
if (isset($_SESSION['applied_coupon'])) {
    $coupon = $_SESSION['applied_coupon'];
    $coupon_id = $coupon['id'];
    if ($subtotal >= $coupon['min_order_amount']) {
        if ($coupon['discount_type'] === 'percent') {
            $discount_amount = ($subtotal * $coupon['discount_value']) / 100;
        } else {
            $discount_amount = $coupon['discount_value'];
        }
    }
}

$shipping_fee = 30000;
$total_amount = max(0, $subtotal - $discount_amount) + $shipping_fee;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Mã bảo mật không hợp lệ.';
    } else {
        $receiver_name = trim($_POST['receiver_name'] ?? '');
        $receiver_phone = trim($_POST['receiver_phone'] ?? '');
        $shipping_address = trim($_POST['shipping_address'] ?? '');
        $note = trim($_POST['note'] ?? '');
        $payment_method = $_POST['payment_method'] ?? 'COD';

        if (empty($receiver_name) || empty($receiver_phone) || empty($shipping_address)) {
            $error = 'Vui lòng điền đầy đủ thông tin nhận hàng.';
        } else {
            try {
                // Bắt đầu Transaction
                $conn->beginTransaction();

                // Kiểm tra lại tồn kho cho từng sản phẩm
                foreach ($cart_items as $item) {
                    $stmt_stk = $conn->prepare("SELECT stock FROM products WHERE id = ? FOR UPDATE");
                    $stmt_stk->execute([$item['product_id']]);
                    $prod_stk = $stmt_stk->fetch();
                    if (!$prod_stk || $prod_stk['stock'] < $item['quantity']) {
                        throw new Exception("Sản phẩm {$item['name']} không đủ số lượng trong kho.");
                    }
                }

                $order_code = 'ORD' . date('Ymd') . rand(1000, 9999);

                // Tạo đơn hàng
                $stmt_order = $conn->prepare("INSERT INTO orders (user_id, order_code, receiver_name, receiver_phone, shipping_address, note, total_amount, discount_amount, shipping_fee, payment_method, payment_status, order_status, coupon_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'unpaid', 'pending', ?)");
                $stmt_order->execute([$user_id, $order_code, $receiver_name, $receiver_phone, $shipping_address, $note, $total_amount, $discount_amount, $shipping_fee, $payment_method, $coupon_id]);
                $order_id = $conn->lastInsertId();

                // Thêm chi tiết đơn hàng và trừ tồn kho, tăng số lượng bán
                foreach ($cart_items as $item) {
                    $price = $item['sale_price'] ?? $item['price'];
                    $stmt_detail = $conn->prepare("INSERT INTO order_details (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
                    $stmt_detail->execute([$order_id, $item['product_id'], $item['name'], $price, $item['quantity']]);

                    // Trừ tồn kho và tăng sold
                    $stmt_up_prod = $conn->prepare("UPDATE products SET stock = stock - ?, sold = sold + ? WHERE id = ?");
                    $stmt_up_prod->execute([$item['quantity'], $item['quantity'], $item['product_id']]);
                }

                // Xóa giỏ hàng
                $stmt_clear = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt_clear->execute([$user_id]);

                // Xóa coupon session
                unset($_SESSION['applied_coupon']);

                $conn->commit();
                $_SESSION['success'] = 'Đặt hàng thành công! Mã đơn hàng của bạn là: ' . $order_code;
                redirect("order-success.php?code=$order_code");

            } catch (Exception $e) {
                $conn->rollBack();
                $error = 'Lỗi đặt hàng: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Thanh toán - Aroma Coffee';
include 'includes/header.php';
?>

<div class="container my-4">
    <h2 class="fw-bold text-cafe mb-4"><i class="bi bi-credit-card me-2"></i>Thanh Toán Đơn Hàng</h2>

    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <form action="checkout.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <div class="row">
            <!-- Thông tin nhận hàng -->
            <div class="col-md-7 mb-4">
                <div class="card border-0 shadow-sm p-4">
                    <h5 class="fw-bold text-cafe mb-3">Thông Tin Người Nhận</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Họ và tên người nhận <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="receiver_name" required value="<?= e($_POST['receiver_name'] ?? $user['full_name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="receiver_phone" required value="<?= e($_POST['receiver_phone'] ?? $user['phone']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Địa chỉ giao hàng chi tiết <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="shipping_address" rows="3" required><?= e($_POST['shipping_address'] ?? $user['address']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ghi chú đơn hàng (Tùy chọn)</label>
                        <textarea class="form-control" name="note" rows="2"><?= e($_POST['note'] ?? '') ?></textarea>
                    </div>

                    <h5 class="fw-bold text-cafe mt-4 mb-3">Phương Thức Thanh Toán</h5>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" id="cod" value="COD" checked>
                        <label class="form-check-label" for="cod">
                            <i class="bi bi-cash-stack me-2"></i>Thanh toán khi nhận hàng (COD)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="bank" value="bank_transfer">
                        <label class="form-check-label" for="bank">
                            <i class="bi bi-bank me-2"></i>Chuyển khoản ngân hàng trực tuyến
                        </label>
                    </div>
                </div>
            </div>

            <!-- Tóm tắt đơn hàng -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm p-4">
                    <h5 class="fw-bold text-cafe mb-3">Đơn Hàng Của Bạn (<?= count($cart_items) ?> sản phẩm)</h5>
                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach ($cart_items as $item): 
                            $price = $item['sale_price'] ?? $item['price'];
                        ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-0 text-truncate" style="max-width: 200px;"><?= e($item['name']) ?></h6>
                                    <small class="text-muted">SL: <?= $item['quantity'] ?></small>
                                </div>
                                <span class="fw-bold"><?= format_currency($price * $item['quantity']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <span class="fw-bold"><?= format_currency($subtotal) ?></span>
                    </div>
                    <?php if ($discount_amount > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <span>Giảm giá:</span>
                            <span>-<?= format_currency($discount_amount) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Phí vận chuyển:</span>
                        <span><?= format_currency($shipping_fee) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold fs-5">Tổng thanh toán:</span>
                        <span class="fw-bold fs-4 text-danger"><?= format_currency($total_amount) ?></span>
                    </div>

                    <button type="submit" class="btn btn-cafe w-100 py-2 fw-bold fs-5">Đặt Hàng Ngay</button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>