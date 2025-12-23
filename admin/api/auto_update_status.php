<?php
require '../../includes/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

// Lấy giờ hiện tại
$current_date = date('Y-m-d');
$current_hour = (int)date('H');
$current_minute = (int)date('i');
$current_shift = '';

// --- LOGIC CA LÀM VIỆC MỚI ---
// Ca Sáng: 7h - 12h
if ($current_hour >= 7 && $current_hour < 12) {
    $current_shift = 'morning';
} 
// Ca Chiều: 12h - 17h
elseif ($current_hour >= 12 && $current_hour < 17) {
    $current_shift = 'afternoon';
}
// Ca Tối: 17h - 23h30
elseif ($current_hour >= 17) {
    // Nếu chưa đến 23h thì chắc chắn là tối
    if ($current_hour < 23) {
        $current_shift = 'evening';
    } 
    // Nếu là 23h thì phải kiểm tra phút (<= 30)
    elseif ($current_hour == 23 && $current_minute <= 30) {
        $current_shift = 'evening';
    }
}

// BƯỚC 1: Reset toàn bộ nhân viên về trạng thái nghỉ
$conn->query("UPDATE users SET status_work = 0");

// BƯỚC 2: Nếu đang trong khung giờ làm việc
if ($current_shift != '') {
    $sql = "UPDATE users u
            JOIN work_schedules ws ON u.id = ws.user_id
            SET u.status_work = 1
            WHERE ws.shift_date = '$current_date' 
            AND ws.shift_type = '$current_shift'
            AND u.status = 1";
    
    $conn->query($sql);
    $msg = "Đã cập nhật: Ca $current_shift ($current_date). Nhân viên có lịch đã chuyển sang trạng thái ĐANG LÀM.";
} else {
    $msg = "Hiện không phải giờ làm việc. Tất cả về trạng thái nghỉ.";
}

echo json_encode(['status' => 'success', 'message' => $msg]);
?>