<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Xử lý Khóa / Mở khóa tài khoản
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'block') {
        $stmt = $conn->prepare("UPDATE users SET status = 'blocked' WHERE id = ? AND role != 'admin'");
        $stmt->execute([$user_id]);
        $_SESSION['success'] = 'Đã khóa tài khoản thành công.';
    } elseif ($action === 'unblock') {
        $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['success'] = 'Đã mở khóa tài khoản thành công.';
    }
    redirect('admin/users.php');
}

// Lấy danh sách tài khoản khách hàng
$users = $conn->query("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as total_orders FROM users u WHERE role = 'customer' ORDER BY id DESC")->fetchAll();

$page_title = 'Quản lý khách hàng - Aroma Coffee Admin';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h2 class="fw-bold text-dark m-0"><i class="bi bi-people-fill text-warning me-2"></i>Quản Lý Khách Hàng</h2>
</div>

<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead class="table-light">
                <tr>
                    <th>Họ và tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Tổng đơn hàng</th>
                    <th>Trạng thái</th>
                    <th>Ngày tham gia</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><strong><?= e($u['full_name']) ?></strong></td>
                        <td><?= e($u['email']) ?></td>
                        <td><?= e($u['phone'] ?? 'Chưa cập nhật') ?></td>
                        <td><span class="badge bg-info text-dark"><?= $u['total_orders'] ?> đơn</span></td>
                        <td>
                            <span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'danger' ?> badge-status">
                                <?= $u['status'] === 'active' ? 'Hoạt động' : 'Đã khóa' ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <?php if ($u['status'] === 'active'): ?>
                                <a href="users.php?action=block&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn khóa tài khoản này?');"><i class="bi bi-lock-fill me-1"></i>Khóa</a>
                            <?php else: ?>
                                <a href="users.php?action=unblock&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-success"><i class="bi bi-unlock-fill me-1"></i>Mở khóa</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>