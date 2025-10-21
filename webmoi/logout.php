<?php
/**
 * FILE: logout.php
 * MÔ TẢ: Đăng xuất tài khoản
 */

require_once 'includes/config.php';

// Xóa tất cả session
session_unset();
session_destroy();

// Tạo session mới
session_start();

// Thông báo
set_message('Đã đăng xuất thành công!', 'success');

// Chuyển về trang chủ
redirect('index.php');
?>