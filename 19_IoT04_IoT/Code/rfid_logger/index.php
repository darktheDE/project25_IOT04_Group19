<?php
$pageTitle = "Dashboard";
require_once 'includes/db_connect.php';
require_once 'includes/header.php';

// Lấy thông tin truy cập cuối cùng
$lastLog = null;
$sqlLast = "SELECT tag_uid, tag_name_at_scan, access_result, scan_time
            FROM scan_logs
            ORDER BY scan_time DESC
            LIMIT 1";
$resultLast = $conn->query($sqlLast);
if ($resultLast && $resultLast->num_rows > 0) {
    $lastLog = $resultLast->fetch_assoc();
}

// --- Dữ liệu trạng thái placeholder (Cần logic cập nhật thực tế) ---
$doorStatusText = "Chưa rõ"; $doorStatusClass = "status-secondary";
$deviceStatusText = "Offline"; $deviceStatusClass = "status-danger";
// Bạn cần cách để NodeMCU cập nhật trạng thái này (ví dụ: qua MQTT hoặc request định kỳ)
// --------------------------------------------------------------------

?>

<h1><?php echo $pageTitle; ?></h1>

<div class="dashboard-grid">
    <div class="card card-status"> <!-- Thêm class để styling -->
        <h2>Trạng Thái Hệ Thống</h2>
        <div class="status-item">Trạng thái cửa: <span class="status-text <?php echo $doorStatusClass; ?>"><?php echo $doorStatusText; ?></span></div>
        <div class="status-item">Kết nối thiết bị: <span class="status-text <?php echo $deviceStatusClass; ?>"><?php echo $deviceStatusText; ?></span></div>
        <hr style="border-top: 1px dashed var(--border-color); margin: 15px 0;">
        <div class="status-item">Truy cập cuối:
            <?php if ($lastLog): ?>
                <strong><?php echo htmlspecialchars($lastLog['tag_name_at_scan'] ? $lastLog['tag_name_at_scan'] : $lastLog['tag_uid']); ?></strong>
                <br>
                <small>Lúc: <?php echo date("H:i:s d/m/Y", strtotime($lastLog['scan_time'])); ?> -
                <?php
                    $resultText = htmlspecialchars($lastLog['access_result']);
                    $resultClass = 'status-secondary'; // Default
                    if ($lastLog['access_result'] == 'AUTHORIZED') { $resultText = 'Thành công'; $resultClass = 'status-success'; }
                    elseif ($lastLog['access_result'] == 'UNAUTHORIZED') { $resultText = 'Từ chối'; $resultClass = 'status-danger'; }
                    elseif (strpos($lastLog['access_result'], 'ERROR') !== false) { $resultText = 'Lỗi'; $resultClass = 'status-warning';}
                ?>
                <span class="status-text <?php echo $resultClass; ?>"><?php echo $resultText; ?></span>
                </small>
            <?php else: ?>
                <span>Chưa có dữ liệu</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="card card-control">
        <h2>Điều Khiển Từ Xa</h2>
        <p style="text-align: center; color: var(--secondary-color); margin-bottom: 20px;">Nhấn nút bên dưới để gửi yêu cầu mở cửa ngay lập tức.</p>
        <button id="remoteOpenBtn" class="remote-open-button">🔓 MỞ CỬA</button>
        <div id="remote-message"></div>
    </div>

    <div class="card card-logs">
        <h2>Hoạt Động Gần Đây</h2>
        <div class="log-list-container">
            <ul id="log-list" class="log-list">
                <li>Đang tải dữ liệu...</li>
            </ul>
        </div>
         <div id="notification-area" style="font-size: 0.9em; color: var(--secondary-color); text-align: center; margin-top:10px;"></div>
    </div>
</div>


<?php require_once 'includes/footer.php'; ?>