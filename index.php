<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ôn Thi Tư Tưởng Hồ Chí Minh</title>
    
    <link rel="stylesheet" href="style.css">

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

<body class="text-slate-800 antialiased">

<?php
    session_start();    
    $is_logged_in = isset($_SESSION['user_id']);
    $username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
?>

<header class="fixed w-full z-50 bg-white/95 backdrop-blur-md shadow-sm border-b border-slate-100 transition-all duration-300" id="main-header">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <a href="index.php" class="flex items-center gap-3 cursor-pointer">
                <div class="w-10 h-10 bg-brand-red rounded-lg flex items-center justify-center text-white shadow-lg">
                    <i class="fa-solid fa-star text-xl animate-pulse"></i>
                </div>
                <div>
                    <h1 class="font-serif font-bold text-xl text-brand-red tracking-tight">HCM Ideology</h1>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-8">
                <a href="index.php" class="text-slate-600 hover:text-brand-red font-medium transition-colors">Trang chủ</a>
                <a href="#exam-section" class="text-slate-600 hover:text-brand-red font-medium transition-colors">Đề thi</a>
                <a href="#chapter-section" class="text-slate-600 hover:text-brand-red font-medium transition-colors">Ôn chương</a>
                <a href="#resources" class="text-slate-600 hover:text-brand-red font-medium transition-colors">Tài liệu</a>
            </nav>

            <div class="hidden md:flex items-center gap-4">
                <?php if ($is_logged_in): ?>
                    <div class="flex items-center gap-3 pl-4 border-l border-slate-200">
                        <div class="text-right hidden lg:block">
                            <div class="text-xs text-slate-400">Xin chào,</div>
                            <div class="font-bold text-slate-700 text-sm"><?php echo htmlspecialchars($username); ?></div>
                        </div>
                        <div class="relative group">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($username); ?>&background=random" class="w-10 h-10 rounded-full border-2 border-white shadow-md cursor-pointer">
                            
                            <div class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 py-2 hidden group-hover:block animate-fade-in">
                                <a href="#" class="block px-4 py-2 text-sm text-slate-600 hover:bg-red-50 hover:text-brand-red"><i class="fa-solid fa-user mr-2"></i> Hồ sơ cá nhân</a>
                                <a href="#" class="block px-4 py-2 text-sm text-slate-600 hover:bg-red-50 hover:text-brand-red"><i class="fa-solid fa-clock-rotate-left mr-2"></i> Lịch sử thi</a>
                                <div class="border-t border-slate-100 my-1"></div>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-bold"><i class="fa-solid fa-right-from-bracket mr-2"></i> Đăng xuất</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn-primary px-6 py-2.5 rounded-full font-bold shadow-md hover:shadow-lg transform transition active:scale-95 flex items-center gap-2">
                        <i class="fa-regular fa-user"></i> Tài Khoản
                    </a>
                <?php endif; ?>
            </div>

            <button class="md:hidden text-slate-600 text-2xl hover:text-brand-red">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>
