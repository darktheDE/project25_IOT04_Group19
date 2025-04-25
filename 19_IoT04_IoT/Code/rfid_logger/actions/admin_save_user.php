<?php
// File: actions/admin_save_user.php
session_start();
require_once '../includes/db_connect.php';

// --- KIỂM TRA ĐĂNG NHẬP VÀ QUYỀN ADMIN ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "Lỗi: Không có quyền thực hiện hành động này.";
    $_SESSION['message_type'] = 'error';
    header('Location: ../index.php'); // Chuyển về dashboard
    exit;
}

// Chỉ xử lý nếu là POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user'; // Mặc định là user nếu không có
    $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0; // Mặc định là khóa nếu không có

    // --- Validate Dữ Liệu ---
    $errors = [];
    if (empty($username)) { $errors[] = "Tên đăng nhập không được để trống."; }
    if (strlen($username) < 3) { $errors[] = "Tên đăng nhập phải có ít nhất 3 ký tự."; }
    if (empty($password)) { $errors[] = "Mật khẩu không được để trống."; }
    if (strlen($password) < 6) { $errors[] = "Mật khẩu phải có ít nhất 6 ký tự."; } // Nên có yêu cầu độ phức tạp cao hơn
    if ($password !== $confirm_password) { $errors[] = "Mật khẩu xác nhận không khớp."; }
    if (!in_array($role, ['admin', 'user'])) { $errors[] = "Vai trò không hợp lệ."; }
    if (!in_array($is_active, [0, 1])) { $errors[] = "Trạng thái không hợp lệ."; }

    // Kiểm tra username đã tồn tại chưa
    if (empty($errors)) {
        $stmt_check = $conn->prepare("SELECT user_id FROM web_users WHERE username = ?");
        if ($stmt_check) {
            $stmt_check->bind_param("s", $username);
            $stmt_check->execute();
            $stmt_check->store_result(); // Cần thiết để lấy num_rows
            if ($stmt_check->num_rows > 0) {
                $errors[] = "Tên đăng nhập '" . htmlspecialchars($username) . "' đã tồn tại.";
            }
            $stmt_check->close();
        } else {
            $errors[] = "Lỗi kiểm tra tên đăng nhập.";
            error_log("Check Username Prepare Error: " . $conn->error);
        }
    }

    // --- Nếu không có lỗi thì tiến hành lưu ---
    if (empty($errors)) {
        // Mã hóa mật khẩu
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        if ($password_hash === false) {
             $_SESSION['message'] = "Lỗi nghiêm trọng: Không thể mã hóa mật khẩu.";
             $_SESSION['message_type'] = 'error';
             header('Location: ../admin_add_user.php'); // Quay lại form
             exit;
        }

        // Chuẩn bị câu lệnh INSERT
        $stmt_insert = $conn->prepare("INSERT INTO web_users (username, password_hash, full_name, role, is_active) VALUES (?, ?, ?, ?, ?)");
        if ($stmt_insert) {
            $stmt_insert->bind_param("ssssi", $username, $password_hash, $fullname, $role, $is_active);

            if ($stmt_insert->execute()) {
                // Thành công
                $_SESSION['message'] = "Đã thêm tài khoản '" . htmlspecialchars($username) . "' thành công!";
                $_SESSION['message_type'] = 'success';
                header('Location: ../admin_manage_users.php'); // Chuyển về trang danh sách
                exit;
            } else {
                // Lỗi thực thi
                $_SESSION['message'] = "Lỗi khi lưu tài khoản vào database: " . $stmt_insert->error;
                $_SESSION['message_type'] = 'error';
                error_log("Save User Execute Error: " . $stmt_insert->error);
            }
            $stmt_insert->close();
        } else {
            // Lỗi chuẩn bị
             $_SESSION['message'] = "Lỗi khi chuẩn bị lưu tài khoản: " . $conn->error;
             $_SESSION['message_type'] = 'error';
             error_log("Save User Prepare Error: " . $conn->error);
        }

    } else {
        // Có lỗi validate -> Lưu lỗi và dữ liệu cũ vào session, quay lại form
        $_SESSION['form_error'] = implode("<br>", $errors); // Nối các lỗi lại
        $_SESSION['old_input'] = $_POST; // Lưu lại những gì người dùng đã nhập
        header('Location: ../admin_add_user.php');
        exit;
    }

} else {
    // Nếu không phải POST request
    $_SESSION['message'] = "Yêu cầu không hợp lệ.";
    $_SESSION['message_type'] = 'error';
    header('Location: ../admin_manage_users.php');
    exit;
}

// Đóng kết nối (dù thực tế các lệnh exit ở trên đã dừng script)
$conn->close();
?>