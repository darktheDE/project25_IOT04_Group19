<?php
// File: includes/db_connect.php
$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "rfid_system"; // Đảm bảo đúng tên DB

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    error_log("Database Connection Error: " . $conn->connect_error); // Ghi log lỗi
    die("Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại sau."); // Hiển thị thông báo chung
}
$conn->set_charset("utf8mb4");
?>