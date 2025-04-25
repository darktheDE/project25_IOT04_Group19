-- --------------------------------------------------------
-- Database: `rfid_system`
-- Tạo database nếu chưa tồn tại (tùy chọn)
-- CREATE DATABASE IF NOT EXISTS `rfid_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `rfid_system`; -- Chọn database này để thực hiện các lệnh tiếp theo

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tags`
-- Bảng này lưu thông tin các thẻ RFID được phép hoặc không được phép
--

CREATE TABLE `tags` (
  `id` INT AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT 'ID tự tăng, khóa chính',
  `uid` VARCHAR(20) UNIQUE NOT NULL COMMENT 'Mã UID duy nhất của thẻ RFID (dạng HEX)',
  `name` VARCHAR(100) NULL COMMENT 'Tên chủ thẻ hoặc tên gợi nhớ (tùy chọn)',
  `description` TEXT NULL COMMENT 'Mô tả thêm về thẻ (tùy chọn)',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Trạng thái thẻ: 1 = Hoạt động (được phép), 0 = Bị khóa (không được phép)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian thẻ được thêm vào hệ thống'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lưu trữ thông tin các thẻ RFID';

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `scan_logs`
-- Bảng này ghi lại lịch sử mỗi lần quét thẻ, bao gồm cả kết quả truy cập
--

CREATE TABLE `scan_logs` (
  `log_id` INT AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT 'ID tự tăng của mỗi dòng log',
  `tag_uid` VARCHAR(20) NOT NULL COMMENT 'UID của thẻ được quét',
  `tag_name_at_scan` VARCHAR(100) NULL COMMENT 'Tên thẻ lấy từ bảng `tags` tại thời điểm quét (nếu có)',
  `access_result` VARCHAR(20) NULL COMMENT 'Kết quả truy cập: AUTHORIZED, UNAUTHORIZED, hoặc mã lỗi',
  `scan_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT 'Thời gian server ghi nhận lần quét'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ghi lại lịch sử các lần quét thẻ';

-- (Tùy chọn nhưng khuyến nghị) Thêm chỉ mục (index) để tăng tốc truy vấn trên bảng logs
ALTER TABLE `scan_logs` ADD INDEX `idx_tag_uid` (`tag_uid`);
ALTER TABLE `scan_logs` ADD INDEX `idx_scan_time` (`scan_time`);
ALTER TABLE `scan_logs` ADD INDEX `idx_access_result` (`access_result`);


-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `door_commands`
-- Bảng này dùng để lưu trữ lệnh mở cửa từ giao diện web cho NodeMCU đọc
--

CREATE TABLE `door_commands` (
  `id` INT AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT 'Chỉ dùng dòng có id=1',
  `command` VARCHAR(20) NOT NULL DEFAULT 'IDLE' COMMENT 'Lệnh hiện tại: IDLE hoặc OPEN_REQUEST', `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Thời gian lệnh được cập nhật cuối'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lưu lệnh điều khiển cửa từ web';

-- Thêm dòng dữ liệu ban đầu và duy nhất cho bảng lệnh
-- NodeMCU và PHP sẽ luôn kiểm tra và cập nhật dòng có id=1 này
INSERT INTO `door_commands` (id, command) VALUES (1, 'IDLE');

-- --------------------------------------------------------

-- (Tùy chọn) Nếu bạn muốn tạo bảng người dùng web ngay bây giờ
--
-- Cấu trúc bảng cho bảng `web_users`
-- Bảng này lưu thông tin tài khoản đăng nhập vào giao diện web
--

CREATE TABLE `web_users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT 'ID tự tăng của người dùng web',
  `username` VARCHAR(50) UNIQUE NOT NULL COMMENT 'Tên đăng nhập (phải là duy nhất)',
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'Mật khẩu đã được mã hóa an toàn (bcrypt)',
  `full_name` VARCHAR(100) NULL COMMENT 'Tên đầy đủ của người dùng (tùy chọn)',
  `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user' COMMENT 'Vai trò người dùng: admin hoặc user',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Trạng thái tài khoản: 1 = Hoạt động, 0 = Bị khóa',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian tài khoản được tạo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lưu trữ tài khoản người dùng web';

-- Bạn cần chạy file PHP riêng để tạo tài khoản admin đầu tiên với mật khẩu đã mã hóa.

-- --------------------------------------------------------