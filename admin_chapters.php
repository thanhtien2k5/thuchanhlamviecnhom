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

// 2. XỬ LÝ FORM (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    try {
        // --- XỬ LÝ CHƯƠNG ---
        if ($action == 'save_chapter') {
            $ten = $_POST['ten_chuong'];
            $stt = (int)$_POST['so_thu_tu'];
            $noi_dung = $_POST['noi_dung'];
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : 0;

            if ($id > 0) {
                // Update
                $stmt = $conn->prepare("UPDATE chuong SET ten_chuong=?, so_thu_tu=?, noi_dung=? WHERE id=?");
                $stmt->execute([$ten, $stt, $noi_dung, $id]);
                $message = "Đã cập nhật chương thành công!";
            } else {
                // Insert
                $stmt = $conn->prepare("INSERT INTO chuong (ten_chuong, so_thu_tu, noi_dung) VALUES (?, ?, ?)");
                $stmt->execute([$ten, $stt, $noi_dung]);
                $message = "Đã thêm chương mới thành công!";
            }
            $msgType = "success";
        }
        elseif ($action == 'delete_chapter') {
            $id = (int)$_POST['id'];
            // Xóa bài học con trước (nếu không có cascade)
            $conn->exec("DELETE FROM bai_hoc WHERE chuong_id = $id");
            // Xóa chương
            $conn->exec("DELETE FROM chuong WHERE id = $id");
            $message = "Đã xóa chương và các bài học liên quan!";
            $msgType = "success";
        }

        // --- XỬ LÝ BÀI HỌC ---
        elseif ($action == 'save_lesson') {
            $cid = (int)$_POST['chuong_id'];
            $ten = $_POST['ten_bai'];
            $stt = (int)$_POST['thu_tu'];
            $video = $_POST['video_url'];
            $noi_dung = $_POST['noi_dung'];
            $lid = !empty($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : 0;

            if ($lid > 0) {
                // Update
                $sql = "UPDATE bai_hoc SET chuong_id=?, ten_bai=?, thu_tu=?, video_url=?, noi_dung=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$cid, $ten, $stt, $video, $noi_dung, $lid]);
                $message = "Đã cập nhật bài học thành công!";
            } else {
                // Insert
                $sql = "INSERT INTO bai_hoc (chuong_id, ten_bai, thu_tu, video_url, noi_dung, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$cid, $ten, $stt, $video, $noi_dung]);
                $message = "Đã thêm bài học mới!";
            }
            $msgType = "success";
        }
        elseif ($action == 'delete_lesson') {
            $id = (int)$_POST['id'];
            $conn->exec("DELETE FROM bai_hoc WHERE id = $id");
            $message = "Đã xóa bài học thành công!";
            $msgType = "success";
        }

    } catch (Exception $e) {
        $message = "Lỗi: " . $e->getMessage();
        $msgType = "error";
    }
}

// 3. LẤY DỮ LIỆU ĐỂ HIỂN THỊ
// Lấy danh sách chương
$chapters = $conn->query("SELECT * FROM chuong ORDER BY so_thu_tu ASC")->fetchAll(PDO::FETCH_ASSOC);

// Xử lý chế độ Sửa (Edit Mode)
$editChapter = null;
$editLesson = null;
$view = isset($_GET['view']) ? $_GET['view'] : 'list'; // 'list', 'edit_chapter', 'edit_lesson'

