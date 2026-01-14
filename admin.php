<?php
session_start();
require_once 'db.php';

// 1. Kiểm tra quyền Admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: index.php"); 
    exit;
}

$message = "";
$msgType = "";

// Hàm hỗ trợ lấy ID câu hỏi ngẫu nhiên
function getRandomQuestionIds($conn, $cid, $level) {
    $stmt = $conn->prepare("SELECT id FROM cau_hoi WHERE chuong_id = :cid AND muc_do = :level ORDER BY RAND()");
    $stmt->execute([':cid' => $cid, ':level' => $level]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// 2. Xử lý Logic Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $conn->beginTransaction();

        // --- Logic: Làm mới đề theo Chương ---
        if ($_POST['action'] == 'mix_chapter') {
            $chapId = (int)$_POST['chapter_id'];
            
            // Lấy 6 đề thi của chương
            $stmt = $conn->prepare("SELECT id FROM bai_thi WHERE chuong_id = :cid ORDER BY id ASC LIMIT 6");
            $stmt->execute([':cid' => $chapId]);
            $examIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (count($examIds) < 6) throw new Exception("Chương $chapId chưa đủ 6 đề thi để trộn.");

            // Xóa câu hỏi cũ
            $conn->exec("DELETE FROM bai_thi_cau_hoi WHERE bai_thi_id IN (" . implode(',', $examIds) . ")");

            // Lấy kho câu hỏi
            $poolDe = getRandomQuestionIds($conn, $chapId, 'de');
            $poolTB = getRandomQuestionIds($conn, $chapId, 'trung_binh');
            $poolKho = getRandomQuestionIds($conn, $chapId, 'kho');

            $stmtInsert = $conn->prepare("INSERT INTO bai_thi_cau_hoi (bai_thi_id, cau_hoi_id, thu_tu) VALUES (:eid, :qid, 0)");

            // Chia bài
            foreach ($examIds as $eid) {
                $questions = array_merge(
                    array_splice($poolDe, 0, 13),
                    array_splice($poolTB, 0, 14),
                    array_splice($poolKho, 0, 13)
                );
                shuffle($questions);
                foreach ($questions as $qid) $stmtInsert->execute([':eid' => $eid, ':qid' => $qid]);
            }
            $message = "Đã làm mới thành công 6 đề thi của Chương $chapId!";
            $msgType = "success";

        // --- Logic: Trộn đề thi thử ---
        } elseif ($_POST['action'] == 'mix_mock_test') {
            $mockExamIds = $conn->query("SELECT id FROM bai_thi WHERE loai_de = 'thi_thu'")->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($mockExamIds)) throw new Exception("Chưa có đề thi thử nào.");

            $conn->exec("DELETE FROM bai_thi_cau_hoi WHERE bai_thi_id IN (" . implode(',', $mockExamIds) . ")");

            $structure = [1 => 7, 2 => 7, 3 => 7, 4 => 7, 5 => 6, 6 => 6];
            $stmtInsert = $conn->prepare("INSERT INTO bai_thi_cau_hoi (bai_thi_id, cau_hoi_id, thu_tu) VALUES (:eid, :qid, 0)");

            foreach ($mockExamIds as $eid) {
                $examQuestions = [];
                foreach ($structure as $chapId => $limit) {
                    $ids = $conn->query("SELECT id FROM cau_hoi WHERE chuong_id = $chapId ORDER BY RAND() LIMIT $limit")->fetchAll(PDO::FETCH_COLUMN);
                    $examQuestions = array_merge($examQuestions, $ids);
                }
                shuffle($examQuestions);
                foreach ($examQuestions as $qid) $stmtInsert->execute([':eid' => $eid, ':qid' => $qid]);
            }
            $message = "Đã trộn mới thành công " . count($mockExamIds) . " đề thi thử!";
            $msgType = "success";
        }

        $conn->commit();
    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        $message = "Lỗi: " . $e->getMessage();
        $msgType = "error";
    }
}

