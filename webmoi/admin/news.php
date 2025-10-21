<?php
/**
 * FILE: admin/news.php
 * MÔ TẢ: Trang quản lý danh sách bài viết (list, delete).
 */

require_once '../includes/config.php';
global $conn;

// Kiểm tra quyền truy cập admin
if (!is_logged_in() || !is_admin()) {
    set_message('Bạn không có quyền truy cập trang quản trị!', 'error');
    redirect('login.php');
}

// --- XỬ LÝ XÓA BÀI VIẾT ---
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    // Sử dụng Prepared Statement để xóa an toàn
    $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        set_message('Xóa bài viết thành công!', 'success');
    } else {
        set_message('Lỗi khi xóa bài viết: ' . $stmt->error, 'error');
    }
    $stmt->close();
    redirect('news.php'); // Chuyển hướng để xóa tham số delete_id trên URL
}

// --- TRUY VẤN DANH SÁCH BÀI VIẾT ---
$sql = "SELECT id, title, category, author, date_posted, views, image_url 
        FROM news 
        ORDER BY date_posted DESC";
$result = $conn->query($sql);
$articles = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Bài viết - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/anmin.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        /* CSS cho nút Xóa */
        .btn-delete { background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; // Giả sử bạn có file sidebar.php ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1>📰 Quản lý Bài viết (<?php echo count($articles); ?>)</h1>
                <a href="add_news.php" class="btn btn-primary">➕ Thêm Bài viết Mới</a>
            </div>

            <?php
            $message = get_message();
            if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>

            <div class="data-table">
                <?php if (!empty($articles)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ảnh</th>
                                <th>Tiêu đề & Danh mục</th>
                                <th>Tác giả</th>
                                <th>Ngày đăng</th>
                                <th>Lượt xem</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($articles as $article): ?>
                                <tr>
                                    <td><?php echo $article['id']; ?></td>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="Ảnh bài viết" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    </td>
                                    <td style="max-width: 350px;">
                                        <strong><?php echo htmlspecialchars($article['title']); ?></strong>
                                        <div class="badge badge-info" style="font-size: 0.75rem; display: block; margin-top: 5px;"><?php echo htmlspecialchars($article['category']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($article['author']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($article['date_posted'])); ?></td>
                                    <td><?php echo number_format($article['views']); ?></td>
                                    <td style="width: 150px;">
                                        <a href="edit_news.php?id=<?php echo $article['id']; ?>" class="btn btn-info" style="padding: 5px 10px; font-size: 0.85rem;">Sửa</a>
                                        <button class="btn-delete" onclick="confirmDelete(<?php echo $article['id']; ?>, '<?php echo htmlspecialchars($article['title']); ?>')">Xóa</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; padding: 40px; color: #999;">Chưa có bài viết nào được đăng. Hãy thêm bài viết mới!</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function confirmDelete(id, title) {
            if (confirm(`Bạn có chắc chắn muốn xóa bài viết:\n"${title}" (ID: ${id})?\n\nHành động này không thể hoàn tác.`)) {
                window.location.href = `news.php?delete_id=${id}`;
            }
        }
    </script>
</body>
</html>