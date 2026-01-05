<?php
require '../../includes/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

$start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-t');

// Sắp xếp: Ngày tăng dần -> Ca Sáng(1) -> Chiều(2) -> Tối(3)
$sql = "SELECT s.*, u.fullname 
        FROM work_schedules s 
        JOIN users u ON s.user_id = u.id 
        WHERE s.shift_date BETWEEN '$start' AND '$end' 
        ORDER BY s.shift_date ASC, 
        CASE s.shift_type 
            WHEN 'morning' THEN 1 
            WHEN 'afternoon' THEN 2 
            WHEN 'evening' THEN 3 
        END";

$result = $conn->query($sql);
$data = [];

$days_vn = [
    'Sun' => 'Chủ nhật', 'Mon' => 'Thứ 2', 'Tue' => 'Thứ 3', 
    'Wed' => 'Thứ 4', 'Thu' => 'Thứ 5', 'Fri' => 'Thứ 6', 'Sat' => 'Thứ 7'
];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['day_name'] = $days_vn[date('D', strtotime($row['shift_date']))];
        $row['date_fmt'] = date('d/m/Y', strtotime($row['shift_date']));
        
        // Tạo label đẹp cho Ca
        if($row['shift_type'] == 'morning') $row['shift_txt'] = 'Sáng (7h-12h)';
        elseif($row['shift_type'] == 'afternoon') $row['shift_txt'] = 'Chiều (12h-17h)';
        else $row['shift_txt'] = 'Tối (17h-23h30)';
        
        $data[] = $row;
    }
}

echo json_encode(['data' => $data]);
?>