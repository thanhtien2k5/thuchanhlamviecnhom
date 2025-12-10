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
    <script src="unique.js" defer></script>

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
</head>

<body class="text-slate-800 antialiased bg-slate-50 flex flex-col min-h-screen overflow-x-hidden">

    <!-- LOADING SPINNER SIÊU ĐẸP -->
    <div id="page-loader>
        <div class="loader-spinner"></div>
    </div>

    <!-- HEADER -->
    <header class="fixed w-full z-50 bg-white/95 backdrop-blur-md shadow-sm border-b border-slate-100 transition-all duration-300" id="main-header">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="index.php" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-brand-red rounded-lg flex items-center justify-center text-white shadow-lg">
                        <i class="fa-solid fa-star text-xl animate-pulse"></i>
                    </div>
                    <h1 class="font-serif font-bold text-xl text-brand-red tracking-tight">HCM Ideology</h1>
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
                                <div class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 py-2 hidden group-hover:block">
                                    <a href="#" class="block px-4 py-2 text-sm text-slate-600 hover:bg-red-50 hover:text-brand-red"><i class="fa-solid fa-user mr-2"></i> Hồ sơ</a>
                                    <div class="border-t border-slate-100 my-1"></div>
                                    <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-bold"><i class="fa-solid fa-right-from-bracket mr-2"></i> Đăng xuất</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="bg-gradient-to-r from-brand-red to-red-700 text-white px-6 py-2.5 rounded-full font-bold shadow-md hover:shadow-lg transform transition active:scale-95 flex items-center gap-2">
                            <i class="fa-regular fa-user"></i> Tài Khoản
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </div>
    </header>



<!-- HERO SECTION ĐÃ ĐƯỢC CHỈNH NHỎ GỌN + ĐẸP HƠN -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden hero-bg">
    <!-- Nền tĩnh cực đẹp: cờ Việt Nam + gradient đỏ sao vàng -->
    <div class="absolute inset-0 bg-gradient-to-br from-brand-red via-red-800 to-brand-dark"></div>
    <div class="absolute inset-0 bg-[url('https://upload.wikimedia.org/wikipedia/commons/thumb/2/21/Flag_of_Vietnam.svg/2560px-Flag_of_Vietnam.svg.png')] bg-cover bg-center opacity-20"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-brand-red/40"></div>
    
    <!-- Glow + blob nhẹ vẫn giữ để đẹp lung linh -->
    <div class="hero-glow"></div>
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-10 left-10 w-96 h-96 bg-brand-gold/30 rounded-full blur-3xl parallax" data-speed="0.3"></div>
        <div class="absolute bottom-20 right-20 w-80 h-80 bg-yellow-500/25 rounded-full blur-3xl parallax" data-speed="-0.3"></div>
    </div>

    <!-- Blob nhẹ -->
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-10 left-10 w-80 h-80 bg-brand-gold/20 rounded-full blur-3xl parallax" data-speed="0.3"></div>
        <div class="absolute bottom-10 right-10 w-72 h-72 bg-yellow-500/20 rounded-full blur-3xl parallax" data-speed="-0.2"></div>
    </div>

    <div class="container mx-auto px-6 relative z-10 text-center">
        <!-- Tag nhỏ -->
        <span class="inline-block px-5 py-2 bg-white/15 backdrop-blur border border-white/30 rounded-full text-white text-sm font-medium mb-8 fade-up">
            Cập nhật đề thi mới nhất 2025
        </span>

        <!-- TIÊU ĐỀ ĐÃ NHỎ LẠI – VỪA MẮT, ĐẸP -->
      <h1 class="font-black text-5xl xs:text-6xl sm:text-7xl md:text-8xl leading-tight">
    <span class="block text-white drop-shadow-2xl fade-up">CHINH PHỤC</span>
    <span class="block text-white drop-shadow-2xl fade-up mt-3 md:mt-5 text-shadow-glow-white">
        TƯ TƯỞNG 
    </span>
    <span class="block text-brand-gold drop-shadow-2xl fade-up mt-3 md:mt-5 text-shadow-gold glow-gold">
        HỒ CHÍ MINH
    </span>
