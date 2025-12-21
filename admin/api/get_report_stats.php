<?php
require '../tinh-nang/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

// 1. Nhận ngày bắt đầu - kết thúc (Mặc định là 7 ngày gần nhất)
$end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-6 days'));

// Thêm giờ vào để lấy trọn vẹn ngày
$start_sql = "$start_date 00:00:00";
$end_sql = "$end_date 23:59:59";

// 2. TÍNH TỔNG QUAN (Chỉ tính đơn đã thanh toán 'paid')
$sql_summary = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(final_amount) as total_revenue,
                    SUM(total_price) as total_original,
                    SUM(total_price - final_amount) as total_discount
                FROM orders 
                WHERE status = 'paid' 
                AND order_date BETWEEN '$start_sql' AND '$end_sql'";

$res_summary = mysqli_query($conn, $sql_summary);
$row = mysqli_fetch_assoc($res_summary);

$summary = [
    'revenue' => $row['total_revenue'] ?? 0,
    'orders' => $row['total_orders'] ?? 0,
    'discount' => $row['total_discount'] ?? 0,
    // Giả sử tiền mặt = doanh thu thực (nếu bạn có thanh toán thẻ thì cần tách sau)
    'cash' => $row['total_revenue'] ?? 0 
];

// 3. DỮ LIỆU BIỂU ĐỒ (Group by Date)
$sql_chart = "SELECT DATE(order_date) as date, SUM(final_amount) as revenue 
              FROM orders 
              WHERE status = 'paid' 
              AND order_date BETWEEN '$start_sql' AND '$end_sql'
              GROUP BY DATE(order_date) 
              ORDER BY date ASC";

$res_chart = mysqli_query($conn, $sql_chart);
$labels = [];
$data = [];

while ($c = mysqli_fetch_assoc($res_chart)) {
    $labels[] = date('d/m', strtotime($c['date']));
    $data[] = (int)$c['revenue'];
}

// 4. DANH SÁCH CHI TIẾT (Để hiển thị bảng xem trước)
$sql_list = "SELECT o.id, o.order_date, o.final_amount, u.fullname 
             FROM orders o 
             JOIN users u ON o.user_id = u.id 
             WHERE o.status = 'paid' 
             AND o.order_date BETWEEN '$start_sql' AND '$end_sql' 
             ORDER BY o.order_date DESC";
$res_list = mysqli_query($conn, $sql_list);
$table_html = '';
while($l = mysqli_fetch_assoc($res_list)){
    $table_html .= '<tr>
        <td>#'.$l['id'].'</td>
        <td>'.date('H:i d/m/Y', strtotime($l['order_date'])).'</td>
        <td>'.$l['fullname'].'</td>
        <td class="text-right font-weight-bold">'.number_format($l['final_amount']).' đ</td>
    </tr>';
}

echo json_encode([
    'summary' => $summary,
    'chart' => ['labels' => $labels, 'data' => $data],
    'table' => $table_html
]);
?>