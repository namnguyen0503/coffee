<?php
require '../../includes/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

// 1. Nhận ngày
$end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-6 days'));
$start_sql = "$start_date 00:00:00";
$end_sql = "$end_date 23:59:59";

// 2. TÍNH DOANH THU (Như cũ)
$sql_revenue = "SELECT 
    COUNT(*) as total_orders,
    SUM(final_amount) as total_revenue,
    SUM(total_price - final_amount) as total_discount
FROM orders WHERE status = 'paid' AND order_date BETWEEN '$start_sql' AND '$end_sql'";
$row_rev = mysqli_query($conn, $sql_revenue)->fetch_assoc();

// 3. TÍNH GIÁ VỐN (COGS) - LOGIC MỚI QUAN TRỌNG
// B3.1: Tính giá nhập trung bình của TẤT CẢ nguyên liệu từ trước đến nay
$sql_avg_cost = "SELECT ingredient_id, SUM(cost) as total_import_cost, SUM(quantity) as total_import_qty 
                 FROM inventory_log 
                 WHERE type = 'import' 
                 GROUP BY ingredient_id";
$query_avg = mysqli_query($conn, $sql_avg_cost);
$avg_prices = []; // Mảng lưu giá vốn: [id_nguyen_lieu => gia_1_don_vi]

while($r = mysqli_fetch_assoc($query_avg)){
    if($r['total_import_qty'] > 0){
        $avg_prices[$r['ingredient_id']] = $r['total_import_cost'] / $r['total_import_qty'];
    } else {
        $avg_prices[$r['ingredient_id']] = 0;
    }
}

// B3.2: Lấy tổng lượng xuất kho (tiêu hao) trong khoảng thời gian báo cáo
$sql_usage = "SELECT ingredient_id, SUM(quantity) as used_qty 
              FROM inventory_log 
              WHERE type = 'export' 
              AND created_at BETWEEN '$start_sql' AND '$end_sql' 
              GROUP BY ingredient_id";
$query_usage = mysqli_query($conn, $sql_usage);

$total_cogs = 0; // Tổng giá vốn hàng bán
while($u = mysqli_fetch_assoc($query_usage)){
    $ing_id = $u['ingredient_id'];
    $qty = $u['used_qty'];
    // Nếu có giá nhập thì nhân, không thì coi như = 0
    $price = isset($avg_prices[$ing_id]) ? $avg_prices[$ing_id] : 0;
    $total_cogs += ($qty * $price);
}

// 4. Tổng hợp dữ liệu
$revenue = $row_rev['total_revenue'] ?? 0;
$profit = $revenue - $total_cogs; // Lợi nhuận = Doanh thu - Giá vốn

// 5. Dữ liệu biểu đồ (Vẫn vẽ doanh thu)
$sql_chart = "SELECT DATE(order_date) as date, SUM(final_amount) as revenue 
              FROM orders WHERE status = 'paid' AND order_date BETWEEN '$start_sql' AND '$end_sql'
              GROUP BY DATE(order_date) ORDER BY date ASC";
$res_chart = mysqli_query($conn, $sql_chart);
$labels = []; $data = [];
while ($c = mysqli_fetch_assoc($res_chart)) {
    $labels[] = date('d/m', strtotime($c['date']));
    $data[] = (int)$c['revenue'];
}

// 6. Trả về kết quả
echo json_encode([
    'summary' => [
        'revenue' => $revenue,
        'orders' => $row_rev['total_orders'] ?? 0,
        'discount' => $row_rev['total_discount'] ?? 0,
        'cogs' => $total_cogs, // Giá vốn (Tiền nguyên liệu)
        'profit' => $profit    // Lợi nhuận thực
    ],
    'chart' => ['labels' => $labels, 'data' => $data],
    'table' => "" // Bạn có thể giữ code bảng cũ ở đây
]);
?>