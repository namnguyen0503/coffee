<?php
require '../../includes/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

// 1. Nhận tham số ngày tháng
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$end_date   = isset($_GET['end'])   ? $_GET['end']   : date('Y-m-d');

$start_datetime = $start_date . " 00:00:00";
$end_datetime   = $end_date . " 23:59:59";

// =================================================================================
// BƯỚC 1: TÍNH GIÁ VỐN TRUNG BÌNH THEO FIFO (Hàng tồn kho thực tế)
// =================================================================================
// Logic: Chỉ tính giá vốn dựa trên số lượng hàng đang còn tồn trong kho.
// Bỏ qua giá của những lô hàng cũ đã bán hết.

$ing_costs = []; // Mảng lưu giá vốn 1 đơn vị: [id_nguyen_lieu => gia_1_don_vi]

// Lấy danh sách nguyên liệu và tồn kho hiện tại
$sql_ing = "SELECT id, quantity, unit FROM ingredients";
$query_ing = mysqli_query($conn, $sql_ing);

while ($ing = mysqli_fetch_assoc($query_ing)) {
    $ing_id = $ing['id'];
    $current_stock = $ing['quantity']; 
    
    $calculated_cost = 0;
    
    // Nếu còn hàng tồn -> Lần ngược lịch sử nhập để tính giá
    if ($current_stock > 0) {
        // Lấy các lần nhập hàng, mới nhất lên trước
        $sql_hist = "SELECT quantity, price FROM warehouse_history 
                     WHERE ingredient_id = $ing_id 
                     AND (action_type = 'import' OR action_type = 'add') 
                     ORDER BY id DESC";
        $q_hist = mysqli_query($conn, $sql_hist);
        
        $collected_qty = 0;
        $total_value = 0;

        while ($h = mysqli_fetch_assoc($q_hist)) {
            $batch_qty = $h['quantity'];   // Số lượng nhập (ví dụ: 5000g)
            $batch_price = $h['price'];    // Tổng tiền nhập (ví dụ: 1.000.000đ)
            
            // --- XỬ LÝ QUAN TRỌNG: TÍNH ĐƠN GIÁ CỦA LÔ NÀY ---
            // Nếu DB lưu 'price' là TỔNG TIỀN (thường gặp):
            $unit_price = ($batch_qty > 0) ? ($batch_price / $batch_qty) : 0;
            
            // Nếu DB lưu 'price' là ĐƠN GIÁ (ít gặp hơn), hãy mở comment dòng dưới:
            // $unit_price = $batch_price; 

            // Lấy số lượng cần từ lô này để khớp với tồn kho hiện tại
            $needed = $current_stock - $collected_qty;
            
            if ($needed <= 0) break; // Đã lấy đủ

            if ($batch_qty >= $needed) {
                // Lô này nhiều hơn cần thiết -> lấy phần cần thiết
                $collected_qty += $needed;
                $total_value += ($needed * $unit_price);
            } else {
                // Lô này ít hơn -> lấy hết lô này và tìm tiếp lô cũ hơn
                $collected_qty += $batch_qty;
                $total_value += ($batch_qty * $unit_price);
            }
        }
        
        // Giá trung bình = Tổng giá trị / Tổng số lượng thu thập được
        if ($collected_qty > 0) {
            $calculated_cost = $total_value / $collected_qty;
        }
    } 
    
    // TRƯỜNG HỢP KHO HẾT HÀNG (Stock <= 0)
    // Lấy giá của lần nhập gần nhất để làm giá vốn tạm tính
    if ($calculated_cost == 0) {
        $sql_last = "SELECT quantity, price FROM warehouse_history 
                     WHERE ingredient_id = $ing_id 
                     AND (action_type = 'import' OR action_type = 'add') 
                     ORDER BY id DESC LIMIT 1";
        $q_last = mysqli_query($conn, $sql_last);
        if ($last = mysqli_fetch_assoc($q_last)) {
            if ($last['quantity'] > 0) {
                // Giả định price là TỔNG TIỀN
                $calculated_cost = $last['price'] / $last['quantity'];
                
                // Nếu price là ĐƠN GIÁ thì dùng:
                // $calculated_cost = $last['price'];
            }
        }
    }

    $ing_costs[$ing_id] = $calculated_cost;
}

