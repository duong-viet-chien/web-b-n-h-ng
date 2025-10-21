<?php
/**
 * FILE: cart.php
 * MÔ TẢ: Trang giỏ hàng - Xem, cập nhật, xóa sản phẩm, và chọn sản phẩm để thanh toán
 */

require_once 'includes/config.php';

// Xử lý cập nhật giỏ hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_cart'])) {
        // Cập nhật số lượng
        foreach ($_POST['quantity'] as $product_id => $quantity) {
            update_cart($product_id, (int)$quantity);
        }
        set_message('Đã cập nhật giỏ hàng!', 'success');
        redirect('cart.php');
    }
    
    if (isset($_POST['remove_item'])) {
        // Xóa sản phẩm
        $product_id = (int)$_POST['product_id'];
        remove_from_cart($product_id);
        set_message('Đã xóa sản phẩm khỏi giỏ hàng!', 'success');
        redirect('cart.php');
    }
    
    if (isset($_POST['clear_cart'])) {
        // Xóa toàn bộ giỏ hàng
        clear_cart();
        set_message('Đã xóa toàn bộ giỏ hàng!', 'success');
        redirect('cart.php');
    }
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$cart_total = get_cart_total(); 
$cart_count = get_cart_count(); 
?>
<?php include 'includes/header.php'; ?>

    <?php
    $message = get_message();
    if ($message): ?>
        <div class="container mt-2">
            <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'error'; ?>">
                <?php echo $message['message']; ?>
            </div>
        </div>
    <?php endif; ?>

    <section class="cart-section">
        <div class="container">
            <h2 class="section-title">Giỏ hàng của bạn</h2>    
            
            <?php if (!empty($cart)): ?>
                
                <div class="cart-content-wrapper"> 
                    
                    <form method="POST" action="" class="cart-left">
                        <div class="cart-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Chọn</th> 
                                        <th>Ảnh</th>
                                        <th>Sản phẩm</th>
                                        <th>Giá</th>
                                        <th>Số lượng</th>
                                        <th>Tổng</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart as $item): ?>
                                        <tr data-product-id="<?php echo $item['id']; ?>">
                                            <td>
                                                <input type="checkbox" 
                                                       name="selected_items[]" 
                                                       value="<?php echo $item['id']; ?>" 
                                                       checked
                                                       class="product-selector"
                                                       data-price="<?php echo $item['price']; ?>"
                                                       data-quantity="<?php echo $item['quantity']; ?>"
                                                       style="transform: scale(1.3);">
                                            </td>
                                            <td>
                                               <?php if ($item['image'] && $item['image'] != 'no-image.jpg'): ?>
                                                <img src="uploads/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                               <?php else: ?>
                                                    <div style="width: 100px; height: 100px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 5px; font-size: 2rem;">
                                                        📦
                                                    </div>
                                               <?php endif; ?>
                                            </td>
                                            
                                            <td>
                                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                            </td>
                                            <td><span class="product-price" data-price-value="<?php echo $item['price']; ?>"><?php echo format_currency($item['price']); ?></span></td>
                                            <td>
                                                <input type="number" 
                                                             name="quantity[<?php echo $item['id']; ?>]" 
                                                             value="<?php echo $item['quantity']; ?>" 
                                                             min="1" 
                                                             class="product-quantity"
                                                             style="width: 60px; padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                                            </td>
                                            <td>
                                                <strong class="product-subtotal"><?php echo format_currency($item['price'] * $item['quantity']); ?></strong>
                                            </td>
                                            <td>
                                                <form method="POST" action="" style="display: inline;">
                                                    <button type="submit" name="remove_item" 
                                                            onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')"
                                                            class="btn btn-danger" style="padding: 0.5rem 1rem;">
                                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                                        Xóa
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="cart-actions-bottom" style="display: flex; justify-content: space-between; margin-top: 1rem;">
                            <button type="submit" name="update_cart" class="btn btn-primary">
                                Cập nhật giỏ hàng
                            </button>
                            <button type="submit" name="clear_cart" 
                                    onclick="return confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?')"
                                    class="btn btn-danger">
                                Xóa toàn bộ giỏ hàng
                            </button>
                        </div>
                    </form> <form method="POST" action="checkout.php" id="checkout-form">
                        <div class="cart-summary">
                            <h3>Tổng đơn hàng</h3>
                            <hr>
                            <div style="display: flex; justify-content: space-between; margin: 1rem 0;">
                                <span>Tổng sản phẩm được chọn:</span>
                                <strong id="selected-product-count">0</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin: 1rem 0;">
                                <span>Tạm tính:</span>
                                <strong id="subtotal-display">0 ₫</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin: 1rem 0;">
                                <span>Phí vận chuyển:</span>
                                <strong>Miễn phí</strong>
                            </div>
                            <hr>
                            <div class="cart-total" style="display: flex; justify-content: space-between; font-size: 1.2rem;">
                                <span>Tổng cộng:</span>
                                <span><span id="total-display">0 ₫</span></span>
                            </div>
                            <div class="mt-2">
                                <input type="hidden" name="checkout_product_ids" id="checkout-product-ids" value="">
                                
                                <button type="submit" class="btn btn-success" id="checkout-button" style="width: 100%; text-align: center; display: block; padding: 1rem;">
                                    Tiến hành thanh toán
                                </button>
                                <a href="products.php" class="btn btn-primary" style="width: 100%; text-align: center; display: block; padding: 1rem; margin-top: 1rem;">
                                    ← Tiếp tục mua hàng
                                </a>
                            </div>
                        </div>
                    </form>

                </div> <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const productRows = document.querySelectorAll('.cart-table tbody tr');
                        const totalCountDisplay = document.getElementById('selected-product-count');
                        const subtotalDisplay = document.getElementById('subtotal-display');
                        const totalDisplay = document.getElementById('total-display');
                        const checkoutProductIdsInput = document.getElementById('checkout-product-ids');
                        const checkoutForm = document.getElementById('checkout-form');

                        // Hàm định dạng tiền tệ (cần thiết nếu hàm format_currency PHP không chạy ở client)
                        function formatCurrencyJS(amount) {
                            return new Intl.NumberFormat('vi-VN', { 
                                style: 'currency', 
                                currency: 'VND', 
                                minimumFractionDigits: 0 
                            }).format(amount).replace('₫', '').trim() + ' ₫';
                        }
                        
                        function updateSummary() {
                            let totalSelectedProducts = 0;
                            let newSubtotal = 0;
                            const selectedIds = [];
                            
                            productRows.forEach(row => {
                                const selector = row.querySelector('.product-selector');
                                const quantityInput = row.querySelector('.product-quantity');
                                const subtotalCell = row.querySelector('.product-subtotal');
                                const price = parseFloat(row.querySelector('.product-price').dataset.priceValue);

                                // Cập nhật tổng phụ cho từng dòng dựa trên số lượng hiện tại trong input
                                const currentQuantity = parseInt(quantityInput.value);
                                const currentSubtotal = price * currentQuantity;

                                if (subtotalCell) {
                                    subtotalCell.textContent = formatCurrencyJS(currentSubtotal);
                                }

                                // Chỉ tính vào tổng nếu checkbox được chọn
                                if (selector.checked) {
                                    newSubtotal += currentSubtotal;
                                    totalSelectedProducts += currentQuantity;
                                    selectedIds.push(selector.value);
                                }
                            });
                            
                            // Cập nhật hiển thị tổng
                            totalCountDisplay.textContent = totalSelectedProducts;
                            subtotalDisplay.textContent = formatCurrencyJS(newSubtotal);
                            totalDisplay.textContent = formatCurrencyJS(newSubtotal); 

                            // Cập nhật input ẩn để gửi ID sản phẩm đã chọn
                            checkoutProductIdsInput.value = selectedIds.join(',');
                            
                            // Vô hiệu hóa nút thanh toán nếu không có sản phẩm nào được chọn
                            const checkoutButton = document.getElementById('checkout-button');
                            if (newSubtotal === 0) {
                                checkoutButton.disabled = true;
                                checkoutButton.textContent = 'Vui lòng chọn sản phẩm';
                            } else {
                                checkoutButton.disabled = false;
                                checkoutButton.textContent = 'Tiến hành thanh toán';
                            }
                        }

                        // Gán sự kiện cho Checkbox và Input số lượng
                        document.querySelectorAll('.product-selector, .product-quantity').forEach(element => {
                            element.addEventListener('change', updateSummary);
                            element.addEventListener('input', updateSummary);
                        });
                        
                        // Cập nhật lần đầu khi tải trang
                        updateSummary();
                    });
                </script>


            <?php else: ?>
                <div class="empty-cart">
                    <div style="font-size: 5rem;">🛒</div>
                    <h3>Giỏ hàng trống</h3>
                    <p>Bạn chưa có sản phẩm nào trong giỏ hàng</p>
                    <a href="products.php" class="btn btn-primary mt-2">Mua sắm ngay</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // ... (các biến khác)

        // Hàm cập nhật tổng tiền và ID sản phẩm đã chọn
        function updateSummary() {
            let totalSelectedProducts = 0;
            let newSubtotal = 0;
            const selectedIds = [];
            
            productRows.forEach(row => {
                const selector = row.querySelector('.product-selector');
                const quantityInput = row.querySelector('.product-quantity');
                // ...
                
                // CHÚ Ý TẠI ĐÂY:
                if (selector.checked) { // Đảm bảo chỉ tính toán và thêm vào selectedIds nếu checked
                    newSubtotal += currentSubtotal;
                    totalSelectedProducts += currentQuantity;
                    selectedIds.push(selector.value); // <--- DÒNG NÀY RẤT QUAN TRỌNG
                }
            });
            
            // Cập nhật input ẩn để gửi ID sản phẩm đã chọn
            checkoutProductIdsInput.value = selectedIds.join(','); // <--- DÒNG NÀY RẤT QUAN TRỌNG
            
            // ... (cập nhật hiển thị và vô hiệu hóa nút)
        }

        // Gán sự kiện cho Checkbox và Input số lượng
        document.querySelectorAll('.product-selector, .product-quantity').forEach(element => {
            element.addEventListener('change', updateSummary);
            element.addEventListener('input', updateSummary);
        });
        
        // Cập nhật lần đầu khi tải trang
        updateSummary();
    });
</script>

<?php include 'includes/footer.php'; ?>
