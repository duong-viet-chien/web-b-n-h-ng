<?php
/**
 * FILE: product_detail.php
 * MÔ TẢ: Chi tiết sản phẩm với giao diện chuyên nghiệp
 */

require_once 'includes/config.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    redirect('products.php');
}

//Xử lý thêm vào giỏ hàng và Mua ngay
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['add_to_cart']) || isset($_POST['buy_now']))) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $is_buy_now = isset($_POST['buy_now']); // Kiểm tra xem người dùng nhấn nút Mua ngay

    if ($quantity > 0) {
        $sql = "SELECT * FROM products WHERE id = $product_id";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();

            if ($product['stock'] >= $quantity) {
                add_to_cart($product['id'], $product['name'], $product['price'], $product['image'], $quantity);

                if ($is_buy_now) {
                    // Chuyển hướng đến trang giỏ hàng/thanh toán nếu nhấn "Mua ngay"
                    set_message('✅ Đã thêm sản phẩm vào giỏ hàng! Đang chuyển hướng...', 'success');
                    redirect('cart.php'); // CHUYỂN HƯỚNG ĐẾN TRANG GIỎ HÀNG
                } else {
                    // Chuyển hướng về trang chi tiết sản phẩm nếu nhấn "Thêm vào giỏ hàng"
                    set_message('✅ Đã thêm sản phẩm vào giỏ hàng!', 'success');
                    redirect('product_detail.php?id=' . $product_id);
                }
            } else {
                set_message('❌ Số lượng sản phẩm không đủ!', 'error');
            }
        }
    }
    // Sau khi xử lý form, chuyển hướng để tránh resubmission
    redirect('product_detail.php?id=' . $product_id);
}
// Lấy thông tin sản phẩm
$sql = "SELECT p.*, c.name as category_name, c.id as category_id
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = $product_id";
$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    set_message('Sản phẩm không tồn tại!', 'error');
    redirect('products.php');
}

$product = $result->fetch_assoc();

// Lấy sản phẩm liên quan
$related_sql = "SELECT * FROM products 
                WHERE category_id = {$product['category_id']} 
                AND id != $product_id 
                AND stock > 0
                ORDER BY RAND() 
                LIMIT 4";
