<?php

require_once 'db.php';

// Tắt báo lỗi hiển thị để không hỏng JSON khi nộp bài
error_reporting(0);
ini_set('display_errors', 0);

$exam_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$user_id = 1; // ID người dùng mẫu (cần có trong bảng nguoi_dung)

// --- XỬ LÝ NỘP BÀI (AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) throw new Exception("Không có dữ liệu.");

        $score = $input['score'];
        $correct_count = $input['correct_count'];
        $details = $input['answers']; // Mảng câu trả lời: [{question_id: 1, selected: 'A'}, ...]

        $conn->beginTransaction();

        // 1. Lưu vào bảng 'lich_su_lam_bai'
        // Lưu ý: bảng của bạn có cột 'ten_bai_thi', ta lấy tạm tên từ client hoặc query thêm
        $stmtName = $conn->prepare("SELECT tieu_de FROM bai_thi WHERE id = :id");
        $stmtName->execute([':id' => $exam_id]);
        $examTitle = $stmtName->fetchColumn();

        $sqlHistory = "INSERT INTO lich_su_lam_bai (nguoi_dung_id, bai_thi_id, ten_bai_thi, diem_so, thoi_gian_bat_dau, thoi_gian_ket_thuc) 
                       VALUES (:uid, :eid, :title, :score, NOW(), NOW())"; // Tạm thời start/end giống nhau
        $stmtHis = $conn->prepare($sqlHistory);
        $stmtHis->execute([
            ':uid' => $user_id,
            ':eid' => $exam_id,
            ':title' => $examTitle,
            ':score' => $score
        ]);
        $history_id = $conn->lastInsertId();

        // 2. Lưu vào bảng 'chi_tiet_bai_lam'
        $sqlDetail = "INSERT INTO chi_tiet_bai_lam (lich_su_id, cau_hoi_id, cau_tra_loi, dung_sai) 
                      VALUES (:ls_id, :qh_id, :ans, :is_correct)";
        $stmtDet = $conn->prepare($sqlDetail);

        foreach ($details as $d) {
            // Xác định đúng sai (Server check lại cho chắc hoặc tin Client)
            // Ở đây mình nhận cờ is_correct từ client gửi lên cho nhanh (dựa vào logic JS bên dưới)
            $stmtDet->execute([
                ':ls_id' => $history_id,
                ':qh_id' => $d['question_id'],
                ':ans'   => $d['selected'], // Lưu 'A', 'B', 'C', hoặc 'D'
                ':is_correct' => $d['is_correct'] ? 1 : 0
            ]);
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'result_id' => $history_id]);
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

// --- PHẦN HIỂN THỊ GIAO DIỆN (GET) ---
ini_set('display_errors', 1); // Bật lại lỗi để debug giao diện
error_reporting(E_ALL);

// 1. Lấy thông tin bài thi
$stmtExam = $conn->prepare("SELECT * FROM bai_thi WHERE id = :id");
$stmtExam->execute([':id' => $exam_id]);
$examInfo = $stmtExam->fetch(PDO::FETCH_ASSOC);

if (!$examInfo) die("Không tìm thấy bài thi!");

// 2. Lấy câu hỏi từ bảng 'cau_hoi' thông qua bảng trung gian 'bai_thi_cau_hoi'
$sqlQ = "SELECT ch.* FROM cau_hoi ch
         JOIN bai_thi_cau_hoi btch ON ch.id = btch.cau_hoi_id
         WHERE btch.bai_thi_id = :eid";
$stmtQ = $conn->prepare($sqlQ);
$stmtQ->execute([':eid' => $exam_id]);
$rawQuestions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

// 3. Chuẩn hóa dữ liệu cho Javascript (Vì bảng của bạn là cauA, cauB... nên cần map lại thành mảng options)
$formattedQuestions = [];
foreach ($rawQuestions as $row) {
    // Tạo cấu trúc options giả lập để tái sử dụng giao diện cũ
    $options = [
        ['code' => 'A', 'content' => $row['cauA']],
        ['code' => 'B', 'content' => $row['cauB']],
        ['code' => 'C', 'content' => $row['cauC']],
        ['code' => 'D', 'content' => $row['cauD']]
    ];
    
    // Xáo trộn đáp án nếu muốn (ở đây mình giữ nguyên thứ tự A-B-C-D cho dễ)
    
    $formattedQuestions[] = [
        'id' => $row['id'],
        'content' => $row['cauHoi'],
        'correct_code' => $row['dapAn'], // Lưu đáp án đúng (A/B/C/D) để JS chấm
        'options' => $options
    ];
}

