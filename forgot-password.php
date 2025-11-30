<?php
// === PHẦN 1: CÁC HÀM TẠO GIAO DIỆN ===
// (Đây là các hàm copy từ các file PHP trước để tái sử dụng giao diện)

// Hàm này sẽ in ra phần đầu HTML và toàn bộ CSS
function print_html_header($title) {
    echo "<!DOCTYPE html>";
    echo "<html lang='vi'>";
    echo "<head>";
    echo "    <meta charset='UTF-8'>";
    echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "    <title>" . $title . "</title>";
    echo "    ";
    echo "    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'>";
    
    // === CSS GỘP VÀO ===
    echo "    <style>";
    echo "
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            display: grid;
            place-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #ffffff;
            padding: 2.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h1 {
            font-size: 1.75rem;
            color: #333;
            margin-bottom: 1rem;
        }
        .icon-container {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }
        .icon-success { color: #007bff; } /* Đổi thành màu xanh dương cho thông báo */
        
        .message-content p {
            font-size: 1rem;
            color: #555;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .btn-redirect {
            display: inline-block;
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
            box-sizing: border-box;
        }
        .btn-success { background-color: #007bff; }
        .btn-success:hover { background-color: #0056b3; }
    ";
    echo "    </style>";
    echo "</head>";
    echo "<body>";
}

// Hàm in ra phần kết thúc HTML
function print_html_footer() {
    echo "</body>";
    echo "</html>";
}

// === PHẦN 2: XỬ LÝ LOGIC QUÊN MẬT KHẨU ===
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Lấy email người dùng nhập
    $email_to_check = htmlspecialchars($_POST['email']);

    // --- MÔ PHỎNG VIỆC GỬI EMAIL ---
    // Ngoài đời thực, chúng ta sẽ kiểm tra email có trong DB không và gửi link.
    // Ở đây, chúng ta sẽ *luôn* hiển thị một thông báo thành công.
    // Đây là cách làm bảo mật để người khác không thể đoán email nào đã đăng ký.

    // 2. In ra giao diện thông báo
    print_html_header("Kiểm tra Email");

    echo "<div class='login-container'>";
    echo "    <div class='icon-container icon-success'>";
    echo "        <i class='fas fa-envelope-open-text'></i>"; // Icon Email
    echo "    </div>";
    echo "    <h1>Kiểm tra Email của bạn</h1>";
    echo "    <div class='message-content'>";
    echo "        <p>Yêu cầu đã được xử lý. Nếu tài khoản <strong>" . $email_to_check . "</strong> có tồn tại trong hệ thống, chúng tôi đã gửi một hướng dẫn lấy lại mật khẩu đến email đó.</p>";
    echo "        <p style='font-size: 0.9rem; color: #777;'>(Vì đây là bản demo, bạn sẽ không nhận được email thật. Bạn có thể tự mở file <strong>users.txt</strong> để kiểm tra).</p>";
    echo "    </div>";
    echo "    <a href='index.html' class='btn-redirect btn-success'>Quay lại trang Đăng Nhập</a>";
    echo "</div>";

    print_html_footer();

} else {
    // Nếu ai đó cố gắng truy cập file này trực tiếp
    echo "Vui lòng truy cập thông qua biểu mẫu.";
}
?>