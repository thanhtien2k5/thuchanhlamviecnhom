<?php
session_start();
require_once 'db.php';

// 1. CHECK QUYỀN ADMIN
// Chỉ Admin mới được vào trang này
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";
$msgType = "";

// 2. XỬ LÝ FORM (THÊM / SỬA / XÓA)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // --- XÓA USER ---
    if ($action == 'delete') {
        $id = (int)$_POST['id'];
        
        // CHẶN: Không cho phép tự xóa chính mình
        if ($id == $_SESSION['user_id']) {
            $message = "Bạn không thể xóa tài khoản đang đăng nhập!";
            $msgType = "error";
        } else {
            try {
                // Xóa lịch sử làm bài của user này trước (nếu cần thiết, để tránh lỗi khóa ngoại)
                // $conn->exec("DELETE FROM lich_su_lam_bai WHERE nguoi_dung_id = $id");
                
                $conn->exec("DELETE FROM nguoi_dung WHERE id = $id");
                $message = "Đã xóa người dùng #$id thành công!";
                $msgType = "success";
            } catch (Exception $e) {
                $message = "Lỗi xóa: " . $e->getMessage();
                $msgType = "error";
            }
        }
    }

    // --- THÊM HOẶC SỬA USER ---
    elseif ($action == 'save') {
        try {
            $fullname = trim($_POST['ho_va_ten']);
            $email = trim($_POST['email']);
            $role = $_POST['role'];
            $password = $_POST['mat_khau']; // Có thể rỗng nếu đang sửa
            $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;

            // Kiểm tra trùng Email (trừ chính user đang sửa)
            $sqlCheck = "SELECT id FROM nguoi_dung WHERE email = :email AND id != :id";
            $stmtCheck = $conn->prepare($sqlCheck);
            $stmtCheck->execute([':email' => $email, ':id' => $edit_id]);
            
            if ($stmtCheck->rowCount() > 0) {
                throw new Exception("Email '$email' đã tồn tại trong hệ thống!");
            }

            if ($edit_id > 0) {
                // UPDATE (Sửa)
                if (!empty($password)) {
                    // Nếu nhập mật khẩu mới -> Hash và cập nhật
                    $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE nguoi_dung SET ho_va_ten=?, email=?, mat_khau=?, role=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$fullname, $email, $hashed_pass, $role, $edit_id]);
                } else {
                    // Nếu để trống mật khẩu -> Giữ nguyên mật khẩu cũ
                    $sql = "UPDATE nguoi_dung SET ho_va_ten=?, email=?, role=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$fullname, $email, $role, $edit_id]);
                }
                $message = "Đã cập nhật thông tin người dùng #$edit_id!";
            } else {
                // INSERT (Thêm mới)
                if (empty($password)) throw new Exception("Vui lòng nhập mật khẩu cho tài khoản mới!");
                
                $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO nguoi_dung (ho_va_ten, email, mat_khau, role) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$fullname, $email, $hashed_pass, $role]);
                $message = "Đã thêm người dùng mới thành công!";
            }
            $msgType = "success";

        } catch (Exception $e) {
            $message = "Lỗi: " . $e->getMessage();
            $msgType = "error";
        }
    }
}

// 3. LỌC & TÌM KIẾM & PHÂN TRANG
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Số user trên 1 trang
$offset = ($page - 1) * $limit;

// Xây dựng Query
$sqlWhere = "WHERE 1=1"; // Điều kiện mặc định luôn đúng
$params = [];

if (!empty($search_query)) {
    $sqlWhere .= " AND (ho_va_ten LIKE :search OR email LIKE :search)";
    $params[':search'] = "%$search_query%";
}

// Đếm tổng
$stmtCount = $conn->prepare("SELECT COUNT(*) FROM nguoi_dung $sqlWhere");
$stmtCount->execute($params);
$totalRows = $stmtCount->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Lấy danh sách
$sqlList = "SELECT * FROM nguoi_dung $sqlWhere ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmtList = $conn->prepare($sqlList);
$stmtList->execute($params);
$users = $stmtList->fetchAll(PDO::FETCH_ASSOC);

