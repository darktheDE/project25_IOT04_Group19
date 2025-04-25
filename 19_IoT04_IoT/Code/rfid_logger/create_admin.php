<?php
// File: create_admin.php (CHỈ CHẠY 1 LẦN RỒI XÓA)
// *** BẬT HIỂN THỊ LỖI ĐỂ DEBUG ***
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// **********************************

// --- Kiểm tra xem file kết nối DB có tồn tại không trước khi require ---
$db_connect_path = 'includes/db_connect.php'; // Đường dẫn tương đối từ file này
if (!file_exists($db_connect_path)) {
    die("Lỗi nghiêm trọng: Không tìm thấy file '{$db_connect_path}'. Vui lòng kiểm tra lại cấu trúc thư mục.");
}
require_once $db_connect_path;

echo "Đã kết nối database (nếu không có lỗi ở trên)...<br>"; // Thêm dòng này để kiểm tra require_once

// --- Thông tin tài khoản Admin cần tạo ---
$admin_username = 'admin'; // Đặt tên đăng nhập bạn muốn
$admin_password_plain = 'Admin@123'; // !!! ĐẶT MẬT KHẨU MẠNH VÀ AN TOÀN HƠN !!!
$admin_full_name = 'Quản trị viên hệ thống'; // Tên đầy đủ

// --- MÃ HÓA MẬT KHẨU ---
// Sử dụng thuật toán mã hóa mạnh mặc định của PHP (thường là bcrypt)
$password_hash = password_hash($admin_password_plain, PASSWORD_DEFAULT);

// Kiểm tra xem mã hóa có thành công không
if ($password_hash === false) {
    die("Lỗi nghiêm trọng: Không thể mã hóa mật khẩu.");
}

echo "Mật khẩu đã được mã hóa thành công.<br>"; // Thêm dòng kiểm tra

// --- Chuẩn bị câu lệnh SQL để thêm Admin ---
// Sử dụng Prepared Statement để an toàn hơn
$stmt = $conn->prepare("INSERT INTO web_users (username, password_hash, full_name, role, is_active) VALUES (?, ?, ?, 'admin', 1)");

// Kiểm tra xem prepare có thành công không
if ($stmt === false) {
    // Hiển thị lỗi chi tiết từ MySQL nếu prepare thất bại
    die("Lỗi khi chuẩn bị câu lệnh SQL: (" . $conn->errno . ") " . $conn->error);
}

echo "Câu lệnh SQL đã được chuẩn bị.<br>"; // Thêm dòng kiểm tra

// --- Gán giá trị vào câu lệnh và thực thi ---
// 'sss' nghĩa là 3 tham số đều là kiểu string
$stmt->bind_param("sss", $admin_username, $password_hash, $admin_full_name);

echo "Đang thực thi câu lệnh INSERT...<br>"; // Thêm dòng kiểm tra

if ($stmt->execute()) {
    // --- THÀNH CÔNG ---
    echo "==============================================<br>";
    echo "<b>THÀNH CÔNG!</b><br>";
    echo "Tài khoản admin '<b>" . htmlspecialchars($admin_username) . "</b>' đã được tạo.<br>";
    echo "Mật khẩu bạn đã đặt là: <b>" . htmlspecialchars($admin_password_plain) . "</b> (Hãy nhớ mật khẩu này!)<br>";
    echo "<br>";
    echo "<b style='color:red;'>!!! CẢNH BÁO: XÓA FILE create_admin.php NÀY KHỎI SERVER NGAY BÂY GIỜ ĐỂ ĐẢM BẢO AN TOÀN !!!</b>";
    echo "<br>==============================================";

} else {
    // --- THẤT BẠI ---
    echo "==============================================<br>";
    echo "<b>LỖI KHI THỰC THI!</b><br>";
    // Hiển thị lỗi chi tiết từ MySQL
    echo "Không thể thêm tài khoản admin vào database.<br>";
    echo "Mã lỗi: " . $stmt->errno . "<br>";
    echo "Chi tiết lỗi: " . $stmt->error . "<br>";
    echo "<br><b>Nguyên nhân phổ biến:</b> Có thể tên đăng nhập '<b>" . htmlspecialchars($admin_username) . "</b>' đã tồn tại trong bảng 'web_users'. Hãy kiểm tra lại database bằng phpMyAdmin.";
    echo "<br>==============================================";
}

// --- Đóng câu lệnh và kết nối ---
$stmt->close();
$conn->close();
?>