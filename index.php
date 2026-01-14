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
<header 
  class="fixed w-full z-50 bg-white/95 backdrop-blur-md shadow-sm border-b border-slate-100 
         rounded-b-3xl mx-auto left-0 right-0 transition-all duration-300"
  id="main-header">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-16 md:h-20">
            <!-- Logo -->
            <a href="index.php" class="flex items-center gap-3">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-gradient-to-br from-brand-red to-red-600 rounded-full flex items-center justify-center text-white shadow-md">
                    <i class="fa-solid fa-star text-sm md:text-base"></i>
                </div>
                <h1 class="font-serif font-bold text-lg md:text-xl lg:text-2xl text-brand-red tracking-tight">HCM Ideology</h1>
            </a>

            <!-- Desktop Navigation -->
            <nav class="flex items-center gap-6 lg:gap-8">
                <a href="index.php" class="text-slate-700 hover:text-brand-red font-medium transition-colors text-sm lg:text-base">Trang chủ</a>
                <a href="#exam-section" class="text-slate-700 hover:text-brand-red font-medium transition-colors text-sm lg:text-base">Đề thi</a>
                <a href="#chapter-section" class="text-slate-700 hover:text-brand-red font-medium transition-colors text-sm lg:text-base">Ôn chương</a>
            </nav>

            <!-- Desktop Auth -->
            <div class="flex items-center gap-2 lg:gap-3">
                <?php if ($is_logged_in): ?>
                    <div class="flex items-center gap-2 pl-3 border-l border-slate-200">
                        <div class="text-right">
                            <div class="text-xs text-slate-500">Xin chào</div>
                            <div class="font-bold text-slate-800 text-sm truncate max-w-[140px]"><?php echo htmlspecialchars($username); ?></div>
                        </div>
                        <div class="relative group">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($username); ?>&background=random&size=40" class="w-10 h-10 rounded-full border border-slate-200 shadow-sm cursor-pointer">
                            <div class="absolute right-0 top-full mt-2 w-40 bg-white rounded-xl shadow-lg border border-slate-100 py-2 hidden group-hover:block z-50">
                                <a href="#" class="block px-4 py-2 text-sm text-slate-600 hover:bg-red-50 hover:text-brand-red transition-colors"><i class="fa-solid fa-user mr-2 text-xs"></i> Hồ sơ</a>
                                <div class="border-t border-slate-100 my-1"></div>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium transition-colors"><i class="fa-solid fa-right-from-bracket mr-2 text-xs"></i> Đăng xuất</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="bg-gradient-to-r from-brand-red to-red-600 text-white px-5 py-2 rounded-full font-medium shadow-sm hover:shadow-md transform transition active:scale-95 flex items-center gap-2 text-sm">
                        <i class="fa-regular fa-user text-xs"></i> Tài Khoản
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>


<!-- HERO SECTION ĐÃ ĐƯỢC CHỈNH NHỎ GỌN + ĐẸP HƠN -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden hero-bg rounded-b-[1rem]">
    <div class="absolute inset-0 bg-gradient-to-br from-brand-red via-red-800 to-brand-dark"></div>
    <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"%3E%3Crect width="100" height="50" fill="%23DA251D"/%3E%3Crect y="50" width="100" height="50" fill="%23FFFF00"/%3E%3C/svg%3E')] bg-cover bg-center opacity-15"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-brand-red/30"></div>

    <div class="container mx-auto px-4 sm:px-6 relative z-10 text-center">

        <!-- Tiêu đề -->
        <h1 class="font-black text-5xl xs:text-6xl sm:text-7xl md:text-8xl leading-tight">
    <span class="block text-white drop-shadow-xl fade-up mt-3 md:mt-5 text-shadow-glow-white">
         ÔN THI
    </span>
    <span class="block text-brand-gold drop-shadow-2xl fade-up mt-3 md:mt-5 text-shadow-gold glow-gold">
       TƯ TƯỞNG HỒ CHÍ MINH
    </span>
