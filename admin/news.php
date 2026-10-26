<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Xóa tin tức
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = 'Đã xóa bài viết.';
    redirect('admin/news.php');
}

// Thêm tin tức mới
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $image_url = trim($_POST['thumbnail_url'] ?? '');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

        $thumbnail = 'https://placehold.co/600x400?text=News';
        if (!empty($image_url)) {
            $thumbnail = $image_url;
        } elseif (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $upload = upload_file($_FILES['thumbnail'], __DIR__ . '/../uploads/news');
            if ($upload['success']) {
                $thumbnail = BASE_URL . '/uploads/news/' . $upload['filename'];
            }
        }

        $stmt = $conn->prepare("INSERT INTO news (title, slug, content, thumbnail, status) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$title, $slug, $content, $thumbnail]);
        $_SESSION['success'] = 'Đã đăng bài viết tin tức mới!';
        redirect('admin/news.php');
    }
}

$news_list = $conn->query("SELECT * FROM news ORDER BY id DESC")->fetchAll();

$page_title = 'Quản lý tin tức - Aroma Coffee Admin';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h2 class="fw-bold text-dark m-0"><i class="bi bi-newspaper text-warning me-2"></i>Quản Lý Tin Tức & Bài Viết</h2>
</div>

<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card card-custom p-4">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-plus-circle-fill me-2 text-warning"></i>Thêm Bài Viết Mới</h5>
            <form action="news.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tiêu đề bài viết <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title" required placeholder="VD: Bí quyết pha Cà Phê Phin chuẩn vị">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nội dung bài viết</label>
                    <textarea class="form-control" name="content" rows="6" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">URL Ảnh đại diện (Link ngoài)</label>
                    <input type="url" class="form-control" name="thumbnail_url" placeholder="https://domain.com/image.jpg">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Hoặc Upload từ máy</label>
                    <input type="file" class="form-control" name="thumbnail" accept="image/*">
                </div>

                <button type="submit" class="btn btn-cafe w-100 py-2"><i class="bi bi-floppy-fill me-1"></i> Đăng bài viết</button>
            </form>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card card-custom p-4">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-list-stars me-2 text-warning"></i>Danh Sách Bài Viết</h5>
            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Ảnh</th>
                            <th>Tiêu đề</th>
                            <th>Ngày đăng</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($news_list as $n): ?>
                            <tr>
                                <td><img src="<?= e($n['thumbnail']) ?>" class="img-preview-box" onerror="this.src='https://placehold.co/100x100?text=Error';"></td>
                                <td><strong><?= e($n['title']) ?></strong></td>
                                <td><small><?= date('d/m/Y', strtotime($n['created_at'])) ?></small></td>
                                <td>
                                    <a href="news.php?action=delete&id=<?= $n['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa bài viết này?');"><i class="bi bi-trash-fill"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>