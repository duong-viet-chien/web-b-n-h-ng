<?php
/**
 * FILE: admin/edit_news.php
 * MÔ TẢ: Trang chỉnh sửa bài viết đã có.
 */

require_once '../includes/config.php';
global $conn;

// Kiểm tra quyền truy cập admin
if (!is_logged_in() || !is_admin()) {
    set_message('Bạn không có quyền truy cập trang quản trị!', 'error');
    redirect('login.php');
}

// 1. Lấy ID bài viết từ URL
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id === 0) {
    set_message('Không tìm thấy ID bài viết cần chỉnh sửa.', 'error');
    redirect('news.php');
}

$article = null;
$errors = array();
$categories = ['Công nghệ', 'Tin tức', 'Khuyến mãi', 'Đánh giá']; // Danh mục giả định

// --- TRUY VẤN DỮ LIỆU CŨ ---
$stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_message('Bài viết không tồn tại.', 'error');
    redirect('news.php');
}

// Lưu dữ liệu cũ vào biến $article và các biến form
$article = $result->fetch_assoc();
$title = $article['title'];
$category = $article['category'];
$excerpt = $article['excerpt'];
$content = $article['content'];
$image_url = $article['image_url'];
$stmt->close();


// --- XỬ LÝ KHI FORM ĐƯỢC SUBMIT (CHỈNH SỬA) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Lấy và làm sạch dữ liệu mới
    $new_title = clean_input($_POST['title'] ?? '');
    $new_category = clean_input($_POST['category'] ?? '');
    $new_excerpt = clean_input($_POST['excerpt'] ?? '');
    $new_content = $_POST['content'] ?? '';
    $new_image_url = clean_input($_POST['image_url'] ?? '');

    // 3. Kiểm tra lỗi
    if (empty($new_title)) { $errors['title'] = 'Tiêu đề không được để trống.'; }
    if (empty($new_category)) { $errors['category'] = 'Vui lòng chọn danh mục.'; }
    if (empty($new_excerpt)) { $errors['excerpt'] = 'Tóm tắt không được để trống.'; }
    if (empty($new_content)) { $errors['content'] = 'Nội dung chi tiết không được để trống.'; }
    if (empty($new_image_url)) { $errors['image_url'] = 'URL ảnh không được để trống.'; }

    // 4. Nếu không có lỗi, tiến hành Cập nhật vào DB
    if (empty($errors)) {
        // Làm sạch nội dung chi tiết
        $safe_content = $conn->real_escape_string($new_content);
        
        $sql = "UPDATE news SET 
                title = ?, 
                category = ?, 
                excerpt = ?, 
                content = ?, 
                image_url = ?, 
                date_updated = NOW() 
                WHERE id = ?";
        
        $stmt_update = $conn->prepare($sql);
        $stmt_update->bind_param("sssssi", 
                                $new_title, 
                                $new_category, 
                                $new_excerpt, 
                                $safe_content, 
                                $new_image_url, 
                                $article_id);

        if ($stmt_update->execute()) {
            set_message('Chỉnh sửa bài viết thành công!', 'success');
            // Cập nhật lại các biến hiển thị trên form
            $title = $new_title;
            $category = $new_category;
            $excerpt = $new_excerpt;
            $content = $new_content;
            $image_url = $new_image_url;
            // Chuyển hướng để xóa POST data và hiển thị thông báo
            redirect("edit_news.php?id=$article_id"); 
        } else {
            set_message('Lỗi khi cập nhật bài viết: ' . $stmt_update->error, 'error');
        }
        $stmt_update->close();
    } else {
        set_message('Vui lòng kiểm tra lại các trường bị lỗi.', 'error');
        
        // Gán lại dữ liệu POST để giữ trên form sau khi báo lỗi
        $title = $new_title;
        $category = $new_category;
        $excerpt = $new_excerpt;
        $content = $new_content;
        $image_url = $new_image_url;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Bài viết - Admin</title>
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
        <?php include 'sidebar.php'; // Nhúng sidebar ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1>📝 Chỉnh sửa Bài viết: #<?php echo $article_id; ?></h1>
            </div>

            <?php
            $message = get_message();
            if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form action="edit_news.php?id=<?php echo $article_id; ?>" method="POST">
                    
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
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                        <a href="news.php" class="btn btn-secondary">← Quay lại danh sách</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>