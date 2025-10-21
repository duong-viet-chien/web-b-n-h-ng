<?php
/**
 * FILE: admin/add_news.php
 * MÔ TẢ: Trang thêm bài viết mới.
 */

require_once '../includes/config.php';
global $conn; // Sử dụng biến kết nối đã có

// Kiểm tra quyền truy cập admin
if (!is_logged_in() || !is_admin()) {
    set_message('Bạn không có quyền truy cập trang quản trị!', 'error');
    redirect('login.php');
}

// Khởi tạo các biến
$title = $category = $excerpt = $content = $image_url = $author = '';
$errors = array();

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Lấy và làm sạch dữ liệu
    $title = clean_input($_POST['title'] ?? '');
    $category = clean_input($_POST['category'] ?? '');
    $excerpt = clean_input($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? ''; // Giữ nguyên nội dung để xử lý sau (đã có real_escape_string trong clean_input cho title/excerpt)
    $image_url = clean_input($_POST['image_url'] ?? '');
    $author = clean_input($_SESSION['full_name'] ?? 'Admin'); // Lấy tên người dùng đang đăng nhập

    // 2. Kiểm tra lỗi
    if (empty($title)) {
        $errors['title'] = 'Tiêu đề không được để trống.';
    }
    if (empty($category)) {
        $errors['category'] = 'Vui lòng chọn danh mục.';
    }
    if (empty($excerpt)) {
        $errors['excerpt'] = 'Tóm tắt không được để trống.';
    }
    if (empty($content)) {
        $errors['content'] = 'Nội dung chi tiết không được để trống.';
    }
    if (empty($image_url)) {
        $errors['image_url'] = 'URL ảnh không được để trống.';
    }

    // 3. Nếu không có lỗi, tiến hành lưu vào DB
    if (empty($errors)) {
        // Làm sạch nội dung chi tiết bằng real_escape_string thủ công vì nó là nội dung lớn
        $safe_content = $conn->real_escape_string($content);
        
        $sql = "INSERT INTO news (title, category, author, excerpt, content, image_url, date_posted) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        // Sử dụng Prepared Statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $title, $category, $author, $excerpt, $safe_content, $image_url);

        if ($stmt->execute()) {
            set_message('Thêm bài viết mới thành công!', 'success');
            redirect('news.php'); // Chuyển hướng sang trang quản lý bài viết
        } else {
            set_message('Lỗi khi thêm bài viết: ' . $stmt->error, 'error');
        }
        $stmt->close();
    } else {
        set_message('Vui lòng kiểm tra lại các trường bị lỗi.', 'error');
    }
}

// Danh sách danh mục giả định (bạn nên lấy từ bảng categories nếu có)
$categories = ['Công nghệ', 'Tin tức', 'Khuyến mãi', 'Đánh giá'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Bài viết Mới - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/anmin.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#content',
            plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            height: 400
        });
    </script>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; // Giả sử bạn có file sidebar.php ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1>✍️ Thêm Bài viết Mới</h1>
            </div>

            <?php
            $message = get_message();
            if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form action="add_news.php" method="POST">
                    
                    <div class="form-group">
                        <label for="title">Tiêu đề Bài viết <span class="required">*</span></label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                        <?php if (isset($errors['title'])): ?><span class="error-message"><?php echo $errors['title']; ?></span><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="category">Danh mục <span class="required">*</span></label>
                        <select id="category" name="category" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['category'])): ?><span class="error-message"><?php echo $errors['category']; ?></span><?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="excerpt">Tóm tắt / Giới thiệu ngắn <span class="required">*</span></label>
                        <textarea id="excerpt" name="excerpt" rows="3" required><?php echo htmlspecialchars($excerpt); ?></textarea>
                        <?php if (isset($errors['excerpt'])): ?><span class="error-message"><?php echo $errors['excerpt']; ?></span><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="content">Nội dung chi tiết <span class="required">*</span></label>
                        <textarea id="content" name="content" rows="10"><?php echo htmlspecialchars($content); ?></textarea>
                        <?php if (isset($errors['content'])): ?><span class="error-message"><?php echo $errors['content']; ?></span><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="image_url">URL Ảnh đại diện <span class="required">*</span></label>
                        <input type="url" id="image_url" name="image_url" value="<?php echo htmlspecialchars($image_url); ?>" required>
                        <?php if (isset($errors['image_url'])): ?><span class="error-message"><?php echo $errors['image_url']; ?></span><?php endif; ?>
                        <small class="form-text text-muted">Dán liên kết ảnh từ ngoài vào (VD: https://...).</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Thêm Bài viết</button>
                        <a href="news.php" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    </body>
</html>