<?php
require '../tinh-nang/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

// Lấy giờ hiện tại
$current_date = date('Y-m-d');
$current_hour = (int)date('H');
$current_shift = '';

// Xác định ca hiện tại
if ($current_hour >= 7 && $current_hour < 12) {
    $current_shift = 'morning';
} elseif ($current_hour >= 12 && $current_hour < 17) { // Kéo dài đến 5h chiều
    $current_shift = 'afternoon';
} elseif ($current_hour >= 17 && $current_hour < 23) {
    // Nếu bạn muốn thêm ca tối thì thêm vào đây, tạm thời code chỉ có sáng/chiều
    // $current_shift = 'evening';
}

// BƯỚC 1: Reset toàn bộ nhân viên (trừ Admin) về status = 0
// Giả sử Admin (role='admin') luôn luôn active (status=1) để quản lý
$conn->query("UPDATE users SET status = 0 WHERE role != 'admin'");

// BƯỚC 2: Nếu đang trong giờ làm việc, set status = 1 cho người có lịch
if ($current_shift != '') {
    $sql = "UPDATE users u
            JOIN work_schedules ws ON u.id = ws.user_id
            SET u.status = 1
            WHERE ws.shift_date = '$current_date' 
            AND ws.shift_type = '$current_shift'";
    
    $conn->query($sql);
    $msg = "Đã cập nhật: Ca $current_shift ngày $current_date đang hoạt động.";
} else {
    $msg = "Hiện không phải giờ làm việc. Tất cả nhân viên (trừ Admin) đã off.";
}

echo json_encode(['status' => 'success', 'message' => $msg]);
?>