<?php
// admin_tool.php - Chỉ dành cho Admin (Tạm thời ai vào cũng được để bạn test)
require_once 'db.php';

$message = "";

if (isset($_POST['action']) && $_POST['action'] == 'generate_exams') {
    try {
        $conn->beginTransaction();

        // 1. XÓA CÁC LIÊN KẾT CŨ (Làm sạch đề)
        $conn->exec("TRUNCATE TABLE bai_thi_cau_hoi");
        
        // 2. TẠO ĐỀ ÔN TẬP THEO CHƯƠNG (Tự động trộn)
        // Lấy danh sách các đề ôn tập
        $stmtExams = $conn->query("SELECT id, chuong_id, tieu_de FROM bai_thi WHERE loai_de = 'theo_chuong'");
        $exams = $stmtExams->fetchAll(PDO::FETCH_ASSOC);

        foreach ($exams as $exam) {
            // Với mỗi đề, lấy ngẫu nhiên 20 câu hỏi thuộc chương đó
            $sqlInsert = "INSERT INTO bai_thi_cau_hoi (bai_thi_id, cau_hoi_id)
                          SELECT :eid, id FROM cau_hoi 
                          WHERE chuong_id = :cid 
                          ORDER BY RAND() LIMIT 20";
            $stmt = $conn->prepare($sqlInsert);
            $stmt->execute([':eid' => $exam['id'], ':cid' => $exam['chuong_id']]);
        }

        // 3. TẠO ĐỀ THI THỬ (Tự động trộn tổng hợp)
        $stmtTests = $conn->query("SELECT id FROM bai_thi WHERE loai_de = 'thi_thu'");
        $tests = $stmtTests->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tests as $test) {
            // Lấy ngẫu nhiên 40 câu từ toàn bộ kho
            $sqlInsert = "INSERT INTO bai_thi_cau_hoi (bai_thi_id, cau_hoi_id)
                          SELECT :eid, id FROM cau_hoi 
                          ORDER BY RAND() LIMIT 40";
            $stmt = $conn->prepare($sqlInsert);
            $stmt->execute([':eid' => $test['id']]);
        }

        $conn->commit();
        $message = "✅ Đã tạo bộ đề thành công! Tất cả đề thi đã có dữ liệu mới.";
    } catch (Exception $e) {
        $conn->rollBack();
        $message = "❌ Lỗi: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Tool - Quản lý Đề Thi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 p-10">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-2">
            <i class="fa-solid fa-screwdriver-wrench text-blue-600"></i> Công cụ trộn đề thi
        </h1>

        <?php if($message): ?>
            <div class="p-4 mb-6 rounded-lg <?php echo strpos($message, '✅') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="space-y-6">
            <div class="border-l-4 border-blue-500 pl-4 py-1 bg-blue-50">
                <h3 class="font-bold text-blue-700">Chức năng này làm gì?</h3>
                <p class="text-sm text-slate-600 mt-1">
                    Hệ thống sẽ xóa toàn bộ câu hỏi trong các đề thi hiện tại, sau đó lấy ngẫu nhiên từ kho câu hỏi để "bơm" vào lại.
                </p>
                <p class="text-sm text-slate-600 mt-1">
                    👉 Sử dụng khi bạn vừa nhập thêm câu hỏi mới vào kho và muốn cập nhật lại các đề thi.
                </p>
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="generate_exams">
                <button type="submit" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-rotate"></i> Trộn và Tạo đề mới ngay
                </button>
            </form>
            
            <div class="text-center pt-4 border-t border-slate-100">
                <a href="index.php" class="text-slate-500 hover:text-blue-600 font-medium">
                    <i class="fa-solid fa-arrow-left"></i> Quay về trang chủ
                </a>
            </div>
        </div>
    </div>
</body>
</html>