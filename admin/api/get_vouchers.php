<?php
require '../../includes/db_connection.php';
$conn = connect_db();

$sql = "SELECT * FROM vouchers ORDER BY id DESC";
$query = mysqli_query($conn, $sql);

if (mysqli_num_rows($query) > 0) {
    echo '<div class="row">';
    while ($row = mysqli_fetch_assoc($query)) {
        echo '
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="font-weight-bold text-primary mb-1">'.$row['code'].'</h4>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Giảm: '.$row['discount_percent'].'%</div>
                            <small class="text-muted">'.$row['description'].'</small>
                        </div>
                        <div class="text-right">
                             <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                             <button class="btn btn-sm btn-danger mt-2 d-block" onclick="xoaVoucher('.$row['id'].')">
                                <i class="fas fa-trash"></i> Xóa
                             </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }
    echo '</div>';
} else {
    echo '<div class="alert alert-info text-center">Chưa có mã giảm giá nào.</div>';
}
?>