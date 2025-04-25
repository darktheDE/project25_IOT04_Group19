<?php
$pageTitle = "Quản lý Tài Khoản Web";
require_once 'includes/db_connect.php';
require_once 'includes/header.php'; // Header đã kiểm tra đăng nhập

// --- KIỂM TRA QUYỀN ADMIN ---
if ($loggedInUserRole !== 'admin') {
    echo "<h1>Truy cập bị từ chối</h1><p>Bạn không có quyền truy cập trang này.</p>";
    require_once 'includes/footer.php';
    exit;
}

// Xử lý thông báo từ session (sau khi thêm/sửa/xóa)
$message = '';
$message_type = ''; // 'success' hoặc 'error'
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'info';
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}


// Lấy danh sách người dùng web
$web_users = [];
$sql = "SELECT user_id, username, full_name, role, is_active, created_at FROM web_users ORDER BY username ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $web_users[] = $row;
    }
}
?>

<h1><?php echo $pageTitle; ?></h1>

<!-- Hiển thị thông báo (nếu có) -->
<?php if ($message): ?>
<div class="message <?php echo $message_type === 'success' ? 'message-success' : 'message-error'; ?>">
    <?php echo htmlspecialchars($message); ?>
</div>
<style>
    .message { padding: 15px; margin-bottom: 20px; border-radius: var(--border-radius); border: 1px solid; }
    .message-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
    .message-error { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
</style>
<script>
    // Tự động ẩn thông báo sau 5 giây
    setTimeout(() => {
        const msgDiv = document.querySelector('.message');
        if (msgDiv) { msgDiv.style.display = 'none'; }
    }, 5000);
</script>
<?php endif; ?>


<div class="add-button-container">
     <a href="admin_add_user.php" class="add-btn">Thêm Tài Khoản Mới</a>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên Đăng Nhập</th>
                <th>Tên Đầy Đủ</th>
                <th>Vai Trò</th>
                <th>Trạng Thái</th>
                <th>Ngày Tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($web_users) > 0): ?>
                <?php foreach ($web_users as $user): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['full_name'] ? $user['full_name'] : '-'); ?></td>
                        <td><?php echo ucfirst($user['role']); // Viết hoa chữ đầu ?></td>
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span class="status-text status-success">Hoạt động</span>
                            <?php else: ?>
                                <span class="status-text status-secondary">Đã khóa</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date("d/m/Y H:i", strtotime($user['created_at'])); ?></td>
                        <td class="action-buttons">
                            <!-- Nút Sửa (Tùy chọn) -->
                            <!-- <a href="admin_edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn-edit">Sửa</a> -->

                            <?php // Không cho phép tự khóa hoặc xóa chính mình ?>
                            <?php if ($user['user_id'] != $loggedInUserId): ?>
                                <a href="actions/admin_toggle_status.php?id=<?php echo $user['user_id']; ?>"
                                   class="btn-toggle"
                                   onclick="return confirm('Bạn có chắc muốn <?php echo $user['is_active'] ? 'KHÓA' : 'KÍCH HOẠT'; ?> tài khoản \'<?php echo htmlspecialchars($user['username']); ?>\'?')">
                                   <?php echo $user['is_active'] ? 'Khóa' : 'Kích hoạt'; ?>
                                </a>
                                <a href="actions/admin_delete_user.php?id=<?php echo $user['user_id']; ?>"
                                   class="btn-delete"
                                   onclick="return confirm('CẢNH BÁO!\nBạn có chắc muốn XÓA VĨNH VIỄN tài khoản \'<?php echo htmlspecialchars($user['username']); ?>\' không?\nHành động này không thể hoàn tác!')">
                                   Xóa
                                </a>
                            <?php else: ?>
                                <span style="color: var(--secondary-color); font-size: 0.85em;">(Tài khoản hiện tại)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: var(--secondary-color);">Chưa có tài khoản người dùng web nào.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>