<?php
// list_exams.php
require_once 'db.php';
session_start();

// 1. Lấy tham số từ URL
$type = isset($_GET['type']) ? $_GET['type'] : 'thi_thu';
$chuong_id = isset($_GET['chuong_id']) ? (int)$_GET['chuong_id'] : 0;

// 2. Xử lý Logic hiển thị dựa trên tham số
$mode = ''; // Chế độ hiển thị: 'list_chapters', 'list_exams_by_chapter', 'list_exams_thithu'
$data = [];
$pageTitle = "";
$subTitle = "";
$bgClass = "";
$backLink = "index.php"; // Link nút quay lại mặc định

if ($type == 'theo_chuong') {
    $bgClass = "bg-blue-600";
    
    if ($chuong_id > 0) {
        // TRƯỜNG HỢP A: Đã chọn chương -> Hiện danh sách đề của chương đó
        $mode = 'list_exams_by_chapter';
        $backLink = "list_exams.php?type=theo_chuong"; // Quay lại danh sách chương
        
        // Lấy thông tin chương để hiện tiêu đề
        $stmtC = $conn->prepare("SELECT * FROM chuong WHERE id = :id");
        $stmtC->execute([':id' => $chuong_id]);
        $chuongInfo = $stmtC->fetch(PDO::FETCH_ASSOC);
        
        $pageTitle = $chuongInfo ? $chuongInfo['ten_chuong'] : "Ôn tập chương";
        $subTitle = "Danh sách các bài luyện tập";

        // Lấy đề thi thuộc chương này
        $stmt = $conn->prepare("SELECT * FROM bai_thi WHERE loai_de = 'theo_chuong' AND chuong_id = :cid");
        $stmt->execute([':cid' => $chuong_id]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        // TRƯỜNG HỢP B: Chưa chọn chương -> Hiện danh sách 6 chương
        $mode = 'list_chapters';
        $pageTitle = "Ôn Tập Theo Chương";
        $subTitle = "Chọn chương bạn muốn ôn luyện kiến thức";
        
        // Lấy tất cả chương
        $stmt = $conn->prepare("SELECT * FROM chuong ORDER BY so_thu_tu ASC");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} else {
    // TRƯỜNG HỢP C: Thi thử (Hiện danh sách đề thi thử luôn)
    $mode = 'list_exams_thithu';
    $bgClass = "bg-brand-red";
    $pageTitle = "Kho Đề Thi Thử Tổng Hợp";
    $subTitle = "Đề thi chuẩn cấu trúc, có tính giờ";
    
    $stmt = $conn->prepare("SELECT * FROM bai_thi WHERE loai_de = 'thi_thu' ORDER BY id DESC");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
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
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

    <header class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-4 h-16 flex items-center justify-between">
            <a href="<?php echo $backLink; ?>" class="flex items-center gap-2 text-slate-500 hover:text-brand-red transition font-bold">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-brand-red rounded flex items-center justify-center text-white">
                    <i class="fa-solid fa-star text-xs"></i>
                </div>
                <span class="font-serif font-bold text-lg text-slate-800">HCM Ideology</span>
            </div>
            <div class="w-24"></div> </div>
    </header>

    <div class="<?php echo $bgClass; ?> py-12 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 p-10 opacity-10">
            <i class="fa-solid <?php echo ($mode == 'list_exams_thithu') ? 'fa-graduation-cap' : 'fa-layer-group'; ?> text-9xl"></i>
        </div>
        <div class="container mx-auto px-4 relative z-10">
            <h1 class="font-serif text-3xl md:text-4xl font-bold mb-3"><?php echo htmlspecialchars($pageTitle); ?></h1>
            <p class="text-white/80 text-lg"><?php echo htmlspecialchars($subTitle); ?></p>
        </div>
    </div>

    <main class="container mx-auto px-4 py-12 flex-grow">
        
        <?php if ($mode == 'list_chapters'): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($data as $index => $row): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-xl hover:-translate-y-1 transition duration-300 flex flex-col h-full group cursor-pointer relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition">
                            <i class="fa-solid fa-book-open text-6xl text-blue-600"></i>
                        </div>
                        
                        <div class="flex items-center gap-3 mb-4">
                            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full uppercase">
                                Chương <?php echo $row['so_thu_tu']; ?>
                            </span>
                        </div>

                        <h3 class="font-bold text-lg text-slate-800 mb-2 group-hover:text-blue-600 transition line-clamp-2">
                            <?php echo htmlspecialchars($row['ten_chuong']); ?>
                        </h3>
                        
                        <p class="text-sm text-slate-500 mb-6 line-clamp-2 flex-grow">
                            <?php echo htmlspecialchars(strip_tags($row['noi_dung'])); ?>
                        </p>

                        <a href="list_exams.php?type=theo_chuong&chuong_id=<?php echo $row['id']; ?>" class="mt-auto w-full flex items-center justify-center gap-2 bg-white border border-slate-200 text-slate-600 hover:border-blue-600 hover:text-blue-600 py-3 rounded-lg font-bold transition">
                            <span>Vào ôn tập</span> <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        
        <?php else: ?>
            
            <?php if (count($data) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($data as $de): ?>
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-lg hover:-translate-y-1 transition duration-300 flex flex-col h-full group">
                            
                            <div class="flex justify-between items-start mb-4">
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center text-xl
                                    <?php echo ($type == 'theo_chuong') ? 'bg-blue-50 text-blue-600' : 'bg-red-50 text-brand-red'; ?>">
                                    <i class="fa-solid <?php echo ($type == 'theo_chuong') ? 'fa-file-pen' : 'fa-clock'; ?>"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded">
                                    #ID: <?php echo $de['id']; ?>
                                </span>
                            </div>

                            <h3 class="font-bold text-lg text-slate-800 mb-2 group-hover:opacity-80 transition line-clamp-2">
                                <?php echo htmlspecialchars($de['tieu_de']); ?>
                            </h3>

                            <p class="text-sm text-slate-500 mb-6 line-clamp-2 flex-grow">
                                <?php echo htmlspecialchars($de['mo_ta']); ?>
                            </p>

                            <div class="border-t border-slate-100 pt-4 mt-auto flex items-center justify-between">
                                <div class="text-sm font-medium text-slate-600">
                                    <i class="fa-regular fa-clock mr-1"></i> <?php echo $de['thoi_gian']; ?> phút
                                </div>
                                <a href="exam.php?id=<?php echo $de['id']; ?>" class="px-5 py-2 rounded-lg font-bold text-sm transition text-white shadow-md hover:shadow-lg
                                    <?php echo ($type == 'theo_chuong') 
                                        ? 'bg-blue-600 hover:bg-blue-700' 
                                        : 'bg-brand-red hover:bg-red-700'; ?>">
                                    Làm bài
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-20 bg-white rounded-2xl border border-dashed border-slate-300">
                    <i class="fa-solid fa-folder-open text-6xl text-slate-200 mb-4"></i>
                    <h3 class="text-xl font-bold text-slate-600">Chưa có bài tập nào</h3>
                    <p class="text-slate-400">Danh sách câu hỏi đang được cập nhật.</p>
                    <a href="<?php echo $backLink; ?>" class="inline-block mt-6 px-6 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg font-medium transition">
                        Quay lại
                    </a>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </main>

    <footer class="bg-white border-t border-slate-200 py-6 mt-12">
        <div class="container mx-auto px-4 text-center text-sm text-slate-500">
            &copy; 2024 HCM Ideology. Nền tảng ôn thi trực tuyến.
        </div>
    </footer>

</body>
</html>