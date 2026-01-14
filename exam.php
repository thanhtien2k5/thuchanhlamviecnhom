<?php
require_once 'db.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$exam_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin đề
$stmt = $conn->prepare("SELECT * FROM bai_thi WHERE id = :id");
$stmt->execute([':id' => $exam_id]);
$exam = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exam) die("Đề thi không tồn tại.");

// KIỂM TRA LOẠI ĐỀ: Có phải thi thử không?
$isExamMode = ($exam['loai_de'] == 'thi_thu'); 
$timeLimit = isset($exam['thoi_gian']) ? (int)$exam['thoi_gian'] : 45; // Mặc định 45 phút

// Xử lý nộp bài
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = isset($_POST['answer']) ? $_POST['answer'] : []; 
    
    $stmtKey = $conn->prepare("SELECT ch.id, ch.dapAn FROM cau_hoi ch 
                               JOIN bai_thi_cau_hoi btch ON ch.id = btch.cau_hoi_id 
                               WHERE btch.bai_thi_id = :eid");
    $stmtKey->execute([':eid' => $exam_id]);
    $correctKeys = $stmtKey->fetchAll(PDO::FETCH_KEY_PAIR); 

    $total = count($correctKeys);
    $correctCount = 0;
    
    $conn->beginTransaction();
    try {
        $details = [];
        foreach ($correctKeys as $qid => $rightAns) {
            $userAns = isset($answers[$qid]) ? $answers[$qid] : null;
            $isRight = ($userAns === $rightAns) ? 1 : 0;
            if ($isRight) $correctCount++;
            $details[] = ['qid' => $qid, 'ans' => $userAns, 'is_right' => $isRight];
        }

        $score = ($total > 0) ? round(($correctCount / $total) * 10, 2) : 0;

        $stmtH = $conn->prepare("INSERT INTO lich_su_lam_bai (nguoi_dung_id, bai_thi_id, ten_bai_thi, diem_so, thoi_gian_bat_dau, thoi_gian_ket_thuc) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmtH->execute([$user_id, $exam_id, $exam['tieu_de'], $score]);
        $history_id = $conn->lastInsertId();

        $stmtD = $conn->prepare("INSERT INTO chi_tiet_bai_lam (lich_su_id, cau_hoi_id, cau_tra_loi, dung_sai) VALUES (?, ?, ?, ?)");
        foreach ($details as $d) {
            $stmtD->execute([$history_id, $d['qid'], $d['ans'], $d['is_right']]);
        }

        $conn->commit();
        header("Location: result.php?id=$history_id");
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        die("Lỗi hệ thống: " . $e->getMessage());
    }
}

// Lấy danh sách câu hỏi
$stmtQ = $conn->prepare("SELECT ch.* FROM cau_hoi ch
         JOIN bai_thi_cau_hoi btch ON ch.id = btch.cau_hoi_id
         WHERE btch.bai_thi_id = :eid
         ORDER BY btch.thu_tu ASC, btch.cau_hoi_id ASC");
$stmtQ->execute([':eid' => $exam_id]);
$questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($exam['tieu_de']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        input[type="radio"]:checked + label {
            background-color: #fef2f2;
            border-color: #dc2626;
            color: #991b1b;
        }
        input[type="radio"]:checked + label .check-icon { display: block; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

    <form id="examForm" action="" method="POST" class="flex flex-col h-full">
        
        <header class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm h-16 flex-none">
            <div class="container mx-auto px-4 h-full flex items-center justify-between">
                <a href="list_exams.php?type=<?php echo $exam['loai_de']; ?>" class="text-slate-500 hover:text-red-600 font-medium">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Thoát
                </a>
                
                <h1 class="font-bold text-lg text-slate-800 truncate px-4 flex-grow text-center lg:text-left">
                    <?php echo htmlspecialchars($exam['tieu_de']); ?>
                </h1>

                <div class="flex items-center gap-3">
                    
                    <?php if ($isExamMode): ?>
                        <div class="bg-slate-100 px-4 py-1 rounded-full border border-slate-300 flex items-center gap-2 text-red-600 font-bold min-w-[100px] justify-center">
                            <i class="fa-regular fa-clock"></i>
                            <span id="countdown">--:--</span>
                        </div>
                    <?php endif; ?>

                    <button type="submit" onclick="return confirm('Bạn chắc chắn muốn nộp bài?')" class="bg-red-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-red-700 transition shadow-md text-sm">
                        Nộp bài
                    </button>
                </div>
            </div>
        </header>

        <main class="container mx-auto px-4 py-8 flex-grow flex gap-6 items-start relative">
            
            <div class="w-full lg:w-3/4">
                <?php if (empty($questions)): ?>
                    <div class="text-center py-10 text-slate-500">Đề thi này chưa có câu hỏi nào.</div>
                <?php endif; ?>

                <?php foreach ($questions as $index => $q): ?>
                    <div id="cau-<?php echo $index + 1; ?>" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6 scroll-mt-24">
                        <div class="flex gap-4 mb-4">
                            <span class="flex-none h-8 px-3 rounded-full bg-red-100 text-red-700 font-bold text-sm flex items-center justify-center">
                                Câu <?php echo $index + 1; ?>
                            </span>
                            <h3 class="text-lg font-medium text-slate-800 pt-1 leading-snug">
                                <?php echo $q['cauHoi']; ?>
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 ml-0 md:ml-2">
                            <?php $options = ['A' => $q['cauA'], 'B' => $q['cauB'], 'C' => $q['cauC'], 'D' => $q['cauD']]; ?>
                            <?php foreach ($options as $key => $val): ?>
                                <div class="relative group">
                                    <input type="radio" 
                                           name="answer[<?php echo $q['id']; ?>]" 
                                           value="<?php echo $key; ?>" 
                                           id="q<?php echo $q['id']; ?>-<?php echo $key; ?>" 
                                           class="peer hidden">
                                    
                                    <label for="q<?php echo $q['id']; ?>-<?php echo $key; ?>" 
                                           class="block w-full p-4 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50 hover:border-slate-300 transition relative select-none">
                                        <span class="font-bold mr-2 text-slate-500"><?php echo $key; ?>.</span> 
                                        <span><?php echo $val; ?></span>
                                        <i class="fa-solid fa-check absolute top-4 right-4 text-red-600 hidden check-icon"></i>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="mt-8 mb-12 lg:hidden">
                    <button type="submit" class="w-full bg-red-600 text-white text-lg px-6 py-4 rounded-xl font-bold hover:bg-red-700 transition shadow-lg">
                        Nộp bài ngay
                    </button>
                </div>
            </div>

            <div class="hidden lg:block w-1/4 sticky top-24">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 max-h-[calc(100vh-120px)] flex flex-col">
                    <div class="mb-4 pb-4 border-b border-slate-100">
                        <h3 class="font-bold text-slate-700">Mục lục câu hỏi</h3>
                    </div>
                    
                    <div class="overflow-y-auto custom-scrollbar flex-grow pr-1">
                        <div class="grid grid-cols-5 gap-2">
                            <?php foreach ($questions as $i => $q): ?>
                                <a href="#cau-<?php echo $i + 1; ?>" 
                                   class="flex items-center justify-center w-full aspect-square rounded bg-slate-100 hover:bg-red-600 hover:text-white text-slate-600 text-sm font-bold transition duration-200">
                                    <?php echo $i + 1; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </form>

    <?php if ($isExamMode): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let duration = <?php echo $timeLimit * 60; ?>; 
            const display = document.querySelector('#countdown');
            
            const timer = setInterval(function () {
                let minutes = parseInt(duration / 60, 10);
                let seconds = parseInt(duration % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--duration < 0) {
                    clearInterval(timer);
                    alert("Đã hết thời gian làm bài! Hệ thống sẽ tự động nộp bài.");
                    document.getElementById('examForm').submit();
                }
            }, 1000);
        });
    </script>
    <?php endif; ?>

</body>
</html>
