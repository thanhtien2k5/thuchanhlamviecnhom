<?php
// login.php
session_start();
require_once 'db.php';

$error = '';
$success = '';

// XỬ LÝ FORM
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action']; // 'login' hoặc 'register'

    // --- XỬ LÝ ĐĂNG KÝ ---
    if ($action === 'register') {
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            $error = "Mật khẩu nhập lại không khớp!";
        } else {
            // Kiểm tra email đã tồn tại chưa
            $stmt = $conn->prepare("SELECT id FROM nguoi_dung WHERE email = :email");
            $stmt->execute([':email' => $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Email này đã được đăng ký!";
            } else {
                // Mã hóa mật khẩu và lưu vào DB
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO nguoi_dung (ho_va_ten, email, mat_khau) VALUES (:name, :email, :pass)";
                $stmt = $conn->prepare($sql);
                
                if ($stmt->execute([':name' => $fullname, ':email' => $email, ':pass' => $hashed_password])) {
                    $success = "Đăng ký thành công! Hãy đăng nhập ngay.";
                } else {
                    $error = "Có lỗi xảy ra, vui lòng thử lại.";
                }
            }
        }
    } 
    // --- XỬ LÝ ĐĂNG NHẬP ---
    elseif ($action === 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM nguoi_dung WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['mat_khau'])) {
            // Đăng nhập thành công -> Lưu session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['ho_va_ten'];
            
            // Chuyển hướng về trang chủ
            header("Location: index.php");
            exit;
        } else {
            $error = "Email hoặc mật khẩu không đúng!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập / Đăng ký - HCM Ideology</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-red': '#c62828',
                        'brand-gold': '#fbc02d',
                        'brand-dark': '#1e293b',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Merriweather', 'serif'],
                    }
                }
            }
        }
    </script>
    <style>
        /* Hiệu ứng chuyển đổi Form */
        .form-container {
            transition: all 0.5s ease-in-out;
        }
        .hidden-form {
            display: none;
            opacity: 0;
            transform: translateY(20px);
        }
        .visible-form {
            display: block;
            opacity: 1;
            transform: translateY(0);
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>
<body class="hero-bg min-h-screen flex items-center justify-center p-4">

    <div class="glass-card bg-white/95 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden relative border border-white/30">
        
        <a href="index.php" class="absolute top-4 left-4 text-slate-400 hover:text-brand-red transition z-10">
            <i class="fa-solid fa-arrow-left text-xl"></i>
        </a>

        <div class="flex border-b border-slate-100">
            <button onclick="switchTab('login')" id="tab-login" class="flex-1 py-4 font-bold text-center text-brand-red border-b-2 border-brand-red transition bg-red-50/50">
                Đăng Nhập
            </button>
            <button onclick="switchTab('register')" id="tab-register" class="flex-1 py-4 font-bold text-center text-slate-400 hover:text-brand-red transition">
                Đăng Ký
            </button>
        </div>

        <div class="p-8">
            <?php if($error): ?>
                <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm font-medium flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm font-medium flex items-center gap-2">
                    <i class="fa-solid fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form id="form-login" method="POST" class="visible-form space-y-5">
                <input type="hidden" name="action" value="login">
                
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-serif font-bold text-slate-800">Chào mừng trở lại!</h2>
                    <p class="text-slate-500 text-sm">Nhập thông tin để tiếp tục ôn tập.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <div class="relative">
                        <i class="fa-regular fa-envelope absolute left-4 top-3.5 text-slate-400"></i>
                        <input type="email" name="email" required class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-brand-red focus:ring-1 focus:ring-brand-red outline-none transition" placeholder="name@example.com">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Mật khẩu</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-4 top-3.5 text-slate-400"></i>
                        <input type="password" name="password" required class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-brand-red focus:ring-1 focus:ring-brand-red outline-none transition" placeholder="••••••••">
                    </div>
                    <div class="text-right mt-1">
                        <a href="#" class="text-xs text-brand-red hover:underline">Quên mật khẩu?</a>
                    </div>
                </div>

                <button type="submit" class="w-full btn-primary py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transform transition active:scale-95">
                    Đăng Nhập
                </button>
            </form>

            <form id="form-register" method="POST" class="hidden-form space-y-4">
                <input type="hidden" name="action" value="register">
                
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-serif font-bold text-slate-800">Tạo tài khoản mới</h2>
                    <p class="text-slate-500 text-sm">Tham gia cộng đồng ôn thi HCM Ideology.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Họ và tên</label>
                    <div class="relative">
                        <i class="fa-regular fa-user absolute left-4 top-3.5 text-slate-400"></i>
                        <input type="text" name="fullname" required class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-brand-red focus:ring-1 focus:ring-brand-red outline-none transition" placeholder="Nguyễn Văn A">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <div class="relative">
                        <i class="fa-regular fa-envelope absolute left-4 top-3.5 text-slate-400"></i>
                        <input type="email" name="email" required class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-brand-red focus:ring-1 focus:ring-brand-red outline-none transition" placeholder="name@example.com">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Mật khẩu</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-4 top-3.5 text-slate-400"></i>
                        <input type="password" name="password" required class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-brand-red focus:ring-1 focus:ring-brand-red outline-none transition" placeholder="••••••••">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nhập lại mật khẩu</label>
                    <div class="relative">
                        <i class="fa-solid fa-check-double absolute left-4 top-3.5 text-slate-400"></i>
                        <input type="password" name="confirm_password" required class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-brand-red focus:ring-1 focus:ring-brand-red outline-none transition" placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-800 text-white hover:bg-slate-900 py-3 rounded-xl font-bold shadow-lg transform transition active:scale-95">
                    Đăng Ký Ngay
                </button>
            </form>
        </div>
        
        <div class="bg-slate-50 p-6 text-center border-t border-slate-100">
            <p class="text-xs text-slate-500 mb-4">Hoặc tiếp tục với</p>
            <div class="flex justify-center gap-4">
                <button class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-blue-600 hover:bg-blue-50 transition"><i class="fa-brands fa-facebook-f"></i></button>
                <button class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-red-500 hover:bg-red-50 transition"><i class="fa-brands fa-google"></i></button>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            const formLogin = document.getElementById('form-login');
            const formRegister = document.getElementById('form-register');
            const tabLogin = document.getElementById('tab-login');
            const tabRegister = document.getElementById('tab-register');

            if (tab === 'login') {
                formLogin.classList.remove('hidden-form');
                formLogin.classList.add('visible-form');
                formRegister.classList.remove('visible-form');
                formRegister.classList.add('hidden-form');

                tabLogin.className = 'flex-1 py-4 font-bold text-center text-brand-red border-b-2 border-brand-red transition bg-red-50/50';
                tabRegister.className = 'flex-1 py-4 font-bold text-center text-slate-400 hover:text-brand-red transition';
            } else {
                formRegister.classList.remove('hidden-form');
                formRegister.classList.add('visible-form');
                formLogin.classList.remove('visible-form');
                formLogin.classList.add('hidden-form');

                tabRegister.className = 'flex-1 py-4 font-bold text-center text-brand-red border-b-2 border-brand-red transition bg-red-50/50';
                tabLogin.className = 'flex-1 py-4 font-bold text-center text-slate-400 hover:text-brand-red transition';
            }
        }
        
        // Mặc định hiển thị form dựa trên biến PHP (nếu đăng ký thành công thì chuyển qua login)
        <?php if ($success) echo "switchTab('login');"; ?>
    </script>
</body>
</html>