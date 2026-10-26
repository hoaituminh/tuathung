<?php
require_once 'includes/functions.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM news WHERE id = ? AND status = 1");
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    redirect('news.php');
}

$page_title = htmlspecialchars($news['title']) . ' - Aroma Coffee';
include 'includes/header.php';
?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm p-4">
                <h1 class="fw-bold text-cafe mb-3"><?= htmlspecialchars($news['title']) ?></h1>
                <small class="text-muted mb-4 d-block"><i class="bi bi-calendar-event me-1"></i><?= date('d/m/Y H:i', strtotime($news['created_at'])) ?></small>
                
                <?php if (!empty($news['thumbnail'])): ?>
                    <img src="<?= htmlspecialchars($news['thumbnail']) ?>" class="img-fluid rounded mb-4 shadow-sm" alt="<?= htmlspecialchars($news['title']) ?>">
                <?php endif; ?>

                <!-- Thêm nl2br() để giữ nguyên định dạng xuống dòng và cách dòng của bài viết -->
                <div class="article-content" style="line-height: 1.8; color: #444;">
                    <?= nl2br($news['content']) ?>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <a href="news.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách tin tức
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>