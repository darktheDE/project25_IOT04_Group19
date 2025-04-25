<?php
header("Content-Type: text/plain"); // Trả về text đơn giản

// Thông tin kết nối Database
$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "rfid_system"; // Tên database bạn đã tạo

// --- Kết nối Database ---
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    echo "ERROR_DB_CONN";
    exit();
}

// --- Lấy UID từ request GET ---
if (isset($_GET['uid'])) {
    $received_uid = $_GET['uid'];
    $tag_name = null; // Khởi tạo tên thẻ là null

    // --- (Tùy chọn) Lấy tên thẻ từ bảng 'tags' ---
    $stmt_find = $conn->prepare("SELECT name FROM tags WHERE uid = ?");
    if ($stmt_find) {
        $stmt_find->bind_param("s", $received_uid);
        if ($stmt_find->execute()) {
            $result = $stmt_find->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $tag_name = $row['name']; // Lấy tên thẻ nếu tìm thấy
            }
        }
        $stmt_find->close();
        // Không cần báo lỗi nếu không tìm thấy tên, chỉ cần ghi log với tên là NULL
    } else {
         // Có thể log lỗi prepare ở đây nếu cần debug
    }


    // --- Chuẩn bị câu lệnh INSERT vào bảng 'scan_logs' ---
    $stmt_insert = $conn->prepare("INSERT INTO scan_logs (tag_uid, tag_name_at_scan, scan_time) VALUES (?, ?, NOW())");
    if ($stmt_insert === false) {
        echo "ERROR_DB_PREPARE_INSERT";
        $conn->close();
        exit();
    }

    // Bind tham số (UID và Tên thẻ đã lấy được hoặc NULL)
    // Chú ý: NOW() được gọi trực tiếp trong SQL
    $stmt_insert->bind_param("ss", $received_uid, $tag_name);

    // --- Thực thi câu lệnh INSERT ---
    if ($stmt_insert->execute()) {
        echo "LOGGED_OK"; // Phản hồi thành công
    } else {
        // echo "Execute failed: (" . $stmt_insert->errno . ") " . $stmt_insert->error; // Để debug
        echo "ERROR_DB_EXECUTE_INSERT";
    }

    // Đóng statement insert
    $stmt_insert->close();

} else {
    // Thiếu tham số UID
    echo "ERROR_NO_UID";
}

// --- Đóng kết nối Database ---
$conn->close();

?>