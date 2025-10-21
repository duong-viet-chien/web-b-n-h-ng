-- ================================================
-- DATABASE: shop_online
-- Tạo bởi: AI Assistant
-- Mô tả: Cơ sở dữ liệu cho website bán hàng online
-- ================================================

-- Tạo database
CREATE DATABASE IF NOT EXISTS banhanf_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE banhang_db;

-- ================================================
-- BẢNG 1: users (Người dùng)
-- ================================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm tài khoản admin mặc định
-- Username: admin | Password: admin123
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `phone`, `address`, `role`) VALUES
('admin', 'admin@shop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản Trị Viên', '0123456789', 'Hà Nội, Việt Nam', 'admin'),
('user1', 'user1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', '0987654321', 'TP. Hồ Chí Minh', 'user');

-- ================================================
-- BẢNG 2: categories (Danh mục sản phẩm)
-- ================================================
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm danh mục mẫu
INSERT INTO `categories` (`name`, `description`) VALUES
('Điện thoại', 'Điện thoại thông minh các loại'),
('Laptop', 'Máy tính xách tay'),
('Phụ kiện', 'Phụ kiện điện tử'),
('Tablet', 'Máy tính bảng'),
('Đồng hồ', 'Đồng hồ thông minh');

-- ================================================
-- BẢNG 3: products (Sản phẩm)
-- ================================================
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT 'no-image.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm sản phẩm mẫu
INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `stock`, `image`) VALUES
(1, 'iPhone 15 Pro Max', 'Điện thoại iPhone 15 Pro Max 256GB - Chip A17 Pro mạnh mẽ, camera 48MP', 29990000.00, 50, 'iphone15.jpg'),
(1, 'Samsung Galaxy S24 Ultra', 'Samsung Galaxy S24 Ultra 512GB - Màn hình Dynamic AMOLED 2X, Camera 200MP', 27990000.00, 30, 'samsung-s24.jpg'),
(2, 'MacBook Pro M3', 'MacBook Pro 14" M3 Chip - 16GB RAM, 512GB SSD, Màn hình Retina', 42990000.00, 20, 'macbook-m3.jpg'),
(2, 'Dell XPS 15', 'Dell XPS 15 - Intel i7 Gen 13, 16GB RAM, 1TB SSD, RTX 4050', 35990000.00, 15, 'dell-xps15.jpg'),
(3, 'AirPods Pro 2', 'Tai nghe AirPods Pro 2 - Chống ồn chủ động, âm thanh spatial', 5990000.00, 100, 'airpods-pro2.jpg'),
(3, 'Magic Mouse', 'Chuột Apple Magic Mouse - Kết nối Bluetooth, pin sạc', 1990000.00, 80, 'magic-mouse.jpg'),
(4, 'iPad Air M2', 'iPad Air 11" M2 Chip - 128GB, Màn hình Liquid Retina', 14990000.00, 40, 'ipad-air-m2.jpg'),
(5, 'Apple Watch Series 9', 'Apple Watch Series 9 45mm - GPS, Always-On Display, S9 Chip', 10990000.00, 60, 'apple-watch9.jpg');

-- ================================================
-- BẢNG 4: orders (Đơn hàng)
-- ================================================
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipping','completed','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT 'COD',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm đơn hàng mẫu
INSERT INTO `orders` (`user_id`, `full_name`, `email`, `phone`, `address`, `total_amount`, `status`) VALUES
(2, 'Nguyễn Văn A', 'user1@example.com', '0987654321', 'Quận 1, TP. Hồ Chí Minh', 35980000.00, 'completed');

-- ================================================
-- BẢNG 5: order_items (Chi tiết đơn hàng)
-- ================================================
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm chi tiết đơn hàng mẫu
INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `price`, `quantity`, `subtotal`) VALUES
(1, 1, 'iPhone 15 Pro Max', 29990000.00, 1, 29990000.00),
(1, 5, 'AirPods Pro 2', 5990000.00, 1, 5990000.00);

-- ================================================
-- BẢNG 6: cart (Giỏ hàng - Session based)
-- Giỏ hàng được lưu trong session PHP, không cần bảng này
-- Tuy nhiên có thể tạo nếu muốn lưu giỏ hàng vào database
-- ================================================

-- ================================================
-- KẾT THÚC DATABASE
-- ================================================

-- Hiển thị thông tin
SELECT 'Database shop_online đã được tạo thành công!' AS message;
SELECT CONCAT('Tài khoản admin: username = admin, password = admin123') AS admin_info;
SELECT CONCAT('Tài khoản user: username = user1, password = admin123') AS user_info;