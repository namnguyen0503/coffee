<?php
require '../tinh-nang/db_connection.php';$conn = connect_db();

// Lấy tham số tìm kiếm (nếu tìm theo ID đơn hoặc tên nhân viên)
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$sql = "SELECT o.*, u.fullname as staff_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id LIKE '%$search%' OR u.fullname LIKE '%$search%'
        ORDER BY o.order_date DESC LIMIT 50"; // Lấy 50 đơn mới nhất

$query = mysqli_query($conn, $sql);

if (mysqli_num_rows($query) > 0) {
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
        $status_badge = '';
        if($row['status'] == 'paid') $status_badge = '<span class="badge badge-success">Đã thanh toán</span>';
        elseif($row['status'] == 'canceled') $status_badge = '<span class="badge badge-danger">Đã hủy</span>';
        else $status_badge = '<span class="badge badge-warning">Chưa thanh toán</span>';

        $final_price = number_format($row['final_amount'], 0, ',', '.');
        
        // Chỉ hiện nút Hủy nếu đơn chưa hủy
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
                        <i class="fas fa-eye"></i> Chi tiết
                    </button>
                    '.$btn_cancel.'
                </td>
              </tr>';
    }
    echo '</tbody></table></div>';
} else {
    echo '<div class="alert alert-info text-center">Chưa có đơn hàng nào.</div>';
}
?>