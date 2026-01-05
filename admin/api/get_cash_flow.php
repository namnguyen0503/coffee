<?php
require '../../includes/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

$start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$end   = isset($_GET['end'])   ? $_GET['end']   : date('Y-m-d');
$diff_only = isset($_GET['diff_only']) && $_GET['diff_only'] == 'true'; 

$sql = "SELECT curr.*, u.fullname,
        (SELECT prev.end_cash FROM work_sessions prev WHERE prev.id < curr.id ORDER BY prev.id DESC LIMIT 1) as prev_end_cash
        FROM work_sessions curr
        LEFT JOIN users u ON curr.user_id = u.id
        WHERE DATE(curr.start_time) BETWEEN '$start' AND '$end'
        ORDER BY curr.id DESC";

$result = $conn->query($sql);
$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $current_start = floatval($row['start_cash']);
        $prev_end = ($row['prev_end_cash'] !== null) ? floatval($row['prev_end_cash']) : $current_start;
        
        $diff = $current_start - $prev_end;
        $diff_abs = abs($diff); // Lấy giá trị tuyệt đối (Bỏ dấu âm)
        
        $row['prev_end_cash_display'] = ($row['prev_end_cash'] !== null) ? number_format($prev_end) : '-';
        $row['diff_amount'] = $diff;
        
        // Logic hiển thị: Nếu lệch -> Đỏ + Số dương, Nếu khớp -> Xanh
        if ($diff != 0) {
            // Hiển thị số dương (abs) nhưng màu đỏ
            $row['diff_html'] = '<span class="badge badge-danger" style="font-size:14px">'.number_format($diff_abs).'</span>';
        } else {
            $row['diff_html'] = '<span class="badge badge-success">Khớp</span>';
        }

        $row['start_fmt'] = date('H:i d/m', strtotime($row['start_time']));
        
        $is_active = empty($row['end_time']);
        $row['end_fmt'] = $is_active ? '<span class="badge badge-warning">Đang làm</span>' : date('H:i d/m', strtotime($row['end_time']));
        
        $row['start_cash_fmt'] = number_format($row['start_cash']);
        $row['end_cash_fmt']   = $is_active ? '<span class="text-muted text-sm">Chưa chốt</span>' : number_format($row['end_cash']);
        $row['total_sales_fmt'] = number_format($row['total_sales']);
        $row['note'] = $row['note'] ? $row['note'] : '';

        if ($diff_only) {
            if ($diff != 0) $data[] = $row;
        } else {
            $data[] = $row;
        }
    }
}

echo json_encode(['data' => $data]);
?>