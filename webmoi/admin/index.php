<?php
/**
 * FILE: admin/index.php
 * MÔ TẢ: Dashboard quản trị với biểu đồ và thống kê (Đã thêm phần Tin tức)
 */

require_once '../includes/config.php';

// Lưu ý: Nếu bạn đang dùng Lựa chọn 2 (hàm connect_db), bạn phải gọi hàm ở đây:
// global $conn;
// if (!isset($conn)) {
//     $conn = connect_db(); 
// }
global $conn; // Dùng biến toàn cục $conn đã tạo trong config.php (Lựa chọn 1)

if (!is_logged_in() || !is_admin()) {
    set_message('Bạn không có quyền truy cập!', 'error');
    redirect('login.php');
}

// Thống kê
$stats = array();

$sql = "SELECT COUNT(*) as total FROM orders";
$result = $conn->query($sql);
$stats['total_orders'] = $result->fetch_assoc()['total'];

$sql = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'";
$result = $conn->query($sql);
$stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;

$sql = "SELECT COUNT(*) as total FROM products";
$result = $conn->query($sql);
$stats['total_products'] = $result->fetch_assoc()['total'];

// --- THÊM THỐNG KÊ BÀI VIẾT (NEWS) ---
$sql = "SELECT COUNT(*) as total FROM news"; // Giả sử tên bảng là 'news'
$result = $conn->query($sql);
$stats['total_news'] = $result->fetch_assoc()['total'];
// -------------------------------------

$sql = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$result = $conn->query($sql);
$stats['total_users'] = $result->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) as total FROM orders WHERE status = 'pending'";
$result = $conn->query($sql);
$stats['pending_orders'] = $result->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()";
$result = $conn->query($sql);
$stats['today_orders'] = $result->fetch_assoc()['total'];

// Thống kê theo trạng thái
$order_status_data = array();
$statuses = ['pending', 'processing', 'shipping', 'completed', 'cancelled'];
foreach ($statuses as $status) {
    // Dùng prepared statement cho an toàn hơn (dù ở đây chỉ là chuỗi hằng số)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE status = ?");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $order_status_data[$status] = $result->fetch_assoc()['count'];
    $stmt->close();
}

// Doanh thu theo tháng (6 tháng gần đây)
$revenue_data = array();
for ($i = 5; $i >= 0; $i--) {
    $month = date('m', strtotime("-$i months"));
    $year = date('Y', strtotime("-$i months"));
    $sql = "SELECT SUM(total_amount) as total FROM orders WHERE MONTH(created_at) = ? AND YEAR(created_at) = ? AND status != 'cancelled'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $total = $result->fetch_assoc()['total'] ?? 0;
    $revenue_data[date('M/Y', strtotime("-$i months"))] = (int)$total / 1000000; // Quy đổi thành triệu
    $stmt->close();
}

// Đơn hàng mới
$sql = "SELECT o.*, u.username FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC LIMIT 5";
$recent_orders = $conn->query($sql);

