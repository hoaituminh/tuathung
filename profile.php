<?php
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_login();

$user = get_current_user_info($conn);
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Mã bảo mật không hợp lệ.';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';

        if (empty($full_name)) {
            $error = 'Họ tên không được để trống.';
        } else {
            // Nếu đổi mật khẩu
            if (!empty($current_password) || !empty($new_password)) {
                if (!password_verify($current_password, $user['password'])) {
                    $error = 'Mật khẩu hiện tại không chính xác.';
                } elseif (strlen($new_password) < 6) {
                    $error = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, password = ? WHERE id = ?");
                    $stmt->execute([$full_name, $phone, $address, $hashed_password, $user['id']]);
                    $_SESSION['user_name'] = $full_name;
                    $success = 'Cập nhật hồ sơ và đổi mật khẩu thành công!';
                }
            } else {
                $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
                $stmt->execute([$full_name, $phone, $address, $user['id']]);
                $_SESSION['user_name'] = $full_name;
                $success = 'Cập nhật hồ sơ thành công!';
            }
            $user = get_current_user_info($conn);
        }
    }
}

$page_title = 'Hồ sơ cá nhân - Aroma Coffee';
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h3 class="fw-bold text-cafe mb-4"><i class="bi bi-person-badge me-2"></i>Hồ Sơ Cá Nhân</h3>
                    
                    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

                    <form action="profile.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Email (Không thể đổi)</label>
                            <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" name="full_name" required value="<?= e($user['full_name']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" name="phone" value="<?= e($user['phone'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Địa chỉ giao hàng</label>
                            <textarea class="form-control" name="address" rows="3"><?= e($user['address'] ?? '') ?></textarea>
                        </div>

                        <hr class="my-4">
                        <h5 class="fw-bold text-cafe mb-3">Đổi mật khẩu (Bỏ trống nếu không đổi)</h5>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu hiện tại</label>
                            <input type="password" class="form-control" name="current_password">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" class="form-control" name="new_password">
                        </div>

                        <button type="submit" class="btn btn-cafe px-4 py-2">Lưu thay đổi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>