</h1>

        <!-- Mô tả -->
        <p class="mt-8 md:mt-14 text-lg md:text-xl lg:text-xl text-gray-100 max-w-3xl md:max-w-4xl mx-auto leading-relaxed fade-up px-4 font-medium">
            <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm px-5 py-2 rounded-full border border-white/20">
                <i class="fa-solid fa-star text-brand-gold"></i>
                10+ đề chuẩn
            </span>
            <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm px-5 py-2 rounded-full border border-white/20 ml-2">
                <i class="fa-solid fa-lightbulb text-brand-gold"></i>
                Giải thích chi tiết
            </span>
            <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm px-5 py-2 rounded-full border border-white/20 ml-2 block md:inline-block mt-2 md:mt-0">
                <i class="fa-solid fa-chart-line text-brand-gold"></i>
                Xếp hạng realtime
            </span>
        </p>

        <!-- Stats -->
        <div class="mt-10 md:mt-12 lg:mt-14 grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-5 max-w-4xl mx-auto fade-up px-2">
    <div class="text-center bg-white/10 backdrop-blur-sm rounded-lg p-3 md:p-4">
        <div class="text-2xl sm:text-3xl md:text-4xl font-bold text-white counter" data-count="1234">0</div>
        <p class="text-gray-300 text-xs md:text-sm mt-1">Học viên</p>
    </div>
    <div class="text-center bg-white/10 backdrop-blur-sm rounded-lg p-3 md:p-4">
        <div class="text-2xl sm:text-3xl md:text-4xl font-bold text-white counter" data-count="10">0</div>
        <p class="text-gray-300 text-xs md:text-sm mt-1">Đề thi</p>
    </div>
    <div class="text-center bg-white/10 backdrop-blur-sm rounded-lg p-3 md:p-4">
        <div class="text-2xl sm:text-3xl md:text-4xl font-bold text-brand-gold counter" data-count="90">0</div>
        <p class="text-gray-300 text-xs md:text-sm mt-1">% Đạt 8+</p>
    </div>
    <div class="text-center bg-white/10 backdrop-blur-sm rounded-lg p-3 md:p-4">
        <div class="text-2xl sm:text-3xl md:text-4xl font-bold text-white">24/7</div>
        <p class="text-gray-300 text-xs md:text-sm mt-1">Hỗ trợ</p>
    </div>
</div>
    </div>
</section>



<!-- DẢI CHUYỂN TIẾP (OPTIONAL – TẠO CẢM GIÁC TÁCH NỀN) -->
<div class="h-24 bg-gradient-to-b from-black/20 to-slate-50"></div>

</section>

  <!-- LI THUYET -->
