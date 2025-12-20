<?php
session_start();

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Lấy thông tin
$fullname = $_SESSION['fullname'];
$role = $_SESSION['role']; 

// Xử lý hiển thị tên chức vụ cho đẹp
$role_label = 'Nhân viên';
if ($role === 'admin') $role_label = 'Quản lý cấp cao';
if ($role === 'wh-staff') $role_label = 'Thủ kho';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Nguyễn Văn Coffee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .dashboard-container { margin-top: 50px; }
        .welcome-banner { background: #6f4e37; color: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        
        .feature-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            height: 100%;
            text-decoration: none; 
            color: inherit;
            display: block; /* Đảm bảo thẻ a full block */
        }
        .feature-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .card-icon { font-size: 3rem; margin-bottom: 15px; }
        
        /* Màu sắc cho từng role */
        .bg-pos { background: linear-gradient(135deg, #11998e, #38ef7d); color: white; }
        .bg-admin { background: linear-gradient(135deg, #eb3349, #f45c43); color: white; }
        /* [MỚI] Màu cho kho (Xanh dương đậm) */
        .bg-warehouse { background: linear-gradient(135deg, #2c3e50, #4ca1af); color: white; }
    </style>
</head>
<body>

<div class="container dashboard-container">
    <div class="welcome-banner d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0">Xin chào, <?= htmlspecialchars($fullname) ?>!</h2>
            <small>Chức vụ: <strong><?= $role_label ?></strong></small>
        </div>
        <a href="logout.php" class="btn btn-outline-light btn-sm">
            <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
        </a>
    </div>

    <div class="row justify-content-center g-4">
        
        <?php if ($role === 'admin' || $role === 'staff'): ?>
        <div class="col-md-4 col-sm-6">
            <a href="pos/index.php" class="card feature-card bg-pos text-center p-5">
                <div class="card-body">
                    <i class="fa-solid fa-cash-register card-icon"></i>
                    <h3>Bán Hàng</h3>
                    <p>POS & Thu ngân</p>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($role === 'admin' || $role === 'wh-staff'): ?>
        <div class="col-md-4 col-sm-6">
            <a href="warehouse/index.php" class="card feature-card bg-warehouse text-center p-5">
                <div class="card-body">
                    <i class="fa-solid fa-boxes-stacked card-icon"></i>
                    <h3>Kho Hàng</h3>
                    <p>Nhập kho & Kiểm kê</p>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
        <div class="col-md-4 col-sm-6">
            <a href="admin/index.php" class="card feature-card bg-admin text-center p-5">
                <div class="card-body">
                    <i class="fa-solid fa-chart-line card-icon"></i>
                    <h3>Quản Trị</h3>
                    <p>Thực đơn & Doanh thu</p>
                </div>
            </a>
        </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>