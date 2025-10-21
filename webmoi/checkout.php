<?php
/**
 * FILE: checkout.php
 * MÔ TẢ: Trang thanh toán - Nhập thông tin giao hàng, áp dụng mã giảm giá và tạo đơn hàng
 * LƯU Ý: TẤT CẢ LOGIC XỬ LÝ POST VÀ REDIRECT PHẢI ĐẶT TRƯỚC include 'includes/Header.php'
 */

require_once 'includes/config.php';

// =======================================================
// === 1. XỬ LÝ SẢN PHẨM ĐƯỢC CHỌN VÀ LỌC GIỎ HÀNG ===
// =======================================================

// Nhận chuỗi ID sản phẩm đã chọn từ cart.php (POST) và lưu vào Session
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout_product_ids'])) {
    $selected_ids_string = $_POST['checkout_product_ids'];
    $selected_ids = array_filter(array_map('intval', explode(',', $selected_ids_string)));
    
    if (!empty($selected_ids)) {
        $_SESSION['selected_checkout_ids'] = $selected_ids;
        // KHÔNG REDIRECT ở đây, để logic coupon và place_order chạy tiếp
    } else {
        set_message('Vui lòng chọn ít nhất một sản phẩm để thanh toán.', 'error');
        redirect('cart.php');
    }
}

// Lọc giỏ hàng hiện tại ($_SESSION['cart']) dựa trên ID đã lưu trong Session
$checkout_cart = [];
$selected_ids = $_SESSION['selected_checkout_ids'] ?? [];

if (!empty($_SESSION['cart']) && !empty($selected_ids)) {
    foreach ($_SESSION['cart'] as $item) {
        if (in_array($item['id'], $selected_ids)) {
            $checkout_cart[$item['id']] = $item;
        }
    }
}


// --- 2. XỬ LÝ FORM ÁP DỤNG MÃ GIẢM GIÁ ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_coupon'])) {
    $code = clean_input($_POST['coupon_code']);
    
    unset($_SESSION['discount_amount']);
    unset($_SESSION['coupon_code']);

    if (empty($code)) {
        set_message('Vui lòng nhập mã giảm giá.', 'error');
        redirect('checkout.php'); 
    }

    $sql_coupon = "SELECT * FROM coupons WHERE code = '$code' AND is_active = 1 AND expiration_date >= CURDATE()";
    $result_coupon = $conn->query($sql_coupon);
    
    if ($result_coupon && $result_coupon->num_rows > 0) {
        $coupon = $result_coupon->fetch_assoc();
        
        // SỬA: Dùng tổng tiền của giỏ hàng đã lọc
        $cart_total_for_coupon = get_checkout_cart_total($checkout_cart); 
        $shipping_fee = 30000;
        $calculated_discount = 0;
        $is_valid = true;
        
        // KIỂM TRA ĐIỀU KIỆN TỐI THIỂU
        if ($cart_total_for_coupon < $coupon['min_order_amount']) {
            set_message("Mã {$code} chỉ áp dụng cho đơn hàng tối thiểu " . format_currency($coupon['min_order_amount']) . '.', 'error');
            $is_valid = false;
        }

        // KIỂM TRA ĐIỀU KIỆN ĐẶC BIỆT (VIP ONLY)
        $user_id = $_SESSION['user_id'] ?? null;
        if ($user_id) {
            $user_sql = "SELECT role FROM users WHERE id = $user_id";
            $user_result = $conn->query($user_sql);
            $user = $user_result->fetch_assoc();
        }
        
        if ($code == 'VIPONLY' && (!isset($user['role']) || $user['role'] != 'VIP')) {
            set_message("Mã {$code} chỉ dành riêng cho Thành viên VIP.", 'error');
            $is_valid = false;
        }
        
        if ($is_valid) {
            // TÍNH TOÁN GIÁ TRỊ GIẢM
            if ($coupon['discount_type'] == 'percent') {
                $calculated_discount = $cart_total_for_coupon * ($coupon['discount_value'] / 100);
            } else if ($coupon['discount_type'] == 'fixed') {
                $calculated_discount = $coupon['discount_value'];
            }

            // Xử lý mã Freeship
            if ($code == 'NEWMEMBER') {
                $calculated_discount = $shipping_fee; 
                $applied_message = "Mã {$code} đã áp dụng: Miễn phí vận chuyển!";
            } else {
                $applied_message = "Mã {$code} đã áp dụng, giảm: " . format_currency($calculated_discount) . '.';
            }

            // LƯU KẾT QUẢ VÀO SESSION
            $_SESSION['discount_amount'] = $calculated_discount;
            $_SESSION['coupon_code'] = $code;
            
            set_message($applied_message, 'success');
        }
        
    } else {
        set_message('Mã giảm giá không hợp lệ hoặc đã hết hạn.', 'error');
    }
    
    redirect('checkout.php');
}


