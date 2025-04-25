<?php
// File: actions/logout.php
session_start(); // Bắt đầu session để truy cập và hủy nó

// Hủy tất cả các biến session
$_SESSION = array();

// Nếu dùng cookie session, xóa cả cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy session hoàn toàn
session_destroy();

// Chuyển hướng về trang đăng nhập với thông báo
header("Location: ../login.php?logout=1");
exit();
?>