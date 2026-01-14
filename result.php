<?php
require_once 'db.php';

$history_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT ten_bai_thi, diem_so, bai_thi_id FROM lich_su_lam_bai WHERE id = :id");
$stmt->execute([':id' => $history_id]);
$examInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$examInfo) die("Không tìm thấy kết quả bài thi này.");

$sql = "SELECT ct.cau_tra_loi, ct.dung_sai, 
               ch.cauHoi, ch.cauA, ch.cauB, ch.cauC, ch.cauD, ch.dapAn, ch.giaiThich 
        FROM chi_tiet_bai_lam ct
        JOIN cau_hoi ch ON ct.cau_hoi_id = ch.id
        WHERE ct.lich_su_id = :hid";

$stmt = $conn->prepare($sql);
$stmt->execute([':hid' => $history_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalQ = count($questions);
$correctQ = 0;
foreach ($questions as $q) {
    if ($q['dung_sai'] == 1) $correctQ++;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả: <?php echo htmlspecialchars($examInfo['ten_bai_thi']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 pb-20 font-sans">
    
    <header class="bg-white border-b h-16 flex items-center px-4 sticky top-0 z-50 shadow-sm">
        <a href="index.php" class="text-gray-500 hover:text-red-600 font-bold flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Quay lại
        </a>
        <h1 class="ml-4 pl-4 border-l-2 border-gray-300 font-bold truncate">
            <?php echo htmlspecialchars($examInfo['ten_bai_thi']); ?>
        </h1>
    </header>

    <main class="container mx-auto px-4 py-8 max-w-4xl">
        
        <div class="bg-white rounded-xl border border-slate-200 p-8 mb-8 flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm">
            <div>
                <p class="text-sm text-gray-500 font-bold uppercase">Kết quả bài làm</p>
                <h2 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($examInfo['ten_bai_thi']); ?></h2>
            </div>
            
            <div class="flex gap-10">
                <div class="text-center">
                    <div class="text-5xl font-black <?php echo $examInfo['diem_so'] >= 5 ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo number_format($examInfo['diem_so'], 1); ?>
                    </div>
                    <div class="text-xs font-bold text-gray-400 uppercase mt-1">Điểm số</div>
                </div>
                <div class="text-center border-l pl-10">
                    <div class="text-5xl font-black text-slate-700">
                        <?php echo $correctQ; ?><span class="text-2xl text-gray-400 font-medium">/<?php echo $totalQ; ?></span>
                    </div>
                    <div class="text-xs font-bold text-gray-400 uppercase mt-1">Câu đúng</div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <?php foreach($questions as $index => $row): ?>
                <?php 
                    $userAns = $row['cau_tra_loi'];
                    $rightAns = $row['dapAn'];
                    $isCorrect = ($row['dung_sai'] == 1);
                    $borderColor = $isCorrect ? 'border-green-500' : 'border-red-500';
                ?>
                
                <div class="bg-white rounded-xl border-l-4 p-6 shadow-sm <?php echo $borderColor; ?>">
                    <div class="flex gap-3 mb-4">
                        <span class="flex-none w-8 h-8 rounded-lg bg-gray-100 font-bold flex items-center justify-center text-gray-600">
                            <?php echo $index + 1; ?>
                        </span>
                        <h3 class="font-medium text-lg leading-snug"><?php echo $row['cauHoi']; ?></h3>
                    </div>

                    <div class="grid gap-2 ml-11">
                        <?php foreach(['A', 'B', 'C', 'D'] as $code): ?>
                            <?php 
                                $content = $row["cau$code"];
                                $cssClass = "bg-white border-gray-200 text-gray-600";
                                $icon = "";

                                if ($code === $rightAns) {
                                    $cssClass = "bg-green-50 border-green-500 text-green-800 font-bold";
                                    $icon = '<i class="fa-solid fa-check text-green-600"></i>';
                                } 
                                elseif ($code === $userAns) {
                                    $cssClass = "bg-red-50 border-red-400 text-red-800";
                                    $icon = '<i class="fa-solid fa-xmark text-red-600"></i>';
                                }
                            ?>
                            
                            <div class="p-3 border rounded-lg flex justify-between items-center text-sm <?php echo $cssClass; ?>">
                                <span><strong class="mr-2"><?php echo $code; ?>.</strong> <?php echo $content; ?></span>
                                <span><?php echo $icon; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if(!empty($row['giaiThich'])): ?>
                        <div class="mt-4 ml-11 p-3 bg-blue-50 text-blue-800 text-sm rounded-lg border border-blue-100">
                            <strong><i class="fa-solid fa-info-circle"></i> Giải thích:</strong> <?php echo $row['giaiThich']; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-10 text-center">
            <a href="exam.php?id=<?php echo $examInfo['bai_thi_id']; ?>" class="inline-block bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-lg transition shadow-lg">
                <i class="fa-solid fa-rotate-right mr-2"></i> Làm lại đề này
            </a>
        </div>

    </main>
</body>
</html>
