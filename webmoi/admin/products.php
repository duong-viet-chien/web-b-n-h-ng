<?php
/**
 * FILE: admin/products.php
 * MÔ TẢ: Quản lý sản phẩm - Thêm, sửa, xóa
 */

require_once '../includes/config.php';

// Kiểm tra quyền admin
if (!is_logged_in() || !is_admin()) {
    set_message('Bạn không có quyền truy cập!', 'error');
    redirect('login.php');
}

// Lấy danh sách danh mục
$categories_sql = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_sql);

// XỬ LÝ THÊM SẢN PHẨM
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $category_id = (int)$_POST['category_id'];
    $name = clean_input($_POST['name']);
    $description = clean_input($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $image = 'no-image.jpg';
    
    // Xử lý upload ảnh
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $target_dir . $image;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
    }
    
    $sql = "INSERT INTO products (category_id, name, description, price, stock, image) 
            VALUES ($category_id, '$name', '$description', $price, $stock, '$image')";
    
    if ($conn->query($sql)) {
        set_message('Thêm sản phẩm thành công!', 'success');
    } else {
        set_message('Lỗi: ' . $conn->error, 'error');
    }
    redirect('products.php');
}

// XỬ LÝ CẬP NHẬT SẢN PHẨM
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $id = (int)$_POST['id'];
    $category_id = (int)$_POST['category_id'];
    $name = clean_input($_POST['name']);
    $description = clean_input($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    
    // Lấy ảnh cũ
    $sql = "SELECT image FROM products WHERE id = $id";
    $result = $conn->query($sql);
    $old_image = $result->fetch_assoc()['image'];
    $image = $old_image;
    
    // Xử lý upload ảnh mới
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        $image = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $target_dir . $image;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
        
        // Xóa ảnh cũ
        if ($old_image != 'no-image.jpg' && file_exists($target_dir . $old_image)) {
            unlink($target_dir . $old_image);
        }
    }
    
    $sql = "UPDATE products SET 
            category_id = $category_id, 
            name = '$name', 
            description = '$description', 
            price = $price, 
            stock = $stock, 
            image = '$image' 
            WHERE id = $id";
    
    if ($conn->query($sql)) {
        set_message('Cập nhật sản phẩm thành công!', 'success');
    } else {
        set_message('Lỗi: ' . $conn->error, 'error');
    }
    redirect('products.php');
}

// XỬ LÝ XÓA SẢN PHẨM
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Lấy ảnh để xóa
    $sql = "SELECT image FROM products WHERE id = $id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        if ($product['image'] != 'no-image.jpg' && file_exists("../uploads/" . $product['image'])) {
            unlink("../uploads/" . $product['image']);
        }
    }
    
    $sql = "DELETE FROM products WHERE id = $id";
    if ($conn->query($sql)) {
        set_message('Xóa sản phẩm thành công!', 'success');
    } else {
        set_message('Lỗi: ' . $conn->error, 'error');
    }
    redirect('products.php');
}

// Lấy sản phẩm cần sửa
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $sql = "SELECT * FROM products WHERE id = $edit_id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $edit_product = $result->fetch_assoc();
    }
}

// Lấy danh sách sản phẩm
$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC";
$products = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <!-- SIDEBAR -->
        <aside class="admin-sidebar">
            <h2>🛡️ Admin Panel</h2>
            <ul>
                <li><a href="index.php">📊 Dashboard</a></li>
                <li><a href="products.php" class="active">📦 Quản lý sản phẩm</a></li>
                <li><a href="orders.php">🛒 Quản lý đơn hàng</a></li>
                <li><a href="../index.php">🏠 Về trang chủ</a></li>
                <li><a href="logout.php">🚪 Đăng xuất</a></li>
            </ul>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="admin-content">
            <div class="admin-header">
                <h1>Quản lý sản phẩm</h1>
            </div>

            <?php
            $message = get_message();
            if ($message): ?>
                <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'error'; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>

            <!-- FORM THÊM/SỬA SẢN PHẨM -->
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                <h3><?php echo $edit_product ? 'Cập nhật sản phẩm' : 'Thêm sản phẩm mới'; ?></h3>
                <hr style="margin: 1rem 0;">
                
                <form method="POST" action="" enctype="multipart/form-data" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <?php if ($edit_product): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Danh mục *</label>
                        <select name="category_id" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php 
                            $categories_result->data_seek(0);
                            while ($cat = $categories_result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo ($edit_product && $edit_product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tên sản phẩm *</label>
                        <input type="text" name="name" required 
                               value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>">
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Mô tả</label>
                        <textarea name="description" rows="3"><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Giá (VNĐ) *</label>
                        <input type="number" name="price" required 
                               value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Tồn kho *</label>
                        <input type="number" name="stock" required 
                               value="<?php echo $edit_product ? $edit_product['stock'] : ''; ?>">
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Ảnh sản phẩm</label>
                        <input type="file" name="image" accept="image/*">
                        <?php if ($edit_product && $edit_product['image'] != 'no-image.jpg'): ?>
                            <p style="margin-top: 0.5rem; color: #666;">Ảnh hiện tại: <?php echo $edit_product['image']; ?></p>
                        <?php endif; ?>
                    </div>

                    <div style="grid-column: 1 / -1; display: flex; gap: 1rem;">
                        <button type="submit" name="<?php echo $edit_product ? 'update_product' : 'add_product'; ?>" class="btn btn-success">
                            <?php echo $edit_product ? '✏️ Cập nhật' : '➕ Thêm mới'; ?>
                        </button>
                        <?php if ($edit_product): ?>
                            <a href="products.php" class="btn btn-primary">✖️ Hủy</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- DANH SÁCH SẢN PHẨM -->
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h3>Danh sách sản phẩm</h3>
                <hr style="margin: 1rem 0;">
                
                <?php if ($products && $products->num_rows > 0): ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8f9fa;">
                                    <th style="padding: 1rem; text-align: left;">ID</th>
                                    <th style="padding: 1rem; text-align: left;">Ảnh</th>
                                    <th style="padding: 1rem; text-align: left;">Tên sản phẩm</th>
                                    <th style="padding: 1rem; text-align: left;">Danh mục</th>
                                    <th style="padding: 1rem; text-align: left;">Giá</th>
                                    <th style="padding: 1rem; text-align: left;">Tồn kho</th>
                                    <th style="padding: 1rem; text-align: left;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($product = $products->fetch_assoc()): ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 1rem;"><?php echo $product['id']; ?></td>
                                        <td style="padding: 1rem;">
                                            <?php if ($product['image'] && $product['image'] != 'no-image.jpg'): ?>
                                                <img src="../uploads/<?php echo $product['image']; ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                            <?php else: ?>
                                                <div style="width: 50px; height: 50px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 5px;">📦</div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 1rem;"><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td style="padding: 1rem;"><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td style="padding: 1rem;"><?php echo format_currency($product['price']); ?></td>
                                        <td style="padding: 1rem;">
                                            <?php if ($product['stock'] < 10): ?>
                                                <span class="badge badge-warning"><?php echo $product['stock']; ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-success"><?php echo $product['stock']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <a href="products.php?edit=<?php echo $product['id']; ?>" 
                                               class="btn btn-primary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">✏️ Sửa</a>
                                            <a href="products.php?delete=<?php echo $product['id']; ?>" 
                                               onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')"
                                               class="btn btn-danger" style="padding: 0.5rem 1rem;">🗑️ Xóa</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #999;">Chưa có sản phẩm nào</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>