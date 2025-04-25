<?php
// File: login.php
session_start(); // Bắt đầu session để xử lý thông báo lỗi

// Nếu đã đăng nhập rồi thì chuyển hướng về trang chính
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$pageTitle = "Đăng nhập Hệ Thống";
$login_error = '';
if (isset($_SESSION['login_error'])) {
    $login_error = $_SESSION['login_error'];
    unset($_SESSION['login_error']); // Xóa lỗi sau khi hiển thị
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css"> <!-- Dùng chung CSS -->
    <style>
        /* CSS riêng cho trang login */
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: var(--medium-gray);}
        .login-container { background-color: var(--white-color); padding: 30px 40px; border-radius: var(--border-radius); box-shadow: var(--box-shadow); width: 100%; max-width: 400px; text-align: center; }
        .login-container h1 { color: var(--primary-color); margin-bottom: 25px; font-size: 1.8em; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; color: var(--dark-gray);}
        .form-group input[type="text"], .form-group input[type="password"] { width: 100%; } /* Kế thừa từ style.css */
        .login-button { display: block; width: 100%; padding: 12px; font-size: 1.1em; font-weight: 700; color: white; background-color: var(--primary-color); border: none; border-radius: var(--border-radius); cursor: pointer; transition: background-color 0.3s ease; margin-top: 25px;}
        .login-button:hover { background-color: #004085; }
        .error-message { color: var(--danger-color); background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: var(--border-radius); margin-bottom: 20px; font-size: 0.95em; }
        .login-footer { margin-top: 20px; font-size: 0.9em; color: var(--secondary-color); }
        .login-footer a { color: var(--primary-light); text-decoration: none; }
        .login-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Đăng Nhập</h1>

        <?php if ($login_error): ?>
            <div class="error-message"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['logout'])): ?>
             <div class="error-message" style="color: var(--success-color); background-color: #d4edda; border-color: #c3e6cb;">Bạn đã đăng xuất thành công.</div>
        <?php endif; ?>


        <form action="actions/process_login.php" method="POST">
            <div class="form-group">
                <label for="username">Tên đăng nhập:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-button">Đăng Nhập</button>
        </form>
         <div class="login-footer">
             <!-- <a href="#">Quên mật khẩu?</a> -->
         </div>
    </div>
</body>
</html>