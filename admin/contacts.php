<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Xóa phản hồi
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = 'Đã xóa phản hồi.';
    redirect('admin/contacts.php');
}

$contacts = $conn->query("SELECT * FROM contacts ORDER BY id DESC")->fetchAll();

$page_title = 'Quản lý liên hệ - Aroma Coffee Admin';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h2 class="fw-bold text-dark m-0"><i class="bi bi-envelope-paper-fill text-warning me-2"></i>Danh Sách Phản Hồi Liên Hệ</h2>
</div>

<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead class="table-light">
                <tr>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Nội dung lời nhắn</th>
                    <th>Ngày gửi</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $c): ?>
                    <tr>
                        <td><strong><?= e($c['full_name']) ?></strong></td>
                        <td><?= e($c['email']) ?></td>
                        <td><?= e($c['phone'] ?? 'Chưa cung cấp') ?></td>
                        <td><?= e($c['message']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></td>
                        <td>
                            <a href="contacts.php?action=delete&id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa phản hồi này?');"><i class="bi bi-trash-fill"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>