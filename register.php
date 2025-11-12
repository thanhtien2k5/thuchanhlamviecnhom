<?php

function print_html_header($title) {
    echo "<!DOCTYPE html>";
    echo "<html lang='vi'>";
    echo "<head>";
    echo "    <meta charset='UTF-8'>";
    echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "    <title>" . $title . "</title>";
    echo "    ";
    echo "    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'>";
    

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
            padding: 2.5rem; /* Tăng padding cho đẹp hơn */
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h1 {
            font-size: 1.75rem; /* Điều chỉnh lại font */
            color: #333;
            margin-bottom: 1rem;
        }
        
        /* CSS CHO ICON VÀ THÔNG BÁO */
        .icon-container {
            font-size: 4rem; /* Icon to */
            margin-bottom: 1.5rem;
        }
        .icon-success {
            color: #28a745; /* Màu xanh lá thành công */
        }
        .icon-error {
            color: #dc3545; /* Màu đỏ thất bại */
        }
        
        .message-content p {
            font-size: 1rem;
            color: #555;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        /* CSS CHO NÚT QUAY LẠI */
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
            box-sizing: border-box; /* Quan trọng */
        }
        .btn-success {
            background-color: #007bff; /* Nút xanh dương */
        }
        .btn-success:hover {
            background-color: #0056b3;
        }
        .btn-error {
            background-color: #6c757d; /* Nút màu xám */
        }
        .btn-error:hover {
            background-color: #5a6268;
        }
    ";
    echo "    </style>";
    
    
    echo "</head>";
    echo "<body>";
}


function print_html_footer() {
    echo "</body>";
    echo "</html>";
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    $password_confirm = htmlspecialchars($_POST['password_confirm']);


    if ($password != $password_confirm) {
        
        print_html_header("Đăng Ký Thất Bại");
        
        echo "<div class='login-container'>";
        echo "    <div class='icon-container icon-error'>";
        echo "        <i class='fas fa-times-circle'></i>"; 
        echo "    </div>";
        echo "    <h1>Đăng Ký Thất Bại!</h1>";
        echo "    <div class='message-content'>";
        echo "        <p>Mật khẩu xác nhận không khớp. Vui lòng kiểm tra lại.</p>";
        echo "    </div>";
        echo "    <a href='register.html' class='btn-redirect btn-error'>Quay lại trang Đăng Ký</a>";
        echo "</div>";
        
        print_html_footer();
        exit; 
    }

 
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    
    $data_to_save = $email . "," . $hashed_password . "\n";

    
    file_put_contents("users.txt", $data_to_save, FILE_APPEND);

    
    print_html_header("Đăng Ký Thành Công");

    echo "<div class='login-container'>";
    echo "    <div class='icon-container icon-success'>";
    echo "        <i class='fas fa-check-circle'></i>"; 
    echo "    </div>";
    echo "    <h1>Đăng Ký Thành Công!</h1>";
    echo "    <div class='message-content'>";
    echo "        <p>Chào mừng <strong>" . $email . "</strong>! Tài khoản của bạn đã được tạo. Bạn có thể đăng nhập ngay bây giờ.</p>";
    echo "    </div>";
    echo "    <a href='index.html' class='btn-redirect btn-success'>Đi đến trang Đăng Nhập</a>";
    echo "</div>";

    print_html_footer();

} else {
    
    echo "Vui lòng đăng ký thông qua biểu mẫu.";
}
?>