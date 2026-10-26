<?php
require_once 'includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

// Xử lý thêm sản phẩm vào giỏ
if ($action === 'add') {
    $product_id = intval($_GET['product_id'] ?? 0);
    $quantity = max(1, intval($_GET['quantity'] ?? 1));

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if ($product) {
        if ($product['stock'] < $quantity) {
            $_SESSION['error'] = 'Số lượng sản phẩm trong kho không đủ.';
        } else {
            $stmt_check = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt_check->execute([$user_id, $product_id]);
            $cart_item = $stmt_check->fetch();

            if ($cart_item) {
                $new_qty = $cart_item['quantity'] + $quantity;
                if ($new_qty > $product['stock']) {
                    $_SESSION['error'] = 'Tổng số lượng trong giỏ hàng vượt quá tồn kho.';
                } else {
                    $stmt_update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                    $stmt_update->execute([$new_qty, $cart_item['id']]);
                    $_SESSION['success'] = 'Đã cập nhật giỏ hàng thành công!';
                }
            } else {
                $stmt_insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt_insert->execute([$user_id, $product_id, $quantity]);
                $_SESSION['success'] = 'Đã thêm sản phẩm vào giỏ hàng!';
            }
        }
    }
    redirect('cart.php');
}

// Cập nhật số lượng giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $quantities = $_POST['quantity'] ?? [];
        foreach ($quantities as $cart_id => $qty) {
            $qty = max(1, intval($qty));
            $stmt_c = $conn->prepare("SELECT p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.user_id = ?");
            $stmt_c->execute([$cart_id, $user_id]);
            $item = $stmt_c->fetch();
            if ($item && $qty <= $item['stock']) {
                $stmt_up = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                $stmt_up->execute([$qty, $cart_id, $user_id]);
            }
        }
        $_SESSION['success'] = 'Đã cập nhật giỏ hàng.';
    }
    redirect('cart.php');
}

