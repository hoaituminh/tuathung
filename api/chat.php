<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

// Nhận dữ liệu JSON từ client
$input = json_decode(file_get_contents('php://input'), true);
$user_message = trim($input['message'] ?? '');

if (empty($user_message)) {
    echo json_encode(['reply' => 'Vui lòng nhập nội dung cần tư vấn.']);
    exit;
}

try {
    // 1. Lấy toàn bộ Sản phẩm đang bán
    $stmt_p = $conn->query("SELECT p.name, p.price, p.sale_price, p.weight, p.origin, p.roast_level, p.stock, c.name as category_name 
                            FROM products p JOIN categories c ON p.category_id = c.id 
                            WHERE p.status = 1");
    $products = $stmt_p->fetchAll(PDO::FETCH_ASSOC);

    // 2. Lấy danh sách Mã giảm giá (Coupons) đang còn hạn
    $stmt_c = $conn->query("SELECT code, discount_type, discount_value, min_order_amount, end_date 
                            FROM coupons 
                            WHERE status = 1 AND start_date <= CURDATE() AND end_date >= CURDATE()");
    $coupons = $stmt_c->fetchAll(PDO::FETCH_ASSOC);

    // 3. Lấy danh sách Tin tức mới nhất (Dựa chuẩn theo cột title và content của CSDL)
    $stmt_n = $conn->query("SELECT title, content FROM news WHERE status = 1 ORDER BY created_at DESC LIMIT 5");
    $news_list = $stmt_n->fetchAll(PDO::FETCH_ASSOC);

    // 4. Đóng gói dữ liệu vào ngữ cảnh (Context)
    $context = "=== THÔNG TIN CỬA HÀNG AROMA COFFEE ===\n";
    $context .= "- Địa chỉ: 123 Đường Cà Phê, TP.HCM | Hotline: 090 123 4567\n";
    $context .= "- Phí vận chuyển: Đồng giá 30.000đ toàn quốc. MIỄN PHÍ VẬN CHUYỂN cho đơn hàng từ 300.000đ trở lên.\n\n";

    $context .= "=== DANH SÁCH MÃ GIẢM GIÁ ĐANG DÙNG ĐƯỢC ===\n";
    if (empty($coupons)) {
        $context .= "Hiện tại chưa có mã giảm giá nào active.\n\n";
    } else {
        foreach ($coupons as $cp) {
            $val = $cp['discount_type'] === 'percent' ? $cp['discount_value'] . "%" : format_currency($cp['discount_value']);
            $context .= "- Mã: {$cp['code']} | Giảm: {$val} | Đơn tối thiểu: " . format_currency($cp['min_order_amount']) . " | Hạn đến: " . date('d/m/Y', strtotime($cp['end_date'])) . "\n";
        }
        $context .= "\n";
    }

    $context .= "=== GÓC CÀ PHÊ & TIN TỨC MỚI NHẤT ===\n";
    if (empty($news_list)) {
        $context .= "Hiện chưa có bài viết tin tức nào.\n\n";
    } else {
        foreach ($news_list as $n) {
            // Tự động cắt ngắn nội dung bài viết làm tóm tắt an toàn
            $short_content = mb_substr(strip_tags($n['content']), 0, 120) . '...';
            $context .= "- Bài viết: {$n['title']} | Nội dung tóm tắt: {$short_content}\n";
        }
        $context .= "\n";
    }

    $context .= "=== DANH SÁCH SẢN PHẨM TRONG KHO ===\n";
    foreach ($products as $p) {
        $price_str = $p['sale_price'] ? format_currency($p['sale_price']) . " (Giá gốc: " . format_currency($p['price']) . ")" : format_currency($p['price']);
        $context .= "- {$p['name']} | Loại: {$p['category_name']} | Giá: {$price_str} | Trọng lượng: {$p['weight']} | Xuất xứ: {$p['origin']} | Mức rang: {$p['roast_level']} | Tồn: {$p['stock']}\n";
    }

    // 5. Cấu hình System Prompt (Thông minh, linh hoạt, chống rập khuôn)
    $system_instruction = "Bạn là trợ lý ảo thân thiện và chuyên nghiệp của cửa hàng Aroma Coffee.\n"
                        . "QUY TẮC TRẢ LỜI:\n"
                        . "1. Trả lời súc tích, đi thẳng vào vấn đề (từ 2 đến 4 câu). Nếu khách hỏi về tin tức/bài viết, hãy dựa vào danh sách tin tức bên dưới để trả lời.\n"
                        . "2. Linh hoạt thay đổi cách dùng từ và cấu trúc câu ở mỗi lần trả lời, tuyệt đối không lặp lại một khuôn mẫu nhàm chán.\n"
                        . "3. Giữ thái độ niềm nở, tự nhiên như nhân viên bán hàng thực thụ (có thể dùng 'dạ', 'shop', 'bạn').\n"
                        . "4. CHỈ dùng văn bản thuần (Plain Text), TUYỆT ĐỐI KHÔNG dùng ký tự Markdown như **, *, #, -, [].\n"
                        . "5. Sử dụng chính xác dữ liệu thực tế bên dưới để trả lời về giá, mã giảm giá, phí ship (30k, freeship từ 300k).\n\n"
                        . $context;

    // 6. Gọi Google Gemini API
    $apiKey = ''; // Thay Gemini API Key của bạn tại đây
    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent?key=" . $apiKey;

    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $system_instruction . "\nKhách hỏi: " . $user_message]
                ]
            ]
        ]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        $raw_reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Xin lỗi, hiện tại tôi chưa tìm thấy thông tin phù hợp.';
        
        // 7. LỌC BỎ SẠCH KÝ TỰ MARKDOWN BẰNG PHP
        $clean_reply = preg_replace('/[\*\#]/', '', $raw_reply); 
        $clean_reply = trim($clean_reply);

        echo json_encode(['reply' => nl2br(htmlspecialchars($clean_reply))]);
    } else {
        echo json_encode(['reply' => 'Hệ thống AI đang bận, bạn vui lòng thử lại sau nhé.']);
    }

} catch (Exception $e) {
    echo json_encode(['reply' => 'Lỗi kết nối: ' . $e->getMessage()]);
}