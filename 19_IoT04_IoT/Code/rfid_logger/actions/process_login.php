<?php
// File: actions/process_login.php
session_start(); // Luôn bắt đầu session trước
require_once '../includes/db_connect.php'; // Đường dẫn ../ để quay lại

$login_error = "Thông tin đăng nhập không hợp lệ hoặc tài khoản bị khóa."; // Lỗi mặc định

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $login_error = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.";
    } else {
        // Sử dụng prepared statement để chống SQL Injection
        $stmt = $conn->prepare("SELECT user_id, username, password_hash, role, is_active FROM web_users WHERE username = ? LIMIT 1");

        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Kiểm tra tài khoản có active không
                if ($user['is_active'] == 1) {
                    // *** Kiểm tra mật khẩu đã mã hóa ***
                    if (password_verify($password, $user['password_hash'])) {
                        // --- Đăng nhập thành công ---
                        session_regenerate_id(true); // Tạo session ID mới để tăng bảo mật

                        // Lưu thông tin cần thiết vào session
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role']; // Lưu vai trò

                        // Chuyển hướng đến trang dashboard
                        header("Location: ../index.php");
                        $stmt->close();
                        $conn->close();
                        exit(); // Dừng script sau khi chuyển hướng

                    } else {
                        // Sai mật khẩu -> thông báo lỗi chung
                        $login_error = "Tài khoản hoặc mật khẩu không đúng.";
                    }
                } else {
                    // Tài khoản bị khóa
                    $login_error = "Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.";
                }
            } else {
                // Không tìm thấy username -> thông báo lỗi chung
                 $login_error = "Tài khoản hoặc mật khẩu không đúng.";
            }
            $stmt->close();
        } else {
             error_log("Login Prepare Error: " . $conn->error);
             $login_error = "Lỗi hệ thống. Vui lòng thử lại sau.";
        }
    }
} else {
    // Nếu không phải POST request hoặc thiếu dữ liệu
    $login_error = "Yêu cầu không hợp lệ.";
}

// --- Đăng nhập thất bại ---
$_SESSION['login_error'] = $login_error; // Lưu lỗi vào session
header("Location: ../login.php"); // Chuyển hướng về trang đăng nhập
$conn->close();
exit();

?>