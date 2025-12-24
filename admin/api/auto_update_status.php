<?php
require '../../includes/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

// --- QUAN TRỌNG: Cài đặt múi giờ Việt Nam ---
date_default_timezone_set('Asia/Ho_Chi_Minh'); 

// Lấy giờ hiện tại
$current_date = date('Y-m-d');
$current_hour = (int)date('H');
$current_minute = (int)date('i');
$current_shift = '';

// Debug: Ghi log để kiểm tra giờ hệ thống đang hiểu là mấy giờ (xem trong F12 Network nếu cần)
// echo json_encode(['server_time' => date('H:i:s')]); exit; 

// --- LOGIC CA LÀM VIỆC ---
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
    if ($current_hour < 23) {
        $current_shift = 'evening';
    } 
    elseif ($current_hour == 23 && $current_minute <= 30) {
        $current_shift = 'evening';
    }
}

// BƯỚC 1: Reset toàn bộ nhân viên về trạng thái nghỉ
$conn->query("UPDATE users SET status_work = 0");

// BƯỚC 2: Nếu đang trong khung giờ làm việc thì bật status_work = 1 cho ai có lịch
if ($current_shift != '') {
    $sql = "UPDATE users u
            JOIN work_schedules ws ON u.id = ws.user_id
            SET u.status_work = 1
            WHERE ws.shift_date = '$current_date' 
            AND ws.shift_type = '$current_shift'
            AND u.status = 1"; // Chỉ bật nếu tài khoản không bị khóa vĩnh viễn
    
    $conn->query($sql);
    $msg = "Đã cập nhật (Giờ Server: " . date('H:i') . "): Ca $current_shift. Nhân viên có lịch -> ON.";
} else {
    $msg = "Ngoài giờ làm việc (Giờ Server: " . date('H:i') . "). Tất cả -> OFF.";
}

echo json_encode(['status' => 'success', 'message' => $msg]);
?>