// =================================================================================
// BƯỚC 2: TÍNH GIÁ VỐN (BASE COST) CHO TỪNG MÓN
// =================================================================================
$sql_recipe = "SELECT product_id, ingredient_id, quantity_required 
               FROM product_ingredients";
$query_recipe = mysqli_query($conn, $sql_recipe);
$product_base_cost = []; 

if ($query_recipe) {
    while ($row = mysqli_fetch_assoc($query_recipe)) {
        $pid = $row['product_id'];
        $iid = $row['ingredient_id'];
        $qty_needed = $row['quantity_required']; // Lượng cần cho 1 món (ví dụ: 20g)
        
        $cost_one_unit = isset($ing_costs[$iid]) ? $ing_costs[$iid] : 0;
        
        if (!isset($product_base_cost[$pid])) {
            $product_base_cost[$pid] = 0;
        }
        // Cộng dồn tiền nguyên liệu
        $product_base_cost[$pid] += ($qty_needed * $cost_one_unit);
    }
}

// =================================================================================
// BƯỚC 3: TỔNG HỢP DOANH THU & LỢI NHUẬN
// =================================================================================
$sql_orders = "SELECT o.id, o.created_at, o.total_amount, u.fullname as staff_name
               FROM orders o 
               LEFT JOIN users u ON o.user_id = u.id
               WHERE o.created_at BETWEEN '$start_datetime' AND '$end_datetime' 
               AND o.status = 'completed'
               ORDER BY o.created_at DESC";

$query_orders = mysqli_query($conn, $sql_orders);

$total_revenue = 0; 
$total_cogs    = 0; 
$total_orders  = 0;
$report_table  = "";
$chart_data    = []; 

// Tạo khung dữ liệu biểu đồ
$period = new DatePeriod(
     new DateTime($start_date),
     new DateInterval('P1D'),
     (new DateTime($end_date))->modify('+1 day')
);
foreach ($period as $key => $value) {
    $chart_data[$value->format('d/m')] = 0;
}

if (mysqli_num_rows($query_orders) > 0) {
    while ($order = mysqli_fetch_assoc($query_orders)) {
        $order_id = $order['id'];
        $total_revenue += $order['total_amount'];
        $total_orders++;
        
        $date_key = date('d/m', strtotime($order['created_at']));
        if (isset($chart_data[$date_key])) {
            $chart_data[$date_key] += $order['total_amount'];
        }

        // Tính giá vốn (COGS) của đơn hàng này
        $sql_details = "SELECT product_id, quantity FROM order_details WHERE order_id = $order_id";
        $q_details = mysqli_query($conn, $sql_details);
        
        $order_cogs = 0;
        while ($d = mysqli_fetch_assoc($q_details)) {
            $pid = $d['product_id'];
            $qty_sold = $d['quantity'];
            
            if (isset($product_base_cost[$pid])) {
                $order_cogs += $product_base_cost[$pid] * $qty_sold;
            }
        }
        $total_cogs += $order_cogs;

        $report_table .= '<tr>
            <td>#' . $order['id'] . '</td>
            <td>' . date('H:i d/m', strtotime($order['created_at'])) . '</td>
            <td>' . $order['staff_name'] . '</td>
            <td class="text-right text-success font-weight-bold">' . number_format($order['total_amount']) . ' đ</td>
        </tr>';
    }
} else {
    $report_table = '<tr><td colspan="4" class="text-center text-muted">Không có đơn hàng nào.</td></tr>';
}

$response = [
    'summary' => [
        'revenue' => $total_revenue,
        'cogs'    => $total_cogs,
        'profit'  => $total_revenue - $total_cogs,
        'orders'  => $total_orders
    ],
    'table' => $report_table,
    'chart' => [
        'labels' => array_keys($chart_data),
        'data'   => array_values($chart_data)
    ]
];

echo json_encode($response);
?>