$related_result = $conn->query($related_sql);
?>
<?php include 'includes/Header.php'; ?>

 
    <!-- BREADCRUMB -->
    <div class="breadcrumb">
        <div class="container">
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="products.php">Sản phẩm</a></li>
                <li><a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                <li><?php echo htmlspecialchars($product['name']); ?></li>
            </ul>
        </div>
    </div>

    <!-- THÔNG BÁO -->
    <?php
    $message = get_message();
    if ($message): ?>
        <div class="container mt-2">
            <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'error'; ?>">
                <?php echo $message['message']; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- CHI TIẾT SẢN PHẨM -->
    <section class="product-detail">
        <div class="container">
            <div class="product-detail-grid">
                <!-- GALLERY -->
                <div class="product-gallery">
                    <div class="main-image" id="mainImage">
                        <?php if ($product['image'] && $product['image'] != 'no-image.jpg'): ?>
                            <img src="uploads/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 10rem; color: #ddd;">📦</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="thumbnail-images">
                        <?php for ($i = 0; $i < 4; $i++): ?>
                            <div class="thumbnail <?php echo $i == 0 ? 'active' : ''; ?>">
                                <?php if ($product['image'] && $product['image'] != 'no-image.jpg'): ?>
                                    <img src="uploads/<?php echo $product['image']; ?>" alt="Thumbnail">
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f0f0f0;">📦</div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- THÔNG TIN -->
                <div class="product-detail-info">
                    <div class="product-meta">
                        <span class="badge badge-info"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        <span style="color: #999;">SKU: PRO-<?php echo str_pad($product['id'], 5, '0', STR_PAD_LEFT); ?></span>
                    </div>

                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>

                    <div class="product-rating" style="margin: 15px 0;">
                        <div class="stars" style="font-size: 1.2rem;">⭐⭐⭐⭐⭐</div>
                        <span class="rating-count">(<?php echo rand(100, 500); ?> đánh giá)</span>
                        <span style="margin-left: 15px; color: var(--success-color);">✓ Đã bán <?php echo rand(50, 300); ?></span>
                    </div>

                    <div class="product-detail-price">
                        <span class="price-current"><?php echo format_currency($product['price']); ?></span>
                        <?php if (rand(0, 1)): ?>
                            <span class="price-old"><?php echo format_currency($product['price'] * 1.25); ?></span>
                            <span class="badge badge-danger" style="margin-left: 10px;">-20%</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($product['stock'] > 0): ?>
                        <div style="padding: 15px; background: #d4edda; border-radius: 10px; margin: 20px 0;">
                            <p style="margin: 0; color: #155724; font-weight: 500;">
                                ✓ Còn hàng: <strong><?php echo $product['stock']; ?></strong> sản phẩm
                            </p>
                        </div>
                    <?php else: ?>
                        <div style="padding: 15px; background: #f8d7da; border-radius: 10px; margin: 20px 0;">
                            <p style="margin: 0; color: #721c24; font-weight: 500;">
                                ✗ Sản phẩm hiện tại đã hết hàng
                            </p>
                        </div>
                    <?php endif; ?>

                    <div class="product-detail-description">
                        <h3 style="margin-bottom: 15px;">Mô tả sản phẩm</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>

                    <div style="background: var(--light-color); padding: 20px; border-radius: 10px; margin: 25px 0;">
                        <h4 style="margin-bottom: 15px;">🎁 Ưu đãi đặc biệt</h4>
                        <ul style="padding-left: 20px; line-height: 2;">
                            
                            <li>✓ Bảo hành chính hãng 12 tháng</li>
                            <li>✓ Đổi trả miễn phí trong 7 ngày</li>
                            <li>✓ Tặng kèm phụ kiện trị giá 500.000đ</li>
                        </ul>
                    </div>

                    <!-- FORM MUA HÀNG -->
                    <?php if ($product['stock'] > 0): ?>
                        <form method="POST" action="">
                            <div class="quantity-selector">
                                <label style="font-weight: 600; font-size: 1.1rem;">Số lượng:</label>
                                <div class="quantity-input">
                                    <button type="button" onclick="decreaseQty()">−</button>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" readonly>
                                    <button type="button" onclick="increaseQty()">+</button>
                                </div>
                            </div>
                            
                            <div class="product-detail-actions">
                                <button type="submit" name="add_to_cart" class="btn btn-primary" style="background: var(--danger-color);">
                                    🛒 Thêm vào giỏ hàng
                                </button>
                               
                                <button href="products.php" type="submit" name="buy_now" class="btn btn-success">
                                    Mua ngay
                                </button>
                            </div>
                        </form>

                        <div style="display: flex; gap: 15px; margin-top: 20px;">
                            <button class="btn btn-outline" style="flex: 1;">
                                ❤️ Yêu thích
                            </button>
                            <button class="btn btn-outline" style="flex: 1;">
                                🔔 Nhận thông báo
                            </button>
                        </div>
                    <?php else: ?>
                        <button class="btn btn-danger" disabled style="width: 100%; padding: 18px; font-size: 1.1rem;">
                            ⚠️ Sản phẩm tạm hết hàng
                        </button>
                        <button class="btn btn-outline" style="width: 100%; margin-top: 15px;">
                            🔔 Thông báo khi có hàng
                        </button>
                    <?php endif; ?>

                    <div style="margin-top: 30px; padding: 20px; border: 2px dashed var(--border-color); border-radius: 10px;">
                        <h4 style="margin-bottom: 15px;">📞 Cần tư vấn?</h4>
                        <p style="margin-bottom: 10px;">Gọi ngay: <a href="tel:1900xxxx" style="color: var(--primary-color); font-weight: 700; font-size: 1.3rem;">1900-8198</a></p>
                        <p style="color: #666; margin: 0;">Hoặc chat với chúng tôi để được hỗ trợ nhanh nhất</p>
                    </div>
                </div>
            </div>

            <!-- THÔNG TIN CHI TIẾT -->
            <div style="background: #fff; padding: 40px; border-radius: 15px; margin-top: 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                <div style="display: flex; gap: 30px; border-bottom: 2px solid var(--border-color); margin-bottom: 30px;">
                    <button class="tab-btn active" style="padding: 15px 30px; border: none; background: none; cursor: pointer; font-weight: 600; border-bottom: 3px solid var(--primary-color);">
                        📋 Thông số kỹ thuật
                    </button>
                    <button class="tab-btn" style="padding: 15px 30px; border: none; background: none; cursor: pointer; font-weight: 600;">
                        ⭐ Đánh giá (<?php echo rand(50, 200); ?>)
                    </button>
                    <button class="tab-btn" style="padding: 15px 30px; border: none; background: none; cursor: pointer; font-weight: 600;">
                        📦 Chính sách giao hàng
                    </button>
                </div>

                <div style="line-height: 2;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 15px; font-weight: 600; width: 30%;">Thương hiệu</td>
                            <td style="padding: 15px;">Apple / Samsung / Dell</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 15px; font-weight: 600;">Bảo hành</td>
                            <td style="padding: 15px;">12 tháng chính hãng</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 15px; font-weight: 600;">Xuất xứ</td>
                            <td style="padding: 15px;">Chính hãng</td>
                        </tr>
                        <tr>
                            <td style="padding: 15px; font-weight: 600;">Tình trạng</td>
                            <td style="padding: 15px;"><?php echo $product['stock'] > 0 ? 'Còn hàng' : 'Hết hàng'; ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- SẢN PHẨM LIÊN QUAN -->
            <?php if ($related_result && $related_result->num_rows > 0): ?>
                <div style="margin-top: 60px;">
                    <h2 class="section-title">🔗 Sản phẩm liên quan</h2>
                    <div class="products-grid">
                        <?php while ($related = $related_result->fetch_assoc()): ?>
                            <div class="product-card">
                                <div class="product-image-container">
                                    <a href="product_detail.php?id=<?php echo $related['id']; ?>">
                                        <?php if ($related['image'] && $related['image'] != 'no-image.jpg'): ?>
                                            <img src="uploads/<?php echo $related['image']; ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
                                        <?php else: ?>
                                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 5rem;">📦</div>
                                        <?php endif; ?>
                                    </a>
                                </div>
                                <div class="product-info">
                                    <h3><a href="product_detail.php?id=<?php echo $related['id']; ?>"><?php echo htmlspecialchars($related['name']); ?></a></h3>
                                    <div class="product-price">
                                        <span class="price-current"><?php echo format_currency($related['price']); ?></span>
                                    </div>
                                    <div class="product-actions">
                                        <a href="product_detail.php?id=<?php echo $related['id']; ?>" class="btn btn-primary">
                                            Xem chi tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

 <script>
        const maxQty = <?php echo $product['stock']; ?>;
        
        function increaseQty() {
            const input = document.getElementById('quantity');
            if (parseInt(input.value) < maxQty) {
                input.value = parseInt(input.value) + 1;
            }
        }
        
        function decreaseQty() {
            const input = document.getElementById('quantity');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }

        // Thumbnail gallery
        document.querySelectorAll('.thumbnail').forEach((thumb, index) => {
            thumb.addEventListener('click', function() {
                document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
    <?php include 'includes/footer.php';?>
