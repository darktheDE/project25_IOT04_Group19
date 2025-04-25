<?php
// File: actions/get_latest_logs.php
require_once '../includes/db_connect.php'; // Đường dẫn ../ để quay lại thư mục cha rồi vào includes

header('Content-Type: application/json');

$logs = [];
$limit = 5; // Lấy 5 log gần nhất

$sql = "SELECT tag_uid, tag_name_at_scan, access_result, scan_time
        FROM scan_logs
        ORDER BY scan_time DESC
        LIMIT ?";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $limit);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
    } else { error_log("Get Logs Execute Error: " . $stmt->error); }
    $stmt->close();
} else { error_log("Get Logs Prepare Error: " . $conn->error); }

echo json_encode($logs); // Trả về dữ liệu dạng JSON
?>