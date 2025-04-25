#include <SPI.h>
#include <MFRC522.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>

// --- Cấu hình chân ---
#define SS_PIN    D4  // RFID SS/SDA nối với D4 NodeMCU
#define RST_PIN   D3  // RFID RST nối với D3 NodeMCU
#define RELAY_PIN D1  // <<< CHÂN ĐIỀU KHIỂN RELAY (Nối với IN) - Ví dụ: D1
#define LED_BUILTIN 2 // Chân LED tích hợp (Thường là D4/GPIO2)

// --- Cấu hình Thời gian ---
const unsigned long DOOR_UNLOCK_DURATION = 3000; // Thời gian cửa mở (3 giây = 3000 ms)

// ============================================================
// <<< THAY ĐỔI CÁC THÔNG TIN SAU CHO PHÙ HỢP >>>
// --- Cấu hình Wi-Fi ---
const char* ssid = "Phong 312A";         // <<< TÊN WI-FI CỦA BẠN
const char* password = "0336000199"; // <<< MẬT KHẨU WI-FI CỦA BẠN

// --- Cấu hình Server ---
// <<< SỬA LẠI ĐƯỜNG DẪN ĐẾN FILE check_access.php >>>
String serverUrl = "http://192.168.0.102/rfid_logger/check_access.php";
// ============================================================


// --- Khởi tạo đối tượng ---
MFRC522 mfrc522(SS_PIN, RST_PIN);
WiFiClient client;
HTTPClient http;

// --- Biến trạng thái cửa ---
bool isDoorUnlocked = false;        // Lưu trạng thái: true = đang mở, false = đang khóa
unsigned long doorUnlockStartTime = 0; // Lưu thời điểm bắt đầu mở cửa

// --- Hàm Setup: Chạy một lần khi NodeMCU khởi động ---
void setup() {
  Serial.begin(115200);
  while (!Serial);

  // Cấu hình chân Relay là OUTPUT, trạng thái ban đầu là LOW (tắt relay -> cửa khóa)
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, LOW);

  // Cấu hình chân LED tích hợp là OUTPUT, trạng thái ban đầu là HIGH (tắt LED)
  pinMode(LED_BUILTIN, OUTPUT);
  digitalWrite(LED_BUILTIN, HIGH);

  Serial.println("\n========================================");
  Serial.println("   Initializing RFID Access Control System"); // Đổi tên hệ thống
  Serial.println("========================================");

  SPI.begin();        // Khởi tạo SPI
  mfrc522.PCD_Init(); // Khởi tạo RFID

  // Kiểm tra kết nối RFID Reader
  byte v = mfrc522.PCD_ReadRegister(mfrc522.VersionReg);
  Serial.print("MFRC522 ");
  if ((v == 0x00) || (v == 0xFF)) {
    Serial.println("Communication failure, check RFID wiring!");
    signalError(10); // Báo lỗi nghiêm trọng
    while(true);     // Dừng hẳn nếu không có RFID
  } else {
    Serial.println("Reader detected successfully.");
  }

  // Kết nối Wi-Fi
  connectWiFi();
}

// --- Hàm Loop: Chạy lặp đi lặp lại liên tục ---
void loop() {
  // 1. Luôn kiểm tra kết nối Wi-Fi
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("\n[Error] WiFi disconnected. Reconnecting...");
    connectWiFi();
    delay(1000);
    return; // Chờ kết nối lại, không làm gì khác
  }

  // 2. Tự động khóa cửa nếu đang mở và đã hết giờ
  if (isDoorUnlocked && (millis() - doorUnlockStartTime >= DOOR_UNLOCK_DURATION)) {
    lockDoor();
  }

  // 3. Tìm thẻ RFID mới
  if (!mfrc522.PICC_IsNewCardPresent()) {
    delay(50); // Đợi chút để giảm tải
    return;    // Chưa có thẻ
  }

  // 4. Đọc thông tin thẻ
  if (!mfrc522.PICC_ReadCardSerial()) {
    delay(50);
    return;    // Lỗi đọc thẻ
  }

  // 5. Lấy UID thẻ
  Serial.print(">>> Card Detected! UID: ");
  String uidString = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    uidString += (mfrc522.uid.uidByte[i] < 0x10 ? "0" : ""); // Thêm '0' nếu cần
    uidString += String(mfrc522.uid.uidByte[i], HEX);       // Chuyển sang Hex String
  }
  uidString.toUpperCase(); // Chuyển thành chữ hoa
  Serial.println(uidString);

  // 6. Gửi UID lên Server để kiểm tra quyền truy cập << THAY ĐỔI CHỖ NÀY
  checkAccessWithServer(uidString); // Gọi hàm kiểm tra thay vì hàm log

  // 7. Tạm dừng thẻ hiện tại
  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();

  Serial.println("\n-----------------------------------");
  Serial.println("Waiting for next card scan...");
  // Không cần delay lớn ở đây vì đã có kiểm tra khóa cửa tự động
}

