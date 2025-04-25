<?php
// File: check_command.php
// Nhiệm vụ: NodeMCU gọi file này để xem có yêu cầu mở cửa từ web không.
// Nếu có, trả về "OPEN_REQUESTED" và reset lệnh về "IDLE".

header("Content-Type: text/plain");

// --- Thông tin Database ---
$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "rfid_system";

$response = "NO_REQUEST"; // Mặc định không có yêu cầu

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    // Không trả về lỗi chi tiết cho NodeMCU, chỉ log lại
    error_log("DB Connection Error (check_command): " . $conn->connect_error);
    // Có thể trả về mã lỗi riêng nếu NodeMCU cần xử lý
    // echo "ERROR_DB";
    exit();
}

// --- Kiểm tra lệnh trong bảng door_commands (dòng có id=1) ---
$stmt_check = $conn->prepare("SELECT command FROM door_commands WHERE id = 1 LIMIT 1");
if ($stmt_check) {
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['command'] == 'OPEN_REQUEST') {
            $response = "OPEN_REQUESTED"; // Có yêu cầu!

            // *** QUAN TRỌNG: Reset lệnh về IDLE ngay sau khi đọc ***
            $stmt_reset = $conn->prepare("UPDATE door_commands SET command = 'IDLE' WHERE id = 1");
            if ($stmt_reset) {
                $stmt_reset->execute();
                $stmt_reset->close();
            } else {
                error_log("DB Reset Command Prepare Error: " . $conn->error);
            }
        }
    }
    $stmt_check->close();
} else {
     error_log("DB Check Command Prepare Error: " . $conn->error);
}

$conn->close();

// Trả về kết quả cho NodeMCU
echo $response;
?>