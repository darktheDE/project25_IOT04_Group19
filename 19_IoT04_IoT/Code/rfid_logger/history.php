<?php
$pageTitle = "Lịch sử Truy cập";
require_once 'includes/db_connect.php';
require_once 'includes/header.php';

// Lấy dữ liệu lịch sử (giới hạn 50 dòng gần nhất)
$logs = [];
$sql = "SELECT log_id, tag_uid, tag_name_at_scan, access_result, scan_time
        FROM scan_logs
        ORDER BY scan_time DESC LIMIT 50"; // Lấy 50 dòng mới nhất

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
     while($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}
?>

<h1><?php echo $pageTitle; ?></h1>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID Log</th>
                <th>Thời Gian</th>
                <th>UID Thẻ</th>
                <th>Tên (Khi quét)</th>
                <th>Kết quả</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($logs) > 0): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo $log['log_id']; ?></td>
                        <td><?php echo date("d/m/Y H:i:s", strtotime($log['scan_time'])); ?></td>
                        <td><code><?php echo htmlspecialchars($log['tag_uid']); ?></code></td>
                        <td><?php echo htmlspecialchars($log['tag_name_at_scan'] ? $log['tag_name_at_scan'] : '-'); ?></td>
                        <td>
                            <?php
                                $resultText = htmlspecialchars($log['access_result']);
                                $resultClass = 'status-secondary'; // Default class
                                if ($log['access_result'] == 'AUTHORIZED') {
                                    $resultText = 'Thành công'; $resultClass = 'status-success';
                                } elseif ($log['access_result'] == 'UNAUTHORIZED') {
                                    $resultText = 'Bị từ chối'; $resultClass = 'status-danger';
                                } elseif (strpos($log['access_result'], 'ERROR') !== false) {
                                   $resultText = 'Lỗi hệ thống'; $resultClass = 'status-warning';
                                }
                            ?>
                            <span class="status-text <?php echo $resultClass; ?>"><?php echo $resultText; ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                     <td colspan="5" style="text-align: center; padding: 20px; color: var(--secondary-color);">Không có dữ liệu lịch sử.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<?php require_once 'includes/footer.php'; ?>