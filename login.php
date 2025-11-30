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
        .icon-success { color: #28a745; }
        .icon-error { color: #dc3545; }
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
        .btn-error { background-color: #dc3545; } /* Đổi nút lỗi thành màu đỏ */
        .btn-error:hover { background-color: #c82333; }
    ";
    echo "    </style>";
    echo "</head>";
    echo "<body>";
}

function print_html_footer() {
    echo "</body>";
    echo "</html>";
}

function show_error_page($message, $button_text, $button_link) {
    print_html_header("Đăng Nhập Thất Bại");
    echo "<div class='login-container'>";
    echo "    <div class='icon-container icon-error'>";
    echo "        <i class='fas fa-exclamation-triangle'></i>"; 
    echo "    </div>";
    echo "    <h1>Đăng Nhập Thất Bại!</h1>";
    echo "    <div class='message-content'>";
    echo "        <p>" . $message . "</p>";
    echo "    </div>";
    echo "    <a href='" . $button_link . "' class='btn-redirect btn-error'>" . $button_text . "</a>";
    echo "</div>";
    print_html_footer();
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    
    $email_to_login = htmlspecialchars($_POST['email']);
    $password_to_login = htmlspecialchars($_POST['password']);

    
    $file_path = "users.txt";
    if (!file_exists($file_path)) {
        show_error_page("Không tìm thấy file dữ liệu người dùng.", "Quay lại", "index.html");
    }

    $users_file = fopen($file_path, "r");
    $user_found = false;

    
    while (($line = fgets($users_file)) !== false) {

        $user_data = explode(",", trim($line));
        

        if (count($user_data) == 2) {
            $saved_email = $user_data[0];
            $saved_hashed_password = $user_data[1];

            
            if ($saved_email == $email_to_login) {
                $user_found = true;

                
                if (password_verify($password_to_login, $saved_hashed_password)) {
                    
                    
                    fclose($users_file); 

                    
                    header("Location: trangchu.html");
                    
                
                    exit; 

                } else {
                    
                    fclose($users_file);
                    show_error_page("Mật khẩu bạn nhập không chính xác. Vui lòng thử lại.", "Thử lại", "index.html");
                }
            }
        }
    }

    
    fclose($users_file);

    
    if (!$user_found) {
        show_error_page("Email <strong>" . $email_to_login . "</strong> không tồn tại trong hệ thống.", "Đăng Ký", "register.html");
    }

} else {
    
    echo "Vui lòng đăng nhập thông qua biểu mẫu.";
}
?>