<?php
/**
 * FILE: login.php
 * MÔ TẢ: Đăng nhập với giao diện chuyên nghiệp
 */

require_once 'includes/config.php';

if (is_logged_in()) {
    redirect('index.php');
}

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    
    $errors = array();
    
    if (empty($username)) {
        $errors[] = "Vui lòng nhập tên đăng nhập";
    }
    if (empty($password)) {
        $errors[] = "Vui lòng nhập mật khẩu";
    }
    
    if (empty($errors)) {
        $sql = "SELECT * FROM users WHERE username = '$username' OR email = '$username'";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                
                set_message('✅ Đăng nhập thành công!', 'success');
                
                // Chuyển hướng
                if (isset($_SESSION['redirect_after_login'])) {
                    $redirect = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    redirect($redirect);
                } elseif ($user['role'] === 'admin') {
                    redirect('admin/index.php');
                } else {
                    redirect('index.php');
                }
            } else {
                $errors[] = "Mật khẩu không chính xác";
            }
        } else {
            $errors[] = "Tài khoản không tồn tại";
        }
    }
    
    if (!empty($errors)) {
        set_message(implode('<br>', $errors), 'error');
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
   
</head>
<body class=" login-page">
<div class="login-container">
    <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <img 
                        src="images/logo/login.png" 
                        alt="Logo Shop Của Chiến" 
                        style="max-width: 80px; height: auto; margin-bottom: 10px;" 
                    >
                </div>
                <h2>Đăng nhập</h2>
                <p>Chào mừng bạn quay trở lại!</p>
            </div> 
            <?php
            $message = get_message();?>
                                      
            <?php
            $message = get_message();
            if ($message): ?>
                <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'error'; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Email hoặc tên đăng nhập</label>
                    <input type="text" name="username" required 
                           placeholder="Nhập email hoặc username"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           style="padding-left: 45px; background: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2220%22 height=%2220%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23999%22 stroke-width=%222%22><path d=%22M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2%22/><circle cx=%2212%22 cy=%227%22 r=%224%22/></svg>') no-repeat 15px center;">
                </div>

                <div class="form-group">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" required 
                           placeholder="Nhập mật khẩu"
                           style="padding-left: 45px; background: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2220%22 height=%2220%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23999%22 stroke-width=%222%22><rect x=%223%22 y=%2211%22 width=%2218%22 height=%2211%22 rx=%222%22/><path d=%22M7 11V7a5 5 0 0 1 10 0v4%22/></svg>') no-repeat 15px center;">
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" style="margin-right: 8px;">
                        <span style="font-size: 0.9rem;">Ghi nhớ đăng nhập</span>
                    </label>
                    <a href="#" style="color: var(--primary-color); font-size: 0.9rem;">Quên mật khẩu?</a>
                </div>

                <button type="submit" name="login" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1.1rem; font-weight: 600;">
                    Đăng nhập
                </button>
            </form>

            <div class="divider">
                <span>Hoặc đăng nhập với</span>
            </div>

            <div class="social-login">
    <button class="social-btn">
        <i class="fab fa-facebook-f"></i> Facebook
        </button>
    <button class="social-btn">
        <i class="fab fa-google"></i> Google
        </button>
</div>

            <p style="text-align: center; color: #666; margin-top: 25px;">
                Chưa có tài khoản? <a href="register.php" style="color: var(--primary-color); font-weight: 600;">Đăng ký ngay</a>
            </p>

            <div style="margin-top: 25px; padding: 20px; background: var(--light-color); border-radius: 10px;">
                <p style="margin: 0; font-size: 0.85rem; color: #666; text-align: center;"><strong>Tài khoản demo:</strong></p>
                <p style="margin: 8px 0 0 0; font-size: 0.85rem; color: #666; text-align: center;">
                    👤 User: <code style="background: #fff; padding: 2px 8px; border-radius: 4px;">user1</code> / <code style="background: #fff; padding: 2px 8px; border-radius: 4px;">admin123</code>
                </p>
                <p style="margin: 5px 0 0 0; font-size: 0.85rem; color: #666; text-align: center;">
                    👨‍💼 Admin: <code style="background: #fff; padding: 2px 8px; border-radius: 4px;">admin</code> / <code style="background: #fff; padding: 2px 8px; border-radius: 4px;">admin123</code>
                </p>
            </div>
        </div>

        <div class="back-home">
            <a href="index.php" style="color: #fff; font-weight: 500;">← Quay về trang chủ</a>
        </div>
    </div>
