// File: js/script.js

document.addEventListener('DOMContentLoaded', function() {
    const remoteOpenBtn = document.getElementById('remoteOpenBtn');
    const remoteMsgDiv = document.getElementById('remote-message');
    const logList = document.getElementById('log-list');

    // Chỉ chạy code này nếu các element tồn tại (tức là đang ở trang dashboard)
    if (remoteOpenBtn && remoteMsgDiv && logList) {

        // Xử lý nút Mở Cửa Từ Xa
        remoteOpenBtn.addEventListener('click', () => {
            if (confirm('Bạn có chắc muốn mở cửa từ xa không?')) {
                remoteMsgDiv.textContent = 'Đang gửi yêu cầu...';
                remoteMsgDiv.style.color = 'var(--secondary-color)';

                fetch('actions/trigger_open.php') // Gọi file PHP xử lý
                    .then(response => {
                        if (!response.ok) { throw new Error(`HTTP error! status: ${response.status}`); }
                        return response.text();
                    })
                    .then(data => {
                        remoteMsgDiv.textContent = data;
                        remoteMsgDiv.style.color = data.includes('Lỗi') || data.includes('Không tìm thấy') ? 'var(--danger-color)' : 'var(--success-color)';
                        // Tự động xóa thông báo sau 5 giây
                        setTimeout(() => { remoteMsgDiv.textContent = ''; }, 5000);
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error);
                        remoteMsgDiv.textContent = 'Lỗi kết nối hoặc xử lý!';
                        remoteMsgDiv.style.color = 'var(--danger-color)';
                        setTimeout(() => { remoteMsgDiv.textContent = ''; }, 5000);
                    });
            }
        });

        // Hàm lấy log mới nhất (Polling)
        function fetchLatestLogs() {
            fetch('actions/get_latest_logs.php') // Gọi file PHP lấy log
                .then(response => {
                    if (!response.ok) { throw new Error(`HTTP error! status: ${response.status}`);}
                    return response.json(); // Mong đợi JSON
                })
                .then(logs => {
                    logList.innerHTML = ''; // Xóa log cũ
                    if (logs && logs.length > 0) {
                        logs.forEach(log => {
                            const li = document.createElement('li');
                            // Định dạng thời gian H:i:s
                            const time = new Date(log.scan_time).toLocaleTimeString('vi-VN', { hour12: false });
                            let message = `${time} - UID: ${log.tag_uid} `;
                            if (log.tag_name_at_scan) {
                                message += `(${log.tag_name_at_scan}) `;
                            }

                            let resultClass = '';
                            if (log.access_result === 'AUTHORIZED') {
                                message += '✅'; resultClass = 'log-success';
                            } else if (log.access_result === 'UNAUTHORIZED'){
                                message += '🚫'; resultClass = 'log-fail';
                            } else {
                                message += `⚠️ (${log.access_result || '?'})`; resultClass = 'log-error';
                            }
                            li.textContent = message;
                            li.className = resultClass; // Thêm class CSS
                            logList.appendChild(li);
                        });
                    } else {
                        logList.innerHTML = '<li>Chưa có hoạt động nào gần đây.</li>';
                    }
                })
                .catch(error => {
                    console.error('Lỗi lấy log:', error);
                    // Có thể hiển thị lỗi trên UI nếu muốn, nhưng tạm thời chỉ log console
                    // logList.innerHTML = '<li>Lỗi tải dữ liệu log.</li>';
                });
        }

        // Lấy log lần đầu khi tải trang
        fetchLatestLogs();

        // Tự động cập nhật log mỗi 5 giây
        setInterval(fetchLatestLogs, 5000); // 5000ms = 5 giây
    }

    // Thêm active class cho menu dựa trên trang hiện tại (ví dụ đơn giản)
    const currentPage = window.location.pathname.split("/").pop();
    const navLinks = document.querySelectorAll('header nav ul li a');
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });

}); // End DOMContentLoaded