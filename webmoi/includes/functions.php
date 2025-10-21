<?php
// includes/functions.php
// includes/functions.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function e($s) {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// ... (Các hàm tiện ích khác của bạn) ...

// Hàm mới để tính tổng tiền của giỏ hàng đã lọc (checkout_cart)
function get_checkout_cart_total($cart_data) {
    // Thêm kiểm tra is_array() để đảm bảo ổn định
    $total = 0;
    if (is_array($cart_data)) {
        foreach ($cart_data as $item) {
            // Đảm bảo các trường 'price' và 'quantity' tồn tại
            $price = $item['price'] ?? 0;
            $quantity = $item['quantity'] ?? 0;
            $total += $price * $quantity;
        }
    }
    return $total;
}