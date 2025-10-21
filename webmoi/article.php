<?php
/**
 * FILE: article.php
 * MÔ TẢ: Trang hiển thị nội dung chi tiết của một bài viết.
 */

require_once 'includes/config.php';
global $conn; // Sử dụng biến kết nối đã có

// 1. Lấy ID bài viết từ URL
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$article = null;

if ($article_id > 0) {
    // 2. Truy vấn dữ liệu chi tiết bài viết
    $sql = "SELECT id, title, category, author, date_posted, views, image_url, content 
            FROM news 
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $article = $result->fetch_assoc();
        
        // 3. Tăng lượt xem (views) ngay sau khi load bài viết
        $update_views_sql = "UPDATE news SET views = views + 1 WHERE id = ?";
        $stmt_update = $conn->prepare($update_views_sql);
        $stmt_update->bind_param("i", $article_id);
        $stmt_update->execute();
        $stmt_update->close();
    }
    $stmt->close();
}

// Nếu không tìm thấy bài viết, có thể chuyển hướng về trang tin tức
if (!$article) {
    header("Location: news.php");
    exit();
}

// Chuyển đổi nội dung (content) thành HTML an toàn
$article_content = nl2br(htmlspecialchars($article['content'])); // nl2br giúp xuống dòng được hiển thị
?>

<?php include 'includes/Header.php'?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> | <?php echo SITE_NAME; ?></title> 
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        /* CSS Cơ bản cho trang chi tiết */
        .article-container { max-width: 900px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .article-header h1 { font-size: 36px; color: #333; margin-bottom: 10px; line-height: 1.3; }
        .article-meta { color: #888; font-size: 14px; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .article-meta span { margin-right: 15px; }
        .article-image { width: 100%; height: auto; border-radius: 8px; margin-bottom: 25px; object-fit: cover; }
        .article-content p { font-size: 17px; line-height: 1.7; color: #333; margin-bottom: 20px; }
        .article-content strong { font-weight: 700; }
        .content-wrap { padding: 0 15px; }
    </style>
</head>
<body>
    <div class="breadcrumb">
        <div class="container">
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="news.php">Tin tức</a></li>
                <li><?php echo htmlspecialchars($article['title']); ?></li>
            </ul>
        </div>
    </div>

    <section class="article-section">
        <div class="container">
            <div class="article-container">
                <div class="article-header">
                    <span class="badge badge-primary"><?php echo htmlspecialchars($article['category']); ?></span>
                    <h1><?php echo htmlspecialchars($article['title']); ?></h1>
                </div>

                <div class="article-meta">
                    <span>👤 Tác giả: <?php echo htmlspecialchars($article['author']); ?></span>
                    <span>📅 Ngày đăng: <?php echo date('d/m/Y', strtotime($article['date_posted'])); ?></span>
                    <span>👁️ Lượt xem: <?php echo number_format($article['views'] + 1); // +1 vì ta vừa tăng views ?></span>
                </div>

                <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="article-image">

                <div class="article-content content-wrap">
                    <?php 
                        // Hiển thị nội dung, sử dụng nl2br để giữ định dạng xuống dòng đơn giản.
                        echo $article_content; 
                    ?>
                    
                    <?php if (empty(trim($article['content']))): ?>
                        <p style="font-style: italic; color: #555;">(Lưu ý: Nội dung chi tiết cho bài viết này đang bị trống trong Database. Đây là nội dung giả định:)</p>
                        <p>Đây là phần giới thiệu chi tiết về chủ đề: **<?php echo htmlspecialchars($article['title']); ?>**. Bài viết này đi sâu vào các khía cạnh kỹ thuật và ứng dụng thực tế. Hy vọng thông tin này sẽ giúp bạn có cái nhìn toàn diện nhất.</p>
                        <p>Cảm ơn bạn đã theo dõi bài viết này. Hãy tiếp tục khám phá các nội dung khác trên trang web của chúng tôi!</p>
                    <?php endif; ?>
                </div>

                <div style="margin-top: 30px; text-align: center;">
                    <a href="news.php" class="btn btn-secondary">← Trở về trang Tin tức</a>
                </div>
            </div>
        </div>
    </section>

</body>
<?php include 'includes/footer.php'?>
</html>