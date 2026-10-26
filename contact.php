<?php
require_once 'includes/functions.php';

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Mã bảo mật không hợp lệ.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            $error = 'Vui lòng điền đầy đủ các thông tin bắt buộc.';
        } else {
            $stmt = $conn->prepare("INSERT INTO contacts (name, email, phone, message, status) VALUES (?, ?, ?, ?, 'new')");
            if ($stmt->execute([$name, $email, $phone, $message])) {
                $success = 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất.';
            } else {
                $error = 'Đã có lỗi xảy ra, vui lòng thử lại.';
            }
        }
    }
}

$page_title = 'Liên hệ - Aroma Coffee';
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-6 mb-4">
            <h2 class="fw-bold text-cafe mb-3">Liên Hệ Với Chúng Tôi</h2>
            <p class="text-muted mb-4">Bạn có thắc mắc về sản phẩm cà phê hạt, chính sách đại lý hay cần tư vấn về dụng cụ pha chế? Hãy để lại thông tin cho chúng tôi.</p>
            
            <p class="mb-2"><i class="bi bi-geo-alt-fill text-cafe me-2"></i>Đơn Luân, Việt Nam</p>
            <p class="mb-2"><i class="bi bi-telephone-fill text-cafe me-2"></i>1900 6868</p>
            <p class="mb-2"><i class="bi bi-envelope-fill text-cafe me-2"></i>support@aromacoffee.vn</p>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4">
                <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

                <form action="contact.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <div class="mb-3">
                        <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nội dung tin nhắn <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="message" rows="4" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-cafe w-100 py-2">Gửi Tin Nhắn</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>