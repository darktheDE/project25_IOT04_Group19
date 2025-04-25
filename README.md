# Hệ Thống Bảo Mật Cửa Ra Vào Thông Minh (IoT Smart Door Lock)

Đây là đồ án cuối kỳ môn học Vạn Vật Kết Nối Internet (IoT) - INOT431780_04 tại Trường Đại học Sư phạm Kỹ thuật TP.HCM. Dự án xây dựng một hệ thống kiểm soát cửa ra vào sử dụng vi điều khiển NodeMCU ESP8266, tích hợp xác thực bằng thẻ RFID (RC522), Keypad cảm ứng (TTP224 - sử dụng 2 nút), và cho phép quản lý, điều khiển từ xa qua giao diện Web được xây dựng bằng PHP và MySQL (chạy trên XAMPP). Hệ thống cũng bao gồm nguồn dự phòng UPS để đảm bảo hoạt động liên tục.

## Tính năng chính

*   Xác thực đa phương thức: Thẻ RFID, Keypad (mô phỏng PIN sử dụng 2 nút).
*   Mở cửa bằng nút nhấn vật lý (từ bên trong).
*   Điều khiển đóng/mở cửa từ xa qua giao diện Web.
*   Quản lý tập trung thông tin thẻ RFID qua CSDL MySQL.
*   Giao diện Web cho phép:
    *   Đăng nhập User/Admin.
    *   User/Admin: Xem trạng thái, điều khiển cửa.
    *   Admin: Quản lý thẻ RFID (thêm/xóa - *cần hoàn thiện giao diện*), xem lịch sử truy cập, quản lý tài khoản Web.
*   Ghi log lịch sử truy cập vào CSDL.
*   Hoạt động với nguồn dự phòng UPS khi mất điện lưới.
*   Phản hồi trạng thái qua LED tích hợp trên NodeMCU.

## Công nghệ sử dụng

*   **Phần cứng:** NodeMCU ESP8266, Module RFID RC522, Keypad cảm ứng TTP224, Module Relay 5V, Khóa Solenoid 12V, Mạch UPS 12V, Pin 18650, Mạch Buck 5V, Nút nhấn, Dây cắm, Breadboard.
*   **Firmware:** Arduino C++ (trên nền tảng Arduino IDE).
*   **Backend:** PHP, MySQL (chạy trên XAMPP).
*   **Frontend:** HTML, CSS, JavaScript (sử dụng jQuery AJAX).
*   **Giao thức:** HTTP, SPI.

## Cấu trúc thư mục
**Lưu ý:** những code liên quan chỉ có ở Repo GitHub
```
.
├── flowchart/               # Chứa các file sơ đồ (.drawio: ERD, Khối, Mạch, Thuật toán)
│   ├── DBIOT.drawio
│   ├── Nhom25_SoDoMach.drawio
│   ├── setup.drawio
│   └── SoDoKhoi.drawio
├── Nhom25_rfid/             # Chứa mã nguồn firmware Arduino (.ino) cho NodeMCU ESP8266
│   └── Nhom25_RFID.ino
├── realPics/                # Chứa hình ảnh thực tế của mô hình phần cứng
│   └── ... (nhiều file ảnh .jpg)
├── rfid_logger/             # Chứa mã nguồn ứng dụng Web Server (PHP, HTML, CSS, JS)
│   ├── actions/             # Các file PHP xử lý hành động (check_access, check_command, trigger_open,...)
│   ├── css/                 # File CSS định dạng giao diện (style.css)
│   ├── includes/            # Các file PHP dùng chung (db_connect, header, footer)
│   ├── js/                  # File JavaScript (script.js)
│   ├── admin_add_user.php
│   ├── admin_manage_users.php
│   ├── check_access.php     # API cho NodeMCU kiểm tra thẻ (nên chuyển vào actions/)
│   ├── create_admin.php     # Script tạo tài khoản admin ban đầu (ví dụ)
│   ├── history.php          # Trang xem lịch sử (Admin)
│   ├── index.php            # Trang Dashboard chính
│   ├── log_scan.php         # Có thể là API ghi log? (cần xem lại code)
│   ├── login.php            # Trang đăng nhập
│   ├── test_connection.php  # Script kiểm tra kết nối DB
│   └── users.php            # Trang quản lý user (Admin)?
├── Nhom25_BaoCao.pdf        # File báo cáo cuối kỳ định dạng PDF
├── Nhom25_PPT.pdf           # File slide trình bày định dạng PDF
└── rfid_database.sql        # File SQL để tạo cấu trúc cơ sở dữ liệu và bảng
```