<section id="chapter-section" class="fade-up py-16 bg-gradient-to-b from-slate-50 to-white">

    <!-- TIÊU ĐỀ -->
    <div class="text-center mb-12 px-4">
        <div class="inline-flex items-center justify-center gap-4 mb-6">
            <div class="w-12 h-1 bg-gradient-to-r from-transparent to-brand-red"></div>
            <div class="flex items-center gap-3 text-brand-red">
                <i class="fa-solid fa-layer-group text-3xl md:text-4xl"></i>
                <span class="text-2xl md:text-3xl font-bold text-brand-dark">HỆ THỐNG KIẾN THỨC</span>
            </div>
            <div class="w-12 h-1 bg-gradient-to-l from-transparent to-brand-red"></div>
        </div>
    </div>

    <?php
    try {
        $stmt = $conn->prepare("SELECT * FROM chuong ORDER BY so_thu_tu ASC");
        $stmt->execute();
        $dsChuong = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $dsChuong = [];
    }
    ?>

    <!-- DANH SÁCH CHƯƠNG -->
    <div class="container mx-auto px-4 md:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 stagger-group">
            <?php foreach ($dsChuong as $index => $chuong): 
                $hidden = $index >= 2 ? 'hidden extra-chapter' : '';
                $delay = $index * 100;
            ?>
                <div class="<?php echo $hidden; ?> h-full">
                    <div class="group bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-500 border border-slate-100 hover:border-brand-red/20 hover:-translate-y-2 h-full flex flex-col animate-fadeIn" style="animation-delay: <?php echo $delay; ?>ms">
                        
                        <!-- Phần trên: Số chương và badge -->
                        <div class="flex items-center justify-between mb-6">
                            <span class="inline-flex items-center gap-2 text-brand-red font-bold text-sm bg-red-50 px-4 py-2 rounded-full">
                                <i class="fa-solid fa-bookmark"></i>
                                Chương <?php echo $chuong['so_thu_tu']; ?>
                            </span>
                            <span class="text-slate-400 text-sm">
                                <?php echo $index + 1; ?>/<?php echo count($dsChuong); ?>
                            </span>
                        </div>

                        <!-- Tiêu đề chương -->
                        <h3 class="text-2xl font-bold mb-4 text-slate-900 group-hover:text-brand-red transition-colors duration-300 line-clamp-2 min-h-[3.5rem]">
                            <?php echo htmlspecialchars($chuong['ten_chuong']); ?>
                        </h3>

                        <!-- Đường phân cách -->
                        <div class="h-px w-16 bg-gradient-to-r from-brand-red to-transparent mb-6"></div>

                        <!-- Nội dung mô tả -->
                        <div class="flex-grow mb-6">
                            <p class="text-slate-600 leading-relaxed line-clamp-3">
                                <?php echo htmlspecialchars(strip_tags($chuong['noi_dung'])); ?>
                            </p>
                        </div>

                        <!-- Phần dưới: Thông tin và nút -->
                        <div class="mt-auto pt-6 border-t border-slate-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center text-slate-500 text-sm">
                                    <i class="far fa-clock mr-2"></i>
                                    <span><?php echo ceil(str_word_count(strip_tags($chuong['noi_dung'])) / 200); ?> phút đọc</span>
                                </div>
                                <a href="chuong.php?id=<?php echo $chuong['id']; ?>"
                                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-red text-white font-semibold rounded-full hover:bg-red-700 transition-all duration-300 shadow-md hover:shadow-lg group/btn">
                                    <span>Chi tiết</span>
                                    <i class="fa-solid fa-arrow-right text-sm group-hover/btn:translate-x-1 transition-transform"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- NÚT XEM THÊM -->
        <?php if (count($dsChuong) > 2): ?>
            <div class="text-center mt-12">
                <button onclick="toggleChapters()"
                        id="toggleChaptersBtn"
                        class="group inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-slate-800 to-slate-900 text-white rounded-full font-bold
                               hover:from-brand-red hover:to-red-700 transition-all duration-500 shadow-xl hover:shadow-2xl">
                    <span>Xem thêm <?php echo count($dsChuong) - 2; ?> chương</span>
                    <i class="fa-solid fa-chevron-down group-hover:rotate-180 transition-transform duration-500"></i>
                </button>
                <p class="mt-3 text-slate-500 text-sm">
                    Tổng cộng <?php echo count($dsChuong); ?> chương học
                </p>
            </div>
        <?php endif; ?>
    </div>
</section>


        
    <!-- MAIN CONTENT: NGÂN HÀNG CÂU HỎI ÔN TẬP -->