$jsonData = json_encode(['info' => $examInfo, 'questions' => $formattedQuestions]);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($examInfo['tieu_de']); ?></title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { theme: { extend: { colors: { 'brand-red': '#c62828', 'brand-gold': '#fbc02d', 'brand-dark': '#1e293b' }, fontFamily: { sans: ['Inter', 'sans-serif'], serif: ['Merriweather', 'serif'] } } } }
    </script>
    <style>
        .option-card:hover { background-color: #fef2f2; border-color: #c62828; }
        .option-selected { background-color: #fef2f2; border-color: #c62828; position: relative; }
        .option-selected::after { content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #c62828; }
        .q-grid-item.active { background: #1e293b; color: white; border-color: #1e293b; }
        .q-grid-item.answered { background: #c62828; color: white; border-color: #c62828; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex flex-col overflow-hidden">
    <header class="bg-white/95 backdrop-blur-md shadow-sm border-b border-slate-100 h-16 flex-none z-50">
        <div class="container mx-auto px-4 h-full flex justify-between items-center">
            <h1 class="font-serif font-bold text-lg text-brand-red truncate max-w-xs">
                <?php echo htmlspecialchars($examInfo['tieu_de']); ?>
            </h1>
            <div class="glass-card px-4 py-1 rounded-full flex items-center gap-2 border-brand-red/20">
                <i class="fa-regular fa-clock text-brand-red"></i>
                <span id="timer" class="font-mono font-bold text-xl text-slate-800">--:--</span>
            </div>
            <button onclick="submitExam()" class="bg-brand-red text-white px-5 py-2 rounded-lg font-bold shadow-md hover:bg-red-700 text-sm">Nộp bài</button>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 py-6 flex gap-6 overflow-hidden h-[calc(100vh-64px)]">
        <div class="w-full lg:w-3/4 flex flex-col h-full gap-4">
            <div class="w-full bg-slate-200 rounded-full h-1.5 flex-none"><div id="progress-bar" class="bg-brand-gold h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div></div>
            <div class="glass-card bg-white rounded-2xl p-8 flex-grow overflow-y-auto shadow-sm border border-slate-100 relative">
                <div id="question-container"></div>
            </div>
            <div class="flex justify-between items-center flex-none py-2">
                <button onclick="changeQuestion(-1)" id="btn-prev" class="px-6 py-3 rounded-xl border border-slate-200 hover:bg-slate-50 disabled:opacity-50 font-semibold text-slate-600">Trước</button>
                <button onclick="changeQuestion(1)" id="btn-next" class="px-8 py-3 rounded-xl bg-brand-red text-white font-bold shadow-lg">Sau</button>
            </div>
        </div>
        <div class="hidden lg:flex w-1/4 flex-col h-full">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 h-full flex flex-col">
                <h3 class="font-bold text-slate-700 mb-4">Danh sách câu hỏi</h3>
                <div class="flex-grow overflow-y-auto pr-2">
                    <div id="question-grid" class="grid grid-cols-5 gap-2"></div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const fullData = <?php echo $jsonData; ?>;
        const quizData = fullData.questions;
        const examInfo = fullData.info;

        let currentQuestionIndex = 0;
        let userAnswers = new Array(quizData.length).fill(null); // Lưu mã đáp án: 'A', 'B',...
        let timeLeft = (examInfo.thoi_gian || 45) * 60; 
        let timerInterval;

        document.addEventListener('DOMContentLoaded', () => {
            renderGrid();
            loadQuestion(0);
            startTimer();
        });

        function renderGrid() {
            const grid = document.getElementById('question-grid');
            grid.innerHTML = '';
            quizData.forEach((_, i) => {
                const item = document.createElement('div');
                item.className = `q-grid-item flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 text-sm font-semibold cursor-pointer hover:bg-slate-100 ${i===0?'active':''}`;
                item.innerText = i + 1;
                item.onclick = () => loadQuestion(i);
                item.id = `grid-item-${i}`;
                grid.appendChild(item);
            });
        }

        function loadQuestion(index) {
            currentQuestionIndex = index;
            const q = quizData[index];
            const container = document.getElementById('question-container');
            
            document.querySelectorAll('.q-grid-item').forEach(e => e.classList.remove('active'));
            document.getElementById(`grid-item-${index}`).classList.add('active');

            let html = `
                <div class="mb-6 animate-fade-in">
                    <span class="text-brand-red font-bold text-sm bg-red-50 px-3 py-1 rounded-full mb-3 inline-block">Câu ${index + 1}</span>
                    <h3 class="text-xl font-serif font-bold text-slate-800">${q.content}</h3>
                </div>
                <div class="space-y-3 animate-fade-in">
            `;

            q.options.forEach(opt => {
                // Kiểm tra xem đã chọn chưa (so sánh mã A,B,C,D)
                const isSelected = userAnswers[index] === opt.code ? 'option-selected' : '';
                html += `
                    <div onclick="selectAnswer(${index}, '${opt.code}')" 
                         class="option-card p-4 rounded-xl border border-slate-200 flex items-center gap-4 bg-white cursor-pointer transition-all ${isSelected}">
                        <div class="w-8 h-8 rounded-full border-2 border-slate-300 flex items-center justify-center flex-none text-sm font-bold text-slate-500">${opt.code}</div>
                        <span class="text-slate-700 font-medium">${opt.content}</span>
                    </div>
                `;
            });
            html += `</div>`;
            container.innerHTML = html;
            updateButtons();
        }

        function selectAnswer(index, code) {
            userAnswers[index] = code;
            document.getElementById(`grid-item-${index}`).classList.add('answered');
            loadQuestion(index); // Re-render để hiện màu chọn
            updateProgressBar();
        }

        function updateButtons() {
            document.getElementById('btn-prev').disabled = currentQuestionIndex === 0;
            const nextBtn = document.getElementById('btn-next');
            if (currentQuestionIndex === quizData.length - 1) {
                nextBtn.innerText = 'Nộp bài';
                nextBtn.onclick = submitExam;
                nextBtn.classList.replace('bg-brand-red', 'bg-green-600');
            } else {
                nextBtn.innerText = 'Sau';
                nextBtn.onclick = () => changeQuestion(1);
                nextBtn.classList.remove('bg-green-600');
                nextBtn.classList.add('bg-brand-red');
            }
        }

        function changeQuestion(dir) {
            const newIndex = currentQuestionIndex + dir;
            if (newIndex >= 0 && newIndex < quizData.length) loadQuestion(newIndex);
        }

        function updateProgressBar() {
            const count = userAnswers.filter(a => a).length;
            document.getElementById('progress-bar').style.width = `${(count/quizData.length)*100}%`;
        }

        function startTimer() {
            const el = document.getElementById('timer');
            timerInterval = setInterval(() => {
                if(timeLeft <= 0) { clearInterval(timerInterval); submitExam(); return; }
                timeLeft--;
                const m = Math.floor(timeLeft/60).toString().padStart(2,'0');
                const s = (timeLeft%60).toString().padStart(2,'0');
                el.innerText = `${m}:${s}`;
            }, 1000);
        }

        function submitExam() {
            clearInterval(timerInterval);
            let correct = 0;
            const details = [];

            quizData.forEach((q, i) => {
                const choice = userAnswers[i]; // 'A', 'B' ... hoặc null
                const isTrue = (choice === q.correct_code); // So sánh với đáp án đúng từ DB
                if(isTrue) correct++;
                
                details.push({
                    question_id: q.id,
                    selected: choice, // Gửi 'A', 'B'... lên server
                    is_correct: isTrue
                });
            });

            const score = (correct / quizData.length) * 10;

            fetch('exam.php?id=<?php echo $exam_id; ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ score, correct_count: correct, answers: details })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') window.location.href = `result.php?id=${data.result_id}`;
                else alert('Lỗi: ' + data.message);
            })
            .catch(err => alert('Lỗi kết nối server'));
        }
    </script>
</body>
</html>