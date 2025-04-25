<?php
// File: trigger_open.php
// Nhiệm vụ: Cập nhật lệnh mở cửa trong database khi nút trên web được nhấn.

header("Content-Type: text/plain");

// --- Thông tin Database ---
$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "rfid_system";

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    echo "Lỗi kết nối DB!";
    exit();
}

// --- Cập nhật lệnh trong bảng door_commands ---
// Chỉ cập nhật dòng có id=1 (hoặc dòng duy nhất bạn có)
$stmt = $conn->prepare("UPDATE door_commands SET command = 'OPEN_REQUEST' WHERE id = 1");

if ($stmt) {
    if ($stmt->execute()) {
        // Kiểm tra xem có dòng nào được cập nhật không
        if ($stmt->affected_rows > 0) {
             echo "Yêu cầu mở cửa đã được gửi!";
        } else {
             // Có thể là do không có dòng nào với id=1
             echo "Không tìm thấy bản ghi lệnh để cập nhật.";
             // Thử INSERT nếu không có dòng id=1
             $conn->query("INSERT IGNORE INTO door_commands (id, command) VALUES (1, 'OPEN_REQUEST')");
             // Kiểm tra lại lần nữa nếu cần
        }
    } else {
        echo "Lỗi khi thực thi cập nhật lệnh!";
        error_log("DB Update Command Error: " . $stmt->error);
    }
    $stmt->close();
} else {
    echo "Lỗi khi chuẩn bị cập nhật lệnh!";
    error_log("DB Update Command Prepare Error: " . $conn->error);
}

$conn->close();
?>