<section class="my-24" id="bank-questions-section">
            <div class="text-center mb-12">
                <h2 class="font-serif text-3xl md:text-4xl font-bold text-slate-800 mb-4 border-l-4 border-green-600 pl-4 inline-block">
                    <i class="fa-solid fa-database mr-3 text-green-600"></i> Ngân Hàng Câu Hỏi Ôn Tập
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Card 1: Luyện ngẫu nhiên -->
                <div class="bg-white rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 border border-green-100 flex flex-col h-full group">
                    <div class="p-8 text-center flex flex-col flex-grow">
                        <div class="w-24 h-24 mx-auto bg-green-50 text-green-600 rounded-full flex items-center justify-center mb-6 text-5xl group-hover:bg-green-600 group-hover:text-white transition">
                            <i class="fa-solid fa-shuffle"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-800 mb-4">Luyện Ngẫu Nhiên</h3>
                        <p class="text-slate-600 mb-8 flex-grow">
                            Hệ thống chọn ngẫu nhiên 40–50 câu từ toàn bộ ngân hàng để bạn luyện tập không giới hạn.
                        </p>
                        <a href="practice_questions.php?mode=random" class="mt-auto w-full py-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-2xl shadow-lg transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-play"></i> Bắt đầu ngay
                        </a>
                    </div>
                </div>

                <!-- Card 2: Theo chương/chủ đề -->
                <div class="bg-white rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 border border-blue-100 flex flex-col h-full group">
                    <div class="p-8 text-center flex flex-col flex-grow">
                        <div class="w-24 h-24 mx-auto bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-6 text-5xl group-hover:bg-blue-600 group-hover:text-white transition">
                            <i class="fa-solid fa-list-ol"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-800 mb-4">Theo Chương / Chủ Đề</h3>
                        <p class="text-slate-600 mb-8 flex-grow">
                            Chọn chương cụ thể (nếu database có phân loại) để ôn tập trọng tâm.
                        </p>
                        <a href="practice_questions.php?mode=by_chapter" class="mt-auto w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-search"></i> Chọn chương
                        </a>
                    </div>
                </div>

                <!-- Card 3: Xem toàn bộ ngân hàng -->
                <div class="bg-white rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 border border-orange-100 flex flex-col h-full group relative overflow-hidden">
                    <div class="absolute top-4 right-4 bg-orange-100 text-orange-700 text-xs font-bold px-3 py-1 rounded-full animate-pulse">
                        MỚI
                    </div>
                    <div class="p-8 text-center flex flex-col flex-grow">
                        <div class="w-24 h-24 mx-auto bg-orange-50 text-orange-600 rounded-full flex items-center justify-center mb-6 text-5xl group-hover:bg-orange-600 group-hover:text-white transition">
                            <i class="fa-solid fa-book-open-reader"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-800 mb-4">Xem Toàn Bộ Câu Hỏi</h3>
                        <p class="text-slate-600 mb-8 flex-grow">
                            Duyệt, tìm kiếm và xem đáp án + giải thích chi tiết cho mọi câu hỏi.
                        </p>
                        <a href="practice_questions.php?mode=all" class="mt-auto w-full py-4 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-bold rounded-2xl shadow-lg transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-eye"></i> Mở ngân hàng
                        </a>
                    </div>
                </div>
            </div>
        </section>
    <!-- MAIN CONTENT -->
    <main class="container mx-auto px-4 py-20 space-y-32">

      
       
    