// 3. Lấy thống kê
$stats = [
    'users' => $conn->query("SELECT COUNT(*) FROM nguoi_dung")->fetchColumn(),
    'questions' => $conn->query("SELECT COUNT(*) FROM cau_hoi")->fetchColumn(),
    'exams' => $conn->query("SELECT COUNT(*) FROM bai_thi")->fetchColumn()
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HCM Ideology</title>
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
    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans h-screen flex overflow-hidden">

    <aside class="w-64 bg-brand-dark text-white flex-shrink-0 flex flex-col transition-all duration-300 hidden md:flex">
        <div class="h-20 flex items-center gap-3 px-6 border-b border-slate-700">
            <div class="w-8 h-8 bg-brand-red rounded flex items-center justify-center text-white">
                <i class="fa-solid fa-star text-sm"></i>
            </div>
            <span class="font-serif font-bold text-lg text-white">HCM Admin</span>
        </div>

        <nav class="flex-grow py-6 space-y-1 px-3">
            <a href="#" class="flex items-center gap-3 px-4 py-3 bg-brand-red rounded-xl text-white font-medium shadow-lg shadow-red-900/20">
                <i class="fa-solid fa-gauge-high w-6"></i> Tổng quan
            </a>
            <a href="admin_questions.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition">
                <i class="fa-solid fa-database w-6"></i> Ngân hàng câu hỏi
            </a>
            <a href="admin_chapters.php" class="block px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <i class="fa-solid fa-book-open w-6"></i> Quản lý Chương
            </a>
            <a href="admin_users.php" class="block px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <i class="fa-solid fa-users w-6"></i> Quản lý Người dùng
            </a>
            <a href="index.php" target="_blank" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-brand-gold hover:bg-slate-800 rounded-xl transition mt-8">
                <i class="fa-solid fa-arrow-up-right-from-square w-6"></i> Xem trang chủ
            </a>
        </nav>

        <div class="p-4 border-t border-slate-700">
            <a href="logout.php" class="flex items-center gap-2 text-red-400 hover:text-red-300 font-bold px-2">
                <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
            </a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 md:hidden">
            <span class="font-serif font-bold text-brand-red">HCM Admin</span>
            <button class="text-slate-600"><i class="fa-solid fa-bars text-xl"></i></button>
        </header>

        <div class="flex-1 overflow-y-auto p-6 lg:p-10">
            
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h1 class="font-serif text-3xl font-bold text-slate-800">Bảng điều khiển</h1>
                    <p class="text-slate-500 mt-1">Quản lý hệ thống thi trắc nghiệm Tư tưởng Hồ Chí Minh</p>
                </div>
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold text-slate-600"><?php echo date('d/m/Y'); ?></div>
                    <div class="text-xs text-slate-400">Hôm nay</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-800"><?php echo $stats['users']; ?></div>
                        <div class="text-sm text-slate-500">Người dùng đăng ký</div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-yellow-50 text-yellow-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-circle-question"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-800"><?php echo $stats['questions']; ?></div>
                        <div class="text-sm text-slate-500">Tổng câu hỏi</div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-red-50 text-brand-red flex items-center justify-center text-xl">
                        <i class="fa-solid fa-file-pen"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-800"><?php echo $stats['exams']; ?></div>
                        <div class="text-sm text-slate-500">Đề thi hiện có</div>
                    </div>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="mb-8 p-4 rounded-xl flex items-center gap-3 animate-fade-in <?php echo $msgType == 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
                    <i class="fa-solid <?php echo $msgType == 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
                    <span class="font-medium"><?php echo $message; ?></span>
                </div>
            <?php endif; ?>

            <div class="mb-6">
                <h2 class="font-serif text-xl font-bold text-slate-800 mb-4 border-l-4 border-brand-red pl-3">
                    Công Cụ Trộn Đề Thi (Exam Mixer)
                </h2>
                <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
                    <div class="flex items-start gap-4 mb-6 bg-slate-50 p-4 rounded-xl">
                        <i class="fa-solid fa-robot text-2xl text-brand-gold"></i>
                        <div class="text-sm text-slate-600">
                            <p class="font-bold text-slate-800 mb-1">Cách hoạt động:</p>
                            Khi bấm "Làm mới", hệ thống sẽ xóa câu hỏi cũ, lấy ngẫu nhiên 40 câu mới từ ngân hàng (đảm bảo tỷ lệ Dễ/TB/Khó) và phân phối đều vào 6 đề thi.
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php for($i = 1; $i <= 6; $i++): ?>
                            <div class="border border-slate-200 rounded-2xl p-6 hover:shadow-md transition hover:border-brand-red/30 group">
                                <div class="flex justify-between items-start mb-4">
                                    <span class="bg-red-50 text-brand-red text-xs font-bold px-3 py-1 rounded-full">CHƯƠNG <?php echo $i; ?></span>
                                    <i class="fa-solid fa-layer-group text-slate-300 group-hover:text-brand-red transition"></i>
                                </div>
                                <h3 class="font-bold text-slate-800 mb-2">Bộ 6 đề ôn tập</h3>
                                <p class="text-xs text-slate-400 mb-6">Trạng thái: Đã kích hoạt</p>
                                
                                <form method="POST" onsubmit="return confirm('CẢNH BÁO: Hành động này sẽ thay đổi toàn bộ câu hỏi của các đề thi Chương <?php echo $i; ?>. Bạn có chắc chắn không?');">
                                    <input type="hidden" name="action" value="mix_chapter">
                                    <input type="hidden" name="chapter_id" value="<?php echo $i; ?>">
                                    <button type="submit" class="w-full py-3 rounded-xl bg-slate-800 text-white font-bold text-sm shadow-md hover:bg-brand-red transition flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-shuffle"></i> Làm mới đề
                                    </button>
                                </form>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <div class="mt-10">
                <h2 class="font-serif text-xl font-bold text-slate-800 mb-4 border-l-4 border-blue-600 pl-3">
                    Quản Lý Đề Thi Thử (Tổng Hợp)
                </h2>
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-3xl p-8 border border-blue-100 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-blue-600 text-white rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                            <h3 class="font-bold text-lg text-slate-800">Kho Đề Thi Thử</h3>
                        </div>
                        <p class="text-slate-600 text-sm max-w-xl">
                            Chức năng này sẽ lấy câu hỏi ngẫu nhiên từ <strong>tất cả 6 chương</strong> theo tỷ lệ chuẩn và phân phối vào các đề thi thử.
                        </p>
                    </div>
                    
                    <form method="POST" onsubmit="return confirm('Hành động này sẽ tạo lại nội dung cho TẤT CẢ đề thi thử hiện có. Bạn chắc chắn chứ?');">
                        <input type="hidden" name="action" value="mix_mock_test">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-xl shadow-lg shadow-blue-200 transition transform hover:scale-105 flex items-center gap-3 whitespace-nowrap">
                            <i class="fa-solid fa-shuffle"></i> Trộn Đề Thi Thử
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-200 text-center text-slate-400 text-sm">
                HCM Ideology Admin Panel &copy; <?php echo date('Y'); ?>
            </div>

        </div>
    </main>
</body>
</html>
