<?php
/**
 * FILE: admin/login.php
 * MÔ TẢ: Trang đăng nhập dành cho quản trị viên
 */

require_once '../includes/config.php';

// Nếu đã đăng nhập và là admin thì chuyển về dashboard
if (is_logged_in() && is_admin()) {
    redirect('index.php');
}

// Xử lý đăng nhập admin
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
        $sql = "SELECT * FROM users WHERE (username = '$username' OR email = '$username') AND role = 'admin'";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                
                set_message('Đăng nhập thành công!', 'success');
                redirect('index.php');
            } else {
                $errors[] = "Mật khẩu không chính xác";
            }
        } else {
            $errors[] = "Tài khoản admin không tồn tại";
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
    <title>Đăng nhập Admin - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">


    <style>
       /* body {
            margin: 0;
            font-family: "Segoe UI", sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            animation: fadeIn 1s ease-in-out;
        }*/
        body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(20px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .login-container {
            background: #fff;
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        .login-header {
            margin-bottom: 2rem;
        }

        .login-header i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .login-header h2 {
            margin: 0;
            color: #333;
            font-size: 1.8rem;
        }

        .login-header p {
            color: #777;
            font-size: 0.95rem;
        }

        .form-group {
            text-align: left;
            margin-bottom: 1.5rem;
        }

        .form-group label {
            font-weight: 600;
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: 0.3s;
        }

        .form-group input:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 8px rgba(102,126,234,0.5);
        }

        .btn-login {
            width: 100%;
            background: #ff5252;
            /*linear-gradient(to right, #667eea, #764ba2);*/
            color: #fff;
            font-size: 1.1rem;
            padding: 0.9rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-login:hover {
            transform: scale(1.03);
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }

        .back-home {
            display: inline-block;
            margin-top: 1.2rem;
            color: #ff5252;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .back-home:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            text-align: left;
        }

        .alert-success {
            background: #e6ffed;
            color: #256029;
            border: 1px solid #b7eb8f;
        }

        .alert-error {
            background: #ffe6e6;
            color: #8b0000;
            border: 1px solid #f5c2c7;
        }

        .demo-box {
            margin-top: 1.5rem;
            background: #f9f9f9;
            padding: 1rem;
            font-size: 0.9rem;
            border-radius: 10px;
            color: #555;
            text-align: left;
        }

        .demo-box code {
            background: #eee;
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    
    
<div class="login-container">
    <div class="login-header">
        <img src="../images/logo/login.png" alt="Admin Logo" class="login-logo">
        <h2>Đăng nhập Admin</h2>
        <p>Trang quản trị hệ thống</p>
    </div>

        <?php
        $message = get_message();
        if ($message): ?>
            <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'error'; ?>">
                <?php echo $message['message']; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username"><i class="fa-solid fa-user"></i> Tên đăng nhập</label>
                <input type="text" id="username" name="username" required 
                       placeholder="Nhập tên đăng nhập admin"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock"></i> Mật khẩu</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Nhập mật khẩu">
            </div>

            <button type="submit" name="login" class="btn-login">
                <i class="fa-solid fa-right-to-bracket"></i> Đăng nhập
            </button>
        </form>

        <a href="../index.php" class="back-home"><i class="fa-solid fa-arrow-left"></i> Quay lại trang chủ</a>

        <div class="demo-box">
            <strong>Tài khoản demo Admin:</strong><br>
            Username: <code>admin</code><br>
            Password: <code>admin123</code>
        </div>
    </div>

</body>
</html>
