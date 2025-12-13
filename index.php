<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cổng Chào | Cà Phê Nguyễn Văn</title>
    
    <link rel="stylesheet" href="./pos/css/bootstrap.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #f3e5d8; /* Màu kem sữa nhẹ nhàng */
            background-image: linear-gradient(135deg, #f3e5d8 0%, #e6d2c1 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .welcome-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(111, 78, 55, 0.2); 
            padding: 40px;
            width: 100%;
            max-width: 500px;
            text-align: center;
            border-top: 5px solid #6f4e37; 
        }

        .brand-title {
            color: #6f4e37;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .brand-subtitle {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        .role-btn {
            display: flex;
            align-items: center;
            justify-content: start;
            padding: 15px 20px;
            margin-bottom: 15px;
            border: 2px solid #eee;
            border-radius: 12px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            background: #fff;
        }

        .role-btn:hover {
            border-color: #6f4e37;
            background-color: #fff8f0;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            color: #6f4e37;
        }

        .role-icon {
            width: 50px;
            height: 50px;
            background-color: #eee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.2rem;
            transition: all 0.3s;
        }

        .role-btn:hover .role-icon {
            background-color: #6f4e37;
            color: white;
        }

        .role-info h5 {
            margin: 0;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .role-info p {
            margin: 0;
            font-size: 0.8rem;
            color: #999;
        }
        
        .footer-text {
            margin-top: 20px;
            font-size: 0.8rem;
            color: #aaa;
        }
    </style>
</head>
<body>

    <div class="welcome-card">
        <div class="mb-3">
            <i class="fas fa-coffee fa-3x" style="color: #6f4e37;"></i>
        </div>

        <h2 class="brand-title">Nguyễn Văn Coffee</h2>
        <p class="brand-subtitle">Hệ thống quản lý bán hàng tập trung</p>

        <div class="d-grid gap-3">
            
            <a href="./pos/index.php" class="role-btn">
                <div class="role-icon">
                    <i class="fas fa-cash-register"></i>
                </div>
                <div class="role-info text-start">
                    <h5>Nhân viên Bán hàng</h5>
                    <p>Truy cập máy POS, gọi món & thanh toán</p>
                </div>
                <div class="ms-auto">
                    <i class="fas fa-chevron-right text-muted"></i>
                </div>
            </a>

            <a href="./admin/index.php" class="role-btn">
                <div class="role-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="role-info text-start">
                    <h5>Quản lý Cửa hàng</h5>
                    <p>Truy cập Dashboard, menu, báo cáo</p>
                </div>
                <div class="ms-auto">
                    <i class="fas fa-chevron-right text-muted"></i>
                </div>
            </a>

        </div>

        <p class="footer-text">© 2025 Nguyễn Văn Coffee Project. Developed by Student.</p>
    </div>

</body>
</html>