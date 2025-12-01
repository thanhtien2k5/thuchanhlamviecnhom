<?php
// result.php
require_once 'db.php';

$history_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$history_id) die("Không tìm thấy kết quả.");

// 1. Lấy thông tin chung từ 'lich_su_lam_bai'
$sqlHis = "SELECT * FROM lich_su_lam_bai WHERE id = :id";
$stmtHis = $conn->prepare($sqlHis);
$stmtHis->execute([':id' => $history_id]);
$history = $stmtHis->fetch(PDO::FETCH_ASSOC);

if (!$history) die("Lịch sử không tồn tại.");

// 2. Lấy chi tiết bài làm + Nội dung câu hỏi
// JOIN giữa 'chi_tiet_bai_lam' và 'cau_hoi'
$sqlDet = "SELECT ct.*, ch.cauHoi, ch.cauA, ch.cauB, ch.cauC, ch.cauD, ch.dapAn, ch.giaiThich 
           FROM chi_tiet_bai_lam ct
           JOIN cau_hoi ch ON ct.cau_hoi_id = ch.id
           WHERE ct.lich_su_id = :hid";
$stmtDet = $conn->prepare($sqlDet);
$stmtDet->execute([':hid' => $history_id]);
$details = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

$totalQ = count($details);
$correctQ = 0;
foreach($details as $d) if($d['dung_sai'] == 1) $correctQ++;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả thi</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 pb-20 font-sans">
    
    <header class="bg-white border-b border-slate-200 h-16 flex items-center px-6 sticky top-0 z-50">
        <a href="home.html" class="text-slate-500 hover:text-red-600 font-medium"><i class="fa-solid fa-house mr-2"></i> Trang chủ</a>
        <span class="mx-3 text-slate-300">|</span>
        <h1 class="font-bold text-lg text-slate-800">Kết quả chi tiết</h1>
    </header>

    <main class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 mb-8 text-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-100 rounded-full blur-3xl opacity-50"></div>
            <h2 class="text-2xl font-bold text-slate-800 mb-2"><?php echo htmlspecialchars($history['ten_bai_thi']); ?></h2>
            <div class="flex justify-center items-center gap-12 mt-6">
                <div>
                    <div class="text-5xl font-bold <?php echo $history['diem_so']>=5?'text-green-600':'text-red-600'; ?>">
                        <?php echo number_format($history['diem_so'], 1); ?>
                    </div>
                    <div class="text-slate-500 font-medium mt-1">Điểm số</div>
                </div>
                <div class="text-left space-y-2">
                    <p class="text-slate-600"><i class="fa-solid fa-check-circle text-green-500 mr-2"></i>Đúng: <b><?php echo $correctQ; ?></b>/<?php echo $totalQ; ?></p>
                    <p class="text-slate-600"><i class="fa-solid fa-clock text-blue-500 mr-2"></i>Xong lúc: <?php echo date('H:i d/m', strtotime($history['thoi_gian_ket_thuc'])); ?></p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <?php foreach($details as $index => $row): ?>
                <?php 
                    $userAns = $row['cau_tra_loi']; // A, B, C, D hoặc null
                    $correctAns = $row['dapAn'];    // A, B, C, D
                    $isCorrect = ($row['dung_sai'] == 1);
                    
                    // Mảng map để dễ loop hiển thị
                    $opts = [
                        'A' => $row['cauA'],
                        'B' => $row['cauB'],
                        'C' => $row['cauC'],
                        'D' => $row['cauD']
                    ];
                ?>
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                    <div class="flex gap-3 mb-4">
                        <span class="flex-none px-3 py-1 rounded text-sm font-bold h-fit <?php echo $isCorrect?'bg-green-100 text-green-700':'bg-red-100 text-red-700'; ?>">
                            Câu <?php echo $index + 1; ?>
                        </span>
                        <h3 class="font-medium text-lg text-slate-800"><?php echo $row['cauHoi']; ?></h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php foreach($opts as $code => $content): ?>
                            <?php 
                                // Xác định màu sắc cho từng ô đáp án
                                $class = "bg-white border-slate-200 text-slate-600";
                                $icon = "";

                                if ($code === $correctAns) {
                                    // Đáp án đúng luôn hiện xanh
                                    $class = "bg-green-50 border-green-500 text-green-800 font-medium ring-1 ring-green-500";
                                    $icon = '<i class="fa-solid fa-check text-green-600 float-right mt-1"></i>';
                                } elseif ($code === $userAns && !$isCorrect) {
                                    // Chọn sai thì hiện đỏ
                                    $class = "bg-red-50 border-red-500 text-red-800 font-medium ring-1 ring-red-500";
                                    $icon = '<i class="fa-solid fa-xmark text-red-600 float-right mt-1"></i>';
                                }
                            ?>
                            <div class="p-3 border rounded-lg text-sm <?php echo $class; ?>">
                                <span class="font-bold mr-2"><?php echo $code; ?>.</span> <?php echo $content; ?>
                                <?php echo $icon; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if(!empty($row['giaiThich'])): ?>
                        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-100 rounded-lg text-sm text-slate-700">
                            <strong class="text-yellow-700"><i class="fa-regular fa-lightbulb mr-1"></i> Giải thích:</strong>
                            <?php echo $row['giaiThich']; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-8 text-center">
            <a href="exam.php?id=<?php echo $history['bai_thi_id']; ?>" class="inline-block px-6 py-3 bg-brand-red text-white font-bold rounded-lg shadow hover:bg-red-700">Làm lại đề này</a>
        </div>
    </main>
</body>
</html>