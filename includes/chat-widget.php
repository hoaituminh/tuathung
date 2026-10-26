<!-- Bong bóng chat nổi -->
<div id="aroma-chat-widget" style="position: fixed; bottom: 20px; right: 20px; z-index: 1050;">
    <!-- Nút mở chat -->
    <button id="chat-toggle-btn" class="btn btn-cafe rounded-circle shadow-lg p-3 text-white" style="width: 60px; height: 60px; background-color: #4A3525;">
        <i class="bi bi-chat-dots-fill fs-4"></i>
    </button>

    <!-- Khung chat box (ẩn mặc định) -->
    <div id="chat-box-container" class="card shadow-lg border-0 d-none" style="width: 350px; height: 480px; border-radius: 16px; overflow: hidden;">
        <!-- Header -->
        <div class="card-header bg-cafe text-white d-flex justify-content-between align-items-center py-3" style="background-color: #4A3525;">
            <h6 class="mb-0 fw-bold"><i class="bi bi-robot me-2"></i>Trợ lý AI Aroma Coffee</h6>
            <button type="button" id="chat-close-btn" class="btn-close btn-close-white"></button>
        </div>
        <!-- Nội dung tin nhắn -->
        <div id="chat-messages" class="card-body overflow-auto bg-light p-3" style="height: 360px; display: flex; flex-direction: column; gap: 10px;">
            <div class="d-flex mb-2">
                <div class="bg-white p-3 rounded-3 shadow-sm text-dark small" style="max-width: 80%;">
                    👋 Chào bạn! Mình là trợ lý ảo của Aroma Coffee. Bạn cần tư vấn loại cà phê nào hôm nay?
                </div>
            </div>
        </div>
        <!-- Footer nhập tin nhắn -->
        <div class="card-footer bg-white border-0 p-2">
            <form id="chat-form" class="input-group">
                <input type="text" id="chat-input" class="form-control form-control-sm border-0 bg-light" placeholder="Nhập câu hỏi..." required>
                <button class="btn btn-cafe btn-sm px-3" type="submit" style="background-color: #4A3525; color: #fff;"><i class="bi bi-send-fill"></i></button>
            </form>
        </div>
    </div>
</div>

<style>
#chat-messages { scroll-behavior: smooth; }
.chat-bubble-user { align-self: flex-end; background-color: #4A3525; color: white; padding: 10px 14px; border-radius: 12px 12px 0 12px; max-width: 80%; font-size: 0.9rem; }
.chat-bubble-ai { align-self: flex-start; background-color: white; color: #333; padding: 10px 14px; border-radius: 12px 12px 12px 0; max-width: 80%; font-size: 0.9rem; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
</style>

<script>
document.getElementById('chat-toggle-btn').addEventListener('click', function() {
    var box = document.getElementById('chat-box-container');
    box.classList.toggle('d-none');
    this.classList.toggle('d-none');
});

document.getElementById('chat-close-btn').addEventListener('click', function() {
    document.getElementById('chat-box-container').classList.add('d-none');
    document.getElementById('chat-toggle-btn').classList.remove('d-none');
});

document.getElementById('chat-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var input = document.getElementById('chat-input');
    var message = input.value.trim();
    if (!message) return;

    var chatMsgs = document.getElementById('chat-messages');
    
    // Hiển thị tin nhắn user
    chatMsgs.innerHTML += `<div class="chat-bubble-user">${escapeHtml(message)}</div>`;
    input.value = '';
    chatMsgs.scrollTop = chatMsgs.scrollHeight;

    // Hiển thị trạng thái đang trả lời
    var loadingId = 'loading-' + Date.now();
    chatMsgs.innerHTML += `<div id="${loadingId}" class="chat-bubble-ai text-muted italic">Trợ lý đang suy nghĩ...</div>`;
    chatMsgs.scrollTop = chatMsgs.scrollHeight;

    // Gửi AJAX lên server PHP
    fetch('api/chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: message })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById(loadingId).remove();
        if (data.reply) {
            chatMsgs.innerHTML += `<div class="chat-bubble-ai">${data.reply}</div>`;
        } else {
            chatMsgs.innerHTML += `<div class="chat-bubble-ai text-danger">Xin lỗi, hệ thống đang bận.</div>`;
        }
        chatMsgs.scrollTop = chatMsgs.scrollHeight;
    })
    .catch(err => {
        document.getElementById(loadingId).remove();
        chatMsgs.innerHTML += `<div class="chat-bubble-ai text-danger">Lỗi kết nối máy chủ.</div>`;
    });
});

function escapeHtml(text) {
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
</script>