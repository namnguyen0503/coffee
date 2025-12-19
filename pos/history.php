<?php
session_start();
require_once '../includes/db_connection.php';
global $mysqli;

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];

// Xử lý bộ lọc ngày (nếu khách muốn xem theo ngày cụ thể)
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
$where_clause = "WHERE user_id = $user_id";
if ($filter_date) {
    $where_clause .= " AND DATE(order_date) = '$filter_date'";
}

// Truy vấn danh sách đơn hàng
$query = "SELECT * FROM orders $where_clause ORDER BY order_date DESC";
$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử đơn hàng cá nhân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f1ea; }
        .table-container { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fa-solid fa-clock-rotate-left me-2"></i>Lịch sử đơn hàng của bạn</h4>
        <a href="index.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form class="row g-3" method="GET">
                <div class="col-auto">
                    <label class="form-label">Xem theo ngày:</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="<?= $filter_date ?>">
                </div>
                <div class="col-auto d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm me-2">Lọc</button>
                    <a href="history.php" class="btn btn-outline-secondary btn-sm">Tất cả</a>
                </div>
            </form>
        </div>
    </div>

    <div class="table-container">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Mã đơn</th>
                    <th>Ngày giờ</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $row['id'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['order_date'])) ?></td>
                        <td class="fw-bold"><?= number_format($row['total_price']) ?>đ</td>
                        <td><span class="badge bg-success">Đã thanh toán</span></td>
                        <td>
                            <button class="btn btn-outline-info btn-sm">Xem món</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted">Bạn chưa có đơn hàng nào trong ngày này.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>