<?php
session_start();
require_once 'db.php';

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";
$msgType = "";

// 2. XỬ LÝ FORM
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- CẬP NHẬT THÔNG TIN ---
    if ($action === 'update_info') {
        $fullname = trim($_POST['ho_va_ten']);
        $email = trim($_POST['email']);

        try {
            // Check trùng email
            $stmtCheck = $conn->prepare("SELECT id FROM nguoi_dung WHERE email = ? AND id != ?");
            $stmtCheck->execute([$email, $user_id]);
            if ($stmtCheck->rowCount() > 0) {
                throw new Exception("Email này đã được sử dụng bởi tài khoản khác.");
            }

            $stmt = $conn->prepare("UPDATE nguoi_dung SET ho_va_ten = ?, email = ? WHERE id = ?");
            $stmt->execute([$fullname, $email, $user_id]);
            
            $_SESSION['user_name'] = $fullname;
            
            $message = "Cập nhật thông tin thành công!";
            $msgType = "success";
        } catch (Exception $e) {
            $message = "Lỗi: " . $e->getMessage();
            $msgType = "error";
        }
    }

    // --- ĐỔI MẬT KHẨU ---
    elseif ($action === 'change_password') {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        try {
            $stmt = $conn->prepare("SELECT mat_khau FROM nguoi_dung WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($current_pass, $user['mat_khau'])) {
                throw new Exception("Mật khẩu hiện tại không đúng.");
            }

            if (strlen($new_pass) < 6) {
                throw new Exception("Mật khẩu mới phải có ít nhất 6 ký tự.");
            }

            if ($new_pass !== $confirm_pass) {
                throw new Exception("Mật khẩu xác nhận không khớp.");
            }

            $new_hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmtUpdate = $conn->prepare("UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?");
            $stmtUpdate->execute([$new_hashed_pass, $user_id]);

            $message = "Đổi mật khẩu thành công!";
            $msgType = "success";
        } catch (Exception $e) {
            $message = "Lỗi: " . $e->getMessage();
            $msgType = "error";
        }
    }
}

// 3. LẤY THÔNG TIN USER
$stmt = $conn->prepare("SELECT * FROM nguoi_dung WHERE id = ?");
$stmt->execute([$user_id]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$username = $currentUser['ho_va_ten'];

// 4. LẤY LỊCH SỬ LÀM BÀI (MỚI THÊM)
// Sắp xếp bài mới nhất lên đầu
$stmtHistory = $conn->prepare("SELECT * FROM lich_su_lam_bai WHERE nguoi_dung_id = ? ORDER BY id DESC");
$stmtHistory->execute([$user_id]);
$historyList = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);

// Hàm hỗ trợ tính thời gian làm bài
function calculateDuration($start, $end) {
    $diff = strtotime($end) - strtotime($start);
    if ($diff < 60) return $diff . " giây";
    return floor($diff / 60) . " phút";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - <?php echo htmlspecialchars($username); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Merriweather:wght@400;700;900&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-red': '#c62828',
                        'brand-gold': '#fbc02d',
                        'brand-dark': '#1e293b',
                        'brand-light': '#f8fafc',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Merriweather', 'serif'],
                    }
                }
            }
        }
    </script>
</head>