// Sản phẩm sắp hết hàng
$sql = "SELECT * FROM products WHERE stock < 10 AND stock > 0 ORDER BY stock ASC LIMIT 5";
$low_stock = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/anmin.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h2 style="display: flex; align-items: center; gap: 10px;">
                Admin Panel
            </h2>
            <ul>
                <li><a href="index.php" class="active">📊 Dashboard</a></li>
                <li><a href="products.php">📦 Quản lý sản phẩm</a></li>
                <li><a href="orders.php">🛒 Quản lý đơn hàng</a></li>
                <li><a href="news.php">📰 Quản lý Bài viết</a></li> <li><a href="../index.php" target="_blank">🏠 Xem website</a></li>
                <li><a href="logout.php" style="color: #ff6b6b;">🚪 Đăng xuất</a></li>
            </ul>
            
            <div style="padding: 20px; margin-top: 30px; background: rgba(255,255,255,0.1); border-radius: 10px;">
                <p style="font-size: 0.85rem; opacity: 0.8; margin-bottom: 10px;">Đăng nhập với:</p>
                <p style="font-weight: 600;"><?php echo $_SESSION['full_name'] ?? $_SESSION['username']; ?></p>
                <p style="font-size: 0.85rem; opacity: 0.7;">@<?php echo $_SESSION['username']; ?></p>
            </div>
        </aside>

        <main class="admin-content">
            <div class="admin-header">
                <div>
                    <h1>📊 Dashboard</h1>
                    <p style="color: #666; margin-top: 5px;">Chào mừng trở lại, <strong><?php echo $_SESSION['full_name'] ?? 'Admin'; ?></strong>!</p>
                </div>
                <div style="color: #666;">
                    📅 <?php echo date('l, d/m/Y'); ?>
                </div>
            </div>

            <?php
            $message = get_message();
            if ($message): ?>
                <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'error'; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h3>Tổng đơn hàng</h3>
                    <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
                    <p style="opacity: 0.9; margin-top: 10px;">
                        📦 Hôm nay: <strong>+<?php echo $stats['today_orders']; ?></strong>
                    </p>
                </div>

                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h3>Doanh thu</h3>
                    <div class="stat-value" style="font-size: 1.8rem;"><?php echo format_currency($stats['total_revenue']); ?></div>
                    <p style="opacity: 0.9; margin-top: 10px;">💰 Tổng doanh thu</p>
                </div>

                <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <h3>Sản phẩm</h3>
                    <div class="stat-value"><?php echo $stats['total_products']; ?></div>
                    <p style="opacity: 0.9; margin-top: 10px;">📦 Sản phẩm</p>
                </div>
                
                <div class="stat-card" style="background: linear-gradient(135deg, #ff9a8b 0%, #ff6a88 100%);">
                    <h3>Bài viết</h3>
                    <div class="stat-value"><?php echo $stats['total_news']; ?></div>
                    <p style="opacity: 0.9; margin-top: 10px;">📰 Tổng bài viết</p>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <h3>Khách hàng</h3>
                    <div class="stat-value"><?php echo $stats['total_users']; ?></div>
                    <p style="opacity: 0.9; margin-top: 10px;">👥 Người dùng</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin: 30px 0;">
                <div style="background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h3 style="margin-bottom: 20px; font-size: 1.3rem;">📈 Doanh Thu 6 Tháng Gần Đây</h3>
                    <canvas id="revenueChart" height="70"></canvas>
                </div>

                <div style="background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h3 style="margin-bottom: 20px; font-size: 1.3rem;">📊 Trạng Thái Đơn Hàng</h3>
                    <canvas id="statusChart" height="100"></canvas>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
                <div style="background: #fff; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 4px solid #ffd700;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h4 style="color: #666; font-size: 0.9rem; margin-bottom: 10px;">Đơn chờ xử lý</h4>
                            <div style="font-size: 2rem; font-weight: 700; color: #ffd700;"><?php echo $stats['pending_orders']; ?></div>
                        </div>
                        <div style="font-size: 3rem; opacity: 0.3;">⏳</div>
                    </div>
                </div>

                <div style="background: #fff; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 4px solid #4caf50;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h4 style="color: #666; font-size: 0.9rem; margin-bottom: 10px;">Hoàn thành</h4>
                            <div style="font-size: 2rem; font-weight: 700; color: #4caf50;"><?php echo $order_status_data['completed']; ?></div>
                        </div>
                        <div style="font-size: 3rem; opacity: 0.3;">✅</div>
                    </div>
                </div>

                <div style="background: #fff; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 4px solid #ff5722;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h4 style="color: #666; font-size: 0.9rem; margin-bottom: 10px;">Trung bình/Đơn</h4>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #ff5722;">
                                <?php echo $stats['total_orders'] > 0 ? format_currency($stats['total_revenue'] / $stats['total_orders']) : '0 ₫'; ?>
                            </div>
                        </div>
                        <div style="font-size: 3rem; opacity: 0.3;">💵</div>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                <div class="data-table">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3>📦 Đơn hàng mới nhất</h3>
                        <a href="orders.php" class="btn btn-primary" style="padding: 8px 20px;">Xem tất cả →</a>
                    </div>
                    
                    <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Mã ĐH</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?php echo $order['id']; ?></strong></td>
                                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                        <td><strong style="color: var(--primary-color);"><?php echo format_currency($order['total_amount']); ?></strong></td>
                                        <td><?php echo get_order_status_badge($order['status']); ?></td>
                                        <td><?php echo date('d/m H:i', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="text-align: center; color: #999; padding: 40px;">Chưa có đơn hàng nào</p>
                    <?php endif; ?>
                </div>

                <div style="background: #fff; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h3 style="margin-bottom: 20px;">⚠️ Sắp hết hàng</h3>
                    
                    <?php if ($low_stock && $low_stock->num_rows > 0): ?>
                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            <?php while ($product = $low_stock->fetch_assoc()): ?>
                                <div style="padding: 15px; background: #fff9e6; border-left: 4px solid #ffc107; border-radius: 8px;">
                                    <div style="font-weight: 600; margin-bottom: 5px;"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem;">
                                        <span>Tồn: <strong style="color: <?php echo $product['stock'] < 5 ? '#dc3545' : '#ffc107'; ?>"><?php echo $product['stock']; ?></strong></span>
                                        <a href="products.php?edit=<?php echo $product['id']; ?>" style="color: #667eea;">Cập nhật →</a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p style="text-align: center; color: #999; padding: 20px;">✅ Đủ hàng</p>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 30px;">
                <a href="products.php" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 30px; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-decoration: none; transition: var(--transition);">
                    <div style="font-size: 3rem; margin-bottom: 10px;">➕</div>
                    <div style="font-weight: 600; font-size: 1.1rem;">Thêm sản phẩm</div>
                </a>

                <a href="orders.php" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: #fff; padding: 30px; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-decoration: none; transition: var(--transition);">
                    <div style="font-size: 3rem; margin-bottom: 10px;">📦</div>
                    <div style="font-weight: 600; font-size: 1.1rem;">Xem đơn hàng</div>
                </a>

                <a href="add_news.php" style="background: linear-gradient(135deg, #00c6fb 0%, #005bea 100%); color: #fff; padding: 30px; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-decoration: none; transition: var(--transition);">
                    <div style="font-size: 3rem; margin-bottom: 10px;">✍️</div>
                    <div style="font-weight: 600; font-size: 1.1rem;">Thêm Bài viết</div>
                </a>
                <a href="orders.php?status=pending" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: #fff; padding: 30px; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-decoration: none; transition: var(--transition);">
                    <div style="font-size: 3rem; margin-bottom: 10px;">⏳</div>
                    <div style="font-weight: 600; font-size: 1.1rem;">Chờ xử lý</div>
                </a>
                
            </div>
             <div style="margin-top: 20px; text-align: right;">
                <a href="../index.php" target="_blank" class="btn btn-secondary" style="padding: 10px 20px;">🌐 Xem Website</a>
            </div>
        </main>
    </div>

    <script>
        // Biểu đồ doanh thu theo tháng
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_keys($revenue_data)); ?>,
                datasets: [{
                    label: 'Doanh Thu (Triệu VNĐ)',
                    data: <?php echo json_encode(array_values($revenue_data)); ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            font: { size: 12 },
                            padding: 15
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Biểu đồ trạng thái đơn hàng
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Chờ xử lý', 'Đang xử lý', 'Đang giao', 'Hoàn thành', 'Đã hủy'],
                datasets: [{
                    data: [
                        <?php echo $order_status_data['pending']; ?>,
                        <?php echo $order_status_data['processing']; ?>,
                        <?php echo $order_status_data['shipping']; ?>,
                        <?php echo $order_status_data['completed']; ?>,
                        <?php echo $order_status_data['cancelled']; ?>
                    ],
                    backgroundColor: [
                        '#ffc107',
                        '#2196f3',
                        '#9c27b0',
                        '#4caf50',
                        '#f44336'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 11 },
                            padding: 10
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>