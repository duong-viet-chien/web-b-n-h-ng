<?php
/**
 * FILE: admin/sidebar.php
 * MÔ TẢ: Thanh sidebar dùng chung cho các trang admin.
 */
?>
<aside class="admin-sidebar">
    <h2 style="display: flex; align-items: center; gap: 10px;">
        Admin Panel
    </h2>
    <ul>
        <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">📊 Dashboard</a></li>
        <li><a href="products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">📦 Quản lý sản phẩm</a></li>
        <li><a href="orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">🛒 Quản lý đơn hàng</a></li>
        <li><a href="news.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'news.php' || basename($_SERVER['PHP_SELF']) == 'add_news.php' || basename($_SERVER['PHP_SELF']) == 'edit_news.php') ? 'active' : ''; ?>">📰 Quản lý Bài viết</a></li>
        <li><a href="../index.php" target="_blank">🏠 Xem website</a></li>
        <li><a href="logout.php" style="color: #ff6b6b;">🚪 Đăng xuất</a></li>
    </ul>
    
    <div style="padding: 20px; margin-top: 30px; background: rgba(255,255,255,0.1); border-radius: 10px;">
        <p style="font-size: 0.85rem; opacity: 0.8; margin-bottom: 10px;">Đăng nhập với:</p>
        <p style="font-weight: 600;"><?php echo $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User'; ?></p>
        <p style="font-size: 0.85rem; opacity: 0.7;">@<?php echo $_SESSION['username'] ?? 'Guest'; ?></p>
    </div>
</aside>