<?php
/**
 * FILE: news.php (DYNAMIC VERSION)
 * MÔ TẢ: Trang tin tức và blog lấy dữ liệu động từ MySQL.
 * Yêu cầu: Đã cấu hình includes/config.php và bảng 'news' trong database.
 * ĐÃ SỬA: Chuyển bố cục sang 3 cột tin tức và đưa sidebar xuống cuối trang.
 */

require_once 'includes/config.php'; 
global $conn; // Đã sửa lỗi: Sử dụng biến $conn toàn cục

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 6;

// 1. Tính tổng số trang (cho phân trang)
$count_result = $conn->query("SELECT COUNT(*) AS total FROM news");
$total_rows = $count_result->fetch_assoc()['total'] ?? 0;
$total_pages = ceil($total_rows / $per_page);

// Đảm bảo trang hiện tại không vượt quá tổng số trang
if ($page < 1) $page = 1;
if ($page > $total_pages) {
    $page = $total_pages > 0 ? $total_pages : 1;
}

$offset = ($page - 1) * $per_page;

// 2. Lấy ID bài viết nổi bật (Mới nhất và nhiều views nhất)
$featured_id = 0;
$featured_sql = "SELECT id FROM news ORDER BY date_posted DESC, views DESC LIMIT 1";
$featured_id_result = $conn->query($featured_sql);

if ($featured_id_result && $featured_id_result->num_rows > 0) {
    $featured_id = $featured_id_result->fetch_assoc()['id'];
}

// 3. Lấy dữ liệu bài viết cho trang hiện tại (LOẠI BỎ bài nổi bật)
$sql = "SELECT id, title, category, date_posted, author, views, excerpt, image_url 
           FROM news 
           WHERE id != ? 
           ORDER BY date_posted DESC 
           LIMIT ? OFFSET ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $featured_id, $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

$news_articles = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $news_articles[] = $row;
    }
}
$stmt->close();