// --- 3. XỬ LÝ FORM ĐẶT HÀNG (SỬA LOGIC DÙNG GIỎ HÀNG ĐÃ LỌC) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    
    $full_name = clean_input($_POST['full_name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $address = clean_input($_POST['address']);
    $payment_method = clean_input($_POST['payment_method']);
    $notes = clean_input($_POST['notes']);

    // SỬA: Dùng tổng tiền giỏ hàng đã lọc
    $cart_total = get_checkout_cart_total($checkout_cart); 
    $shipping_fee = 30000;
    $order_discount_amount = $_SESSION['discount_amount'] ?? 0;
    $order_coupon_code = $_SESSION['coupon_code'] ?? '';
    $final_total = ($cart_total + $shipping_fee) - $order_discount_amount;
    if ($final_total < 0) $final_total = 0;
    $user_id = $_SESSION['user_id'];
    
    // SỬA: Giỏ hàng dùng để tạo chi tiết đơn hàng
    $cart_for_order = $checkout_cart; 
    
    $errors = array();
    if (empty($cart_for_order)) $errors[] = "Giỏ hàng thanh toán trống. Vui lòng quay lại giỏ hàng để chọn sản phẩm.";
    if (empty($full_name)) $errors[] = "Vui lòng nhập họ tên";
    if (empty($address)) $errors[] = "Vui lòng nhập địa chỉ";

    if (empty($errors)) {
        global $conn;
        $conn->begin_transaction();
        
        try {
            $sql = "INSERT INTO orders (user_id, full_name, email, phone, address, 
                                     total_amount, coupon_code, discount_amount, payment_method, notes) 
                    VALUES ($user_id, '$full_name', '$email', '$phone', '$address', 
                            $final_total, '$order_coupon_code', $order_discount_amount, '$payment_method', '$notes')";
            
            if ($conn->query($sql)) {
                $order_id = $conn->insert_id;
                
                // SỬA: Vòng lặp chỉ dùng giỏ hàng đã lọc $cart_for_order
                foreach ($cart_for_order as $item) { 
                    $product_id = $item['id'];
                    $product_name = clean_input($item['name']);
                    $price = $item['price'];
                    $quantity = $item['quantity'];
                    $subtotal = $price * $quantity;
                    
                    $sql = "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) 
                            VALUES ($order_id, $product_id, '$product_name', $price, $quantity, $subtotal)";
                    $conn->query($sql);
                    
                    $sql = "UPDATE products SET stock = stock - $quantity WHERE id = $product_id";
                    $conn->query($sql);
                }
                
                $conn->commit();
                
                // QUAN TRỌNG: Xóa các item đã được đặt khỏi giỏ hàng chính ($_SESSION['cart'])
                foreach (array_keys($cart_for_order) as $id) {
                    unset($_SESSION['cart'][$id]);
                }
                
                // Xóa các session tạm thời
                unset($_SESSION['selected_checkout_ids']); 
                unset($_SESSION['coupon_code']);
                unset($_SESSION['discount_amount']);
                
                set_message('Đặt hàng thành công! Mã đơn hàng: #' . $order_id, 'success');
                redirect('index.php'); 
            } else {
                throw new Exception('Lỗi tạo đơn hàng');
            }
        } catch (Exception $e) {
            $conn->rollback();
            set_message('Có lỗi xảy ra: ' . $e->getMessage(), 'error');
        }
    } else {
        set_message(implode('<br>', $errors), 'error');
    }
}

// --- 4. KIỂM TRA BAN ĐẦU VÀ TÍNH TOÁN CUỐI CÙNG (HIỂN THỊ) ---

// SỬA: Kiểm tra giỏ hàng đã lọc
if (empty($checkout_cart)) { 
    set_message('Giỏ hàng thanh toán trống. Vui lòng chọn sản phẩm.', 'error');
    redirect('cart.php');
}

// Kiểm tra đăng nhập
if (!is_logged_in()) {
    set_message('Vui lòng đăng nhập để thanh toán!', 'error');
    redirect('login.php');
}

// Lấy dữ liệu cần thiết cho hiển thị
$cart_to_display = $checkout_cart; // Giỏ hàng đã lọc để hiển thị
$cart_total = get_checkout_cart_total($cart_to_display); // Dùng hàm tính tổng mới

