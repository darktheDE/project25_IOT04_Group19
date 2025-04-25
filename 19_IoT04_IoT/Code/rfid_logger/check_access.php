<?php
// File: check_access.php
// Nhiệm vụ: Nhận UID từ NodeMCU, kiểm tra quyền truy cập trong DB,
//           ghi log kết quả, và trả về "AUTHORIZED" hoặc "UNAUTHORIZED".

header("Content-Type: text/plain"); // Luôn trả về dạng text đơn giản

// --- Thông tin Kết nối Database ---
// !!! Quan trọng: Trong môi trường thực tế, không nên dùng user 'root' không mật khẩu.
// Hãy tạo user riêng với quyền hạn chế cho ứng dụng này.
$dbHost = "localhost";    // Thường là localhost khi chạy XAMPP trên cùng máy
$dbUser = "root";         // User mặc định của XAMPP
$dbPass = "";             // Mật khẩu mặc định của XAMPP (thường là trống)
$dbName = "rfid_system";  // Tên database bạn đã tạo

// --- Kết nối Database ---
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Kiểm tra lỗi kết nối
if ($conn->connect_error) {
    // Ghi lỗi vào log hệ thống (an toàn hơn là echo lỗi chi tiết)
    error_log("Database Connection Error: (" . $conn->connect_errno . ") " . $conn->connect_error);
    echo "ERROR_DB_CONN"; // Phản hồi lỗi chung cho NodeMCU
    exit(); // Dừng script
}

// --- Khởi tạo các biến ---
$received_uid = null;
$tag_name = null; // Sẽ lấy tên thẻ nếu tìm thấy
$access_result = "UNAUTHORIZED"; // Mặc định là không được phép

// --- Xử lý Request từ NodeMCU ---
if (isset($_GET['uid'])) {
    // Lấy UID từ tham số URL (?uid=XXXXX)
    $received_uid = $_GET['uid'];

    // *** Bước 1: Kiểm tra quyền truy cập trong bảng 'tags' ***
    // Chuẩn bị câu lệnh SQL để tránh SQL Injection
    $stmt_check = $conn->prepare("SELECT name FROM tags WHERE uid = ? AND is_active = 1 LIMIT 1");

    if ($stmt_check) {
        // Gán giá trị UID vào câu lệnh (s = string)
        $stmt_check->bind_param("s", $received_uid);

        // Thực thi câu lệnh kiểm tra
        if ($stmt_check->execute()) {
            // Lấy kết quả
            $result = $stmt_check->get_result();

            // Kiểm tra xem có tìm thấy dòng nào không
            if ($result->num_rows > 0) {
                // Tìm thấy thẻ hợp lệ và đang được kích hoạt (is_active = 1)
                $access_result = "AUTHORIZED"; // Thay đổi kết quả thành được phép
                $row = $result->fetch_assoc();
                $tag_name = $row['name']; // Lấy tên thẻ để ghi log (nếu có)
            } else {
                // Không tìm thấy UID này hoặc thẻ này không active (is_active = 0)
                $access_result = "UNAUTHORIZED"; // Kết quả vẫn là không được phép
            }
        } else {
            // Lỗi khi thực thi câu lệnh kiểm tra
            error_log("DB Check Execute Error: " . $stmt_check->error);
            $access_result = "ERROR_DB_CHECK_EXEC";
        }
        // Đóng câu lệnh kiểm tra
        $stmt_check->close();
    } else {
        // Lỗi khi chuẩn bị câu lệnh kiểm tra
        error_log("DB Check Prepare Error: " . $conn->error);
        $access_result = "ERROR_DB_CHECK_PREPARE";
    }

    // *** Bước 2: Ghi log kết quả truy cập vào bảng 'scan_logs' ***
    // Dù thành công hay thất bại, chúng ta vẫn nên ghi log lại
    $stmt_log = $conn->prepare("INSERT INTO scan_logs (tag_uid, tag_name_at_scan, access_result, scan_time) VALUES (?, ?, ?, NOW())");

    if ($stmt_log) {
        // Gán các giá trị vào câu lệnh log (s = string)
        $stmt_log->bind_param("sss", $received_uid, $tag_name, $access_result);

        // Thực thi câu lệnh ghi log
        if (!$stmt_log->execute()) {
            // Nếu ghi log thất bại, chỉ ghi lỗi vào hệ thống, không thay đổi phản hồi chính cho NodeMCU
            error_log("Failed to log RFID scan: UID=" . $received_uid . ", Result=" . $access_result . ", Error: " . $stmt_log->error);
        }
        // Đóng câu lệnh log
        $stmt_log->close();
    } else {
        // Lỗi khi chuẩn bị câu lệnh log
        error_log("Failed to prepare log statement for UID: " . $received_uid . ", Error: " . $conn->error);
    }

} else {
    // Nếu không có tham số 'uid' trong URL
    $access_result = "ERROR_NO_UID";
    // Cũng nên ghi log lỗi này nếu cần theo dõi
    error_log("Access attempt without UID parameter.");
    // Ghi log vào DB nếu muốn (nhưng UID sẽ là null)
    // $stmt_log_no_uid = $conn->prepare("INSERT INTO scan_logs (tag_uid, access_result, scan_time) VALUES (NULL, ?, NOW())");
    // ... (bind và execute)
}

// --- Đóng kết nối Database ---
$conn->close();

// --- Gửi phản hồi cuối cùng về cho NodeMCU ---
// Chỉ in ra "AUTHORIZED", "UNAUTHORIZED", hoặc mã lỗi dạng ERROR_...
echo $access_result;

?>