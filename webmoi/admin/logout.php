<?php
/**
 * FILE: admin/logout.php
 * MÔ TẢ: Đăng xuất admin
 */

require_once '../includes/config.php';

// Xóa tất cả session
session_unset();
session_destroy();

// Tạo session mới
session_start();

// Thông báo
set_message('Đã đăng xuất khỏi trang quản trị!', 'success');

// Chuyển về trang đăng nhập admin
redirect('login.php');
?>