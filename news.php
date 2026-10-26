<?php
require_once 'includes/functions.php';
$news_list = $conn->query("SELECT * FROM news WHERE status = 1 ORDER BY id DESC")->fetchAll();

$page_title = 'Tin tức & Bài viết - Aroma Coffee';
include 'includes/header.php';
?>

<div class="container my-4">
    <h2 class="fw-bold text-cafe mb-4 text-center">Góc Cà Phê & Tin Tức</h2>
    <div class="row">
        <?php foreach ($news_list as $news): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <img src="<?= htmlspecialchars($news['thumbnail']) ?>" class="card-img-top" alt="<?= htmlspecialchars($news['title']) ?>" style="height: 180px; object-fit: cover;">
                    <div class="card-body">
                        <small class="text-muted"><i class="bi bi-calendar-event me-1"></i><?= date('d/m/Y', strtotime($news['created_at'])) ?></small>
                        <h5 class="card-title mt-2 fs-6"><a href="news-detail.php?id=<?= $news['id'] ?>" class="text-decoration-none text-dark fw-bold"><?= htmlspecialchars($news['title']) ?></a></h5>
                    </div>
                    <div class="card-footer bg-white border-0 pb-3">
                        <a href="news-detail.php?id=<?= $news['id'] ?>" class="text-cafe text-decoration-none fw-bold small">Đọc tiếp <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>