$shipping_fee = 30000;
$discount_amount = $_SESSION['discount_amount'] ?? 0;
$coupon_code_applied = $_SESSION['coupon_code'] ?? '';
$sub_total_with_shipping = $cart_total + $shipping_fee;
$final_total = $sub_total_with_shipping - $discount_amount;
if ($final_total < 0) $final_total = 0;

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// --- BẮT ĐẦU OUTPUT HTML ---
?>

<?php include 'includes/Header.php'; ?>


    <?php
    $message = get_message();
    if ($message): ?>
        <div class="container mt-2">
            <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'error'; ?>">
                <?php echo $message['message']; ?>
            </div>
        </div>
    <?php endif; ?>

    <section class="checkout-section">
        <div class="container">
            <h2 class="section-title">Thanh toán đơn hàng của bạn!</h2>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h3>Thông tin giao hàng</h3>
                    <hr style="margin: 1rem 0;">
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="full_name">Họ và tên <span style="color: red;">*</span></label>
                            <input type="text" id="full_name" name="full_name" 
                                     value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span style="color: red;">*</span></label>
                            <input type="email" id="email" name="email" 
                                     value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Số điện thoại <span style="color: red;">*</span></label>
                            <input type="tel" id="phone" name="phone" 
                                     value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="address">Địa chỉ giao hàng <span style="color: red;">*</span></label>
                            <textarea id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="payment_method">Phương thức thanh toán</label>
                            <select id="payment_method" name="payment_method">
                                <option value="COD">Thanh toán khi nhận hàng (COD)</option>
                                <option value="Bank">Chuyển khoản ngân hàng</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="notes">Ghi chú đơn hàng (tùy chọn)</label>
                            <textarea id="notes" name="notes" rows="3" placeholder="Ghi chú về đơn hàng, ví dụ: thời gian hay chỉ dẫn địa điểm giao hàng chi tiết hơn..."></textarea>
                        </div>

                        <button type="submit" name="place_order" class="btn btn-success" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                            Đặt hàng
                        </button>
                    </form>
                </div>

                <div>
                    <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 20px;">
                        <h3>Đơn hàng của bạn</h3>
                        <hr style="margin: 1rem 0;">

                        <?php foreach ($cart_to_display as $item): ?>
                            <div style="display: flex; gap: 1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #eee;">
                                <div style="flex-shrink: 0;">
                                    <?php if ($item['image'] && $item['image'] != 'no-image.jpg'): ?>
                                        <img src="uploads/<?php echo $item['image']; ?>" 
                                            alt="<?php echo htmlspecialchars($item['name']); ?>"
                                            style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                                            📦
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; margin-bottom: 0.25rem;">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </div>
                                    <div style="color: #666; font-size: 0.9rem;">
                                        <?php echo format_currency($item['price']); ?> x <?php echo $item['quantity']; ?>
                                    </div>
                                    <div style="color: #667eea; font-weight: 600;">
                                        <?php echo format_currency($item['price'] * $item['quantity']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <hr>

                        <div style="margin-bottom: 1.5rem;">
                            <form method="POST" action="">
                                <label for="coupon_code" style="font-weight: 600; display: block; margin-bottom: 0.5rem;">Mã giảm giá</label>
                                <div class="coupon-input-group">
                                    <input type="text" id="coupon_code" name="coupon_code" placeholder="Nhập mã giảm giá" 
                                             value="<?php echo htmlspecialchars($coupon_code_applied); ?>">
                                    <button type="submit" name="apply_coupon">Áp dụng</button>
                                </div>
                                <?php if ($coupon_code_applied): ?>
                                    <small style="color: green; margin-top: 0.5rem; display: block;">
                                        Mã **<?php echo htmlspecialchars($coupon_code_applied); ?>** đang được áp dụng.
                                    </small>
                                <?php endif; ?>
                            </form>
                        </div>
                        
                        <hr>
                        
                        <div class="summary-row">
                            <span>Tạm tính:</span>
                            <strong><?php echo format_currency($cart_total); ?></strong>
                        </div>
                        
                        <div class="summary-row">
                            <span>Phí vận chuyển:</span>
                            <strong><?php echo format_currency($shipping_fee); ?></strong>
                        </div>
                        
                        <?php if ($discount_amount > 0): ?>
                        <div class="summary-row" >
                            <span>Giảm giá (<?php echo htmlspecialchars($coupon_code_applied); ?>):</span>
                            <strong style="color: #ef4444;">- <?php echo format_currency($discount_amount); ?></strong>
                        </div>
                        <?php endif; ?>

                        <hr>

                        <div class="summary-row" style="font-size: 1.2rem; font-weight: 700; color: #667eea;">
                            <span>Tổng cộng:</span>
                            <span><?php echo format_currency($final_total); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
</body>
</html>
<?php include 'includes/footer.php'; ?>