<?php
/**
 * Footer chung cho toàn bộ website khách hàng
 */
?>
    <!-- Footer Section -->
    <footer class="bg-dark text-white pt-5 pb-3 mt-auto">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5 class="fw-bold text-warning mb-3"><i class="bi bi-cup-hot-fill me-2"></i>Aroma Coffee</h5>
                    <p class="text-white-50 small">Thưởng thức hương vị cà phê nguyên bản, đậm đà bản sắc Việt Nam từ những hạt cà phê tuyển chọn kỹ lưỡng nhất.</p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold text-white mb-3">Liên Kết Nhanh</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= BASE_URL ?>/index.php" class="text-white-50 text-decoration-none">Trang chủ</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/products.php" class="text-white-50 text-decoration-none">Sản phẩm</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/news.php" class="text-white-50 text-decoration-none">Tin tức</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/contact.php" class="text-white-50 text-decoration-none">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold text-white mb-3">Thông Tin Liên Hệ</h6>
                    <p class="text-white-50 small mb-2"><i class="bi bi-geo-alt-fill me-2"></i>123 Đường Cà Phê, TP.HCM</p>
                    <p class="text-white-50 small mb-2"><i class="bi bi-telephone-fill me-2"></i>090 123 4567</p>
                    <p class="text-white-50 small mb-2"><i class="bi bi-envelope-fill me-2"></i>support@aromacoffee.vn</p>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="text-center text-white-50 small">
                &copy; <?= date('Y') ?> Aroma Coffee. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Nhúng Trợ lý AI Chatbot -->
    <?php include_once __DIR__ . '/chat-widget.php'; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>