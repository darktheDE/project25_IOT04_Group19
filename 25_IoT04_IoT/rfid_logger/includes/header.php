<?php
// File: includes/header.php
session_start(); // Đặt lên đầu tiên

// --- KIỂM TRA ĐĂNG NHẬP ---
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Chuyển hướng ra trang login
    exit; // Dừng thực thi trang hiện tại
}

// Lấy thông tin người dùng đã đăng nhập từ session
$loggedInUserId = $_SESSION['user_id'];
$loggedInUsername = $_SESSION['username'];
$loggedInUserRole = $_SESSION['role']; // 'admin' hoặc 'user'

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Hệ Thống Cửa' : 'Hệ Thống Cửa RFID'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Thêm CSS cho phần user info và logout */
        header .user-info { display: flex; align-items: center; }
        header .user-info span { margin-right: 15px; font-size: 0.9em; opacity: 0.9; }
        header .logout-btn {
            color: var(--white-color); background-color: var(--danger-color);
            padding: 5px 10px; border-radius: var(--border-radius); font-size: 0.85em;
            text-decoration: none; font-weight: 500; transition: background-color 0.2s ease;
        }
        header .logout-btn:hover { background-color: #c82333; }
        /* Responsive cho user info */
         @media (max-width: 768px) {
             header .container { flex-direction: row; justify-content: space-between;}
             header nav { order: 3; width: 100%; margin-top: 10px; justify-content: center; }
             header .user-info { order: 2; margin-left: auto;}
             header .logo { order: 1; }
         }
         @media (max-width: 576px) {
             header .user-info span { display: none; }
         }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">RFID Control</div>
            <nav>
                <ul>
                    <li><a href="index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">Dashboard</a></li>
                    <li><a href="users.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : ''; ?>">Danh Sách Thẻ RFID</a></li>
                    <li><a href="history.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'history.php') ? 'active' : ''; ?>">Lịch Sử</a></li>
                    <?php if ($loggedInUserRole === 'admin'): // Chỉ Admin thấy mục này ?>
                        <li><a href="admin_manage_users.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin_manage_users.php' || strpos($_SERVER['PHP_SELF'], 'admin_add_user.php') !== false) ? 'active' : ''; ?>">Quản lý Tài Khoản Web</a></li>
                    <?php endif; ?>
                    <!-- <li><a href="settings.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'active' : ''; ?>">Cài đặt</a></li> -->
                </ul>
            </nav>
            <div class="user-info">
                <span>Chào, <?php echo htmlspecialchars($loggedInUsername); ?>!</span>
                <a href="actions/logout.php" class="logout-btn">Đăng xuất</a>
            </div>
        </div>
    </header>
    <main>
        <div class="container page-content">