</header>

    <section class="hero-bg pt-32 pb-24 lg:pt-40 lg:pb-32 relative overflow-hidden">
        <div class="absolute top-20 right-10 w-64 h-64 bg-yellow-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
        <div class="absolute bottom-10 left-10 w-64 h-64 bg-red-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>

        <div class="container mx-auto px-4 text-center relative z-10">
            <span class="inline-block py-1 px-3 rounded-full bg-white/10 border border-white/20 text-white text-sm font-medium mb-6 backdrop-blur-sm animate-fade-in">
                ✨ Cập nhật bộ đề thi mới nhất 2024
            </span>
            <h1 class="font-serif text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight animate-fade-in" style="animation-delay: 0.1s;">
                Chinh Phục Môn <br class="hidden md:block"/>
                <span class="text-brand-gold">Tư Tưởng Hồ Chí Minh</span>
            </h1>
            <p class="text-lg md:text-xl text-slate-200 mb-10 max-w-2xl mx-auto font-light animate-fade-in" style="animation-delay: 0.2s;">
                Hệ thống trắc nghiệm thông minh và lộ trình ôn tập giúp bạn đạt điểm A dễ dàng.
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center gap-4 animate-fade-in" style="animation-delay: 0.3s;">
                <button class="bg-brand-gold text-brand-red hover:bg-white font-bold py-4 px-8 rounded-xl shadow-lg transform transition hover:scale-105 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-play"></i> Bắt đầu thi thử
                </button>
            </div>
        </div>
    </section>

    <main class="container mx-auto px-4 py-16 space-y-24">

        <section id="chapter-section">
            <div class="flex justify-between items-end mb-10 border-b border-slate-200 pb-4">
                <div>
                    <h2 class="font-serif text-3xl font-bold text-slate-800 mb-2 border-l-4 border-brand-red pl-4">Ôn Tập Theo Chương</h2>
                    <p class="text-slate-500">Kiến thức nền tảng từng phần</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="glass-card exam-card bg-white rounded-2xl p-6 relative overflow-hidden group cursor-pointer">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition">
                        <i class="fa-solid fa-landmark text-6xl text-brand-red"></i>
                    </div>
                    <span class="text-xs font-bold text-brand-red bg-red-50 px-3 py-1 rounded-full uppercase tracking-wider mb-4 inline-block">Chương I</span>
                    <h3 class="text-xl font-bold text-slate-800 mb-3 group-hover:text-brand-red transition">Cơ sở, quá trình hình thành và phát triển</h3>
                    <div class="flex items-center text-sm text-slate-500 mb-6 gap-4">
                        <span><i class="fa-regular fa-file-lines mr-1"></i> 150 câu hỏi</span>
                    </div>
                    <button class="mt-2 w-full py-2 rounded-lg border border-slate-200 text-slate-600 font-semibold hover:bg-brand-red hover:text-white hover:border-brand-red transition">Ôn tập ngay</button>
                </div>

                <div class="glass-card exam-card bg-white rounded-2xl p-6 relative overflow-hidden group cursor-pointer">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition">
                        <i class="fa-solid fa-flag text-6xl text-brand-red"></i>
                    </div>
                    <span class="text-xs font-bold text-brand-red bg-red-50 px-3 py-1 rounded-full uppercase tracking-wider mb-4 inline-block">Chương II</span>
                    <h3 class="text-xl font-bold text-slate-800 mb-3 group-hover:text-brand-red transition">Tư tưởng về Độc lập dân tộc và CNXH</h3>
                    <div class="flex items-center text-sm text-slate-500 mb-6 gap-4">
                        <span><i class="fa-regular fa-file-lines mr-1"></i> 200 câu hỏi</span>
                    </div>
                    <button class="mt-2 w-full py-2 rounded-lg border border-slate-200 text-slate-600 font-semibold hover:bg-brand-red hover:text-white hover:border-brand-red transition">Ôn tập ngay</button>
                </div>

                <div class="glass-card exam-card bg-white rounded-2xl p-6 relative overflow-hidden group cursor-pointer">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition">
                        <i class="fa-solid fa-users text-6xl text-brand-red"></i>
                    </div>
                    <span class="text-xs font-bold text-brand-red bg-red-50 px-3 py-1 rounded-full uppercase tracking-wider mb-4 inline-block">Chương III</span>
                    <h3 class="text-xl font-bold text-slate-800 mb-3 group-hover:text-brand-red transition">Đảng Cộng sản và Nhà nước VN</h3>
                    <div class="flex items-center text-sm text-slate-500 mb-6 gap-4">
                        <span><i class="fa-regular fa-file-lines mr-1"></i> 180 câu hỏi</span>
                    </div>
                    <button class="mt-2 w-full py-2 rounded-lg border border-slate-200 text-slate-600 font-semibold hover:bg-brand-red hover:text-white hover:border-brand-red transition">Ôn tập ngay</button>
                </div>
            </div>
        </section>

        <section id="exam-section" class="bg-slate-50 -mx-4 px-4 py-16 rounded-3xl">
            <div class="container mx-auto">
                <div class="text-center mb-12">
                    <h2 class="font-serif text-3xl md:text-4xl font-bold text-slate-800 mb-4">Luyện Đề Thi</h2>
                    <p class="text-slate-500 max-w-2xl mx-auto">Chọn hình thức ôn tập phù hợp với lộ trình của bạn.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    <div class="bg-white rounded-xl shadow-sm p-8 hover:shadow-xl transition duration-300 border border-slate-100 flex flex-col items-center text-center group">
                        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-6 text-2xl group-hover:bg-blue-600 group-hover:text-white transition">
                            <i class="fa-solid fa-file-signature"></i>
                        </div>
                        <h3 class="font-bold text-xl text-slate-800 mb-2">Đề Thi Giữa Kỳ</h3>
                        <p class="text-sm text-slate-500 mb-8">Tổng hợp kiến thức nửa đầu học phần. Cấu trúc chuẩn 40 câu / 45 phút.</p>
                        <button class="w-full btn-primary py-3 rounded-lg font-bold shadow-md">
                            Ôn thi ngay
                        </button>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-8 hover:shadow-xl transition duration-300 border border-slate-100 flex flex-col items-center text-center group">
                        <div class="w-16 h-16 bg-red-50 text-brand-red rounded-full flex items-center justify-center mb-6 text-2xl group-hover:bg-brand-red group-hover:text-white transition">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <h3 class="font-bold text-xl text-slate-800 mb-2">Đề Thi Cuối Kỳ</h3>
                        <p class="text-sm text-slate-500 mb-8">Tổng hợp toàn bộ kiến thức môn học. Cấu trúc chuẩn 60 câu / 60 phút.</p>
                        <button class="w-full btn-primary py-3 rounded-lg font-bold shadow-md">
                            Ôn thi ngay
                        </button>
                    </div>

                     <div class="bg-white rounded-xl shadow-sm p-8 hover:shadow-xl transition duration-300 border border-slate-100 flex flex-col items-center text-center group">
                        <div class="w-16 h-16 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center mb-6 text-2xl group-hover:bg-purple-600 group-hover:text-white transition">
                            <i class="fa-solid fa-shuffle"></i>
                        </div>
                        <h3 class="font-bold text-xl text-slate-800 mb-2">Đề Ngẫu Nhiên</h3>
                        <p class="text-sm text-slate-500 mb-8">Hệ thống tự động trích xuất câu hỏi ngẫu nhiên từ ngân hàng dữ liệu.</p>
                        <button class="w-full bg-white border-2 border-slate-200 text-slate-600 hover:border-brand-red hover:text-brand-red py-3 rounded-lg font-bold transition">
                            Tạo đề mới
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 h-full">
                <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
                    <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-trophy text-yellow-500"></i> Bảng Vàng Tuần
                    </h3>
                    <span class="text-xs font-medium text-slate-400 bg-slate-100 px-2 py-1 rounded">Top 5</span>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-yellow-500 font-bold w-4">1</span>
                            <img src="https://ui-avatars.com/api/?name=Nguyen+A&background=fef3c7&color=d97706" class="w-8 h-8 rounded-full">
                            <span class="text-sm font-semibold text-slate-700">Nguyễn Văn A</span>
                        </div>
                        <span class="text-sm font-bold text-brand-red">980đ</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-slate-400 font-bold w-4">2</span>
                            <img src="https://ui-avatars.com/api/?name=Tran+B&background=f1f5f9&color=475569" class="w-8 h-8 rounded-full">
                            <span class="text-sm font-semibold text-slate-700">Trần Thị B</span>
                        </div>
                        <span class="text-sm font-bold text-slate-600">950đ</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-amber-700 font-bold w-4">3</span>
                            <img src="https://ui-avatars.com/api/?name=Le+C&background=ffedd5&color=9a3412" class="w-8 h-8 rounded-full">
                            <span class="text-sm font-semibold text-slate-700">Lê Văn C</span>
                        </div>
                        <span class="text-sm font-bold text-slate-600">920đ</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-slate-400 font-bold w-4">4</span>
                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xs"><i class="fa-solid fa-user"></i></div>
                            <span class="text-sm font-semibold text-slate-700">Phạm Văn D</span>
                        </div>
                        <span class="text-sm font-bold text-slate-600">890đ</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 h-full">
                <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
                    <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                        <i class="fa-regular fa-newspaper text-brand-red"></i> Tin Tức & Mẹo Ôn
                    </h3>
                    <a href="#" class="text-xs font-medium text-brand-red hover:underline">Xem thêm</a>
                </div>

                <div class="space-y-4">
                    <a href="#" class="flex items-start gap-4 group">
                        <div class="w-10 h-10 rounded-lg bg-red-50 text-brand-red flex items-center justify-center flex-shrink-0 group-hover:bg-brand-red group-hover:text-white transition">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800 group-hover:text-brand-red transition">Mẹo ghi nhớ các mốc sự kiện 1911 - 1945</h4>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-1">Sử dụng sơ đồ tư duy để hệ thống hóa kiến thức lịch sử...</p>
                        </div>
                    </a>

                    <a href="#" class="flex items-start gap-4 group">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 group-hover:text-white transition">
                            <i class="fa-solid fa-file-pdf"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800 group-hover:text-brand-red transition">Tổng hợp 100 câu trắc nghiệm Chương 2 (Có đáp án)</h4>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-1">Tài liệu ôn thi giữa kỳ chuẩn bám sát giáo trình mới.</p>
                        </div>
                    </a>

                    <a href="#" class="flex items-start gap-4 group">
                        <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center flex-shrink-0 group-hover:bg-green-600 group-hover:text-white transition">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800 group-hover:text-brand-red transition">Thông báo lịch thi cuối kỳ K62 dự kiến</h4>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-1">Nhà trường công bố danh sách ca thi và phòng thi chính thức.</p>
                        </div>
                    </a>
                </div>
            </div>
        </section>

    </main>

    <footer class="bg-brand-dark text-slate-300 py-10">
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

                <div class="flex gap-8 text-sm font-medium">
                    <a href="#" class="hover:text-white transition">Về chúng tôi</a>
                    <a href="#" class="hover:text-white transition">Điều khoản</a>
                    <a href="#" class="hover:text-white transition">Liên hệ</a>
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

