<?php
require '../../includes/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $shift_date = $_POST['shift_date'];
    $shift_type = $_POST['shift_type'];

    // 1. Kiểm tra trùng lịch (Người này đã làm ca này ngày này chưa?)
    $check = $conn->query("SELECT id FROM work_schedules WHERE user_id = $user_id AND shift_date = '$shift_date' AND shift_type = '$shift_type'");
    if ($check->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Nhân viên này đã được phân công vào ca này rồi!']);
        exit;
    }

    // 2. Thêm lịch
    $sql = "INSERT INTO work_schedules (user_id, shift_date, shift_type) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $shift_date, $shift_type);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Phân ca thành công!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống.']);
    }
}
?>