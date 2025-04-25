<?php
$pageTitle = "Danh Sách Thẻ RFID";
require_once 'includes/db_connect.php';
require_once 'includes/header.php';
// --- KIỂM TRA QUYỀN ADMIN ---
if ($loggedInUserRole !== 'admin') {
    // Nếu không phải admin, hiển thị thông báo và dừng lại
    echo "<h1>Truy cập bị từ chối</h1>";
    echo "<p>Bạn không có quyền truy cập trang này.</p>";
    require_once 'includes/footer.php';
    exit; // Dừng thực thi phần còn lại của trang
}

$tags = [];
$sql = "SELECT id, uid, name, is_active FROM tags ORDER BY name ASC, id ASC"; // Sắp xếp theo tên
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $tags[] = $row;
    }
}
?>

<h1><?php echo $pageTitle; ?></h1>

<div class="add-button-container">
     <!-- Nút Thêm Mới (chưa có chức năng) -->
     <!-- <a href="actions/add_user_form.php" class="add-btn">Thêm Thẻ Mới</a> -->
     <button class="add-btn" disabled title="Chức năng đang phát triển">Thêm Thẻ Mới</button>
</div>


<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên Gán</th>
                <th>UID Thẻ</th>
                <th>Trạng Thái</th>
                <!-- <th>Hành động</th> -->
            </tr>
        </thead>
        <tbody>
            <?php if (count($tags) > 0): ?>
                <?php foreach ($tags as $tag): ?>
                    <tr>
                        <td><?php echo $tag['id']; ?></td>
                        <td><?php echo htmlspecialchars($tag['name'] ? $tag['name'] : '<i>Chưa đặt tên</i>'); ?></td>
                        <td><code><?php echo htmlspecialchars($tag['uid']); ?></code></td>
                        <td>
                            <?php if ($tag['is_active']): ?>
                                <span class="status-text status-success">Hoạt động</span>
                            <?php else: ?>
                                <span class="status-text status-secondary">Đã khóa</span>
                            <?php endif; ?>
                        </td>
                       <!-- <td class="action-buttons"> -->
                            <!-- Các nút Sửa/Xóa/Khóa (chưa có chức năng) -->
                            <!-- <a href="#" class="btn-edit">Sửa</a> -->
                            <!-- <a href="#" class="btn-toggle"><?php echo $tag['is_active'] ? 'Khóa' : 'Mở'; ?></a> -->
                            <!-- <a href="#" class="btn-delete">Xóa</a> -->
                       <!-- </td> -->
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px; color: var(--secondary-color);">Chưa có thẻ nào được thêm vào hệ thống.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>