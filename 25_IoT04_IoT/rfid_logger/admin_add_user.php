<?php
$pageTitle = "Thêm Tài Khoản Web Mới";
require_once 'includes/db_connect.php';
require_once 'includes/header.php'; // Header đã kiểm tra đăng nhập

// --- KIỂM TRA QUYỀN ADMIN ---
if ($loggedInUserRole !== 'admin') {
    echo "<h1>Truy cập bị từ chối</h1><p>Bạn không có quyền truy cập trang này.</p>";
    require_once 'includes/footer.php';
    exit;
}

// Xử lý thông báo lỗi nếu có từ lần submit trước
$error_message = '';
if (isset($_SESSION['form_error'])) {
    $error_message = $_SESSION['form_error'];
    unset($_SESSION['form_error']);
}
// Giữ lại dữ liệu đã nhập nếu có lỗi (tùy chọn)
$old_username = $_SESSION['old_input']['username'] ?? '';
$old_fullname = $_SESSION['old_input']['fullname'] ?? '';
$old_role = $_SESSION['old_input']['role'] ?? 'user';
$old_is_active = $_SESSION['old_input']['is_active'] ?? 1;
unset($_SESSION['old_input']);

?>

<h1><?php echo $pageTitle; ?></h1>

<a href="admin_manage_users.php" style="display: inline-block; margin-bottom: 20px; color: var(--primary-light); text-decoration: none;">« Quay lại Danh sách</a>

<?php if ($error_message): ?>
<div class="message message-error">
    <?php echo nl2br(htmlspecialchars($error_message)); // nl2br để xuống dòng nếu lỗi có nhiều dòng ?>
</div>
<style> /* CSS cho message đã có trong admin_manage_users.php */ </style>
<?php endif; ?>


<form action="actions/admin_save_user.php" method="POST" id="addUserForm">
    <div class="form-group">
        <label for="username">Tên đăng nhập <span style="color:red;">*</span></label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($old_username); ?>" required>
    </div>
    <div class="form-group">
        <label for="full_name">Tên đầy đủ</label>
        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($old_fullname); ?>">
    </div>
     <div class="form-group">
        <label for="password">Mật khẩu <span style="color:red;">*</span></label>
        <input type="password" id="password" name="password" required autocomplete="new-password">
    </div>
     <div class="form-group">
        <label for="confirm_password">Xác nhận mật khẩu <span style="color:red;">*</span></label>
        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
        <div id="password_match_error" style="color: var(--danger-color); font-size: 0.9em; margin-top: 5px; display: none;">Mật khẩu không khớp!</div>
    </div>
     <div class="form-group">
        <label for="role">Vai trò <span style="color:red;">*</span></label>
        <select id="role" name="role" required>
            <option value="user" <?php echo ($old_role == 'user') ? 'selected' : ''; ?>>User (Người dùng thường)</option>
            <option value="admin" <?php echo ($old_role == 'admin') ? 'selected' : ''; ?>>Admin (Quản trị viên)</option>
        </select>
    </div>
    <div class="form-group">
        <label for="is_active">Trạng thái <span style="color:red;">*</span></label>
        <select id="is_active" name="is_active" required>
            <option value="1" <?php echo ($old_is_active == 1) ? 'selected' : ''; ?>>Hoạt động</option>
            <option value="0" <?php echo ($old_is_active == 0) ? 'selected' : ''; ?>>Khóa</option>
        </select>
    </div>
    <div class="form-group">
        <button type="submit" class="add-btn" style="width: auto;">Lưu Tài Khoản</button>
    </div>
</form>

<!-- JavaScript để kiểm tra mật khẩu khớp -->
<script>
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const errorDiv = document.getElementById('password_match_error');
    const form = document.getElementById('addUserForm');

    function validatePassword() {
        if (passwordInput.value !== confirmPasswordInput.value) {
            errorDiv.style.display = 'block';
            return false;
        } else {
            errorDiv.style.display = 'none';
            return true;
        }
    }

    passwordInput.addEventListener('keyup', validatePassword);
    confirmPasswordInput.addEventListener('keyup', validatePassword);

    form.addEventListener('submit', function(event) {
        if (!validatePassword()) {
            event.preventDefault(); // Ngăn form submit nếu mật khẩu không khớp
            alert('Mật khẩu xác nhận không khớp. Vui lòng kiểm tra lại.');
        }
    });
</script>


<?php require_once 'includes/footer.php'; ?>