## Cài đặt và Chạy thử

### Yêu cầu phần mềm (Software Requirements)

*   **Arduino IDE:** Phiên bản 1.8.x hoặc mới hơn.
*   **ESP8266 Board Package:** Cài đặt thông qua Boards Manager trong Arduino IDE (URL: `http://arduino.esp8266.com/stable/package_esp8266com_index.json`).
*   **Thư viện Arduino:** `MFRC522 by GitHubCommunity` (Cài đặt qua Library Manager).
*   **XAMPP:** Phiên bản tích hợp Apache, MySQL, PHP (nên dùng PHP 7.x hoặc 8.x). Tải từ [https://www.apachefriends.org](https://www.apachefriends.org).
*   **Trình duyệt Web:** Chrome, Firefox, Edge,...
*   **(Tùy chọn)** Trình soạn thảo mã nguồn: VS Code, Sublime Text,...
*   **(Tùy chọn)** Công cụ quản lý CSDL: phpMyAdmin (đi kèm XAMPP), HeidiSQL, MySQL Workbench,...

### Phần cứng

1.  Lắp ráp các linh kiện phần cứng theo sơ đồ mạch `flowchart/Nhom25_SoDoMach.drawio` hoặc xem hình ảnh thực tế trong `realPics/`.
2.  Kết nối Adapter 12V (>=1.5A) vào mạch UPS. Đảm bảo Pin 18650 đã được lắp đúng chiều vào mạch UPS.
3.  Kết nối NodeMCU với máy tính qua cáp Micro USB.

### Cơ sở dữ liệu

1.  Khởi động module Apache và MySQL trong XAMPP Control Panel.
2.  Truy cập `http://localhost/phpmyadmin` trên trình duyệt.
3.  Tạo một cơ sở dữ liệu mới, ví dụ đặt tên là `rfid_db`.
4.  Chọn cơ sở dữ liệu `rfid_db` vừa tạo, vào tab "Import".
5.  Nhấn "Choose File" hoặc "Browse...", chọn file `rfid_database.sql` trong thư mục dự án.
6.  Nhấn nút "Go" hoặc "Thực hiện" ở cuối trang để tạo các bảng.
7.  **Tạo tài khoản Admin:** Sau khi tạo bảng, bạn cần tạo ít nhất một tài khoản quản trị trong bảng `web_users`. Có thể thực hiện bằng cách chạy lệnh SQL INSERT trong phpMyAdmin. Ví dụ tạo user 'admin' với mật khẩu 'admin123':
    *   *Lưu ý quan trọng:* **Không bao giờ lưu mật khẩu dạng rõ!** Bạn cần tạo giá trị băm cho mật khẩu. Có thể dùng một script PHP đơn giản để tạo hash:
        ```php
        <?php echo password_hash('admin123', PASSWORD_DEFAULT); ?>
        ```
        Chạy script này (ví dụ lưu thành `hash_gen.php` trong `htdocs` rồi truy cập qua trình duyệt) để lấy chuỗi hash (ví dụ: `$2y$10$...`).
    *   Sau đó chạy lệnh SQL INSERT (thay `GENERATED_PASSWORD_HASH` bằng chuỗi hash bạn vừa tạo):
        ```sql
        INSERT INTO `web_users` (`username`, `password_hash`, `full_name`, `role`, `is_active`) VALUES
        ('admin', 'GENERATED_PASSWORD_HASH', 'Quản trị viên', 'admin', 1);
        ```

### Web Server

1.  Sao chép toàn bộ thư mục `rfid_logger` vào thư mục `htdocs` của XAMPP (ví dụ: `C:/xampp/htdocs/`).
2.  Mở file `rfid_logger/includes/db_connect.php` và kiểm tra/chỉnh sửa thông tin kết nối CSDL nếu cần (thường mặc định là đúng nếu dùng XAMPP trên cùng máy):
    ```php
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = ''; // Mật khẩu mặc định của root trong XAMPP thường là rỗng
    $dbName = 'rfid_db'; // Tên DB đã tạo ở bước trên
    ```

### Firmware NodeMCU

1.  Mở file `Nhom25_rfid/Nhom25_RFID.ino` bằng Arduino IDE.
2.  **Cấu hình:** Tìm và chỉnh sửa các dòng sau cho phù hợp:
    ```cpp
    const char* ssid = "TEN_WIFI_CUA_BAN"; // Thay bằng tên mạng WiFi của bạn
    const char* password = "MAT_KHAU_WIFI"; // Thay bằng mật khẩu WiFi của bạn
    String serverName = "http://DIA_CHI_IP_MAY_CHAY_XAMPP/rfid_logger/"; // Thay bằng IP của máy tính chạy XAMPP
    // Ví dụ: String serverName = "http://192.168.1.15/rfid_logger/";
    ```
    *Lưu ý: NodeMCU và máy tính chạy XAMPP phải kết nối cùng một mạng LAN.*
3.  Trong Arduino IDE:
    *   Vào `Tools > Board > ESP8266 Boards` và chọn "NodeMCU 1.0 (ESP-12E Module)" (hoặc board ESP8266 tương thích).
    *   Vào `Tools > Port` và chọn đúng cổng COM mà NodeMCU đang kết nối.
4.  Nhấn nút "Upload" (biểu tượng mũi tên sang phải) để biên dịch và nạp code.
5.  Mở Serial Monitor (`Tools > Serial Monitor`), chọn baud rate là `115200` để xem log khởi động và thông tin debug (bao gồm cả UID thẻ khi quét lần đầu).

## Hướng dẫn sử dụng

1.  **Khởi động:** Cấp nguồn cho hệ thống. NodeMCU sẽ tự động kết nối Wi-Fi và sẵn sàng hoạt động (quan sát Serial Monitor nếu cần).
2.  **Xác thực tại cửa:**
    *   **RFID:** Đưa thẻ RFID đã được đăng ký và kích hoạt trong CSDL vào gần module RC522. Nếu hợp lệ, relay sẽ kêu "tách" và khóa solenoid mở trong vài giây.
    *   **Keypad (Mô phỏng PIN):** Sử dụng 2 nút trên TTP224 theo logic đã lập trình trong firmware để nhập PIN.
    *   **Nút nhấn:** Nhấn nút vật lý bên trong để mở cửa ngay lập tức.
3.  **Giao diện Web:**
    *   Mở trình duyệt trên máy tính/điện thoại trong cùng mạng LAN, truy cập địa chỉ: `http://DIA_CHI_IP_MAY_CHAY_XAMPP/rfid_logger/`.
    *   Đăng nhập bằng tài khoản đã tạo (ví dụ: `admin` / `admin123`).
    *   **Dashboard:** Xem trạng thái (nếu có cảm biến), nhấn nút "MỞ CỬA" để điều khiển từ xa. Xem các log truy cập gần đây.
    *   **Admin:** Điều hướng đến các trang "Quản lý Thẻ RFID", "Lịch Sử", "Quản lý Tài Khoản Web" từ menu để thực hiện các chức năng quản trị.
4.  **Thêm thẻ RFID mới:**
    *   Quét thẻ RFID mới vào đầu đọc RC522.
    *   Mở Serial Monitor của Arduino IDE để xem UID của thẻ vừa quét.
    *   Đăng nhập vào giao diện Web với quyền Admin.
    *   Vào trang "Quản lý Thẻ RFID", tìm chức năng thêm thẻ và nhập UID vừa lấy được cùng các thông tin khác (tên, mô tả).

## Thông tin Đồ án

*   **Môn học:** Vạn Vật Kết Nối Internet (INOT431780_04)
*   **Trường:** ĐH Sư phạm Kỹ thuật TP.HCM
*   **Khoa:** Công nghệ Thông tin
*   **Giảng viên hướng dẫn:** ThS. Đinh Công Đoan
*   **GitHub:** https://github.com/darktheDE/projectIOT04_Group25
*   **Sinh viên thực hiện:**
    *   Phan Trọng Quí (23133061)
    *   Phan Trọng Phú (23133056)
    *   Đỗ Kiến Hưng (23133030)
*   **Năm học:** 2024-2025
