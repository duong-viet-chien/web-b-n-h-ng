<?php
/**
 * FILE: admin/orders.php
 * MÔ TẢ: Quản lý đơn hàng - Xem chi tiết, cập nhật trạng thái
 */

require_once '../includes/config.php';

// Kiểm tra quyền admin
if (!is_logged_in() || !is_admin()) {
    set_message('Bạn không có quyền truy cập!', 'error');
    redirect('login.php');
}

// XỬ LÝ CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = clean_input($_POST['status']);
    
    $sql = "UPDATE orders SET status = '$status' WHERE id = $order_id";
    if ($conn->query($sql)) {
        set_message('Cập nhật trạng thái đơn hàng thành công!', 'success');
    } else {
        set_message('Lỗi: ' . $conn->error, 'error');
    }
    redirect('orders.php');
}

// Lọc đơn hàng theo trạng thái
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$where = "1=1";
if ($status_filter) {
    $where .= " AND o.status = '$status_filter'";
}

// Lấy danh sách đơn hàng
$sql = "SELECT o.*, u.username FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE $where
        ORDER BY o.created_at DESC";
$orders = $conn->query($sql);

// Lấy chi tiết đơn hàng nếu xem
$order_detail = null;
$order_items = null;
if (isset($_GET['view'])) {
    $view_id = (int)$_GET['view'];
    $sql = "SELECT o.*, u.username FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE o.id = $view_id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $order_detail = $result->fetch_assoc();
        
        // Lấy chi tiết sản phẩm trong đơn
        $sql = "SELECT * FROM order_items WHERE order_id = $view_id";
        $order_items = $conn->query($sql);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <!-- SIDEBAR -->
        <aside class="admin-sidebar">
            <h2>🛡️ Admin Panel</h2>
            <ul>
                <li><a href="index.php">📊 Dashboard</a></li>
                <li><a href="products.php">📦 Quản lý sản phẩm</a></li>
                <li><a href="orders.php" class="active">🛒 Quản lý đơn hàng</a></li>
                <li><a href="../index.php">🏠 Về trang chủ</a></li>
                <li><a href="logout.php">🚪 Đăng xuất</a></li>
            </ul>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="admin-content">
            <div class="admin-header">
                <h1>Quản lý đơn hàng</h1>
            </div>

            <?php
            $message = get_message();
            if ($message): ?>
                <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'error'; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>

            <?php if (!$order_detail): ?>
                <!-- LỌC ĐƠN HÀNG -->
                <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <form method="GET" action="" style="display: flex; gap: 1rem; align-items: center;">
                        <label>Lọc theo trạng thái:</label>
                        <select name="status" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="">Tất cả</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                            <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                            <option value="shipping" <?php echo $status_filter == 'shipping' ? 'selected' : ''; ?>>Đang giao</option>
                            <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Lọc</button>
                    </form>
                </div>

                <!-- DANH SÁCH ĐƠN HÀNG -->
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h3>Danh sách đơn hàng</h3>
                    <hr style="margin: 1rem 0;">
                    
                    <?php if ($orders && $orders->num_rows > 0): ?>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f8f9fa;">
                                        <th style="padding: 1rem; text-align: left;">Mã ĐH</th>
                                        <th style="padding: 1rem; text-align: left;">Khách hàng</th>
                                        <th style="padding: 1rem; text-align: left;">Điện thoại</th>
                                        <th style="padding: 1rem; text-align: left;">Tổng tiền</th>
                                        <th style="padding: 1rem; text-align: left;">Trạng thái</th>
                                        <th style="padding: 1rem; text-align: left;">Ngày đặt</th>
                                        <th style="padding: 1rem; text-align: left;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $orders->fetch_assoc()): ?>
                                        <tr style="border-bottom: 1px solid #eee;">
                                            <td style="padding: 1rem;"><strong>#<?php echo $order['id']; ?></strong></td>
                                            <td style="padding: 1rem;"><?php echo htmlspecialchars($order['full_name']); ?></td>
                                            <td style="padding: 1rem;"><?php echo htmlspecialchars($order['phone']); ?></td>
                                            <td style="padding: 1rem;"><strong><?php echo format_currency($order['total_amount']); ?></strong></td>
                                            <td style="padding: 1rem;"><?php echo get_order_status_badge($order['status']); ?></td>
                                            <td style="padding: 1rem;"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                            <td style="padding: 1rem;">
                                                <a href="orders.php?view=<?php echo $order['id']; ?>" 
                                                   class="btn btn-primary" style="padding: 0.5rem 1rem;">👁️ Xem</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p style="text-align: center; color: #999;">Không có đơn hàng nào</p>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- CHI TIẾT ĐƠN HÀNG -->
                <div style="margin-bottom: 1rem;">
                    <a href="orders.php" class="btn btn-primary">← Quay lại danh sách</a>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                    <!-- THÔNG TIN CHI TIẾT -->
                    <div>
                        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                            <h3>Đơn hàng #<?php echo $order_detail['id']; ?></h3>
                            <hr style="margin: 1rem 0;">
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($order_detail['full_name']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order_detail['email']); ?></p>
                                    <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($order_detail['phone']); ?></p>
                                </div>
                                <div>
                                    <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order_detail['created_at'])); ?></p>
                                    <p><strong>Phương thức TT:</strong> <?php echo $order_detail['payment_method']; ?></p>
                                    <p><strong>Trạng thái:</strong> <?php echo get_order_status_badge($order_detail['status']); ?></p>
                                </div>
                            </div>
                            
                            <p style="margin-top: 1rem;"><strong>Địa chỉ giao hàng:</strong><br>
                            <?php echo nl2br(htmlspecialchars($order_detail['address'])); ?></p>
                            
                            <?php if ($order_detail['notes']): ?>
                                <p style="margin-top: 1rem;"><strong>Ghi chú:</strong><br>
                                <?php echo nl2br(htmlspecialchars($order_detail['notes'])); ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- SẢN PHẨM TRONG ĐƠN -->
                        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <h3>Sản phẩm đã đặt</h3>
                            <hr style="margin: 1rem 0;">
                            
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f8f9fa;">
                                        <th style="padding: 1rem; text-align: left;">Sản phẩm</th>
                                        <th style="padding: 1rem; text-align: left;">Giá</th>
                                        <th style="padding: 1rem; text-align: left;">Số lượng</th>
                                        <th style="padding: 1rem; text-align: left;">Tổng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = $order_items->fetch_assoc()): ?>
                                        <tr style="border-bottom: 1px solid #eee;">
                                            <td style="padding: 1rem;"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                            <td style="padding: 1rem;"><?php echo format_currency($item['price']); ?></td>
                                            <td style="padding: 1rem;"><?php echo $item['quantity']; ?></td>
                                            <td style="padding: 1rem;"><strong><?php echo format_currency($item['subtotal']); ?></strong></td>
                                        </tr>
                                    <?php endwhile; ?>
                                    <tr style="background: #f8f9fa; font-weight: bold;">
                                        <td colspan="3" style="padding: 1rem; text-align: right;">Tổng cộng:</td>
                                        <td style="padding: 1rem; color: #667eea; font-size: 1.2rem;">
                                            <?php echo format_currency($order_detail['total_amount']); ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- CẬP NHẬT TRẠNG THÁI -->
                    <div>
                        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 20px;">
                            <h3>Cập nhật trạng thái</h3>
                            <hr style="margin: 1rem 0;">
                            
                            <form method="POST" action="">
                                <input type="hidden" name="order_id" value="<?php echo $order_detail['id']; ?>">
                                
                                <div class="form-group">
                                    <label>Trạng thái đơn hàng</label>
                                    <select name="status" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px;">
                                        <option value="pending" <?php echo $order_detail['status'] == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                        <option value="processing" <?php echo $order_detail['status'] == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                                        <option value="shipping" <?php echo $order_detail['status'] == 'shipping' ? 'selected' : ''; ?>>Đang giao</option>
                                        <option value="completed" <?php echo $order_detail['status'] == 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                                        <option value="cancelled" <?php echo $order_detail['status'] == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                    </select>
                                </div>
                                
                                <button type="submit" name="update_status" class="btn btn-success" style="width: 100%;">
                                    ✅ Cập nhật trạng thái
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>