<body class="text-slate-800 antialiased bg-slate-50 flex flex-col min-h-screen">

    <header class="fixed w-full z-50 bg-white/95 backdrop-blur-md shadow-sm border-b border-slate-100 transition-all duration-300" id="main-header">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="index.php" class="flex items-center gap-3 cursor-pointer">
                    <div class="w-10 h-10 bg-brand-red rounded-lg flex items-center justify-center text-white shadow-lg">
                        <i class="fa-solid fa-star text-xl"></i>
                    </div>
                    <div>
                        <h1 class="font-serif font-bold text-xl text-brand-red tracking-tight">HCM Ideology</h1>
                    </div>
                </a>

                <nav class="hidden md:flex items-center gap-8">
                    <a href="index.php" class="text-slate-600 hover:text-brand-red font-medium transition-colors">Trang chủ</a>
                    <a href="index.php#exam-section" class="text-slate-600 hover:text-brand-red font-medium transition-colors">Đề thi</a>
                    <a href="index.php#chapter-section" class="text-slate-600 hover:text-brand-red font-medium transition-colors">Ôn chương</a>
                </nav>

                <div class="hidden md:flex items-center gap-4">
                    <div class="flex items-center gap-3 pl-4 border-l border-slate-200">
                        <div class="text-right hidden lg:block">
                            <div class="text-xs text-slate-400">Xin chào,</div>
                            <div class="font-bold text-slate-700 text-sm"><?php echo htmlspecialchars($username); ?></div>
                        </div>
                        <div class="relative group">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($username); ?>&background=random" class="w-10 h-10 rounded-full border-2 border-white shadow-md cursor-pointer">
                            <div class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 py-2 hidden group-hover:block">
                                <a href="profile.php" class="block px-4 py-2 text-sm text-brand-red font-bold bg-red-50"><i class="fa-solid fa-user mr-2"></i> Hồ sơ cá nhân</a>
                                <?php if($currentUser['role'] == 'admin'): ?>
                                    <a href="admin.php" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50"><i class="fa-solid fa-gauge mr-2"></i> Trang quản trị</a>
                                <?php endif; ?>
                                <div class="border-t border-slate-100 my-1"></div>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-bold"><i class="fa-solid fa-right-from-bracket mr-2"></i> Đăng xuất</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-24 flex-grow">
        
        <div class="mb-8 mt-4">
            <h1 class="font-serif text-3xl font-bold text-brand-dark mb-2">Hồ sơ cá nhân</h1>
            <p class="text-slate-500">Quản lý thông tin tài khoản và lịch sử học tập</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-8 p-4 rounded-xl flex items-center gap-3 shadow-sm <?php echo $msgType == 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
                <div class="w-8 h-8 rounded-full flex items-center justify-center bg-white shadow-sm">
                    <i class="fa-solid <?php echo $msgType == 'success' ? 'fa-check' : 'fa-exclamation'; ?>"></i>
                </div>
                <span class="font-medium"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 text-center sticky top-24">
                    <div class="relative inline-block mb-6">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['ho_va_ten']); ?>&background=c62828&color=fff&size=128" 
                             class="w-32 h-32 rounded-full border-4 border-red-50 shadow-md">
                        <div class="absolute bottom-0 right-0 bg-green-500 w-5 h-5 rounded-full border-2 border-white" title="Đang hoạt động"></div>
                    </div>
                    
                    <h2 class="font-serif text-xl font-bold text-slate-800 mb-1"><?php echo htmlspecialchars($currentUser['ho_va_ten']); ?></h2>
                    <p class="text-slate-500 text-sm mb-4"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                    
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider
                        <?php echo $currentUser['role'] === 'admin' ? 'bg-red-100 text-brand-red' : 'bg-blue-50 text-blue-600'; ?>">
                        <?php if($currentUser['role'] === 'admin'): ?>
                            <i class="fa-solid fa-shield-halved"></i> Quản trị viên
                        <?php else: ?>
                            <i class="fa-solid fa-user-graduate"></i> Học viên
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t border-slate-100">
                        <span class="block text-xs text-slate-400 uppercase font-bold mb-1">Mã ID Học Viên</span>
                        <span class="font-mono text-slate-700 font-bold text-lg">#<?php echo str_pad($currentUser['id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-8">
                
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-50">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fa-regular fa-id-card text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-slate-800">Thông tin cơ bản</h3>
                            <p class="text-xs text-slate-500">Cập nhật tên hiển thị và địa chỉ email</p>
                        </div>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="action" value="update_info">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Họ và Tên</label>
                                <input type="text" name="ho_va_ten" value="<?php echo htmlspecialchars($currentUser['ho_va_ten']); ?>" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-gold focus:ring-2 focus:ring-yellow-100 outline-none transition bg-slate-50 focus:bg-white" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Email đăng nhập</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-gold focus:ring-2 focus:ring-yellow-100 outline-none transition bg-slate-50 focus:bg-white" required>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-700 transition shadow-sm flex items-center gap-2">
                                <i class="fa-solid fa-check"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-50">
                        <div class="w-10 h-10 rounded-lg bg-red-50 text-brand-red flex items-center justify-center">
                            <i class="fa-solid fa-lock text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-slate-800">Đổi mật khẩu</h3>
                            <p class="text-xs text-slate-500">Bảo vệ tài khoản của bạn an toàn</p>
                        </div>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Mật khẩu hiện tại</label>
                            <input type="password" name="current_password" required placeholder="••••••••"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-red focus:ring-2 focus:ring-red-100 outline-none transition bg-slate-50 focus:bg-white">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Mật khẩu mới</label>
                                <input type="password" name="new_password" required placeholder="Tối thiểu 6 ký tự"
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-red focus:ring-2 focus:ring-red-100 outline-none transition bg-slate-50 focus:bg-white">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Xác nhận mật khẩu mới</label>
                                <input type="password" name="confirm_password" required placeholder="Nhập lại mật khẩu mới"
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-red focus:ring-2 focus:ring-red-100 outline-none transition bg-slate-50 focus:bg-white">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-red text-white font-bold hover:bg-red-700 transition shadow-lg shadow-red-200 flex items-center gap-2">
                                <i class="fa-solid fa-key"></i> Cập nhật mật khẩu
                            </button>
                        </div>
                    </form>
                </div>

            </div>

            <div class="lg:col-span-3">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-yellow-50 text-brand-gold flex items-center justify-center">
                                <i class="fa-solid fa-clock-rotate-left text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-slate-800">Lịch sử làm bài</h3>
                                <p class="text-xs text-slate-500">Danh sách các đề thi bạn đã thực hiện</p>
                            </div>
                        </div>
                        <div class="bg-slate-100 text-slate-500 text-xs font-bold px-3 py-1 rounded-full">
                            Tổng: <?php echo count($historyList); ?> lần thi
                        </div>
                    </div>

                    <?php if (count($historyList) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="text-slate-400 text-xs uppercase font-bold border-b border-slate-100">
                                        <th class="py-3 pl-4">#</th>
                                        <th class="py-3">Tên bài thi</th>
                                        <th class="py-3 text-center">Điểm số</th>
                                        <th class="py-3">Thời gian nộp</th>
                                        <th class="py-3">Thời lượng</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm text-slate-600 divide-y divide-slate-50">
                                    <?php foreach ($historyList as $index => $history): ?>
                                        <tr class="hover:bg-slate-50 transition duration-200">
                                            <td class="py-4 pl-4 font-mono text-xs text-slate-400"><?php echo $index + 1; ?></td>
                                            <td class="py-4 font-semibold text-slate-800">
                                                <?php echo htmlspecialchars($history['ten_bai_thi']); ?>
                                            </td>
                                            <td class="py-4 text-center">
                                                <span class="inline-block px-3 py-1 rounded-lg font-bold text-sm
                                                    <?php echo ($history['diem_so'] >= 5) ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-600'; ?>">
                                                    <?php echo $history['diem_so']; ?>
                                                </span>
                                            </td>
                                            <td class="py-4">
                                                <?php echo date('d/m/Y H:i', strtotime($history['thoi_gian_ket_thuc'])); ?>
                                            </td>
                                            <td class="py-4 text-slate-500">
                                                <i class="fa-regular fa-clock mr-1 text-xs"></i>
                                                <?php echo calculateDuration($history['thoi_gian_bat_dau'], $history['thoi_gian_ket_thuc']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-2xl">
                                <i class="fa-solid fa-file-pen"></i>
                            </div>
                            <p class="text-slate-500 mb-4">Bạn chưa làm bài thi nào.</p>
                            <a href="index.php#exam-section" class="inline-block text-brand-red font-bold hover:underline">
                                Làm bài thi ngay <i class="fa-solid fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </main>

    <footer class="bg-brand-dark text-slate-300 py-10 mt-auto">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center md:items-start gap-8">
                <div class="text-center md:text-left">
                    <div class="flex items-center justify-center md:justify-start gap-2 mb-4">
                        <div class="w-8 h-8 bg-brand-red rounded flex items-center justify-center text-white">
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <span class="font-serif font-bold text-xl text-white">HCM Ideology</span>
                    </div>
                    <p class="text-sm text-slate-400 max-w-xs">
                        Nền tảng ôn thi trắc nghiệm Tư tưởng Hồ Chí Minh dành cho sinh viên.
                    </p>
                </div>
                <div class="flex gap-4">
                    <a href="#" class="text-slate-400 hover:text-white"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="text-slate-400 hover:text-white"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>
            <div class="border-t border-slate-700 mt-8 pt-8 text-center text-xs text-slate-500">
                <p>&copy; 2024 HCM Ideology.</p>
            </div>
        </div>
    </footer>

    <script>
        window.addEventListener('scroll', function() {
            const header = document.getElementById('main-header');
            if (window.scrollY > 50) {
                header.classList.add('shadow-md');
                header.classList.replace('h-20', 'h-16');
            } else {
                header.classList.remove('shadow-md');
                header.classList.replace('h-16', 'h-20');
            }
        });
    </script>
</body>
</html>