// 4. Lấy dữ liệu chi tiết bài viết nổi bật
$featured_post = null;
if ($featured_id > 0) {
    $featured_sql = "SELECT id, title, excerpt, date_posted, author, views, image_url 
                      FROM news 
                      WHERE id = ?"; 
    $stmt_featured = $conn->prepare($featured_sql);
    $stmt_featured->bind_param("i", $featured_id);
    $stmt_featured->execute();
    $featured_result = $stmt_featured->get_result();

    if ($featured_result && $featured_result->num_rows > 0) {
        $featured_post = $featured_result->fetch_assoc();
    }
    $stmt_featured->close();
}
// --- END LOGIC ---
?>
<?php include 'includes/Header.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin tức - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/news.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- BREADCRUMB -->
    <div class="breadcrumb">
        <div class="container">
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li>Tin tức</li>
            </ul>
        </div>
    </div>

    <!-- NEWS SECTION -->
    <section class="news-section">
        <div class="container">
            <h1 class="section-title"> Tin tức & Bài viết</h1>

            <!-- FEATURED POST -->
            <?php if ($featured_post): ?>
            <div class="featured-post">
                <div class="featured-post-wrap"> 
                    <div class="featured-post-text">
                        <span class="badge badge-success featured-badge">🌟 Bài viết nổi bật</span>
                        <h2><?php echo htmlspecialchars($featured_post['title']); ?></h2>
                        <p class="featured-excerpt"><?php echo htmlspecialchars($featured_post['excerpt']); ?></p>
                        <div class="post-meta-group"> 
                            <div class="meta-item">
                                <div class="meta-label">Đăng bởi</div>
                                <div class="meta-value"><?php echo htmlspecialchars($featured_post['author']); ?></div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Ngày đăng</div>
                                <div class="meta-value"><?php echo date('d/m/Y', strtotime($featured_post['date_posted'])); ?></div> 
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Lượt xem</div>
                                <div class="meta-value"><?php echo number_format($featured_post['views']); ?></div>
                            </div>
                        </div>
                        <a href="article.php?id=<?php echo $featured_post['id']; ?>" class="btn btn-primary featured-btn">Đọc tiếp →</a>
                    </div>
                    <div class="featured-post-image featured-post-right">
                        <img src="<?php echo htmlspecialchars($featured_post['image_url']); ?>" alt="<?php echo htmlspecialchars($featured_post['title']); ?>" class="lazy-load">
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- NEWS GRID -->
           <div class="news-main-wrap-new">
                
                <div class="news-content-area">
                    
                    <div class="news-grid">
                        <?php if (count($news_articles) > 0): ?>
                            <?php foreach ($news_articles as $article): ?>
                                <div class="news-card">
                                    <div class="article-image-wrap">
                                        <img src="<?php echo htmlspecialchars($article['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($article['title']); ?>" 
                                             class="article-image lazy-load">
                                    </div>

                                    <div class="article-content">
                                        <div class="article-badges">
                                            <span class="badge badge-primary"><?php echo htmlspecialchars($article['category']); ?></span>
                                        </div>
                                        <h3 class="article-title">
                                            <a href="article.php?id=<?php echo $article['id']; ?>"><?php echo htmlspecialchars($article['title']); ?></a>
                                        </h3>
                                        <p class="article-excerpt">
                                            <?php echo htmlspecialchars($article['excerpt']); ?>
                                        </p>
                                        <div class="article-footer">
                                            <div class="article-meta-small">
                                                <span>👤 <?php echo htmlspecialchars($article['author']); ?></span>
                                                <span>📅 <?php echo date('d/m/Y', strtotime($article['date_posted'])); ?></span>
                                                <span>👁️ <?php echo number_format($article['views']); ?></span>
                                            </div>
                                            <a href="article.php?id=<?php echo $article['id']; ?>" class="btn btn-secondary news-detail-btn">Chi tiết →</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="grid-column: 1 / -1; text-align: center; color: #999;">Không tìm thấy bài viết nào khác.</p>
                        <?php endif; ?>
                    </div>

                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="news.php?page=<?php echo $page - 1; ?>" class="page-prev">← Trước</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="news.php?page=<?php echo $i; ?>" class="page-number <?php echo $page == $i ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="news.php?page=<?php echo $page + 1; ?>" class="page-next">Sau →</a>
                        <?php endif; ?>
                    </div>
                </div> </div> </div> </section>
            

            <!-- SIDEBAR -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px; margin-top: 60px;">
                <!-- EMPTY SPACE FOR MAIN CONTENT -->
                <div></div>
                
                <!-- SIDEBAR CONTENT -->
                <div>
                    <!-- DANH MỤC BÀI VIẾT -->
                    <div style="background: #fff; padding: 25px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                        <h3 style="margin-bottom: 20px;">📂 Danh mục</h3>
                        <!-- <ul style="list-style: none;"> -->
                            <ul class="category-list">
                            <li><a href="#"><span>📱 Smartphone</span> <span class="count">(24)</span></a></li>
                            <li><a href="#"><span>💻 Laptop</span> <span class="count">(18)</span></a></li>
                            <li><a href="#"><span>🎧 Phụ kiện</span> <span class="count">(15)</span></a></li>
                            <li><a href="#"><span>🎮 Gaming</span> <span class="count">(12)</span></a></li>
                            <li><a href="#"><span>💡 Mẹo & Thủ thuật</span> <span class="count">(31)</span></a></li>
                        </ul>
                    </div>

                    <!-- BÀI VIẾT ĐƯỢC ĐỌC NHIỀU NHẤT -->
                    <div style="background: #fff; padding: 25px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                              <h3 style="margin-bottom: 20px;">🔥 Được đọc nhiều</h3>
                                <div style="display: flex; flex-direction: column; gap: 20px;"></div>
                        <div class="trending-list">
                            <a href="#" class="trending-item">
                                <div class="trending-image">
                                    <img src="https://via.placeholder.com/60x60/667eea/ffffff?text=Trend1" alt="iPhone" class="lazy-load">
                                </div>
                                <div class="trending-info">
                                    <div class="trending-title">Bài viết xu hướng 1</div>
                                    <div class="trending-views">5.2K lượt xem</div>
                                </div>
                            </a>
                            <a href="#" class="trending-item">
                                <div class="trending-image">
                                    <img src="https://via.placeholder.com/60x60/FF6B6B/ffffff?text=Trend2" alt="MacBook" class="lazy-load">
                                </div>
                                <div class="trending-info">
                                    <div class="trending-title">Bài viết xu hướng 2</div>
                                    <div class="trending-views">3.8K lượt xem</div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- NEWSLETTER -->
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 25px; border-radius: 15px; text-align: center;">
                               <h3 style="margin-bottom: 15px;">📧 Theo dõi tin tức</h3>
                                <p>Đăng ký nhận tin tức mới nhất hàng tuần</p>
                                <form class="newsletter-form">
                                    <input type="email" placeholder="Email của bạn" required>
                                    <button type="submit" class="btn btn-newsletter has-ripple">Đăng ký</button>
                                </form>
                            </div>
                        </div>
                    </div>
    </section>    
     <script>
    // ... (Phần script giữ nguyên như code gốc của bạn) ...
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. HIỆU ỨNG ZOOM & ROTATE KHI HOVER
        const hoverEffects = [
            { selector: '.featured-post-image img', scale: '1.08', rotate: '1deg', filter: 'none' },
            { selector: '.article-image', scale: '1.15', rotate: '2deg', filter: 'brightness(0.9) saturate(1.2)' },
            { selector: '.trending-image img', scale: '1.2', rotate: '3deg', filter: 'brightness(1.1) saturate(1.3)' }
        ];

        hoverEffects.forEach(effect => {
            document.querySelectorAll(effect.selector).forEach(img => {
                img.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), filter 0.3s ease';

                img.addEventListener('mouseenter', function() {
                    this.style.transform = `scale(${effect.scale}) rotate(${effect.rotate})`;
                    this.style.filter = effect.filter;
                });
                img.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1) rotate(0deg)';
                    this.style.filter = 'none';
                });
            });
        });
        
        // 2. HIỆU ỨNG RIPPLE KHI CLICK BUTTON
        document.querySelectorAll('.btn').forEach(button => {
            button.style.position = 'relative'; 
            button.style.overflow = 'hidden';
            
            button.addEventListener('click', function(e) {
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                const ripple = document.createElement('span');
                ripple.className = 'ripple-effect';
                ripple.style.width = size + 'px';
                ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                
                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            });
        });
        
        // 3. LAZY LOADING IMAGES & FADE-IN
        function lazyLoadImages() {
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.classList.add('lazy-loaded'); 
                            observer.unobserve(img);
                        }
                    });
                }, { rootMargin: '0px 0px -50px 0px' }); 

                document.querySelectorAll('img.lazy-load').forEach(img => observer.observe(img));
            } else { 
                document.querySelectorAll('img.lazy-load').forEach(img => img.classList.add('lazy-loaded')); 
            }
        }
        lazyLoadImages();
    });

    // 4. THÊM ANIMATIONS CSS VÀO HEAD
    const style = document.createElement('style');
    style.textContent = `
        .ripple-effect {
            position: absolute;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s ease-out;
            pointer-events: none;
        }
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* Hiệu ứng Fade-in cho ảnh đã load */
        .lazy-loaded {
            opacity: 0;
            animation: fadeInImage 0.5s ease forwards;
        }
        @keyframes fadeInImage {
            from { opacity: 0; filter: blur(5px); }
            to { opacity: 1; filter: blur(0); }
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
<?php include 'includes/Footer.php'; ?>
</html>