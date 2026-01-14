<?php
session_start();
require_once 'db.php'; // Kết nối database cauhoi

$mode = $_GET['mode'] ?? 'random';
$chuong_id = isset($_GET['chuong_id']) ? (int)$_GET['chuong_id'] : 0;

$title = "Ôn Tập Câu Hỏi Trắc Nghiệm";
$questions = [];

try {
    if ($mode === 'random') {
        $sql = "SELECT * FROM cau_hoi ORDER BY RAND() LIMIT 50";
        $stmt = $conn->query($sql);
        $questions = $stmt->fetchAll();
        $title = "Luyện Ngẫu Nhiên - 50 Câu Hỏi";
    } 
    elseif ($mode === 'all') {
        $sql = "SELECT * FROM cau_hoi ORDER BY id";
        $stmt = $conn->query($sql);
        $questions = $stmt->fetchAll();
        $title = "Toàn Bộ Ngân Hàng Câu Hỏi (" . count($questions) . " câu)";
    } 
    elseif ($mode === 'by_chapter' && $chuong_id > 0) {
        $stmt = $conn->prepare("SELECT * FROM cau_hoi WHERE chuong_id = :cid ORDER BY RAND() LIMIT 60");
        $stmt->execute([':cid' => $chuong_id]);
        $questions = $stmt->fetchAll();
        $title = "Luyện Tập Chương $chuong_id - " . count($questions) . " Câu";
    } 
    elseif ($mode === 'by_chapter') {
        // Trang chọn chương
        $stmt = $conn->query("SELECT DISTINCT chuong_id FROM cau_hoi ORDER BY chuong_id");
        $chapters = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $title = "Chọn Chương Để Luyện Tập";
    }
} catch (Exception $e) {
    $error = "Lỗi tải câu hỏi: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - HCM Ideology</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen">
    <header class="bg-white shadow-md py-5 sticky top-0 z-10">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-brand-red">HCM Ideology</a>
            <a href="index.php" class="text-slate-600 hover:text-brand-red font-medium">
                <i class="fa-solid fa-arrow-left mr-2"></i>Quay về trang chủ
            </a>
        </div>
    </header>

    <main class="container mx-auto px-4 py-10 max-w-5xl">
        <h1 class="text-4xl font-bold text-center mb-10 text-slate-800"><?php echo htmlspecialchars($title); ?></h1>

        <?php if (isset($error)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php elseif ($mode === 'by_chapter' && !empty($chapters)): ?>
            <!-- Trang chọn chương -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <?php foreach ($chapters as $ch): ?>
                    <a href="practice_questions.php?mode=by_chapter&chuong_id=<?php echo $ch; ?>"
                       class="block bg-white hover:bg-blue-50 border-2 border-slate-200 hover:border-blue-500 rounded-2xl p-8 text-center shadow-md hover:shadow-xl transition transform hover:-translate-y-1">
                        <div class="text-4xl font-bold text-blue-600 mb-2">Chương <?php echo $ch; ?></div>
                        <div class="text-slate-600">Luyện tập ngay</div>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if (empty($chapters)): ?>
                <p class="text-center text-slate-500 text-xl">Chưa có dữ liệu chương nào.</p>
            <?php endif; ?>

        <?php elseif (empty($questions)): ?>
            <p class="text-center text-slate-500 text-xl">Không tìm thấy câu hỏi nào trong chế độ này.</p>

        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white px-8 py-5 text-center">
                    <p class="text-xl font-bold">Tổng cộng: <?php echo count($questions); ?> câu hỏi</p>
                </div>
                <div class="p-8 space-y-10">
                    <?php foreach ($questions as $i => $q): ?>
                        <div class="border border-slate-200 rounded-xl p-6 hover:shadow-lg transition bg-slate-50/50">
                            <p class="font-bold text-xl mb-5 text-slate-800">
                                Câu <?php echo $i + 1; ?>: <?php echo htmlspecialchars($q['cauHoi']); ?>
                                <?php if (!empty($q['muc_do'])): ?>
                                    <span class="ml-3 text-xs font-bold px-3 py-1 rounded-full bg-yellow-100 text-yellow-700">
                                        Mức độ: <?php echo ucfirst(htmlspecialchars($q['muc_do'])); ?>
                                    </span>
                                <?php endif; ?>
                            </p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div class="flex items-start">
                                    <span class="font-bold text-lg mr-3 text-blue-600">A.</span>
                                    <span><?php echo htmlspecialchars($q['cauA']); ?></span>
                                </div>
                                <div class="flex items-start">
                                    <span class="font-bold text-lg mr-3 text-green-600">B.</span>
                                    <span><?php echo htmlspecialchars($q['cauB']); ?></span>
                                </div>
                                <div class="flex items-start">
                                    <span class="font-bold text-lg mr-3 text-orange-600">C.</span>
                                    <span><?php echo htmlspecialchars($q['cauC']); ?></span>
                                </div>
                                <div class="flex items-start">
                                    <span class="font-bold text-lg mr-3 text-purple-600">D.</span>
                                    <span><?php echo htmlspecialchars($q['cauD']); ?></span>
                                </div>
                            </div>

                            <details class="cursor-pointer group">
                                <summary class="text-lg font-bold text-green-600 group-hover:text-green-700 flex items-center gap-2">
                                    <i class="fa-solid fa-lightbulb"></i> Xem đáp án & giải thích
                                    <i class="fa-solid fa-chevron-down ml-auto transition group-open:rotate-180"></i>
                                </summary>
                                <div class="mt-4 p-5 bg-green-50 border border-green-200 rounded-lg">
                                    <p class="font-bold text-green-800 text-lg mb-2">
                                        Đáp án đúng: <span class="text-2xl"><?php echo htmlspecialchars($q['dapAn']); ?></span>
                                    </p>
                                    <?php if (!empty($q['giaiThich'])): ?>
                                        <p class="text-slate-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($q['giaiThich'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </details>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>