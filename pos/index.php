<?php
session_start();
require_once '../includes/db_connection.php';
global $mysqli;

// Bảo vệ trang: Chỉ nhân viên hoặc admin đã đăng nhập mới vào được
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];

// 1. Thống kê nhanh trong ngày của nhân viên này
$today = date('Y-m-d');
$stats_query = "SELECT COUNT(id) as total_orders, SUM(total_price) as total_revenue 
                FROM orders 
                WHERE user_id = $user_id AND DATE(order_date) = '$today' AND status = 'paid'";
$stats_result = $mysqli->query($stats_query);
$stats = $stats_result->fetch_assoc();

// 2. Lấy danh sách nguyên liệu sắp hết để cảnh báo nhân viên
$low_stock_query = "SELECT name, quantity, unit FROM ingredients WHERE quantity <= min_quantity LIMIT 5";
$low_stock_result = $mysqli->query($low_stock_query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhân viên bán hàng | Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --coffee-dark: #6f4e37; --coffee-light: #f4f1ea; }
        body { background-color: var(--coffee-light); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar-user { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .stat-card { border: none; border-radius: 15 shadow: 0 4px 10px rgba(0,0,0,0.05); transition: 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .btn-main { background: var(--coffee-dark); color: white; border-radius: 10px; padding: 15px; font-weight: bold; border: none; width: 100%; transition: 0.3s; text-decoration: none; display: block; text-align: center; }
        .btn-main:hover { background: #5a3e2b; color: white; box-shadow: 0 5px 15px rgba(111, 78, 55, 0.3); }
        .alert-stock { border-left: 5px solid #ffc107; background: white; }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-brown"><i class="fa-solid fa-mug-hot me-2"></i> STAFF DASHBOARD</h3>
        <a href="../index.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Quay lại</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="sidebar-user mb-4 text-center">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($fullname) ?>&background=6f4e37&color=fff" class="rounded-circle mb-3" width="80">
                <h5 class="fw-bold"><?= $fullname ?></h5>
                <span class="badge bg-success mb-3">Đang trong ca làm việc</span>
                <hr>
                <div class="row text-start mt-3">
                    <div class="col-6 border-end">
                        <small class="text-muted d-block">Đơn hôm nay</small>
                        <span class="fw-bold fs-5"><?= $stats['total_orders'] ?? 0 ?></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Doanh thu cá nhân</small>
                        <span class="fw-bold fs-5 text-success"><?= number_format($stats['total_revenue'] ?? 0) ?>đ</span>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold mb-3"><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>CẢNH BÁO KHO</h6>
            <?php if ($low_stock_result->num_rows > 0): ?>
                <?php while($item = $low_stock_result->fetch_assoc()): ?>
                    <div class="alert alert-stock p-2 mb-2 shadow-sm">
                        <small class="d-block fw-bold"><?= $item['name'] ?></small>
                        <small class="text-danger">Còn lại: <?= $item['quantity'] ?> <?= $item['unit'] ?></small>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted small">Nguyên liệu đầy đủ.</p>
            <?php endif; ?>
        </div>

        <div class="col-lg-8">
            <div class="row g-3">
                <div class="col-md-12">
                    <a href="menu.php" class="btn-main fs-4 shadow-sm">
                        <i class="fa-solid fa-cart-plus me-2"></i> BẮT ĐẦU BÁN HÀNG MỚI
                    </a>
                </div>

                <div class="col-md-12">
                    <div class="card stat-card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Đơn hàng gần đây của bạn</h5>
<a href="history.php" class="btn btn-sm btn-link text-decoration-none">Xem tất cả</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Thời gian</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
$recent_orders = $mysqli->query("SELECT id, order_date, total_price, status 
                                FROM orders WHERE user_id = $user_id 
                                ORDER BY order_date DESC LIMIT 5");
while($row = $recent_orders->fetch_assoc()):
?>
<tr>
    <td>#<?= $row['id'] ?></td>
    <td><?= date('H:i', strtotime($row['order_date'])) ?></td>
    <td class="fw-bold"><?= number_format($row['total_price']) ?>đ</td>
    <td>
        <?php if ($row['status'] == 'paid'): ?>
<span class="badge bg-success">Đã trả</span>
        <?php elseif ($row['status'] == 'canceled'): ?>
<span class="badge bg-danger">Đã hủy</span>
        <?php else: ?>
<span class="badge bg-warning text-dark">Đang xử lý</span>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>