<?php
// File: actions/admin_delete_user.php
session_start();
require_once '../includes/db_connect.php';

// --- KIỂM TRA ĐĂNG NHẬP VÀ QUYỀN ADMIN ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "Lỗi: Không có quyền thực hiện hành động này.";
    $_SESSION['message_type'] = 'error';
    header('Location: ../index.php');
    exit;
}

// --- Lấy ID người dùng cần xóa ---
$user_id_to_delete = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($user_id_to_delete === false || $user_id_to_delete === null) {
     $_SESSION['message'] = "Lỗi: ID người dùng không hợp lệ.";
     $_SESSION['message_type'] = 'error';
     header('Location: ../admin_manage_users.php');
     exit;
}

// --- Ngăn chặn tự xóa tài khoản ---
if ($user_id_to_delete == $_SESSION['user_id']) {
    $_SESSION['message'] = "Lỗi: Bạn không thể tự xóa tài khoản của chính mình.";
    $_SESSION['message_type'] = 'error';
    header('Location: ../admin_manage_users.php');
    exit;
}

// --- Thực hiện xóa ---
$stmt = $conn->prepare("DELETE FROM web_users WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id_to_delete);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
             $_SESSION['message'] = "Đã xóa tài khoản thành công (ID: " . $user_id_to_delete . ").";
             $_SESSION['message_type'] = 'success';
        } else {
             $_SESSION['message'] = "Không tìm thấy tài khoản có ID " . $user_id_to_delete . " để xóa.";
             $_SESSION['message_type'] = 'warning'; // Dùng warning thay vì error
        }
    } else {
         $_SESSION['message'] = "Lỗi khi xóa tài khoản: " . $stmt->error;
         $_SESSION['message_type'] = 'error';
         error_log("Delete User Execute Error: " . $stmt->error);
    }
    $stmt->close();
} else {
    $_SESSION['message'] = "Lỗi khi chuẩn bị xóa tài khoản: " . $conn->error;
    $_SESSION['message_type'] = 'error';
    error_log("Delete User Prepare Error: " . $conn->error);
}

$conn->close();
header('Location: ../admin_manage_users.php'); // Quay về trang danh sách
exit;
?>