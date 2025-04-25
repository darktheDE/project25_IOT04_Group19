<?php
// File: actions/admin_toggle_status.php
session_start();
require_once '../includes/db_connect.php';

// --- KIỂM TRA ĐĂNG NHẬP VÀ QUYỀN ADMIN ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "Lỗi: Không có quyền thực hiện hành động này.";
    $_SESSION['message_type'] = 'error';
    header('Location: ../index.php');
    exit;
}

// --- Lấy ID người dùng cần thay đổi trạng thái ---
$user_id_to_toggle = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($user_id_to_toggle === false || $user_id_to_toggle === null) {
     $_SESSION['message'] = "Lỗi: ID người dùng không hợp lệ.";
     $_SESSION['message_type'] = 'error';
     header('Location: ../admin_manage_users.php');
     exit;
}

// --- Ngăn chặn tự khóa tài khoản ---
if ($user_id_to_toggle == $_SESSION['user_id']) {
    $_SESSION['message'] = "Lỗi: Bạn không thể tự khóa tài khoản của chính mình.";
    $_SESSION['message_type'] = 'error';
    header('Location: ../admin_manage_users.php');
    exit;
}

// --- Lấy trạng thái hiện tại và cập nhật ---
// Dùng transaction để đảm bảo đọc và ghi diễn ra gần như đồng thời
$conn->begin_transaction();
try {
    // Lấy trạng thái hiện tại
    $stmt_get = $conn->prepare("SELECT is_active, username FROM web_users WHERE user_id = ? FOR UPDATE"); // FOR UPDATE để khóa dòng
    if (!$stmt_get) throw new Exception("Lỗi chuẩn bị lấy trạng thái: " . $conn->error);
    $stmt_get->bind_param("i", $user_id_to_toggle);
    if (!$stmt_get->execute()) throw new Exception("Lỗi thực thi lấy trạng thái: " . $stmt_get->error);
    $result = $stmt_get->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Không tìm thấy tài khoản có ID " . $user_id_to_toggle . ".");
    }

    $user = $result->fetch_assoc();
    $current_status = $user['is_active'];
    $username_toggled = $user['username'];
    $stmt_get->close();

    // Tính trạng thái mới (đảo ngược)
    $new_status = ($current_status == 1) ? 0 : 1;
    $action_text = ($new_status == 1) ? "kích hoạt" : "khóa";

    // Cập nhật trạng thái mới
    $stmt_update = $conn->prepare("UPDATE web_users SET is_active = ? WHERE user_id = ?");
     if (!$stmt_update) throw new Exception("Lỗi chuẩn bị cập nhật trạng thái: " . $conn->error);
    $stmt_update->bind_param("ii", $new_status, $user_id_to_toggle);
    if (!$stmt_update->execute()) throw new Exception("Lỗi thực thi cập nhật trạng thái: " . $stmt_update->error);

    // Commit transaction nếu thành công
    $conn->commit();
    $_SESSION['message'] = "Đã " . $action_text . " tài khoản '" . htmlspecialchars($username_toggled) . "' thành công.";
    $_SESSION['message_type'] = 'success';
    $stmt_update->close();

} catch (Exception $e) {
    // Rollback nếu có lỗi
    $conn->rollback();
    $_SESSION['message'] = "Lỗi khi thay đổi trạng thái: " . $e->getMessage();
    $_SESSION['message_type'] = 'error';
    error_log("Toggle User Status Error: " . $e->getMessage());
}


$conn->close();
header('Location: ../admin_manage_users.php'); // Quay về trang danh sách
exit;
?>