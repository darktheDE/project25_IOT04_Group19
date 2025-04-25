# Hệ Thống Bảo Mật Cửa Ra Vào Thông Minh (IoT Smart Door Lock) - Nhóm 19 / Đề tài 25

Đây là đồ án cuối kỳ môn học Vạn Vật Kết Nối Internet (IoT) - INOT431780_04 tại Trường Đại học Sư phạm Kỹ thuật TP.HCM (Học kỳ 1, Năm học 2024-2025). Dự án tập trung vào việc xây dựng một hệ thống kiểm soát cửa ra vào sử dụng vi điều khiển NodeMCU ESP8266, tích hợp các phương thức xác thực bao gồm thẻ RFID (sử dụng module RC522), Keypad cảm ứng (TTP224 - mô phỏng PIN bằng 2 nút), và cho phép quản lý, giám sát, điều khiển từ xa thông qua giao diện Web. Hệ thống sử dụng PHP và MySQL (chạy trên XAMPP) cho phần backend và lưu trữ dữ liệu. Một tính năng quan trọng là việc tích hợp nguồn dự phòng UPS để đảm bảo hệ thống có thể hoạt động ngay cả khi mất điện lưới.

## Tính năng chính

*   **Xác thực đa dạng:** Mở cửa bằng thẻ RFID hợp lệ hoặc nhập mã PIN (mô phỏng).
*   **Mở cửa từ bên trong:** Sử dụng nút nhấn vật lý.
*   **Điều khiển & Giám sát từ xa:** Giao diện Web cho phép người dùng (User/Admin) đăng nhập, xem trạng thái (nếu có cảm biến), điều khiển mở cửa.
*   **Quản lý tập trung:**
    *   Admin có thể quản lý danh sách thẻ RFID được phép truy cập thông qua CSDL (*cần hoàn thiện giao diện quản lý trên web*).
    *   Admin có thể xem lịch sử truy cập chi tiết.
    *   Admin có thể quản lý tài khoản người dùng Web.
*   **Lưu trữ Lịch sử:** Ghi lại các lần quét thẻ và kết quả xác thực vào CSDL MySQL.
*   **Nguồn dự phòng:** Hoạt động ổn định khi mất điện lưới nhờ mạch UPS và pin 18650.
*   **Phản hồi:** Sử dụng LED tích hợp để báo hiệu trạng thái hoạt động.

## Công nghệ sử dụng

*   **Phần cứng:** NodeMCU ESP8266, Module RFID RC522, Keypad cảm ứng TTP224, Module Relay 5V, Khóa Solenoid 12V, Mạch UPS 12V, Pin 18650, Mạch Buck 5V, Nút nhấn, Dây cắm, Breadboard.
*   **Firmware:** Arduino C++ (trên nền tảng Arduino IDE).
*   **Backend:** PHP 7.x/8.x, MySQL (MariaDB) - chạy trên XAMPP.
*   **Frontend:** HTML5, CSS3, JavaScript (bao gồm jQuery AJAX).
*   **Giao thức:** HTTP, SPI.

## Cấu trúc thư mục dự án

```
.
├── Code/ # Thư mục chứa toàn bộ mã nguồn
│ ├── Nhom19_DeTai25_RFID/ # Chứa mã nguồn firmware Arduino (.ino)
│ │ └── Nhom19_DeTai25_RFID.ino
│ ├── rfid_logger/ # Chứa mã nguồn ứng dụng Web Server (PHP, HTML, CSS, JS)
│ │ ├── actions/ # Scripts PHP xử lý logic backend
│ │ ├── css/ # Files CSS
│ │ ├── includes/ # Files PHP dùng chung (db connect, header, footer)
│ │ ├── js/ # Files JavaScript
│ │ └── ... (Các file PHP cho từng trang và chức năng)
│ └── rfid_database.sql # File SQL để tạo cơ sở dữ liệu và bảng
├── TaiLieuThamKhao/ # Thư mục chứa tài liệu tham khảo, thiết kế
│ ├── flowchart/ # Các file sơ đồ (.drawio)
│ │ ├── DBIOT.drawio # Sơ đồ ERD CSDL
│ │ ├── Nhom25_SoDoMach.drawio # Sơ đồ mạch (Lưu ý tên file có thể cần đổi thành Nhom19)
│ │ ├── setup.drawio # Lưu đồ thuật toán hàm setup()
│ │ └── SoDoKhoi.drawio # Sơ đồ khối hệ thống
│ ├── HinhAnhDoAn/ # Hình ảnh thực tế của mô hình, giao diện
│ │ └── ... (Các file ảnh)
│ └── README.md # Có thể là file readme chi tiết hơn hoặc ghi chú riêng
├── Nhom19_DeTai25_BaoCao.docx # File báo cáo định dạng Word
├── Nhom19_DeTai25_BaoCao.pdf # File báo cáo định dạng PDF
└── Nhom19_DeTai25_Slide.pdf # File slide trình bày định dạng PDF
```
## Cài đặt và Chạy thử

