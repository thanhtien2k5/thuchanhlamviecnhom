<?php
    session_start();
    
    // 1. KẾT NỐI DATABASE NGAY ĐẦU TRANG
    // Để đảm bảo biến $conn tồn tại xuyên suốt
    require_once 'db.php'; 

    // Kiểm tra xem kết nối có thành công không
    if (!isset($conn)) {
        die("Lỗi: Không tìm thấy biến kết nối ($conn). Vui lòng kiểm tra file db.php!");
    }

    $is_logged_in = isset($_SESSION['user_id']);
    $username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
?>

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

<body class="text-slate-800 antialiased bg-slate-50 flex flex-col min-h-screen">

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
                <a href="#exam-section" class="bg-brand-gold text-brand-red hover:bg-white font-bold py-4 px-8 rounded-xl shadow-lg transform transition hover:scale-105 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-play"></i> Bắt đầu luyện đề
                </a>
            </div>
        </div>
    </section>

    <main class="container mx-auto px-4 py-16 space-y-24 flex-grow">

        <section id="chapter-section">
            <?php
            // Lấy danh sách chương từ DB
            try {
                $stmtChuong = $conn->prepare("SELECT * FROM chuong ORDER BY so_thu_tu ASC");
                $stmtChuong->execute();
                $dsChuong = $stmtChuong->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $dsChuong = [];
            }
            ?>

            <div class="flex justify-between items-end mb-10 border-b border-slate-200 pb-4">
                <div>
                    <h2 class="font-serif text-3xl font-bold text-slate-800 mb-2 border-l-4 border-brand-red pl-4">Ôn Tập Lý Thuyết</h2>
                    <p class="text-slate-500">Kiến thức nền tảng từng phần</p>
                </div>
                <div class="hidden md:block text-sm font-medium text-slate-400 bg-slate-100 px-3 py-1 rounded-full">
                    Tổng: <?php echo count($dsChuong); ?> chương
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
                <?php if (count($dsChuong) > 0): ?>
                    <?php 
                        $icons = ['fa-landmark', 'fa-flag', 'fa-users', 'fa-book-open', 'fa-pen-nib', 'fa-scale-balanced'];
                    ?>
                    <?php foreach ($dsChuong as $index => $chuong): ?>
                        <?php 
                            // Ẩn chương thứ 4 trở đi
                            $isHidden = ($index >= 3) ? 'hidden extra-chapter' : '';
                        ?>
                        <div class="<?php echo $isHidden; ?> glass-card bg-white rounded-2xl p-6 relative overflow-hidden group cursor-pointer flex flex-col h-full animate-fade-in">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition">
                                <i class="fa-solid <?php echo $icons[$index % count($icons)]; ?> text-6xl text-brand-red"></i>
                            </div>
                            <span class="text-xs font-bold text-brand-red bg-red-50 px-3 py-1 rounded-full uppercase tracking-wider mb-4 inline-block w-fit">
                                Chương <?php echo $chuong['so_thu_tu']; ?>
                            </span>
                            <h3 class="text-xl font-bold text-slate-800 mb-3 group-hover:text-brand-red transition line-clamp-2 min-h-[3.5rem]" title="<?php echo htmlspecialchars($chuong['ten_chuong']); ?>">
                                <?php echo htmlspecialchars($chuong['ten_chuong']); ?>
                            </h3>
                            <div class="flex items-center text-sm text-slate-500 mb-6 gap-4 flex-grow">
                                <p class="line-clamp-3">
                                    <?php echo htmlspecialchars(strip_tags($chuong['noi_dung'])); ?>
                                </p>
                            </div>
                            <a href="chuong.php?id=<?php echo $chuong['id']; ?>" class="mt-auto w-full py-2.5 rounded-lg border border-slate-200 text-slate-600 font-semibold text-center hover:bg-brand-red hover:text-white hover:border-brand-red transition block">
                                <i class="fa-solid fa-book-reader mr-2"></i> Đọc lý thuyết
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-3 text-center py-12 bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                        <i class="fa-regular fa-folder-open text-4xl text-slate-300 mb-3"></i>
                        <p class="text-slate-500">Chưa có dữ liệu chương học nào.</p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (count($dsChuong) > 3): ?>
                <div class="flex justify-center">
                    <button id="btn-toggle-chapters" onclick="toggleChapters()" class="group flex items-center gap-2 px-6 py-3 rounded-full bg-white border border-slate-200 text-slate-600 font-bold hover:border-brand-red hover:text-brand-red transition shadow-sm hover:shadow-md">
                        <span id="btn-text">Xem thêm <?php echo count($dsChuong) - 3; ?> chương nữa</span>
                        <i id="btn-icon" class="fa-solid fa-chevron-down text-xs group-hover:animate-bounce"></i>
                    </button>
                </div>
            <?php endif; ?>
        </section>

        <section id="exam-section" class="bg-slate-50 -mx-4 px-4 py-16 rounded-3xl mt-16 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
                <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30"></div>
                <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-red-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30"></div>
            </div>

            <div class="container mx-auto relative z-10">
                <div class="text-center mb-12">
                    <h2 class="font-serif text-3xl md:text-4xl font-bold text-slate-800 mb-4">Luyện Đề Thi</h2>
                    <p class="text-slate-500 max-w-2xl mx-auto">Kho đề thi phong phú được cập nhật liên tục.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                    
                    <div class="bg-white rounded-3xl shadow-sm p-10 hover:shadow-2xl hover:-translate-y-2 transition duration-300 border border-slate-100 flex flex-col items-center text-center group h-full">
                        <div class="w-24 h-24 bg-blue-50 text-blue-600 rounded-3xl flex items-center justify-center mb-6 text-4xl group-hover:bg-blue-600 group-hover:text-white transition shadow-sm transform group-hover:rotate-6">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <h3 class="font-bold text-2xl text-slate-800 mb-3">Luyện Đề Theo Chương</h3>
                        <p class="text-slate-500 mb-8 flex-grow leading-relaxed">
                            Tổng hợp các bài trắc nghiệm ngắn bám sát từng chương học.
                        </p>
                        <a href="list_exams.php?type=theo_chuong" class="w-full btn-outline border-2 border-slate-200 text-slate-600 hover:border-blue-600 hover:text-blue-600 hover:bg-blue-50 py-4 rounded-2xl font-bold transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-list-ul"></i> Xem danh sách đề
                        </a>
                    </div>

                    <div class="bg-white rounded-3xl shadow-xl p-10 transform md:scale-105 border-2 border-brand-red/10 flex flex-col items-center text-center group h-full relative z-10">
                        <div class="absolute top-6 right-6 bg-red-100 text-brand-red text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider animate-pulse">
                            Hot
                        </div>
                        <div class="w-24 h-24 bg-red-50 text-brand-red rounded-3xl flex items-center justify-center mb-6 text-4xl group-hover:bg-brand-red group-hover:text-white transition shadow-sm transform group-hover:-rotate-6">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <h3 class="font-bold text-2xl text-slate-800 mb-3">Thi Thử Tổng Hợp</h3>
                        <p class="text-slate-500 mb-8 flex-grow leading-relaxed">
                            Kho đề thi chuẩn cấu trúc, thời gian thực, có xếp hạng.
                        </p>
                        <a href="list_exams.php?type=thi_thu" class="w-full bg-gradient-to-r from-brand-red to-red-700 text-white py-4 rounded-2xl font-bold shadow-lg hover:shadow-red-500/40 hover:scale-[1.02] transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-play"></i> Vào kho đề thi
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-16">
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
                            <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 font-bold">A</div>
                            <span class="text-sm font-semibold text-slate-700">Nguyễn Văn A</span>
                        </div>
                        <span class="text-sm font-bold text-brand-red">9.8</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-slate-400 font-bold w-4">2</span>
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold">B</div>
                            <span class="text-sm font-semibold text-slate-700">Trần Thị B</span>
                        </div>
                        <span class="text-sm font-bold text-slate-600">9.5</span>
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
                            <h4 class="text-sm font-bold text-slate-800 group-hover:text-brand-red transition">Mẹo ghi nhớ các mốc sự kiện</h4>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-1">Sử dụng sơ đồ tư duy để hệ thống hóa...</p>
                        </div>
                    </a>
                </div>
            </div>
        </section>

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
        // Script header scroll
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

        // Script xem thêm chương
        function toggleChapters() {
            const extraChapters = document.querySelectorAll('.extra-chapter');
            const btnText = document.getElementById('btn-text');
            const btnIcon = document.getElementById('btn-icon');
            const isHidden = extraChapters[0].classList.contains('hidden');

            if (isHidden) {
                extraChapters.forEach(el => {
                    el.classList.remove('hidden');
                    el.classList.add('animate-fade-in');
                });
                btnText.innerText = "Thu gọn danh sách";
                btnIcon.classList.replace('fa-chevron-down', 'fa-chevron-up');
            } else {
                extraChapters.forEach(el => el.classList.add('hidden'));
                btnText.innerText = "Xem thêm <?php echo count($dsChuong) - 3; ?> chương nữa";
                btnIcon.classList.replace('fa-chevron-up', 'fa-chevron-down');
                document.getElementById('chapter-section').scrollIntoView({behavior: 'smooth', block: 'start'});
            }
        }
    </script>
</body>
</html>