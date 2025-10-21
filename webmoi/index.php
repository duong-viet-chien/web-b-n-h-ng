<?php
/**
 * FILE: index.php
 * MÔ TẢ: Trang chủ với giao diện chuyên nghiệp Euro Gear Style
 */

require_once 'includes/config.php';



// Lấy danh mục sản phẩm
$categories_sql = "SELECT * FROM categories ";
$categories = $conn->query($categories_sql);

// Lấy sản phẩm mới nhất
$new_products_sql = "SELECT p.*, c.name as category_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     ORDER BY p.created_at DESC 
                     LIMIT 8";
$new_products = $conn->query($new_products_sql);

// Lấy sản phẩm bán chạy (giả lập - lấy random)
$featured_sql = "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.stock > 0
                 ORDER BY RAND() 
                 LIMIT 4";
$featured_products = $conn->query($featured_sql);
?>
<?php include 'includes/Header.php'; ?>

    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
        
            <div class="swiper-slide hero-slide" style="background-image: url('images/sliders/slider_4.png');">
    <div class="container">
        <div class="hero-content">
            <h2 class="animated slide-in-left" style="--delay: 0.3s;">Siêu Sale</h2> 
            
            <p class="animated slide-in-left" style="--delay: 0.6s;">Giảm giá chớp nhoáng - Mua ngay kẻo lỡ!</p>
            
            <div class="hero-buttons animated slide-in-left" style="--delay: 0.9s;"> 
                <a href="products.php" class="btn-primary">Mua Ngay</a>
                <a href="#" class="btn-outline">Xem Thêm</a>
            </div>
        </div>
    </div>
