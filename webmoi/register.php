<?php
/**
 * FILE: register.php
 * MÔ TẢ: Đăng ký tài khoản với giao diện chuyên nghiệp
 */

require_once 'includes/config.php';

if (is_logged_in()) {
    redirect('index.php');
}

// Xử lý đăng ký
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = clean_input($_POST['username']);
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = clean_input($_POST['full_name']);
    $phone = clean_input($_POST['phone']);
    $address = clean_input($_POST['address']);
    
    $errors = array();
    
    if (empty($username)) {
        $errors[] = "Vui lòng nhập tên đăng nhập";
    } elseif (strlen($username) < 4) {
        $errors[] = "Tên đăng nhập phải có ít nhất 4 ký tự";
    }
    
    if (empty($email)) {
        $errors[] = "Vui lòng nhập email";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }
    
    if (empty($password)) {
        $errors[] = "Vui lòng nhập mật khẩu";
    } elseif (strlen($password) < 6) {
        $errors[] = "Mật khẩu phải có ít nhất 6 ký tự";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Mật khẩu xác nhận không khớp";
    }
    
    if (empty($full_name)) {
        $errors[] = "Vui lòng nhập họ tên";
    }
    
    if (empty($errors)) {
        $check_sql = "SELECT id FROM users WHERE username = '$username'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Tên đăng nhập đã tồn tại";
        }
        
        $check_sql = "SELECT id FROM users WHERE email = '$email'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Email đã được sử dụng";
        }
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, email, password, full_name, phone, address, role) 
                VALUES ('$username', '$email', '$hashed_password', '$full_name', '$phone', '$address', 'user')";
        
        if ($conn->query($sql)) {
            set_message('✅ Đăng ký thành công! Vui lòng đăng nhập.', 'success');
            redirect('login.php');
        } else {
            $errors[] = "Có lỗi xảy ra: " . $conn->error;
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
    <link rel="stylesheet" href="css/register.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
   
</head>
<body class="register-page">
     
   <div class="register-container">
    <div class="register-card">
        
        <div class="register-header">
            <div class="logo">
                <img src="images/logo/login.png" alt="Logo Shop Của Chiến">
            </div>
            <h2>Đăng ký tài khoản</h2>
            <p>Tạo tài khoản để bắt đầu mua sắm!</p>
        </div>
            <?php
            $message = get_message();
            if ($message): ?>
                <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'error'; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>Tên đăng nhập <span style="color: red;">*</span></label>
                        <input type="text" name="username" required 
                               placeholder="Ít nhất 4 ký tự"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Email <span style="color: red;">*</span></label>
                        <input type="email" name="email" required 
                               placeholder="email@example.com"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Họ và tên <span style="color: red;">*</span></label>
                    <input type="text" name="full_name" required 
                           placeholder="Nguyễn Văn A"
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="tel" name="phone" 
                               placeholder="0123456789"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <input type="text" name="address" 
                               placeholder="Địa chỉ của bạn"
                               value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Mật khẩu <span style="color: red;">*</span></label>
                    <input type="password" name="password" id="password" required 
                           placeholder="Ít nhất 6 ký tự">
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <small id="strengthText" style="color: #999; font-size: 0.85rem;"></small>
                </div>

                <div class="form-group">
                    <label>Xác nhận mật khẩu <span style="color: red;">*</span></label>
                    <input type="password" name="confirm_password" required 
                           placeholder="Nhập lại mật khẩu">
                </div>

                <div style="margin: 25px 0;">
                    <label style="display: flex; align-items: start; cursor: pointer;">
                        <input type="checkbox" required style="margin-right: 10px; margin-top: 4px;">
                        <span style="font-size: 0.9rem; line-height: 1.6;">
                            Tôi đồng ý với <a href="#" style="color: var(--primary-color);">Điều khoản sử dụng</a> 
                            và <a href="#" style="color: var(--primary-color);">Chính sách bảo mật</a>
                        </span>
                    </label>
                </div>

                <button type="submit" name="register" class="btn btn-success" style="width: 100%; padding: 15px; font-size: 1.1rem; font-weight: 600;">
                    Đăng ký ngay
                </button>
            </form>

            <p style="text-align: center; color: #666; margin-top: 25px;">
                Đã có tài khoản? <a href="login.php" style="color: var(--primary-color); font-weight: 600;">Đăng nhập ngay</a>
            </p>

            <div style="margin-top: 25px; padding: 20px; background: #e3f2fd; border-radius: 10px; border-left: 4px solid #2196f3;">
                <h4 style="margin-bottom: 10px; color: #1976d2;">🎁 Ưu đãi dành cho thành viên mới</h4>
                <ul style="padding-left: 20px; line-height: 2; color: #666; font-size: 0.9rem;">
                    <li>Giảm 10% cho đơn hàng đầu tiên</li>
                    <li>Miễn phí vận chuyển toàn quốc</li>
                    <li>Tích điểm nhận quà hấp dẫn</li>
                </ul>
            </div>
        </div>

        <div class="back-home">
            <a href="index.php" style="color: #fff; font-weight: 500;">← Quay về trang chủ</a>
        </div>
    </div>

    <script>
        // Password strength indicator
        const password = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        password.addEventListener('input', function() {
            const val = this.value;
            let strength = 0;
            
            if (val.length >= 6) strength += 25;
            if (val.length >= 10) strength += 25;
            if (/[a-z]/.test(val) && /[A-Z]/.test(val)) strength += 25;
            if (/\d/.test(val)) strength += 15;
            if (/[@$!%*?&]/.test(val)) strength += 10;
            
            strengthBar.style.width = strength + '%';
            
            if (strength < 30) {
                strengthBar.style.background = '#dc3545';
                strengthText.textContent = '❌ Yếu - Cần mạnh hơn';
                strengthText.style.color = '#dc3545';
            } else if (strength < 60) {
                strengthBar.style.background = '#ffc107';
                strengthText.textContent = '⚠️ Trung bình - Chấp nhận được';
                strengthText.style.color = '#ffc107';
            } else {
                strengthBar.style.background = '#28a745';
                strengthText.textContent = '✅ Mạnh - Tuyệt vời!';
                strengthText.style.color = '#28a745';
            }
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('❌ Mật khẩu xác nhận không khớp!');
            }
        });
    </script>
</body>
</html>