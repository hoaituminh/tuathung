<?php
require_once 'includes/functions.php';

if (is_logged_in()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Mã bảo mật không hợp lệ.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Vui lòng nhập đầy đủ email và mật khẩu.';
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'blocked') {
                    $error = 'Tài khoản của bạn đã bị khóa.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_role'] = $user['role'];

                    if ($user['role'] === 'admin') {
                        redirect('admin/index.php');
                    } else {
                        redirect('index.php');
                    }
                }
            } else {
                $error = 'Email hoặc mật khẩu không chính xác.';
            }
        }
    }
}

$page_title = 'Đăng nhập - Aroma Coffee';
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h3 class="text-center fw-bold text-cafe mb-4"><i class="bi bi-box-arrow-in-right me-2"></i>Đăng Nhập</h3>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Địa chỉ Email</label>
                            <input type="email" class="form-control" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-cafe w-100 py-2 mb-3">Đăng Nhập</button>
                        
                        <div class="text-center small">
                            <a href="#" class="text-decoration-none text-muted">Quên mật khẩu?</a> &nbsp;|&nbsp;
                            <a href="register.php" class="text-decoration-none text-cafe fw-bold">Đăng ký tài khoản mới</a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="text-center mt-3 small text-muted">
                Tài khoản Admin mẫu: <code>admin@cafe.com</code> / <code>123456</code><br>
                Tài khoản Khách mẫu: <code>khach1@cafe.com</code> / <code>123456</code>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>