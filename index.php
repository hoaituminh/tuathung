<?php
require_once 'includes/functions.php';

// Lấy danh mục nổi bật
$categories = $conn->query("SELECT * FROM categories WHERE status = 1 LIMIT 6")->fetchAll();

// Lấy sản phẩm nổi bật
$featured_products = $conn->query("SELECT * FROM products WHERE status = 1 AND is_featured = 1 LIMIT 8")->fetchAll();

// Lấy sản phẩm mới nhất
$new_products = $conn->query("SELECT * FROM products WHERE status = 1 ORDER BY id DESC LIMIT 8")->fetchAll();

// Lấy tin tức mới
$latest_news = $conn->query("SELECT * FROM news WHERE status = 1 ORDER BY id DESC LIMIT 3")->fetchAll();

$page_title = 'Aroma Coffee - Thưởng thức hương vị nguyên bản';
include 'includes/header.php';
?>

<!-- Hero Banner với Video Background chuyển mờ sang đen -->
<div class="py-5 mb-5 text-white position-relative overflow-hidden" style="min-height: 550px; display: flex; align-items: center; background-color: #000;">
    
    <!-- Thẻ Video Background có gắn id="heroVideo" -->
    <video id="heroVideo" autoplay muted playsinline class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover; z-index: 0; transition: opacity 0.6s ease-in-out;">
        <source src="assets/videos/coffee-bg.mp4" type="video/mp4">
        Trình duyệt của bạn không hỗ trợ thẻ video.
    </video>

    <!-- Nội dung chữ và nút bấm -->
    <div class="container py-5 text-center position-relative animate-fade-in" style="z-index: 2;">
        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3 text-uppercase" style="letter-spacing: 1px;">Aroma Coffee Premium</span>
        <h1 class="display-3 fw-bold mb-3">Hương Vị Cà Phê Nguyên Bản</h1>
        <p class="lead mb-4 col-md-8 mx-auto opacity-90 fs-5">Tuyển chọn từ những hạt cà phê hảo hạng nhất của cao nguyên Việt Nam, mang đến trải nghiệm tuyệt vời cho từng ngụm cà phê.</p>
        <a href="products.php" class="btn btn-warning btn-lg px-5 py-3 fw-bold rounded-pill shadow text-dark btn-hover-grow">
            Khám Phá Menu <i class="bi bi-arrow-right ms-2"></i>
        </a>
    </div>
</div>

<!-- Đoạn JavaScript xử lý hiệu ứng mờ dần sang đen khi lặp video -->
<script>
const video = document.getElementById('heroVideo');

// Khi video chạy gần hết (còn 0.6 giây), giảm độ mờ về 0 (chìm vào nền đen)
video.addEventListener('timeupdate', function() {
    if (video.duration - video.currentTime <= 0.6) {
        video.style.opacity = 0; 
    }
});

// Khi video kết thúc, tua về đầu, phát lại và sáng rõ trở lại từ nền đen
video.addEventListener('ended', function() {
    video.currentTime = 0;
    video.play();
    video.style.opacity = 1; 
});
</script>

<div class="container">
    <!-- Danh mục sản phẩm -->
    <section class="mb-5 animate-fade-in">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-cafe display-6">Danh Mục Sản Phẩm</h2>
            <div class="mx-auto bg-warning" style="width: 60px; height: 4px; border-radius: 2px;"></div>
        </div>
        <div class="row g-4">
            <?php foreach ($categories as $cat): ?>
                <div class="col-md-4 col-lg-2 col-6">
                    <a href="products.php?category=<?= $cat['id'] ?>" class="text-decoration-none">
                        <div class="card border-0 shadow-sm text-center h-100 p-3 category-card">
                            <div class="overflow-hidden rounded-circle mx-auto mb-3" style="width: 90px; height: 90px; border: 3px solid #F4E8D1;">
                                <img src="<?= e($cat['image']) ?>" class="w-100 h-100" style="object-fit: cover;" onerror="this.src='https://placehold.co/100x100?text=Category';">
                            </div>
                            <h6 class="card-title text-dark fw-bold mb-0"><?= e($cat['name']) ?></h6>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Sản phẩm nổi bật -->
    <section class="mb-5 animate-fade-in">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h3 class="fw-bold text-cafe m-0 display-6">Sản Phẩm Nổi Bật</h3>
                <small class="text-muted">Những món được yêu thích nhất tại Aroma</small>
            </div>
            <a href="products.php?featured=1" class="btn btn-outline-cafe btn-sm rounded-pill px-3">Xem tất cả <i class="bi bi-chevron-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach ($featured_products as $prod): ?>
                <div class="col-6 col-md-3">
                    <div class="card h-100 shadow-sm border-0 product-card">
                        <div class="img-wrapper">
                            <?php if ($prod['sale_price']): ?>
                                <span class="badge bg-danger position-absolute top-0 start-0 m-3 z-1 px-3 py-2 rounded-pill shadow-sm">Giảm giá</span>
                            <?php endif; ?>
                            <a href="product-detail.php?id=<?= $prod['id'] ?>">
                                <img src="<?= e($prod['image']) ?>" class="card-img-top" style="height: 220px; object-fit: cover;" onerror="this.src='https://placehold.co/400x300?text=Product';">
                            </a>
                        </div>
                        <div class="card-body d-flex flex-column p-3">
                            <h5 class="card-title fs-6 mb-2">
                                <a href="product-detail.php?id=<?= $prod['id'] ?>" class="text-decoration-none text-dark fw-bold text-truncate d-block"><?= e($prod['name']) ?></a>
                            </h5>
                            <div class="mt-auto">
                                <div class="mb-2">
                                    <?php if ($prod['sale_price']): ?>
                                        <span class="text-danger fw-bold fs-5 me-2"><?= format_currency($prod['sale_price']) ?></span>
                                        <span class="text-muted text-decoration-line-through small"><?= format_currency($prod['price']) ?></span>
                                    <?php else: ?>
                                        <span class="text-cafe fw-bold fs-5"><?= format_currency($prod['price']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <a href="product-detail.php?id=<?= $prod['id'] ?>" class="btn btn-cafe btn-sm w-100 rounded-pill"><i class="bi bi-cart-plus me-1"></i> Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Tin tức góc cà phê -->
    <section class="mb-5 animate-fade-in">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-cafe display-6">Góc Cà Phê & Tin Tức</h3>
            <p class="text-muted">Cùng lắng nghe những câu chuyện về cà phê</p>
        </div>
        <div class="row g-4">
            <?php foreach ($latest_news as $news): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0 product-card">
                        <img src="<?= e($news['thumbnail']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;" onerror="this.src='https://placehold.co/600x400?text=News';">
                        <div class="card-body">
                            <small class="text-muted"><i class="bi bi-calendar-event me-1"></i><?= date('d/m/Y', strtotime($news['created_at'])) ?></small>
                            <h5 class="card-title mt-2 fs-6 fw-bold"><a href="news-detail.php?id=<?= $news['id'] ?>" class="text-decoration-none text-dark"><?= e($news['title']) ?></a></h5>
                        </div>
                        <div class="card-footer bg-white border-0 pb-3">
                            <a href="news-detail.php?id=<?= $news['id'] ?>" class="text-cafe text-decoration-none fw-bold small">Đọc tiếp <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>