// --- Hàm phụ: Kết nối Wi-Fi ---
void connectWiFi() {
  Serial.print("Connecting to WiFi: ");
  Serial.print(ssid);

  WiFi.mode(WIFI_STA); // Đặt chế độ Station
  WiFi.begin(ssid, password); // Bắt đầu kết nối

  int connect_timeout = 0;
  // Nháy LED chậm trong khi chờ kết nối
  while (WiFi.status() != WL_CONNECTED && connect_timeout < 30) { // Chờ tối đa 15 giây
    digitalWrite(LED_BUILTIN, LOW); delay(250);
    digitalWrite(LED_BUILTIN, HIGH); delay(250);
    Serial.print(".");
    connect_timeout++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi connected successfully!");
    Serial.print("NodeMCU IP address: ");
    Serial.println(WiFi.localIP());
    signalSuccess(1); // Báo thành công
  } else {
    Serial.println("\n[Error] WiFi connection Failed! Check SSID/Password.");
    signalError(5); // Báo lỗi
  }
}

// --- Hàm phụ: Kiểm tra Truy cập với Server << ĐỔI TÊN VÀ SỬA LOGIC ---
void checkAccessWithServer(String uid) {
  // Tạo URL đầy đủ: http://server_ip/path/check_access.php?uid=XXXXXX
  String fullUrl = serverUrl + "?uid=" + uid; // Đảm bảo serverUrl đã trỏ đến check_access.php

  Serial.print("Checking access with server: ");
  Serial.println(fullUrl);

  // Bắt đầu yêu cầu HTTP GET
  http.begin(client, fullUrl); // Sử dụng WiFiClient

  // Gửi yêu cầu và nhận mã trạng thái HTTP
  int httpCode = http.GET();

  // Xử lý phản hồi từ Server
  if (httpCode > 0) { // Có phản hồi (mã 200, 404, 500, ...)
    String payload = http.getString(); // Lấy nội dung phản hồi
    Serial.print("Server Response Code: ");
    Serial.println(httpCode);
    Serial.print("Server Response Payload: ");
    Serial.println(payload);

    // Dựa vào nội dung phản hồi để hành động << LOGIC MỚI
    if (payload == "AUTHORIZED") {
      unlockDoor();     // Gọi hàm mở cửa
      signalSuccess(2); // Nháy LED 2 lần báo được phép
    } else if (payload == "UNAUTHORIZED") {
      denyAccess();     // Gọi hàm từ chối (chỉ in log)
      signalError(2);   // Nháy LED 2 lần nhanh báo bị từ chối
    } else {
      // Các trường hợp lỗi khác từ server (ERROR_DB_CONN, ERROR_NO_UID, 404 Not Found...)
      Serial.println("[Warning] Unknown or error response from server.");
      signalError(3); // Nháy LED 3 lần báo lỗi server/lỗi lạ
    }
  } else { // Lỗi khi gửi yêu cầu HTTP (httpCode <= 0)
    Serial.print("[Error] HTTP GET request failed, error: ");
    Serial.println(http.errorToString(httpCode).c_str());
    signalError(5); // Nháy LED 5 lần báo lỗi kết nối HTTP
  }

  // Luôn kết thúc kết nối HTTP để giải phóng tài nguyên
  http.end();
}

// --- Hàm phụ: Mở Khóa Cửa << HÀM MỚI ---
void unlockDoor() {
  if (!isDoorUnlocked) { // Chỉ mở nếu đang khóa
    Serial.println(">>> Access Granted! Unlocking door for " + String(DOOR_UNLOCK_DURATION / 1000) + " seconds...");
    digitalWrite(RELAY_PIN, HIGH); // Kích hoạt Relay -> BẬT Solenoid
    isDoorUnlocked = true;         // Cập nhật trạng thái cửa
    doorUnlockStartTime = millis();  // Ghi lại thời điểm bắt đầu mở
  } else {
    // Nếu quét thẻ hợp lệ khi cửa đang mở -> reset thời gian mở
    Serial.println("Door already unlocked. Resetting unlock timer...");
    doorUnlockStartTime = millis();
  }
}

// --- Hàm phụ: Khóa Cửa << HÀM MỚI ---
void lockDoor() {
  Serial.println("<<< Locking door (Time up)...");
  digitalWrite(RELAY_PIN, LOW); // Tắt Relay -> TẮT Solenoid
  isDoorUnlocked = false;       // Cập nhật trạng thái cửa
}

// --- Hàm phụ: Từ chối Truy cập << HÀM MỚI ---
void denyAccess() {
  Serial.println(">>> Access Denied!");
  // Không làm gì với relay cả, cửa vẫn khóa
}

// --- Hàm phụ: Báo hiệu bằng LED tích hợp << HÀM MỚI ---
// Nháy LED xanh (sáng dài) báo thành công
void signalSuccess(int blinks) {
  for (int i = 0; i < blinks; i++) {
    digitalWrite(LED_BUILTIN, LOW); // Bật LED (LOW = ON)
    delay(200);
    digitalWrite(LED_BUILTIN, HIGH); // Tắt LED
    delay(200);
  }
}

// Nháy LED đỏ (nháy nhanh) báo lỗi/từ chối
void signalError(int blinks) {
  for (int i = 0; i < blinks; i++) {
    digitalWrite(LED_BUILTIN, LOW); // Bật LED
    delay(75);
    digitalWrite(LED_BUILTIN, HIGH); // Tắt LED
    delay(75);
  }
}