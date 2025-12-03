<?php
session_start();
require_once 'db.php'; // Kết nối database

// 1. Lấy ID chương từ URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Lỗi: ID chương không hợp lệ.");
}
$chuong_id = intval($_GET['id']);

// 2. Lấy thông tin Chương
try {
    $stmt = $conn->prepare("SELECT * FROM chuong WHERE id = ?");
    $stmt->execute([$chuong_id]);
    $chuong = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chuong) {
        die("Lỗi: Không tìm thấy chương này.");
    }
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// 3. Lấy danh sách Bài học thuộc chương
try {
    $stmtBai = $conn->prepare("SELECT * FROM bai_hoc WHERE chuong_id = ? ORDER BY thu_tu ASC");
    $stmtBai->execute([$chuong_id]);
    $dsBaiHoc = $stmtBai->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $dsBaiHoc = [];
}

// Hàm hỗ trợ chuyển link Youtube thường thành link Embed (nếu cần)
function getYoutubeEmbedUrl($url) {
    $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_-]+)\??/i';
    $longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))([a-zA-Z0-9_-]+)/i';

    if (preg_match($longUrlRegex, $url, $matches)) {
        return "https://www.youtube.com/embed/" . $matches[3];
    }

    if (preg_match($shortUrlRegex, $url, $matches)) {
        return "https://www.youtube.com/embed/" . $matches[1];
    }
    return $url;
}

$is_logged_in = isset($_SESSION['user_id']);
$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($chuong['ten_chuong']); ?> - Ôn Thi Tư Tưởng HCM</title>
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

    <header class="fixed w-full z-50 bg-white/95 backdrop-blur-md shadow-sm border-b border-slate-100">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="index.php" class="flex items-center gap-2 text-slate-500 hover:text-brand-red transition">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại trang chủ
                </a>
                <h1 class="font-bold text-lg text-slate-800 truncate max-w-md hidden md:block">
                    <?php echo htmlspecialchars($chuong['ten_chuong']); ?>
                </h1>
                <div class="w-8"></div> </div>
        </div>
    </header>

    <main class="container mx-auto px-4 pt-24 pb-16 flex-grow">
        
        <div class="max-w-4xl mx-auto mb-10 text-center">
            <span class="inline-block py-1 px-3 rounded-full bg-brand-red/10 text-brand-red text-sm font-bold mb-4">
                CHƯƠNG <?php echo $chuong['so_thu_tu']; ?>
            </span>
            <h1 class="font-serif text-3xl md:text-4xl font-bold text-slate-900 mb-6">
                <?php echo htmlspecialchars($chuong['ten_chuong']); ?>
            </h1>
            <div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-200 text-left">
                <p class="text-slate-600 leading-relaxed">
                    <?php echo nl2br(htmlspecialchars($chuong['noi_dung'])); ?>
                </p>
            </div>
        </div>

        <div class="max-w-4xl mx-auto space-y-8">
            <h2 class="text-2xl font-bold text-slate-800 border-l-4 border-brand-gold pl-4 mb-6">
                Nội dung bài học
            </h2>

            <?php if (count($dsBaiHoc) > 0): ?>
                <?php foreach ($dsBaiHoc as $index => $bai): ?>
                    <article class="bg-white rounded-2xl shadow-md overflow-hidden border border-slate-100 transition hover:shadow-lg" id="bai-<?php echo $bai['id']; ?>">
                        <div class="bg-slate-50 p-4 border-b border-slate-100 flex items-center justify-between cursor-pointer" onclick="toggleBaiHoc(<?php echo $index; ?>)">
                            <h3 class="font-bold text-lg text-brand-dark flex items-center gap-3">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-brand-red text-white text-sm">
                                    <?php echo $index + 1; ?>
                                </span>
                                <?php echo htmlspecialchars($bai['ten_bai']); ?>
                            </h3>
                            <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-300" id="icon-<?php echo $index; ?>"></i>
                        </div>

                        <div class="<?php echo $index === 0 ? '' : 'hidden'; ?> p-6 md:p-8 space-y-6" id="content-<?php echo $index; ?>">
                            
                            <?php if (!empty($bai['video_url'])): ?>
                                <div class="relative aspect-video rounded-xl overflow-hidden shadow-sm bg-black">
                                    <iframe 
                                        class="absolute top-0 left-0 w-full h-full"
                                        src="<?php echo getYoutubeEmbedUrl($bai['video_url']); ?>" 
                                        title="<?php echo htmlspecialchars($bai['ten_bai']); ?>"
                                        frameborder="0" 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                        allowfullscreen>
                                    </iframe>
                                </div>
                            <?php endif; ?>

                            <div class="prose prose-slate max-w-none text-slate-600 leading-loose text-justify">
                                <?php 
                                    // Cho phép render HTML nếu nội dung trong DB có thẻ HTML (cân nhắc bảo mật nếu cần)
                                    // Nếu nội dung chỉ là text thuần thì dùng nl2br(htmlspecialchars(...))
                                    echo $bai['noi_dung']; 
                                ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-12 bg-white rounded-2xl border border-dashed border-slate-300">
                    <i class="fa-solid fa-person-digging text-4xl text-slate-300 mb-3"></i>
                    <p class="text-slate-500">Nội dung bài học đang được cập nhật.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="max-w-4xl mx-auto mt-12 flex justify-between">
            <a href="index.php" class="px-6 py-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 font-medium transition">
                <i class="fa-solid fa-arrow-left mr-2"></i> Chọn chương khác
            </a>
            <a href="list_exams.php?chuong_id=<?php echo $chuong_id; ?>" class="px-6 py-3 rounded-xl bg-brand-red text-white hover:bg-red-700 font-bold shadow-lg shadow-red-200 transition">
                Luyện đề chương này <i class="fa-solid fa-arrow-right ml-2"></i>
            </a>
        </div>

    </main>

    <footer class="bg-brand-dark text-slate-400 py-8 text-center text-sm">
        <div class="container mx-auto">
            <p>&copy; 2024 HCM Ideology. Hệ thống ôn thi trực tuyến.</p>
        </div>
    </footer>

    <script>
        // Script để đóng/mở (Accordion) các bài học
        function toggleBaiHoc(index) {
            const content = document.getElementById(`content-${index}`);
            const icon = document.getElementById(`icon-${index}`);
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }
    </script>

</body>
</html>