</div>
            
            <div class="swiper-slide hero-slide" style="background-image: url('images/sliders/slider_7.png');">
                <div class="container">
                    <div class="hero-content">
                        <h2>Freeship Max</h2>
                        <p>Miễn phí vận chuyển toàn quốc</p>
                        <div class="hero-buttons">
                            <a href="products.php" class="btn-primary">Mua Ngay</a>
                            <a href="#" class="btn-outline">Xem Thêm</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="swiper-slide hero-slide" style="background-image: url('images/sliders/slider_8.png');">
                <div class="container">
                    <div class="hero-content">
                        <h2>Sản Phẩm Mới</h2>
                        <p>Cập nhật xu hướng công nghệ 2025</p>
                        <div class="hero-buttons">
                            <a href="products.php" class="btn-primary">Mua Ngay</a>
                            <a href="#" class="btn-outline">Xem Thêm</a>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
        <div class="swiper-pagination"></div>
    </div> 

    <section class="category-section">
        <div class="container category-slider-container">
            <h2 class="section-title">Danh mục sản phẩm</h2>
            <button class="slider-btn prev" onclick="slideCategories(-250)">&#10094;</button>
            <button class="slider-btn next" onclick="slideCategories(250)">&#10095;</button>
            <div class="category-slider">
                <?php 
                $categories->data_seek(0);
                $project_root = '/webmoi/';     
                $i = 0;
                while ($cat = $categories->fetch_assoc()): 
                    $image_url = $project_root . htmlspecialchars($cat['image_path']); 
                ?>
                    <a href="products.php?category=<?php echo $cat['id']; ?>" class="category-card category-item">
                        <div class="category-image-wrapper">
                            <img 
                                src="<?php echo $image_url; ?>" 
                                alt="<?php echo htmlspecialchars($cat['name']); ?> ảnh đại diện"
                                class="category-thumbnail" 
                            >
                        </div> 
                        
                        <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                        
                        <p><?php echo htmlspecialchars($cat['description']); ?></p>
                    </a>
                <?php 
                    $i++;
                endwhile; 
                ?>
            </div>
        </div>
    </section>
 

    <section class="products-section" style="background: var(--light-color);">
        <div class="container featured-slider-container"> 
            <h2 class="section-title">Sản phẩm nổi bật</h2>
            
            <button class="slider-btn featured-prev" onclick="slideProducts(-300)">&#10094;</button>
            <button class="slider-btn featured-next" onclick="slideProducts(300)">&#10095;</button>
            
            <?php if ($featured_products && $featured_products->num_rows > 0): ?>
                <div class="product-slider-horizontal"> 
                    <?php while ($product = $featured_products->fetch_assoc()): ?>
                        <div class="product-card featured-product-card"> 
                            <?php if ($product['stock'] < 10 && $product['stock'] > 0): ?>
                                <span class="product-badge sale">🔥 Sắp hết</span>
                            <?php endif; ?>
                            
                            <div class="product-quick-actions">
                                <button class="quick-action-btn" title="Yêu thích">❤️</button>
                                <button class="quick-action-btn" title="Xem nhanh">👁️</button>
                            </div>
                            
                            <div class="product-image-container">
                                <?php if ($product['image'] && $product['image'] != 'no-image.jpg'): ?>
                                    <img src="uploads/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 5rem;">📦</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info">
                                <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                <h3><a href="product_detail.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                                
                                <div class="product-price">
                                    <span class="price-current"><?php echo format_currency($product['price']); ?></span>
                                </div>
                                
                                <?php if ($product['stock'] > 0): ?>
                                    <p class="product-stock in-stock">✓ Còn hàng: <?php echo $product['stock']; ?> sản phẩm</p>
                                <?php else: ?>
                                    <p class="product-stock out-of-stock">✗ Hết hàng</p>
                                <?php endif; ?>
                                
                                <div class="product-actions">
                                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                                         🛒 Mua ngay
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="products-section">
        <div class="container">
            <h2 class="section-title">Sản phẩm mới nhất</h2>
            
            <?php if ($new_products && $new_products->num_rows > 0): ?>
                <div class="products-grid">
                    <?php while ($product = $new_products->fetch_assoc()): ?>
                        <div class="product-card">
                            <span class="product-badge new">🆕 Mới</span>
                            
                            <div class="product-quick-actions">
                                <button class="quick-action-btn" title="Yêu thích">❤️</button>
                                <button class="quick-action-btn" title="Xem nhanh">👁️</button>
                            </div>
                            
                            <div class="product-image-container">
                                <?php if ($product['image'] && $product['image'] != 'no-image.png'): ?>
                                    <img src="uploads/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 5rem;">📦</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info">
                                <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                <h3><a href="product_detail.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                                
                                
                                
                                
                                <div class="product-price">
                                    <span class="price-current"><?php echo format_currency($product['price']); ?></span>
                                </div>
                                
                                <?php if ($product['stock'] > 0): ?>
                                    <p class="product-stock in-stock">✓ Còn hàng</p>
                                <?php else: ?>
                                    <p class="product-stock out-of-stock">✗ Hết hàng</p>
                                <?php endif; ?>
                                
                                <div class="product-actions">
                                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                                         🛒 Mua ngay
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="text-center mt-3">
                    <a href="products.php" class="btn btn-outline btn-large">Xem tất cả sản phẩm →</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

     

    <script>
var swiper = new Swiper(".mySwiper", {
    effect: "fade", // Hiệu ứng mờ Shopee
    loop: true,
    autoplay: {
        delay: 6000, // Chuyển slide sau 6 giây
        disableOnInteraction: false
    },
    speed: 600, // Hiệu ứng mượt trong 600ms
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    }
});

// Hàm trượt danh mục sản phẩm
function slideCategories(distance) {
    // Lấy container trượt
    const slider = document.querySelector('.category-slider');
    
    if (slider) {
        // Cuộn ngang container một khoảng cách nhất định (distance là pixel)
        slider.scrollBy({
            left: distance, 
            behavior: 'smooth' 
        });
    }
}

// HÀM MỚI: Hàm trượt sản phẩm nổi bật
function slideProducts(distance) {
    // Lấy container trượt sản phẩm nổi bật
    const slider = document.querySelector('.product-slider-horizontal');
    
    if (slider) {
        // Cuộn ngang container một khoảng cách nhất định
        slider.scrollBy({
            left: distance, 
            behavior: 'smooth' 
        });
    }
}
</script>
<script src="https://uhchat.net/code.php?f=a014b6"></script>
<?php include 'includes/footer.php'; ?>