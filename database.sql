CREATE DATABASE IF NOT EXISTS cafe_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cafe_shop;

-- 1. Bảng users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT DEFAULT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    status ENUM('active', 'blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng products
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    weight VARCHAR(50) DEFAULT NULL,
    origin VARCHAR(100) DEFAULT NULL,
    roast_level VARCHAR(50) DEFAULT NULL,
    stock INT NOT NULL DEFAULT 0,
    sold INT NOT NULL DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bảng product_images
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bảng coupons
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percent', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2) DEFAULT 0,
    quantity INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Bảng cart
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Bảng orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_code VARCHAR(50) NOT NULL UNIQUE,
    receiver_name VARCHAR(100) NOT NULL,
    receiver_phone VARCHAR(20) NOT NULL,
    shipping_address TEXT NOT NULL,
    note TEXT DEFAULT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    shipping_fee DECIMAL(10,2) DEFAULT 0,
    payment_method VARCHAR(50) DEFAULT 'COD',
    payment_status VARCHAR(50) DEFAULT 'unpaid',
    order_status VARCHAR(50) DEFAULT 'pending',
    coupon_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Bảng order_details
CREATE TABLE IF NOT EXISTS order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Bảng reviews
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Bảng wishlist
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Bảng contacts
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    message TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'mới',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Bảng news
CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    thumbnail VARCHAR(255) DEFAULT NULL,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- DỮ LIỆU MẪU (SEED DATA) ---
-- Mật khẩu gốc cho admin: Admin@123 (đã hash password)
-- Mật khẩu gốc cho khách: User@123
INSERT INTO users (full_name, email, phone, password, address, role, status) VALUES
('Quản Trị Viên', 'admin@cafe.com', '0901234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123 Đường Cà Phê, TP.HCM', 'admin', 'active'),
('Nguyễn Văn Khách', 'khach1@cafe.com', '0912345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '456 Lê Lợi, TP.HCM', 'customer', 'active'),
('Trần Thị Khách', 'khach2@cafe.com', '0923456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '789 Nguyễn Huệ, TP.HCM', 'customer', 'active');

INSERT INTO categories (name, slug, description, image, status) VALUES
('Cà phê hạt', 'ca-phe-hat', 'Cà phê hạt rang mộc nguyên chất, đa dạng chủng loại.', 'ca-phe-hat.jpg', 1),
('Cà phê bột', 'ca-phe-bot', 'Cà phê bột pha phin truyền thống thơm ngon.', 'ca-phe-bot.jpg', 1),
('Cà phê hòa tan', 'ca-phe-hoa-tan', 'Tiện lợi, nhanh chóng, chuẩn vị cà phê sữa đá.', 'ca-phe-hoa-tan.jpg', 1),
('Dụng cụ pha chế', 'dung-cu-pha-che', 'Phin pha, máy pha cà phê gia đình và phụ kiện.', 'dung-cu.jpg', 1),
('Trà & Khác', 'tra-va-khac', 'Các loại trà thảo mộc và thức uống thay thế.', 'tra.jpg', 1);

INSERT INTO products (category_id, name, slug, description, price, sale_price, image, weight, origin, roast_level, stock, sold, is_featured, status) VALUES
(1, 'Cà phê hạt Arabica Cầu Đất', 'ca-phe-hat-arabica-cau-dat', 'Hạt Arabica nguyên chất vùng Cầu Đất, vị chua thanh, hương hoa quả.', 180000, 165000, 'arabica.jpg', '500g', 'Lâm Đồng, Việt Nam', 'Vừa (Medium)', 100, 25, 1, 1),
(1, 'Cà phê hạt Robusta Buôn Ma Thuột', 'ca-phe-hat-robusta-buon-ma-thuot', 'Hạt Robusta đậm đà, vị đắng đậm, hàm lượng cafein cao.', 140000, NULL, 'robusta.jpg', '500g', 'Đắk Lắk, Việt Nam', 'Đậm (Dark)', 150, 40, 1, 1),
(1, 'Cà phê hạt Espresso Blend', 'ca-phe-hat-espresso-blend', 'Phối trộn hoàn hảo giữa Arabica và Robusta chuyên dùng cho máy pha.', 210000, 190000, 'espresso.jpg', '500g', 'Việt Nam & Brazil', 'Vừa thiên đậm', 80, 18, 1, 1),
(2, 'Cà phê bột Phin Đặc Biệt', 'ca-phe-bot-phin-dac-biet', 'Xay mịn vừa phải, chuyên pha phin truyền thống đậm vị Việt Nam.', 120000, NULL, 'phin-dac-biet.jpg', '500g', 'Buôn Ma Thuột', 'Vừa', 200, 60, 1, 1),
(2, 'Cà phê bột Moka Cầu Đất', 'ca-phe-bot-moka-cau-dat', 'Hương thơm quyến rũ, hậu vị ngọt sâu.', 230000, 210000, 'moka.jpg', '500g', 'Cầu Đất', 'Nhạt (Light-Medium)', 50, 12, 1, 1),
(3, 'Cà phê hòa tan 3in1 Sữa Đá', 'ca-phe-hoa-tan-3in1-sua-da', 'Hộp 20 gói tiện lợi chuẩn vị cà phê sữa đá truyền thống.', 75000, 68000, 'hoa-tan.jpg', '480g', 'Việt Nam', 'N/A', 300, 120, 1, 1),
(4, 'Phin pha cà phê Inox cao cấp', 'phin-pha-ca-phen-inox-cao-cap', 'Phin inox sáng bóng, lỗ đục đều giúp chiết xuất cà phê hoàn hảo.', 85000, NULL, 'phin-inox.jpg', '1 cái', 'Việt Nam', 'N/A', 90, 30, 0, 1),
(4, 'Ấm pha cà phê Moka Pot 3 Cups', 'am-pha-ca-phen-moka-pot-3-cups', 'Ấm pha kiểu Ý pha trực tiếp trên bếp từ hoặc bếp ga.', 350000, 320000, 'moka-pot.jpg', '1 cái', 'Ý', 'N/A', 40, 15, 1, 1),
(5, 'Trà Earl Grey Hoa Hồng', 'tra-earl-grey-hoa-hong', 'Trà đen hương cam bergamot kết hợp cánh hoa hồng sấy lạnh.', 110000, NULL, 'earl-grey.jpg', '200g', 'Lâm Đồng', 'N/A', 70, 22, 0, 1);

INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, quantity, start_date, end_date, status) VALUES
('CAFEWELCOME', 'percent', 10.00, 100000, 50, '2026-01-01', '2026-12-31', 1),
('FREESHIP', 'fixed', 30000, 200000, 100, '2026-01-01', '2026-12-31', 1);