<section id="exam-section"
    class="max-w-7xl mx-auto px-6 py-28">

    <!-- TIÊU ĐỀ TRUNG TÂM -->
    <div class="text-center mb-20 fade-up">
        <h2 class="text-4xl md:text-5xl font-bold text-brand-dark">
            Lộ Trình Ôn Thi Tư Tưởng Hồ Chí Minh
        </h2>
        <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">
            Chọn hình thức học phù hợp để tối ưu điểm số và tiết kiệm thời gian ôn tập
        </p>
    </div>

    <!-- 2 CARD -->
    <div class="grid lg:grid-cols-2 gap-16 items-stretch">

        <!-- CARD TRÁI -->
        <div class="bg-white rounded-3xl shadow-xl
                    p-12 flex flex-col justify-between
                    fade-up slide-left h-full">

            <div class="space-y-6">
                <h3 class="text-3xl font-bold text-brand-dark">
                    Luyện Đề Thi
                </h3>

                <ul class="space-y-4 text-lg text-slate-700">
                    <li class="flex gap-3">
                        <i class="fa-solid fa-check text-brand-gold mt-1"></i>
                        10+ đề thi từ 2018–2024
                    </li>
                    <li class="flex gap-3">
                        <i class="fa-solid fa-check text-brand-gold mt-1"></i>
                        Giải thích chi tiết từng câu
                    </li>
                    <li class="flex gap-3">
                        <i class="fa-solid fa-check text-brand-gold mt-1"></i>
                        Xếp hạng toàn quốc realtime
                    </li>
                    <li class="flex gap-3">
                        <i class="fa-solid fa-check text-brand-gold mt-1"></i>
                        Cá nhân hóa theo điểm yếu AI
                    </li>
                </ul>
            </div>

            <a href="list_exams.php?type=thi_thu"
               class="mt-10 inline-flex justify-center items-center gap-3
                      bg-gradient-to-r from-brand-red to-red-700
                      text-white px-10 py-5 rounded-2xl
                      font-bold text-xl shadow-lg
                      hover:shadow-red-500/40 hover:scale-105
                      transition pulse-btn">
                🚀 Vào kho đề thi ngay
            </a>
        </div>

        <!-- CARD PHẢI -->
        <div class="bg-white rounded-3xl shadow-xl
                    p-12 flex flex-col justify-between
                    fade-up slide-right h-full">

            <div class="space-y-6">
                <h3 class="text-3xl font-bold text-brand-dark">
                    Ôn Tập Theo Chương
                </h3>

                <ul class="space-y-4 text-lg text-slate-700">
                    <li class="flex gap-3">
                        <i class="fa-solid fa-layer-group text-brand-gold mt-1"></i>
                        Ôn tập kiến thức từng chương
                    </li>
                    <li class="flex gap-3">
                        <i class="fa-solid fa-pen-ruler text-brand-gold mt-1"></i>
                        Bài tập chuyên sâu theo chủ đề
                    </li>
                    <li class="flex gap-3">
                        <i class="fa-solid fa-chart-line text-brand-gold mt-1"></i>
                        Theo dõi tiến độ học tập
                    </li>
                    <li class="flex gap-3">
                        <i class="fa-solid fa-shield-halved text-brand-gold mt-1"></i>
                        Củng cố nền tảng vững chắc
                    </li>
                </ul>
            </div>

            <a href="list_exams.php?type=theo_chuong"
               class="mt-10 inline-flex justify-center items-center gap-3
                      bg-gradient-to-r from-brand-red to-red-700
                      text-white px-10 py-5 rounded-2xl
                      font-bold text-xl shadow-lg
                      hover:shadow-red-500/40 hover:scale-105
                      transition pulse-btn">
                📘 Vào ôn theo chương ngay
            </a>
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
    <footer class="bg-slate-900 text-slate-400 py-6 mt-auto">
    <div class="container mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-sm">
            © 2025 Ôn Thi Tư Tưởng Hồ Chí Minh
        </p>

        <div class="flex gap-5 text-sm">
            <a href="#" class="hover:text-brand-gold">Giới thiệu</a>
            <a href="#" class="hover:text-brand-gold">Liên hệ</a>
            <a href="#" class="hover:text-brand-gold">Điều khoản</a>
        </div>
    </div>
</footer>


         
    <!-- SCRIPTS -->
  <script>
    // Header scroll - chỉ thêm bóng khi scroll
    window.addEventListener('scroll', () => {
        const header = document.getElementById('main-header');
        header.classList.toggle('shadow-md', window.scrollY > 50);
    });

    // Toggle chương
    function toggleChapters() {
        document.querySelectorAll('.extra-chapter').forEach(el => el.classList.toggle('hidden'));
        const btn = event.target.closest('button');
        btn.innerHTML = btn.innerHTML.includes('Xem thêm') 
            ? 'Thu gọn lại' 
            : `Xem thêm ${document.querySelectorAll('.extra-chapter').length} chương`;
    }

    // Counter animation - đơn giản hơn
    const counters = document.querySelectorAll('.counter');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = parseInt(entry.target.getAttribute('data-count'));
                let count = 0;
                const increment = target / 50;
                const timer = setInterval(() => {
                    count += increment;
                    entry.target.textContent = Math.floor(count).toLocaleString('vi-VN');
                    if (count >= target) {
                        entry.target.textContent = target === 90 
                            ? target.toLocaleString('vi-VN') + '%' 
                            : target.toLocaleString('vi-VN') + '+';
                        clearInterval(timer);
                    }
                }, 30);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });
    
    counters.forEach(c => counterObserver.observe(c));
    
    // Page loader
    window.addEventListener('load', () => {
        setTimeout(() => {
            const loader = document.getElementById('page-loader');
            if (loader) loader.style.display = 'none';
        }, 800);
    });
</script>
</body>
</html>