<?php
require '../../includes/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $shift_date = $_POST['shift_date'];
    $shift_type = $_POST['shift_type'];
    
    // Nhận cờ force_unlock từ client gửi lên
    $force_unlock = isset($_POST['force_unlock']) && $_POST['force_unlock'] == 'true';

    // 1. KIỂM TRA TRẠNG THÁI TÀI KHOẢN (status)
    $user_check = $conn->query("SELECT status, fullname FROM users WHERE id = $user_id");
    $user_data = $user_check->fetch_assoc();

    if ($user_data && $user_data['status'] == 0) {
        // Nếu chưa có lệnh mở khóa, trả về yêu cầu xác nhận
        if (!$force_unlock) {
            echo json_encode([
                'status' => 'locked_user', 
                'message' => "Nhân viên " . $user_data['fullname'] . " đang bị KHÓA (Nghỉ việc).\nBạn có muốn KÍCH HOẠT LẠI nhân viên này để xếp lịch không?"
            ]);
            exit;
        } else {
            // Nếu Admin đồng ý -> Cập nhật status = 1 (Mở khóa tài khoản)
            $conn->query("UPDATE users SET status = 1 WHERE id = $user_id");
        }
    }

    // 2. KIỂM TRA TRÙNG LỊCH
    $check = $conn->query("SELECT id FROM work_schedules WHERE user_id = $user_id AND shift_date = '$shift_date' AND shift_type = '$shift_type'");
    if ($check->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Nhân viên này đã được phân công vào ca này rồi!']);
        exit;
    }

    // 3. THÊM LỊCH VÀO DATABASE
    $sql = "INSERT INTO work_schedules (user_id, shift_date, shift_type) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $shift_date, $shift_type);

    if ($stmt->execute()) {
        $msg = 'Phân ca thành công!';
        if ($force_unlock) {
            $msg .= ' (Tài khoản nhân viên đã được mở khóa)';
        }
        echo json_encode(['status' => 'success', 'message' => $msg]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống: ' . $conn->error]);
    }
}
?>