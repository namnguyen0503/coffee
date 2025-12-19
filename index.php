<?php
session_start();

// 1. Kiểm tra đăng nhập (Middleware)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Lấy thông tin người dùng từ session
$fullname = $_SESSION['fullname'];
$role = $_SESSION['role']; // 'admin' hoặc 'staff'
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống quản lý Cafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .dashboard-container { margin-top: 50px; }
        .welcome-banner { background: #6f4e37; color: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        
        /* Style cho các thẻ chức năng (Card) */
        .feature-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            height: 100%;
            text-decoration: none; /* Bỏ gạch chân link */
            color: inherit;
        }
        .feature-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .card-icon { font-size: 3rem; margin-bottom: 15px; }
        .bg-pos { background: linear-gradient(135deg, #11998e, #38ef7d); color: white; }
        .bg-admin { background: linear-gradient(135deg, #eb3349, #f45c43); color: white; }
    </style>
</head>
<body>

<div class="container dashboard-container">
    <div class="welcome-banner d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0">Xin chào, <?= htmlspecialchars($fullname) ?>!</h2>
            <small>Chức vụ: <?= ($role === 'admin') ? 'Quản lý' : 'Nhân viên' ?></small>
        </div>
        <a href="logout.php" class="btn btn-outline-light btn-sm">
            <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
        </a>
    </div>

    <div class="row justify-content-center g-4">
        
        <div class="col-md-5 col-sm-6">
            <a href="pos/index.php" class="card feature-card bg-pos text-center p-5">
                <div class="card-body">
                    <i class="fa-solid fa-cash-register card-icon"></i>
                    <h3>Bán Hàng (POS)</h3>
                    <p>Tạo đơn hàng, tính tiền cho khách</p>
                </div>
            </a>
        </div>

        <?php if ($role === 'admin'): ?>
        <div class="col-md-5 col-sm-6">
            <a href="admin/index.php" class="card feature-card bg-admin text-center p-5">
                <div class="card-body">
                    <i class="fa-solid fa-chart-line card-icon"></i>
                    <h3>Quản Trị (Admin)</h3>
                    <p>Quản lý thực đơn, xem doanh thu</p>
                </div>
            </a>
        </div>
        <?php endif; ?>

        </div>
</div>

</body>
</html>