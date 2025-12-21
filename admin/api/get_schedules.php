<?php
require '../tinh-nang/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

// Lấy ngày bắt đầu tuần (Monday) từ tham số hoặc mặc định là tuần này
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('monday this week'));
$end_date = date('Y-m-d', strtotime($start_date . ' +6 days'));

$sql = "SELECT ws.id, ws.user_id, ws.shift_date, ws.shift_type, u.fullname 
        FROM work_schedules ws
        JOIN users u ON ws.user_id = u.id
        WHERE ws.shift_date BETWEEN '$start_date' AND '$end_date'";

$query = mysqli_query($conn, $sql);
$schedules = [];
while ($row = mysqli_fetch_assoc($query)) {
    $schedules[] = $row;
}

echo json_encode(['status' => 'success', 'data' => $schedules, 'start_date' => $start_date]);
?>