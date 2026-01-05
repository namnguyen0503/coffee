<?php
require '../../includes/db_connection.php';
$conn = connect_db();

// 1. Nhận tham số tìm kiếm
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// 2. Nhận tham số ngày tháng (Mặc định lấy ngày hôm nay nếu không chọn)
$start_date = isset($_GET['start']) && !empty($_GET['start']) ? $_GET['start'] : date('Y-m-d');
$end_date   = isset($_GET['end']) && !empty($_GET['end']) ? $_GET['end'] : date('Y-m-d');

// Thêm giờ vào để lấy trọn vẹn ngày
$start_sql = "$start_date 00:00:00";
$end_sql   = "$end_date 23:59:59";

// 3. Viết câu lệnh SQL (Kết hợp tìm kiếm và lọc ngày)
$sql = "SELECT o.*, u.fullname as staff_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE (o.id LIKE '%$search%' OR u.fullname LIKE '%$search%')
        AND (o.order_date BETWEEN '$start_sql' AND '$end_sql')
        ORDER BY o.id DESC"; // Sắp xếp đơn mới nhất lên đầu

$query = mysqli_query($conn, $sql);

// --- PHẦN HIỂN THỊ HTML (GIỮ NGUYÊN GIAO DIỆN CŨ CỦA BẠN) ---
if ($query && mysqli_num_rows($query) > 0) {
    echo '<div class="table-responsive"><table class="table table-hover table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Mã Đơn</th>
                    <th>Thời gian</th>
                    <th>Người bán</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>';
    
    while ($row = mysqli_fetch_assoc($query)) {
        // Xử lý trạng thái
        $status_badge = '';
        if($row['status'] == 'paid') $status_badge = '<span class="badge badge-success">Đã thanh toán</span>';
        elseif($row['status'] == 'canceled') $status_badge = '<span class="badge badge-danger">Đã hủy</span>';
        else $status_badge = '<span class="badge badge-warning">Chưa thanh toán</span>';

        $final_price = number_format($row['final_amount'], 0, ',', '.');
        
        // Nút hủy chỉ hiện khi chưa hủy
        $btn_cancel = ($row['status'] != 'canceled') ? 
            '<button class="btn btn-sm btn-danger ml-1" onclick="xacNhanHuy('.$row['id'].')">
                <i class="fas fa-ban"></i> Hủy
            </button>' : '';

        echo '<tr>
                <td><strong>#'.$row['id'].'</strong></td>
                <td>'.date('H:i d/m/Y', strtotime($row['order_date'])).'</td>
                <td>'.$row['staff_name'].'</td>
                <td class="font-weight-bold text-success">'.$final_price.' đ</td>
                <td>'.$status_badge.'</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-info" onclick="xemChiTietDon('.$row['id'].')">
                        <i class="fas fa-eye"></i> Xem
                    </button>
                    '.$btn_cancel.'
                </td>
              </tr>';
    }
    echo '</tbody></table></div>';
} else {
    echo '<div class="alert alert-info text-center mt-3">
            Không tìm thấy đơn hàng nào từ <b>'.date('d/m/Y', strtotime($start_date)).'</b> đến <b>'.date('d/m/Y', strtotime($end_date)).'</b>.
          </div>';
}
?>