</h1>

        <p class="mt-10 text-lg md:text-xl text-gray-200 max-w-3xl mx-auto leading-relaxed fade-up">
            10+ đề chuẩn • Giải thích chi tiết • Xếp hạng realtime • AI gợi ý điểm yếu
        </p>

        <div class="mt-14 fade-up">
            <a href="#exam-section"
               class="inline-flex items-center gap-4 px-10 py-5 bg-brand-gold text-brand-red font-black text-xl rounded-xl shadow-2xl hover:shadow-brand-gold/60 transform hover:scale-105 transition">
                Bắt đầu luyện đề ngay
                <i class="fa-solid fa-play"></i>
            </a>
        </div>

        <!-- SỐ LIỆU HỌC VIÊN – MÀU ĐEN RÕ NÉT, NHỎ GỌN -->
        <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto fade-up">
            <div class="text-center">
                <div class="text-4xl md:text-5xl font-black text-white counter" data-count="1234">0</div>
                <p class="text-gray-300 mt-2">Học viên</p>
            </div>
            <div class="text-center">
                <div class="text-4xl md:text-5xl font-black text-white counter" data-count="10">0</div>
                <p class="text-gray-300 mt-2">Đề thi</p>
            </div>
            <div class="text-center">
                <div class="text-4xl md:text-5xl font-black text-brand-gold counter" data-count="90">0</div>
                <p class="text-gray-300 mt-2">% Đạt 8+</p>
            </div>
            <div class="text-center">
                <div class="text-4xl md:text-5xl font-black text-white">24/7</div>
                <p class="text-gray-300 mt-2">Hỗ trợ</p>
            </div>
        </div>
    </div>
</section>
    <!-- MAIN CONTENT -->
    <main class="container mx-auto px-4 py-20 space-y-32">

        <!-- ÔN CHƯƠNG -->
        <section id="chapter-section" class="fade-up">
            <?php
            try {
                $stmt = $conn->prepare("SELECT * FROM chuong ORDER BY so_thu_tu ASC");
                $stmt->execute();
                $dsChuong = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $dsChuong = [];
            }
            ?>

            <div class="text-center mb-16 text-reveal">
                <h2 class="font-serif text-4xl md:text-5xl font-bold text-brand-dark mb-4">
                    Ôn Tập Lý Thuyết Theo Chương
                </h2>
                <p class="text-xl text-slate-600">Kiến thức nền tảng – bám sát giáo trình chuẩn</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 stagger-group">
                <?php 
                $icons = ['fa-landmark', 'fa-flag', 'fa-users', 'fa-book-open', 'fa-pen-nib', 'fa-scale-balanced'];
                foreach ($dsChuong as $index => $chuong):
                    $hidden = $index >= 3 ? 'hidden extra-chapter' : '';
                ?>
                    <div class="glass-card float-card bg-white rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all stagger-item <?php echo $hidden; ?>">
                        <span class="text-brand-red font-bold text-sm bg-red-50 px-4 py-2 rounded-full">
                            Chương <?php echo $chuong['so_thu_tu']; ?>
                        </span>
                        <h3 class="text-2xl font-bold mt-6 mb-4 text-slate-800">
                            <?php echo htmlspecialchars($chuong['ten_chuong']); ?>
                        </h3>
                        <p class="text-slate-600 leading-relaxed line-clamp-3">
                            <?php echo htmlspecialchars(strip_tags($chuong['noi_dung'])); ?>
                        </p>
                        <a href="chuong.php?id=<?php echo $chuong['id']; ?>" class="mt-8 inline-block text-brand-red font-bold hover:text-brand-gold transition">
                            Xem<i class="fa-solid fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($dsChuong) > 3): ?>
                <div class="text-center mt-12">
                    <button onclick="toggleChapters()" class="px-8 py-4 bg-brand-red text-white rounded-full font-bold hover:bg-red-700 transition shadow-lg">
                        Xem thêm <?php echo count($dsChuong) - 3; ?> chương
                    </button>
                </div>
            <?php endif; ?>
        </section>

        <!-- LUYỆN ĐỀ + THỐNG KÊ -->
        <section id="exam-section" class="grid lg:grid-cols-2 gap-16 items-center">
            <div class="space-y-12 fade-up slide-left">
                <h2 class="text-4xl md:text-5xl font-bold text-brand-dark">Luyện Đề Thi Chuẩn Cấu Trúc</h2>
                <ul class="space-y-6 text-lg text-slate-700">
                    <li><i class="fa-solid fa-check text-brand-gold mr-3"></i> 10+ đề thi từ 2018-10 điểm</li>
                    <li><i class="fa-solid fa-check text-brand-gold mr-3"></i> Giải thích chi tiết từng câu</li>
                    <li><i class="fa-solid fa-check text-brand-gold mr-3"></i> Xếp hạng toàn quốc realtime</li>
                    <li><i class="fa-solid fa-check text-brand-gold mr-3"></i> Luyện theo điểm yếu AI</li>
                </ul>
                <a href="list_exams.php?type=thi_thu" class="inline-block bg-gradient-to-r from-brand-red to-red-700 text-white px-10 py-5 rounded-2xl font-bold text-xl shadow-2xl hover:shadow-red-500/50 transform hover:scale-105 transition pulse-btn">
                    Vào kho đề thi ngay
                </a>
            </div>

            <div class="grid grid-cols-2 gap-8 text-center fade-up slide-right">
    
                    <div class="bg-white border-4 border-brand-red/20 text-slate-900 p-10 rounded-3xl shadow-2xl">
                    <div class="counter text-6xl font-black text-brand-dark" data-count="1234">0</div>
                    <p class="mt-4 text-xl font-bold">Học viên đã chinh phục</p>
            </div>
                <div class="bg-gradient-to-br from-brand-gold to-yellow-600 text-brand-dark p-10 rounded-3xl">
                    <div class="counter text-6xl font-black" data-count="90">0</div>
                    <p class="mt-4 text-xl">% Đạt 8+ điểm</p>
                </div>
            </div>
        </section>
      
     