// Xử lý biến edit (khi bấm nút Sửa)
$editUser = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $stmtEdit = $conn->prepare("SELECT * FROM nguoi_dung WHERE id = ?");
    $stmtEdit->execute([$eid]);
    $editUser = $stmtEdit->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Người dùng - Admin</title>
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
            <a href="admin_questions.php" class="block px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <i class="fa-solid fa-database w-6"></i> Ngân hàng câu hỏi
            </a>
            <a href="admin_chapters.php" class="block px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <i class="fa-solid fa-book-open w-6"></i> Quản lý Chương
            </a>
            <a href="admin_users.php" class="block px-4 py-3 rounded-lg bg-indigo-600 text-white shadow-md">
                <i class="fa-solid fa-users w-6"></i> Quản lý Người dùng
            </a>
            <a href="admin.php" class="block px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition mt-8">
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
            
            <h1 class="text-2xl font-bold mb-6">Quản lý Người Dùng</h1>

            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $msgType == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-indigo-200 mb-8">
                <h2 class="font-bold text-lg mb-4 text-indigo-600 border-b pb-2 flex justify-between items-center">
                    <?php echo $editUser ? "✏️ Sửa User: " . htmlspecialchars($editUser['ho_va_ten']) : "➕ Thêm Người Dùng Mới"; ?>
                </h2>
                
                <form method="POST" action="admin_users.php?search=<?php echo urlencode($search_query); ?>&page=<?php echo $page; ?>">
                    <input type="hidden" name="action" value="save">
                    <?php if ($editUser): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $editUser['id']; ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-bold mb-1">Họ và Tên</label>
                            <input type="text" name="ho_va_ten" class="w-full border p-2 rounded focus:border-indigo-500 outline-none" 
                                   value="<?php echo $editUser['ho_va_ten'] ?? ''; ?>" required placeholder="Nguyễn Văn A">
                        </div>
                        
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-bold mb-1">Email (Tên đăng nhập)</label>
                            <input type="email" name="email" class="w-full border p-2 rounded focus:border-indigo-500 outline-none" 
                                   value="<?php echo $editUser['email'] ?? ''; ?>" required placeholder="email@example.com">
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-sm font-bold mb-1">Mật khẩu</label>
                            <input type="password" name="mat_khau" class="w-full border p-2 rounded focus:border-indigo-500 outline-none" 
                                   placeholder="<?php echo $editUser ? 'Để trống nếu không đổi pass' : 'Nhập mật khẩu...'; ?>">
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-sm font-bold mb-1">Vai trò (Role)</label>
                            <select name="role" class="w-full border p-2 rounded focus:border-indigo-500 outline-none bg-slate-50">
                                <option value="user" <?php echo ($editUser && $editUser['role'] == 'user') ? 'selected' : ''; ?>>Học viên (User)</option>
                                <option value="admin" <?php echo ($editUser && $editUser['role'] == 'admin') ? 'selected' : ''; ?>>Quản trị viên (Admin)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 font-bold shadow">
                            <?php echo $editUser ? "Lưu thay đổi" : "Thêm User"; ?>
                        </button>
                        <?php if($editUser): ?>
                            <a href="admin_users.php?search=<?php echo urlencode($search_query); ?>" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400 font-bold">Hủy</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <div class="text-sm text-gray-500">
                    Tổng: <b><?php echo $totalRows; ?></b> người dùng.
                </div>
                
                <form action="admin_users.php" method="GET" class="relative w-full md:w-80">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" 
                           placeholder="Tìm tên hoặc email..." 
                           class="w-full pl-10 pr-10 py-2 border border-slate-300 rounded-lg focus:border-indigo-500 outline-none">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400"></i>
                    <?php if(!empty($search_query)): ?>
                        <a href="admin_users.php" class="absolute right-3 top-2.5 text-slate-400 hover:text-red-500">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-100 text-slate-600 font-bold uppercase text-xs">
                        <tr>
                            <th class="p-4 w-16">ID</th>
                            <th class="p-4">Họ và Tên</th>
                            <th class="p-4">Email</th>
                            <th class="p-4 text-center">Vai trò</th>
                            <th class="p-4 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($users as $u): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-4 font-bold text-slate-400">#<?php echo $u['id']; ?></td>
                                <td class="p-4 font-semibold text-slate-800">
                                    <div class="flex items-center gap-3">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($u['ho_va_ten']); ?>&background=random&size=32" class="w-8 h-8 rounded-full">
                                        <?php echo htmlspecialchars($u['ho_va_ten']); ?>
                                        <?php if($u['id'] == $_SESSION['user_id']) echo '<span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded ml-2">(Bạn)</span>'; ?>
                                    </div>
                                </td>
                                <td class="p-4 text-slate-600"><?php echo htmlspecialchars($u['email']); ?></td>
                                <td class="p-4 text-center">
                                    <?php if($u['role'] == 'admin'): ?>
                                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold border border-red-200">Admin</span>
                                    <?php else: ?>
                                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold border border-green-200">User</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="admin_users.php?search=<?php echo urlencode($search_query); ?>&page=<?php echo $page; ?>&edit=<?php echo $u['id']; ?>" class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        
                                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" onsubmit="return confirm('CẢNH BÁO: Bạn có chắc chắn muốn xóa tài khoản <?php echo $u['ho_va_ten']; ?>? Hành động này không thể hoàn tác.');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded transition" title="Xóa">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="p-2 text-gray-300 cursor-not-allowed" title="Không thể xóa chính mình"><i class="fa-solid fa-ban"></i></span>
                                        <?php endif; ?>
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
                        $baseLink = "admin_users.php?search=" . urlencode($search_query) . "&page=";
                    ?>
                    <?php if ($page > 1): ?>
                        <a href="<?php echo $baseLink . ($page - 1); ?>" class="px-3 py-1 rounded bg-white border hover:bg-slate-100 text-slate-600"><i class="fa-solid fa-chevron-left"></i></a>
                    <?php endif; ?>

                    <?php for($p=1; $p<=$totalPages; $p++): ?>
                        <a href="<?php echo $baseLink . $p; ?>" class="px-3 py-1 rounded border font-bold transition <?php echo $page == $p ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-600 hover:bg-slate-100'; ?>">
                            <?php echo $p; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo $baseLink . ($page + 1); ?>" class="px-3 py-1 rounded bg-white border hover:bg-slate-100 text-slate-600"><i class="fa-solid fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>
</body>
</html>