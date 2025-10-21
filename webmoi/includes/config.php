<?php
/**
 * FILE: config.php
 * MÔ TẢ: Kết nối database và các cấu hình chung
 * CÁCH DÙNG: include 'config.php'; ở đầu mỗi file PHP
 */

// Bắt đầu session (quan trọng cho giỏ hàng và đăng nhập)
session_start();

// ================================================
// CẤU HÌNH DATABASE
// ================================================
define('DB_HOST', 'localhost');      // Địa chỉ MySQL server
define('DB_USER', 'root');           // Username MySQL (mặc định XAMPP là root)
define('DB_PASS', '');               // Password MySQL (mặc định XAMPP là rỗng)
define('DB_NAME', 'banhang_db');    // Tên database

// ================================================
// KẾT NỐI DATABASE
// ================================================
try {
    // Tạo kết nối MySQLi
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Kiểm tra kết nối
    if ($conn->connect_error) {
        die("Kết nối database thất bại: " . $conn->connect_error);
    }
    
    // Thiết lập charset UTF-8 để hiển thị tiếng Việt
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Lỗi: " . $e->getMessage());
}
require_once 'functions.php';
require_once 'db.php';
// ================================================
// CẤU HÌNH WEBSITE
// ================================================
define('SITE_NAME', 'Shop Của Chiến');
define('SITE_URL', 'http://localhost/webbanhang');  // Thay đổi nếu cần
define('ADMIN_EMAIL', 'duongvietchien2005@gmail.com');

// ================================================
// HÀM TIỆN ÍCH
// ================================================

/**
 * Làm sạch dữ liệu đầu vào (chống XSS, SQL Injection)
 */
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

/**
 * Kiểm tra người dùng đã đăng nhập chưa
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Kiểm tra có phải admin không
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Chuyển hướng trang
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Hiển thị thông báo (lưu trong session)
 */
function set_message($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

/**
 * Lấy và xóa thông báo
 */
function get_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Format tiền VNĐ
 */
function format_currency($number) {
    return number_format($number, 0, ',', '.') . ' ₫';
}

/**
 * Khởi tạo giỏ hàng nếu chưa có
 */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

/**
 * Tính tổng số lượng sản phẩm trong giỏ
 */
function get_cart_count() {
    $count = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
    }
    return $count;
}

/**
 * Tính tổng tiền trong giỏ hàng
 */
function get_cart_total() {
    $total = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
    }
    return $total;
}

/**
 * Thêm sản phẩm vào giỏ hàng
 */
function add_to_cart($product_id, $name, $price, $image, $quantity = 1) {
    // Kiểm tra sản phẩm đã có trong giỏ chưa
    if (isset($_SESSION['cart'][$product_id])) {
        // Nếu có rồi thì tăng số lượng
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        // Nếu chưa có thì thêm mới
        $_SESSION['cart'][$product_id] = [
            'id' => $product_id,
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'quantity' => $quantity
        ];
    }
    return true;
}

/**
 * Cập nhật số lượng sản phẩm trong giỏ
 */
function update_cart($product_id, $quantity) {
    if ($quantity <= 0) {
        // Nếu số lượng <= 0 thì xóa sản phẩm
        unset($_SESSION['cart'][$product_id]);
    } else {
        // Cập nhật số lượng
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    }
    return true;
}

/**
 * Xóa sản phẩm khỏi giỏ hàng
 */
function remove_from_cart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        return true;
    }
    return false;
}

/**
 * Xóa toàn bộ giỏ hàng
 */
function clear_cart() {
    $_SESSION['cart'] = array();
    return true;
}

/**
 * Lấy trạng thái đơn hàng theo badge
 */
function get_order_status_badge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Chờ xử lý</span>',
        'processing' => '<span class="badge badge-info">Đang xử lý</span>',
        'shipping' => '<span class="badge badge-primary">Đang giao</span>',
        'completed' => '<span class="badge badge-success">Hoàn thành</span>',
        'cancelled' => '<span class="badge badge-danger">Đã hủy</span>'
    ];
    return $badges[$status] ?? $status;
}

?>

<?php
$project_root = 'http://localhost/webmoi/'; ?>