### Yêu cầu phần mềm (Software Requirements)

*   **Arduino IDE:** Phiên bản 1.8.x hoặc mới hơn.
*   **ESP8266 Board Package:** Cài đặt qua Boards Manager (URL: `http://arduino.esp8266.com/stable/package_esp8266com_index.json`).
*   **Thư viện Arduino:** `MFRC522 by GitHubCommunity` (Cài đặt qua Library Manager).
*   **XAMPP:** Phiên bản mới nhất tích hợp Apache, MySQL, PHP.
*   **Trình duyệt Web:** Chrome, Firefox, Edge, etc.

### Phần cứng

1.  Tham khảo sơ đồ mạch trong `TaiLieuThamKhao/flowchart/` và hình ảnh trong `TaiLieuThamKhao/HinhAnhDoAn/` để lắp ráp các linh kiện.
2.  Kết nối nguồn 12V vào mạch UPS và đảm bảo pin 18650 được lắp đúng.
3.  Kết nối NodeMCU với máy tính qua cáp Micro USB.

### Cơ sở dữ liệu

1.  Khởi động Apache và MySQL trong XAMPP Control Panel.
2.  Truy cập `http://localhost/phpmyadmin`.
3.  Tạo database mới (ví dụ: `rfid_db`).
4.  Import file `Code/rfid_database.sql` vào database vừa tạo để tạo cấu trúc bảng.
5.  Tạo tài khoản admin trong bảng `web_users` (tham khảo hướng dẫn tạo hash mật khẩu ở README trước hoặc trong báo cáo).

### Web Server

1.  Sao chép toàn bộ thư mục `Code/rfid_logger` vào thư mục `htdocs` của XAMPP.
2.  Kiểm tra và chỉnh sửa file `Code/rfid_logger/includes/db_connect.php` nếu cần thiết lập thông tin kết nối CSDL khác mặc định.

### Firmware NodeMCU

1.  Mở file `Code/Nhom19_DeTai25_RFID/Nhom19_DeTai25_RFID.ino` bằng Arduino IDE.
2.  **Cấu hình:** Chỉnh sửa các thông tin `ssid`, `password` (cho WiFi) và `serverName` (IP máy chạy XAMPP) trong code.
3.  Chọn đúng Board ("NodeMCU 1.0 (ESP-12E Module)") và Port trong menu `Tools`.
4.  Nhấn "Upload" để nạp code.
5.  Mở Serial Monitor (baud rate 115200) để theo dõi.

## Hướng dẫn sử dụng

(Tương tự như README trước, mô tả các bước quét thẻ, nhập PIN mô phỏng, nhấn nút, truy cập web, đăng nhập, điều khiển, quản lý thẻ/user...)

## Thông tin Đồ án

*   **Môn học:** Vạn Vật Kết Nối Internet (INOT431780_04)
*   **Đề tài:** Hệ Thống Bảo Mật Cửa Ra Vào Thông Minh
*   **Nhóm thực hiện:** Nhóm 19 (Đề tài 25)
    *   Phan Trọng Quí (23133061)
    *   Phan Trọng Phú (23133056)
    *   Đỗ Kiến Hưng (23133030)
*   **Giảng viên hướng dẫn:** ThS. Đinh Công Đoan
*   **Trường:** ĐH Sư phạm Kỹ thuật TP.HCM
*   **Năm học:** 2024-2025