// Xóa khỏi giỏ
if ($action === 'remove') {
    $cart_id = intval($_GET['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    $_SESSION['success'] = 'Đã xóa sản phẩm khỏi giỏ hàng.';
    redirect('cart.php');
}

// Áp dụng coupon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $coupon_code = trim($_POST['coupon_code'] ?? '');
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND status = 1 AND start_date <= CURDATE() AND end_date >= CURDATE()");
    $stmt->execute([$coupon_code]);
    $coupon = $stmt->fetch();

    if ($coupon) {
        $_SESSION['applied_coupon'] = $coupon;
        $_SESSION['success'] = 'Áp dụng mã giảm giá thành công!';
    } else {
        $_SESSION['error'] = 'Mã giảm giá không hợp lệ hoặc đã hết hạn.';
    }
    redirect('cart.php');
}

if (isset($_GET['remove_coupon'])) {
    unset($_SESSION['applied_coupon']);
    redirect('cart.php');
}

// Lấy danh sách sản phẩm trong giỏ
$stmt_cart = $conn->prepare("SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock, p.slug FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt_cart->execute([$user_id]);
$cart_items = $stmt_cart->fetchAll();

$subtotal = 0;
foreach ($cart_items as $item) {
    $price = $item['sale_price'] ?? $item['price'];
    $subtotal += $price * $item['quantity'];
}

$discount_amount = 0;
$coupon_discount_percent = 0;
if (isset($_SESSION['applied_coupon'])) {
    $coupon = $_SESSION['applied_coupon'];
    if ($subtotal >= $coupon['min_order_amount']) {
        if ($coupon['discount_type'] === 'percent') {
            $discount_amount = ($subtotal * $coupon['discount_value']) / 100;
            $coupon_discount_percent = $coupon['discount_value'];
        } else {
            $discount_amount = $coupon['discount_value'];
        }
    } else {
        unset($_SESSION['applied_coupon']);
    }
}

$shipping_fee = $subtotal > 0 ? 30000 : 0;
$total_amount = max(0, $subtotal - $discount_amount) + $shipping_fee;

$page_title = 'Giỏ hàng - Aroma Coffee';
include 'includes/header.php';
?>

<div class="container my-4">
    <h2 class="fw-bold text-cafe mb-4"><i class="bi bi-cart3 me-2"></i>Giỏ Hàng Của Bạn</h2>

    <?php if (empty($cart_items)): ?>
        <div class="text-center py-5 card border-0 shadow-sm p-4">
            <p class="text-muted fs-5 mb-3">Giỏ hàng của bạn đang trống.</p>
            <a href="products.php" class="btn btn-cafe w-auto mx-auto px-4">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8 mb-4">
                <form action="cart.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <div class="card border-0 shadow-sm p-3">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Đơn giá</th>
                                        <th style="width: 140px;">Số lượng</th>
                                        <th>Thành tiền</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): 
                                        $price = $item['sale_price'] ?? $item['price'];
                                        $item_total = $price * $item['quantity'];
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= e($item['image']) ?>" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <div>
                                                        <h6 class="mb-0"><a href="product-detail.php?id=<?= $item['product_id'] ?>" class="text-decoration-none text-dark fw-bold"><?= e($item['name']) ?></a></h6>
                                                        <small class="text-muted">Tồn kho: <?= $item['stock'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= format_currency($price) ?></td>
                                            <td>
                                                <!-- Bộ nút tăng giảm số lượng -->
                                                <div class="input-group input-group-sm">
                                                    <button class="btn btn-outline-secondary px-2" type="button" onclick="changeCartQty(<?= $item['id'] ?>, -1)"><i class="bi bi-dash-lg"></i></button>
                                                    <input type="number" class="form-control text-center fw-bold px-1 cart-qty-input" 
                                                           id="cart_qty_<?= $item['id'] ?>" 
                                                           name="quantity[<?= $item['id'] ?>]" 
                                                           value="<?= $item['quantity'] ?>" 
                                                           min="1" max="<?= $item['stock'] ?>"
                                                           data-price="<?= $price ?>"
                                                           data-id="<?= $item['id'] ?>"
                                                           onchange="updateCartPriceUI()"
                                                           style="-moz-appearance: textfield;">
                                                    <button class="btn btn-outline-secondary px-2" type="button" onclick="changeCartQty(<?= $item['id'] ?>, 1)"><i class="bi bi-plus-lg"></i></button>
                                                </div>
                                            </td>
                                            <td class="fw-bold text-cafe item-total-price" id="item_total_<?= $item['id'] ?>"><?= format_currency($item_total) ?></td>
                                            <td>
                                                <a href="cart.php?action=remove&id=<?= $item['id'] ?>" class="text-danger" title="Xóa"><i class="bi bi-trash fs-5"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <a href="products.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Tiếp tục mua sắm</a>
                            <button type="submit" name="update_cart" class="btn btn-outline-cafe btn-sm"><i class="bi bi-arrow-clockwise me-1"></i>Cập nhật giỏ hàng</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tổng đơn hàng -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 mb-3">
                    <h5 class="fw-bold text-cafe mb-3">Mã Giảm Giá</h5>
                    <?php if (isset($_SESSION['applied_coupon'])): ?>
                        <div class="alert alert-success d-flex justify-content-between align-items-center mb-0 py-2">
                            <span>Đã áp dụng: <b><?= e($_SESSION['applied_coupon']['code']) ?></b></span>
                            <a href="cart.php?remove_coupon=1" class="btn-close btn-sm"></a>
                        </div>
                    <?php else: ?>
                        <form action="cart.php" method="POST">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" name="coupon_code" placeholder="Nhập mã..." required>
                                <button class="btn btn-cafe" type="submit" name="apply_coupon">Áp dụng</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="card border-0 shadow-sm p-4">
                    <h5 class="fw-bold text-cafe mb-3">Cộng Giỏ Hàng</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <span class="fw-bold" id="ui_subtotal"><?= format_currency($subtotal) ?></span>
                    </div>
                    <?php if ($discount_amount > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <span>Giảm giá:</span>
                            <span id="ui_discount">-<?= format_currency($discount_amount) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Phí vận chuyển:</span>
                        <span id="ui_shipping"><?= format_currency($shipping_fee) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold fs-5">Tổng cộng:</span>
                        <span class="fw-bold fs-4 text-danger" id="ui_total"><?= format_currency($total_amount) ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-cafe w-100 py-2 fw-bold">Tiến Hành Thanh Toán</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function formatVND(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
}

function changeCartQty(cartId, step) {
    var input = document.getElementById('cart_qty_' + cartId);
    var val = parseInt(input.value) + step;
    var min = parseInt(input.min) || 1;
    var max = parseInt(input.max) || 999;
    if (val >= min && val <= max) {
        input.value = val;
        updateCartPriceUI();
    }
}

function updateCartPriceUI() {
    var inputs = document.querySelectorAll('.cart-qty-input');
    var newSubtotal = 0;

    inputs.forEach(function(input) {
        var qty = parseInt(input.value) || 1;
        var price = parseFloat(input.dataset.price) || 0;
        var id = input.dataset.id;
        var itemTotal = qty * price;
        newSubtotal += itemTotal;

        var itemTotalEl = document.getElementById('item_total_' + id);
        if (itemTotalEl) {
            itemTotalEl.innerText = formatVND(itemTotal);
        }
    });

    var subtotalEl = document.getElementById('ui_subtotal');
    if (subtotalEl) subtotalEl.innerText = formatVND(newSubtotal);

    var discountAmount = 0;
    <?php if (isset($_SESSION['applied_coupon'])): ?>
        var couponType = "<?= $_SESSION['applied_coupon']['discount_type'] ?>";
        var couponVal = <?= floatval($_SESSION['applied_coupon']['discount_value']) ?>;
        if (couponType === 'percent') {
            discountAmount = (newSubtotal * couponVal) / 100;
        } else {
            discountAmount = couponVal;
        }
        var discountEl = document.getElementById('ui_discount');
        if (discountEl) discountEl.innerText = '-' + formatVND(discountAmount);
    <?php endif; ?>

    var shippingFee = newSubtotal > 0 ? 30000 : 0;
    var totalAmount = Math.max(0, newSubtotal - discountAmount) + shippingFee;

    var totalEl = document.getElementById('ui_total');
    if (totalEl) totalEl.innerText = formatVND(totalAmount);
}
</script>

<?php include 'includes/footer.php'; ?>