/**
 * Logic Thoát ca dành riêng cho trang chủ (Root)
 * Không phụ thuộc vào các biến giỏ hàng của POS
 */
function endShiftRoot() {
    const cashInput = document.getElementById('root-end-cash-input');
    const noteInput = document.getElementById('root-end-note-input');

    if (!cashInput.value || cashInput.value === "") {
        alert("Vui lòng nhập số tiền mặt thực tế trước khi chốt ca!");
        return;
    }

    if (!confirm("Xác nhận chốt ca và đăng xuất khỏi hệ thống?")) return;

    const cash = cashInput.value;
    const note = noteInput.value;

    fetch('core/session_manager.php?action=end_shift', { // Lưu ý đường dẫn core/
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ end_cash: cash, note: note })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message); // Hiển thị doanh thu hệ thống tính toán
            window.location.href = 'login.php'; // Thoát ra trang login
        } else {
            alert("Lỗi: " + data.message);
        }
    })
    .catch(err => {
        console.error("Lỗi kết nối:", err);
        alert("Đã xảy ra lỗi kết nối với máy chủ.");
    });
}

// Hàm kiểm tra trạng thái ca khi vào trang chủ (Nếu cần bắt buộc chốt)
function checkShiftStatusRoot() {
    fetch('core/session_manager.php?action=check_status')
    .then(res => res.json())
    .then(data => {
        if (data.success && !data.is_open) {
             // Nếu muốn trang chủ cũng hiện modal bắt vào ca thì copy modal start shift sang
             console.log("Chưa vào ca.");
        }
    });
}

