<?php // File: includes/footer.php ?>
        </div> <!-- // .page-content .container -->
    </main>
    <footer>
        <div class="container">
            <p>© <?php echo date("Y"); ?> Hệ Thống Cửa RFID by Phan Trong Phu -  Phan Trong Qui - Do Kien Hung</p>
        </div>
    </footer>
    <script src="js/script.js"></script> <!-- Link đến file JS -->
</body>
</html>
<?php
// Đóng kết nối DB nếu biến $conn được tạo từ db_connect.php
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>