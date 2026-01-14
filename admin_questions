<?php
session_start();
require_once 'db.php';

// 1. CHECK QUYỀN ADMIN
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";
$msgType = "";

// 2. XỬ LÝ FORM (THÊM / SỬA / XÓA)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- XÓA CÂU HỎI ---
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $id = (int)$_POST['id'];
        try {
            $conn->exec("DELETE FROM bai_thi_cau_hoi WHERE cau_hoi_id = $id");
            $conn->exec("DELETE FROM cau_hoi WHERE id = $id");
            $message = "Đã xóa câu hỏi #$id thành công!";
            $msgType = "success";
        } catch (Exception $e) {
            $message = "Lỗi xóa: " . $e->getMessage();
            $msgType = "error";
        }
    }

    // --- THÊM HOẶC SỬA CÂU HỎI ---
    elseif (isset($_POST['action']) && $_POST['action'] == 'save') {
        try {
            $chuong = (int)$_POST['chuong_id'];
            $noi_dung = $_POST['cauHoi'];
            $a = $_POST['cauA']; $b = $_POST['cauB']; $c = $_POST['cauC']; $d = $_POST['cauD'];
            $dap_an = $_POST['dapAn'];
            $giai_thich = $_POST['giaiThich'];
            $muc_do = $_POST['muc_do'];
            $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;

            if ($edit_id > 0) {
                // UPDATE
                $sql = "UPDATE cau_hoi SET chuong_id=?, cauHoi=?, cauA=?, cauB=?, cauC=?, cauD=?, dapAn=?, giaiThich=?, muc_do=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$chuong, $noi_dung, $a, $b, $c, $d, $dap_an, $giai_thich, $muc_do, $edit_id]);
                $message = "Đã cập nhật câu hỏi #$edit_id!";
            } else {
                // INSERT
                $sql = "INSERT INTO cau_hoi (chuong_id, cauHoi, cauA, cauB, cauC, cauD, dapAn, giaiThich, muc_do) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$chuong, $noi_dung, $a, $b, $c, $d, $dap_an, $giai_thich, $muc_do]);
                $message = "Đã thêm câu hỏi mới thành công!";
            }
            $msgType = "success";
        } catch (Exception $e) {
            $message = "Lỗi lưu dữ liệu: " . $e->getMessage();
            $msgType = "error";
        }
    }
}

// 3. LỌC & TÌM KIẾM & PHÂN TRANG
$filter_chapter = isset($_GET['chapter']) ? (int)$_GET['chapter'] : 1;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Xây dựng câu Query động
$sqlWhere = "WHERE chuong_id = :chapter";
$params = [':chapter' => $filter_chapter];

if (!empty($search_query)) {
    // Tìm theo nội dung câu hỏi HOẶC tìm theo ID
    $sqlWhere .= " AND (cauHoi LIKE :search OR id = :search_exact)";
    $params[':search'] = "%$search_query%";
    $params[':search_exact'] = $search_query; // Dùng cho trường hợp nhập số ID
}

// Đếm tổng số câu (để phân trang)
$stmtCount = $conn->prepare("SELECT COUNT(*) FROM cau_hoi $sqlWhere");
$stmtCount->execute($params); // Khi đếm cũng phải dùng params y hệt
$totalRows = $stmtCount->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Lấy danh sách câu hỏi
$sqlList = "SELECT * FROM cau_hoi $sqlWhere ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmtList = $conn->prepare($sqlList);
$stmtList->execute($params); // Thực thi query chính
$questions = $stmtList->fetchAll(PDO::FETCH_ASSOC);