if ($view == 'edit_chapter' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM chuong WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editChapter = $stmt->fetch(PDO::FETCH_ASSOC);
}
if ($view == 'edit_lesson' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM bai_hoc WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editLesson = $stmt->fetch(PDO::FETCH_ASSOC);
}
// Nếu bấm "Thêm bài học" cho 1 chương cụ thể
$addLessonForChapter = isset($_GET['add_lesson_for']) ? (int)$_GET['add_lesson_for'] : 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Chương & Bài học - Admin</title>
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
            <a href="admin_chapters.php" class="block px-4 py-3 rounded-lg bg-blue-600 text-white shadow-md">
                <i class="fa-solid fa-book-open w-6"></i> Quản lý Chương
            </a>
             <a href="admin_users.php" class="block px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
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
            
            <h1 class="text-2xl font-bold mb-6">Quản lý Chương & Bài Học</h1>

            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $msgType == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($view == 'edit_chapter' || $view == 'add_chapter'): ?>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-blue-200 mb-8">
                    <h2 class="font-bold text-lg mb-4 text-blue-600 border-b pb-2">
                        <?php echo $editChapter ? "✏️ Sửa Chương" : "➕ Thêm Chương Mới"; ?>
                    </h2>
                    <form method="POST" action="admin_chapters.php">
                        <input type="hidden" name="action" value="save_chapter">
                        <?php if($editChapter): ?><input type="hidden" name="id" value="<?php echo $editChapter['id']; ?>"><?php endif; ?>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-bold mb-1">Số thứ tự</label>
                                <input type="number" name="so_thu_tu" class="w-full border p-2 rounded" value="<?php echo $editChapter['so_thu_tu'] ?? ''; ?>" required>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-bold mb-1">Tên chương</label>
                                <input type="text" name="ten_chuong" class="w-full border p-2 rounded" value="<?php echo $editChapter['ten_chuong'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold mb-1">Mô tả / Nội dung chương</label>
                            <textarea name="noi_dung" rows="3" class="w-full border p-2 rounded"><?php echo $editChapter['noi_dung'] ?? ''; ?></textarea>
                        </div>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded font-bold hover:bg-blue-700">Lưu Chương</button>
                        <a href="admin_chapters.php" class="ml-2 text-gray-500 hover:underline">Hủy</a>
                    </form>
                </div>

            <?php elseif ($view == 'edit_lesson' || $addLessonForChapter > 0): ?>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-green-200 mb-8">
                    <h2 class="font-bold text-lg mb-4 text-green-600 border-b pb-2">
                        <?php echo $editLesson ? "✏️ Sửa Bài Học" : "➕ Thêm Bài Học Mới"; ?>
                    </h2>
                    <form method="POST" action="admin_chapters.php">
                        <input type="hidden" name="action" value="save_lesson">
                        <?php if($editLesson): ?><input type="hidden" name="lesson_id" value="<?php echo $editLesson['id']; ?>"><?php endif; ?>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-bold mb-1">Thuộc Chương</label>
                                <select name="chuong_id" class="w-full border p-2 rounded bg-gray-50">
                                    <?php foreach($chapters as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" 
                                            <?php echo ($editLesson && $editLesson['chuong_id'] == $c['id']) || ($addLessonForChapter == $c['id']) ? 'selected' : ''; ?>>
                                            Chương <?php echo $c['so_thu_tu']; ?>: <?php echo $c['ten_chuong']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-1">Thứ tự bài</label>
                                <input type="number" name="thu_tu" class="w-full border p-2 rounded" value="<?php echo $editLesson['thu_tu'] ?? ''; ?>" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-1">Video URL (Youtube Embed)</label>
                                <input type="text" name="video_url" class="w-full border p-2 rounded" placeholder="https://www.youtube.com/embed/..." value="<?php echo $editLesson['video_url'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold mb-1">Tên bài học</label>
                            <input type="text" name="ten_bai" class="w-full border p-2 rounded font-bold" value="<?php echo $editLesson['ten_bai'] ?? ''; ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold mb-1">Nội dung bài học (HTML)</label>
                            <textarea name="noi_dung" rows="5" class="w-full border p-2 rounded font-mono text-sm"><?php echo $editLesson['noi_dung'] ?? ''; ?></textarea>
                        </div>
                        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded font-bold hover:bg-green-700">Lưu Bài Học</button>
                        <a href="admin_chapters.php" class="ml-2 text-gray-500 hover:underline">Hủy</a>
                    </form>
                </div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-slate-700">Danh sách Chương trình học</h2>
                <a href="admin_chapters.php?view=add_chapter" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-700 shadow flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Thêm Chương
                </a>
            </div>

            <div class="space-y-6">
                <?php foreach($chapters as $ch): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-100 p-4 flex justify-between items-center border-b border-slate-200">
                            <div class="flex items-center gap-3">
                                <span class="bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full">Chương <?php echo $ch['so_thu_tu']; ?></span>
                                <h3 class="font-bold text-lg text-slate-800"><?php echo htmlspecialchars($ch['ten_chuong']); ?></h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="admin_chapters.php?view=edit_chapter&id=<?php echo $ch['id']; ?>" class="text-blue-600 hover:bg-blue-100 p-2 rounded" title="Sửa Chương">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form method="POST" onsubmit="return confirm('Xóa chương này sẽ xóa hết các bài học bên trong. Bạn chắc chắn chứ?');" class="inline">
                                    <input type="hidden" name="action" value="delete_chapter">
                                    <input type="hidden" name="id" value="<?php echo $ch['id']; ?>">
                                    <button type="submit" class="text-red-600 hover:bg-red-100 p-2 rounded" title="Xóa Chương"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </div>

                        <div class="p-4 bg-white">
                            <?php 
                                // Lấy bài học của chương này
                                $stmtL = $conn->prepare("SELECT * FROM bai_hoc WHERE chuong_id = ? ORDER BY thu_tu ASC");
                                $stmtL->execute([$ch['id']]);
                                $lessons = $stmtL->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            
                            <?php if(count($lessons) > 0): ?>
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-slate-500 uppercase bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-2 w-16">STT</th>
                                            <th class="px-4 py-2">Tên bài học</th>
                                            <th class="px-4 py-2 w-32">Video</th>
                                            <th class="px-4 py-2 text-right">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php foreach($lessons as $l): ?>
                                            <tr class="hover:bg-slate-50">
                                                <td class="px-4 py-3 font-bold text-slate-400"><?php echo $l['thu_tu']; ?></td>
                                                <td class="px-4 py-3 font-medium text-slate-700"><?php echo htmlspecialchars($l['ten_bai']); ?></td>
                                                <td class="px-4 py-3">
                                                    <?php echo !empty($l['video_url']) ? '<span class="text-green-600"><i class="fa-solid fa-video"></i> Có</span>' : '<span class="text-slate-400">Không</span>'; ?>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <a href="admin_chapters.php?view=edit_lesson&id=<?php echo $l['id']; ?>" class="text-blue-600 hover:underline mr-3">Sửa</a>
                                                    <form method="POST" onsubmit="return confirm('Xóa bài học này?');" class="inline">
                                                        <input type="hidden" name="action" value="delete_lesson">
                                                        <input type="hidden" name="id" value="<?php echo $l['id']; ?>">
                                                        <button type="submit" class="text-red-600 hover:underline">Xóa</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-slate-400 text-sm italic py-2">Chưa có bài học nào trong chương này.</div>
                            <?php endif; ?>

                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <a href="admin_chapters.php?add_lesson_for=<?php echo $ch['id']; ?>" class="text-sm font-bold text-green-600 hover:text-green-800 flex items-center gap-1 w-fit">
                                    <i class="fa-solid fa-plus-circle"></i> Thêm bài học vào Chương <?php echo $ch['so_thu_tu']; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </main>
</body>
</html>