<script>
    // Hiện marquee khi cuộn qua hero
    window.addEventListener('scroll', () => {
        const marquee = document.getElementById('bottom-marquee');
        if (window.scrollY > window.innerHeight * 0.7) {
            marquee.classList.remove('translate-y-full');
        } else {
            marquee.classList.add('translate-y-full');
        }
    });
</script>
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


    <!-- FOOTER -->
    <footer class="bg-brand-dark text-slate-300 py-16 mt-auto">
        <div class="container mx-auto px-4 text-center">
            <div class="flex items-center justify-center gap-3 mb-6">
                <div class="w-12 h-12 bg-brand-red rounded flex items-center justify-center">
                    <i class="fa-solid fa-star text-white text-2xl"></i>
                </div>
                <span class="font-serif text-3xl font-bold text-white">HCM Ideology</span>
            </div>
            <p class="text-lg">&copy; 2024–2025 HCM Ideology. Đã giúp hơn 1.000 sinh viên đạt điểm cao.</p>
        </div>
    </footer>

         
    <!-- SCRIPTS -->
    <script>
        // Header scroll
        window.addEventListener('scroll', () => {
            const header = document.getElementById('main-header');
            header.classList.toggle('shadow-md', window.scrollY > 50);
            header.classList.toggle('h-16', window.scrollY > 50);
            header.classList.toggle('h-20', window.scrollY <= 50);
        });

        // Toggle chương
        function toggleChapters() {
            document.querySelectorAll('.extra-chapter').forEach(el => el.classList.toggle('hidden'));
            const btn = event.target.closest('button');
            btn.innerHTML = btn.innerHTML.includes('Xem thêm') 
                ? 'Thu gọn lại' 
                : 'Xem thêm chương';
        }

        // Counter animation
        const counters = document.querySelectorAll('.counter');
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = parseInt(entry.target.getAttribute('data-count'));
                    let count = 0;
                    const timer = setInterval(() => {
                        count += Math.ceil(target / 80);
                        entry.target.textContent = count.toLocaleString('vi-VN');
                        if (count >= target) {
                            entry.target.textContent = target.toLocaleString('vi-VN') + (target === 98 ? '%' : '+');
                            clearInterval(timer);
                        }
                    }, 30);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(c => counterObserver.observe(c));
    </script>
</body>
</html>