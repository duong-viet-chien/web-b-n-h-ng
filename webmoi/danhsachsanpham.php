<?php
/**
 * FILE: products.php
 * MÔ TẢ: Trang danh sách sản phẩm - Có lọc theo danh mục, tìm kiếm
 */

require_once 'includes/config.php';

// Lấy danh mục
$categories_sql = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_sql);

// Xử lý lọc và tìm kiếm
$where = "1=1";
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

if ($search) {
    $where .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}

if ($category_id > 0) {
    $where .= " AND p.category_id = $category_id";
}

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Đếm tổng số sản phẩm
$count_sql = "SELECT COUNT(*) as total FROM products p WHERE $where";
$count_result = $conn->query($count_sql);
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $per_page);

// Lấy danh sách sản phẩm
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE $where 
        ORDER BY p.created_at DESC 
        LIMIT $per_page OFFSET $offset";
$result = $conn->query($sql);
?>
<?php include 'includes/Header.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- HEADER -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1><a href="index.php"><?php echo SITE_NAME; ?></a></h1>
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php">Trang chủ</a></li>
                        <li><a href="products.php">Sản phẩm</a></li>
                        <?php if (is_logged_in()): ?>
                            <li><a href="#">Xin chào, <?php echo $_SESSION['username']; ?></a></li>
                            <li><a href="logout.php">Đăng xuất</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Đăng nhập</a></li>
                            <li><a href="register.php">Đăng ký</a></li>
                        <?php endif; ?>
                        <li class="cart-icon">
                            <a href="cart.php">
                                🛒 Giỏ hàng
                                <?php if (get_cart_count() > 0): ?>
                                    <span class="cart-count"><?php echo get_cart_count(); ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- PRODUCTS SECTION -->
    <section class="products-section">
        <div class="container">
            <h2 class="section-title">Tất cả sản phẩm</h2>

            <!-- BỘ LỌC & TÌM KIẾM -->
            <div class="filter-section" style="background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                <form method="GET" action="products.php" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <!-- Tìm kiếm -->
                    <div style="flex: 1; min-width: 250px;">
                        <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    
                    <!-- Lọc theo danh mục -->
                    <div style="min-width: 200px;">
                        <select name="category" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="0">Tất cả danh mục</option>
                            <?php 
                            $categories_result->data_seek(0);
                            while ($cat = $categories_result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <!-- Nút tìm kiếm -->
                    <div>
                        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">
                            🔍 Tìm kiếm
                        </button>
                    </div>
                </form>
            </div>

            <!-- KẾT QUẢ -->
            <p style="margin-bottom: 1rem; color: #666;">
                Tìm thấy <strong><?php echo $total_products; ?></strong> sản phẩm
            </p>

            <?php if ($result && $result->num_rows > 0): ?>
                <div class="products-grid">
                    <?php while ($product = $result->fetch_assoc()): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if ($product['image'] && $product['image'] != 'no-image.jpg'): ?>
                                    <img src="uploads/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div style="width: 100%; height: 250px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999;">
                                        Không có ảnh
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...</p>
                                <div class="product-price"><?php echo format_currency($product['price']); ?></div>
                                
                                <?php if ($product['stock'] > 0): ?>
                                    <p class="product-stock">Còn hàng: <?php echo $product['stock']; ?> sản phẩm</p>
                                <?php else: ?>
                                    <p class="product-stock out-of-stock">Hết hàng</p>
                                <?php endif; ?>
                                
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">Xem chi tiết</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- PHÂN TRANG -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination" style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="products.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_id; ?>" 
                               class="btn <?php echo $page == $i ? 'btn-primary' : ''; ?>" 
                               style="padding: 0.5rem 1rem;">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-cart">
                    <p style="font-size: 1.2rem;">Không tìm thấy sản phẩm nào.</p>
                    <a href="products.php" class="btn btn-primary mt-1">Xem tất cả</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

        <?php include 'includes/footer.php'; ?>
</body>
</html>