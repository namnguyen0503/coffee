<?php
// admin/api/get_report_stats.php
require '../../includes/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

// 1. Nhận tham số ngày tháng từ Client
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$end_date   = isset($_GET['end'])   ? $_GET['end']   : date('Y-m-d');

$start_datetime = $start_date . " 00:00:00";
$end_datetime   = $end_date . " 23:59:59";

// =================================================================================
// BƯỚC 1: TÍNH GIÁ VỐN TRUNG BÌNH CỦA TỪNG NGUYÊN LIỆU (Dựa trên inventory_log)
// =================================================================================
// Logic: Tổng tiền nhập / Tổng số lượng nhập (Weighted Average Cost)
// Chỉ lấy các log có type = 'import'

$ing_costs = []; // Mảng [ingredient_id => price_per_unit]

$sql_avg_cost = "SELECT 
                    ingredient_id, 
                    SUM(cost) as total_import_cost, 
                    SUM(quantity) as total_import_qty
                 FROM inventory_log 
                 WHERE type = 'import' 
                 GROUP BY ingredient_id";

$result_avg = mysqli_query($conn, $sql_avg_cost);
while ($row = mysqli_fetch_assoc($result_avg)) {
    if ($row['total_import_qty'] > 0) {
        $ing_costs[$row['ingredient_id']] = $row['total_import_cost'] / $row['total_import_qty'];
    }
}

// Fallback: Nếu nguyên liệu chưa từng nhập trong log, lấy giá từ bảng ingredients (nếu bạn đã thêm cột avg_price)
// Hoặc mặc định là 0 nếu không tìm thấy.

// =================================================================================
// BƯỚC 2: TÍNH GIÁ VỐN CỦA TỪNG SẢN PHẨM (PRODUCT BASE COST)
// =================================================================================
// Dựa vào bảng recipes và giá vốn nguyên liệu vừa tính ở trên

$product_base_cost = []; // Mảng [product_id => cost]

$sql_recipes = "SELECT product_id, ingredient_id, quantity_required FROM recipes";
$result_recipes = mysqli_query($conn, $sql_recipes);

while ($row = mysqli_fetch_assoc($result_recipes)) {
    $pid = $row['product_id'];
    $iid = $row['ingredient_id'];
    $qty = $row['quantity_required'];
    
    // Lấy giá vốn nguyên liệu, nếu không có thì bằng 0
    $unit_cost = isset($ing_costs[$iid]) ? $ing_costs[$iid] : 0;
    
    if (!isset($product_base_cost[$pid])) {
        $product_base_cost[$pid] = 0;
    }
    $product_base_cost[$pid] += ($qty * $unit_cost);
}

// =================================================================================
// BƯỚC 3: LẤY DANH SÁCH ĐƠN HÀNG VÀ TÍNH TOÁN
// =================================================================================

$total_revenue = 0;
$total_cogs    = 0; // Cost of Goods Sold (Giá vốn hàng bán)
$total_orders  = 0;
$report_table  = '';

// Chỉ lấy đơn hàng đã thanh toán ('paid')
$sql_orders = "SELECT o.id, o.order_date, o.total_price, o.final_amount, o.payment_method, u.fullname as staff_name 
               FROM orders o
               LEFT JOIN users u ON o.user_id = u.id
               WHERE o.status = 'paid' 
               AND o.order_date BETWEEN '$start_datetime' AND '$end_datetime'
               ORDER BY o.order_date DESC";

$query_orders = mysqli_query($conn, $sql_orders);

if (mysqli_num_rows($query_orders) > 0) {
    while ($order = mysqli_fetch_assoc($query_orders)) {
        $order_id = $order['id'];
        
        // 1. Tính doanh thu thực tế (Ưu tiên final_amount nếu có discount)
        $revenue = ($order['final_amount'] > 0) ? $order['final_amount'] : $order['total_price'];
        $total_revenue += $revenue;
        $total_orders++;

        // 2. Tính giá vốn của đơn hàng này
        $order_cogs = 0;
        
        // Lấy chi tiết món trong đơn
        $sql_items = "SELECT product_id, quantity FROM order_items WHERE order_id = $order_id";
        $query_items = mysqli_query($conn, $sql_items);
        
        while ($item = mysqli_fetch_assoc($query_items)) {
            $pid = $item['product_id'];
            $qty_sold = $item['quantity'];
            
            // Nếu sản phẩm có công thức, cộng dồn giá vốn
            if (isset($product_base_cost[$pid])) {
                $order_cogs += $product_base_cost[$pid] * $qty_sold;
            }
        }
        $total_cogs += $order_cogs;
        
        // Tính lợi nhuận đơn lẻ
        $order_profit = $revenue - $order_cogs;

        // Tạo dòng HTML cho bảng báo cáo chi tiết
        $report_table .= '<tr>
            <td>#' . $order_id . '</td>
            <td>' . date('H:i d/m/Y', strtotime($order['order_date'])) . '</td>
            <td>' . htmlspecialchars($order['staff_name']) . '</td>
            <td class="text-right">' . number_format($revenue) . ' đ</td>
            <td class="text-right text-danger">' . number_format($order_cogs) . ' đ</td>
            <td class="text-right text-success font-weight-bold">' . number_format($order_profit) . ' đ</td>
        </tr>';
    }
} else {
    $report_table = '<tr><td colspan="6" class="text-center text-muted">Không có đơn hàng đã thanh toán trong khoảng thời gian này.</td></tr>';
}

// =================================================================================
// BƯỚC 4: TRẢ VỀ JSON
// =================================================================================

$response = [
    'status' => 'success',
    'summary' => [
        'revenue' => $total_revenue,
        'cogs'    => $total_cogs,
        'profit'  => $total_revenue - $total_cogs,
        'orders'  => $total_orders
    ],
    'html' => $report_table
];

echo json_encode($response);
?>