// Xử lý biến edit (nếu bấm nút sửa)
$editQuestion = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $stmtEdit = $conn->prepare("SELECT * FROM cau_hoi WHERE id = ?");
    $stmtEdit->execute([$eid]);
    $editQuestion = $stmtEdit->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Câu hỏi - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 flex h-screen overflow-hidden">

    <aside class="w-64 bg-slate-900 text-white flex-shrink-0 flex flex-col hidden md:flex">
        <div class="h-16 flex items-center px-6 border-b border-slate-700 font-bold text-lg">
            HCM Admin
        </div>
        <nav class="p-4 space-y-2">
            <a href="admin.php" class="block px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <i class="fa-solid fa-gauge-high w-6"></i> Tổng quan
            </a>
            <a href="admin_questions.php" class="block px-4 py-3 rounded-lg bg-red-600 text-white shadow-md">
                <i class="fa-solid fa-database w-6"></i> Ngân hàng câu hỏi
            </a>
            <a href="admin_chapters.php" class="block px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <i class="fa-solid fa-book-open w-6"></i> Quản lý Chương
            </a>
            <a href="admin_users.php" class="block px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
            <i class="fa-solid fa-users w-6"></i> Quản lý Người dùng
            </a>
            <a href="admin.php" class="block px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <i class="fa-solid fa-arrow-right-from-bracket w-6"></i> Về trang chủ
            </a>
        </nav>
        <div class="p-4 border-t border-slate-700">
            <a href="logout.php" class="flex items-center gap-2 text-red-400 hover:text-red-300 font-bold px-2">
                <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
            </a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden">
        <div class="flex-1 overflow-y-auto p-6">
            
            <h1 class="text-2xl font-bold mb-6">Ngân hàng câu hỏi</h1>

            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $msgType == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-8 transition-all duration-300">
                <h2 class="font-bold text-lg mb-4 text-blue-600 border-b pb-2 flex justify-between items-center">
                    <?php echo $editQuestion ? "✏️ Sửa câu hỏi #".$editQuestion['id'] : "➕ Thêm câu hỏi mới"; ?>
                </h2>
                
                <form method="POST" action="admin_questions.php?chapter=<?php echo $filter_chapter; ?>&search=<?php echo urlencode($search_query); ?>&page=<?php echo $page; ?>">
                    <input type="hidden" name="action" value="save">
                    <?php if ($editQuestion): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $editQuestion['id']; ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-bold mb-1">Chương</label>
                            <select name="chuong_id" class="w-full border p-2 rounded focus:border-blue-500 outline-none">
                                <?php for($i=1; $i<=6; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($editQuestion && $editQuestion['chuong_id'] == $i) || (!$editQuestion && $filter_chapter == $i) ? 'selected' : ''; ?>>Chương <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-1">Mức độ</label>
                            <select name="muc_do" class="w-full border p-2 rounded focus:border-blue-500 outline-none">
                                <option value="de" <?php echo ($editQuestion && $editQuestion['muc_do'] == 'de') ? 'selected' : ''; ?>>Dễ (Nhận biết)</option>
                                <option value="trung_binh" <?php echo ($editQuestion && $editQuestion['muc_do'] == 'trung_binh') ? 'selected' : ''; ?>>Trung bình (Thông hiểu)</option>
                                <option value="kho" <?php echo ($editQuestion && $editQuestion['muc_do'] == 'kho') ? 'selected' : ''; ?>>Khó (Vận dụng)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-1">Đáp án đúng</label>
                            <select name="dapAn" class="w-full border p-2 rounded font-bold text-red-600 focus:border-blue-500 outline-none">
                                <option value="A" <?php echo ($editQuestion && $editQuestion['dapAn'] == 'A') ? 'selected' : ''; ?>>A</option>
                                <option value="B" <?php echo ($editQuestion && $editQuestion['dapAn'] == 'B') ? 'selected' : ''; ?>>B</option>
                                <option value="C" <?php echo ($editQuestion && $editQuestion['dapAn'] == 'C') ? 'selected' : ''; ?>>C</option>
                                <option value="D" <?php echo ($editQuestion && $editQuestion['dapAn'] == 'D') ? 'selected' : ''; ?>>D</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-1">Nội dung câu hỏi</label>
                        <textarea name="cauHoi" rows="2" class="w-full border p-2 rounded focus:border-blue-500 outline-none" required><?php echo $editQuestion['cauHoi'] ?? ''; ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div><input type="text" name="cauA" placeholder="Đáp án A" class="w-full border p-2 rounded focus:border-blue-500 outline-none" value="<?php echo $editQuestion['cauA'] ?? ''; ?>" required></div>
                        <div><input type="text" name="cauB" placeholder="Đáp án B" class="w-full border p-2 rounded focus:border-blue-500 outline-none" value="<?php echo $editQuestion['cauB'] ?? ''; ?>" required></div>
                        <div><input type="text" name="cauC" placeholder="Đáp án C" class="w-full border p-2 rounded focus:border-blue-500 outline-none" value="<?php echo $editQuestion['cauC'] ?? ''; ?>" required></div>
                        <div><input type="text" name="cauD" placeholder="Đáp án D" class="w-full border p-2 rounded focus:border-blue-500 outline-none" value="<?php echo $editQuestion['cauD'] ?? ''; ?>" required></div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-1 text-gray-500">Giải thích (Không bắt buộc)</label>
                        <input type="text" name="giaiThich" class="w-full border p-2 rounded focus:border-blue-500 outline-none" value="<?php echo $editQuestion['giaiThich'] ?? ''; ?>">
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 font-bold shadow">
                            <?php echo $editQuestion ? "Lưu thay đổi" : "Thêm câu hỏi"; ?>
                        </button>
                        <?php if($editQuestion): ?>
                            <a href="admin_questions.php?chapter=<?php echo $filter_chapter; ?>&search=<?php echo urlencode($search_query); ?>" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400 font-bold">Hủy</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                
                <div class="flex gap-2 overflow-x-auto pb-2 md:pb-0 w-full md:w-auto">
                    <?php for($i=1; $i<=6; $i++): ?>
                        <a href="admin_questions.php?chapter=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>" 
                           class="px-3 py-1.5 rounded-full text-sm font-bold whitespace-nowrap transition <?php echo $filter_chapter == $i ? 'bg-slate-800 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
                            Chương <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>

                <form action="admin_questions.php" method="GET" class="flex w-full md:w-auto">
                    <input type="hidden" name="chapter" value="<?php echo $filter_chapter; ?>">
                    <div class="relative w-full md:w-64">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" 
                               placeholder="Tìm nội dung hoặc ID..." 
                               class="w-full pl-10 pr-10 py-2 border border-slate-300 rounded-l-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none text-sm">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                        
                        <?php if(!empty($search_query)): ?>
                            <a href="admin_questions.php?chapter=<?php echo $filter_chapter; ?>" class="absolute right-2 top-2 text-slate-400 hover:text-red-500">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-r-lg hover:bg-blue-700 font-bold text-sm">
                        Tìm
                    </button>
                </form>
            </div>
            
            <?php if ($totalRows > 0): ?>
                <div class="text-sm text-gray-500 mb-2">
                    Tìm thấy <b><?php echo $totalRows; ?></b> câu hỏi 
                    <?php echo !empty($search_query) ? "cho từ khóa \"$search_query\"" : "trong Chương $filter_chapter"; ?>.
                </div>
            <?php else: ?>
                <div class="bg-yellow-50 text-yellow-700 p-4 rounded-lg text-center mb-6 border border-yellow-200">
                    <i class="fa-solid fa-circle-exclamation mr-2"></i> Không tìm thấy câu hỏi nào phù hợp!
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-100 text-slate-600 font-bold uppercase text-xs">
                        <tr>
                            <th class="p-4 w-16">ID</th>
                            <th class="p-4">Nội dung câu hỏi</th>
                            <th class="p-4 w-20 text-center">Đ/Án</th>
                            <th class="p-4 w-24">Mức độ</th>
                            <th class="p-4 w-24 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($questions as $q): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-4 font-bold text-slate-400">#<?php echo $q['id']; ?></td>
                                <td class="p-4">
                                    <div class="font-medium text-slate-800 mb-1 text-base">
                                        <?php 
                                            // Highlight từ khóa tìm kiếm
                                            $noiDung = htmlspecialchars($q['cauHoi']);
                                            if(!empty($search_query)) {
                                                echo str_ireplace($search_query, "<span class='bg-yellow-200'>$search_query</span>", $noiDung);
                                            } else {
                                                echo $noiDung;
                                            }
                                        ?>
                                    </div>
                                    <div class="text-xs text-slate-500 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 mt-2 bg-slate-50 p-2 rounded">
                                        <span><b class="text-slate-700">A.</b> <?php echo htmlspecialchars($q['cauA']); ?></span>
                                        <span><b class="text-slate-700">B.</b> <?php echo htmlspecialchars($q['cauB']); ?></span>
                                        <span><b class="text-slate-700">C.</b> <?php echo htmlspecialchars($q['cauC']); ?></span>
                                        <span><b class="text-slate-700">D.</b> <?php echo htmlspecialchars($q['cauD']); ?></span>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-block w-8 h-8 leading-8 rounded-full bg-red-100 text-red-700 font-bold text-sm">
                                        <?php echo $q['dapAn']; ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <?php 
                                        $badges = [
                                            'de' => '<span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold border border-green-200">Dễ</span>',
                                            'trung_binh' => '<span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold border border-yellow-200">TB</span>',
                                            'kho' => '<span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold border border-red-200">Khó</span>'
                                        ];
                                        echo $badges[$q['muc_do']] ?? $q['muc_do'];
                                    ?>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="admin_questions.php?chapter=<?php echo $filter_chapter; ?>&search=<?php echo urlencode($search_query); ?>&page=<?php echo $page; ?>&edit=<?php echo $q['id']; ?>" class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa câu hỏi #<?php echo $q['id']; ?>?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $q['id']; ?>">
                                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded transition" title="Xóa">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="mt-6 flex justify-center gap-2">
                <?php 
                    // Tạo query string cơ bản cho link phân trang
                    $baseLink = "admin_questions.php?chapter=$filter_chapter&search=" . urlencode($search_query) . "&page=";
                ?>
                
                <?php if ($page > 1): ?>
                    <a href="<?php echo $baseLink . ($page - 1); ?>" class="px-3 py-1 rounded bg-white border hover:bg-slate-100 text-slate-600">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php for($p=1; $p<=$totalPages; $p++): ?>
                    <?php if ($p == 1 || $p == $totalPages || ($p >= $page - 2 && $p <= $page + 2)): ?>
                        <a href="<?php echo $baseLink . $p; ?>" 
                           class="px-3 py-1 rounded border font-bold transition <?php echo $page == $p ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-600 hover:bg-slate-100'; ?>">
                            <?php echo $p; ?>
                        </a>
                    <?php elseif ($p == $page - 3 || $p == $page + 3): ?>
                        <span class="px-2">...</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?php echo $baseLink . ($page + 1); ?>" class="px-3 py-1 rounded bg-white border hover:bg-slate-100 text-slate-600">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </main>
</body>
</html>
