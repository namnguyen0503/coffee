<?php
session_start();

// 1. Gọi file kết nối
require_once './includes/db_connection.php';

// 2. Khai báo sử dụng biến toàn cục $mysqli
global $mysqli;

// --- PHẦN MỚI: TỰ ĐỘNG CẬP NHẬT TRẠNG THÁI CA LÀM VIỆC (LOGIC TIMEZONE) ---
// Giúp đảm bảo dữ liệu status_work luôn đúng theo giờ thực tế khi nhân viên bấm vào trang
if ($mysqli) {
    date_default_timezone_set('Asia/Ho_Chi_Minh'); // Quan trọng: Set giờ VN
    
    $cur_date = date('Y-m-d');
    $cur_hour = (int)date('H');
    $cur_min  = (int)date('i');
    $shift = '';

    // Xác định ca hiện tại
    if ($cur_hour >= 7 && $cur_hour < 12) {
        $shift = 'morning';
    } elseif ($cur_hour >= 12 && $cur_hour < 17) {
        $shift = 'afternoon';
    } elseif ($cur_hour >= 17) {
        // Ca tối: 17h -> 23h30
        if ($cur_hour < 23 || ($cur_hour == 23 && $cur_min <= 30)) {
            $shift = 'evening';
        }
    }

    // Bước A: Reset tất cả nhân viên về trạng thái nghỉ (0)
    $mysqli->query("UPDATE users SET status_work = 0");

    // Bước B: Nếu đang trong giờ làm việc, bật trạng thái (1) cho người có lịch
    if ($shift) {
        $stmt_update = $mysqli->prepare("
            UPDATE users u
            JOIN work_schedules ws ON u.id = ws.user_id
            SET u.status_work = 1
            WHERE ws.shift_date = ? AND ws.shift_type = ? AND u.status = 1
        ");
        if ($stmt_update) {
            $stmt_update->bind_param("ss", $cur_date, $shift);
            $stmt_update->execute();
            $stmt_update->close();
        }
    }
}
// --------------------------------------------------------------------------

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Kiểm tra kết nối
    if (!$mysqli) {
        die("Lỗi: Biến kết nối CSDL (\$mysqli) bị null. Vui lòng kiểm tra file db_connection.php");
    }

    // 3. Sửa câu lệnh SELECT: Lấy thêm cột status_work để kiểm tra
    $stmt = $mysqli->prepare("SELECT id, fullname, password, role, status_work FROM users WHERE username = ? AND status = 1");
    
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Kiểm tra mật khẩu
            if (password_verify($password, $user['password'])) {
                
                // --- PHẦN MỚI: KIỂM TRA status_work ---
                // Nếu không phải Admin VÀ status_work là 0 -> Chặn lại
                if ($user['role'] !== 'admin' && $user['status_work'] == 0) {
                    $error = "Bạn KHÔNG CÓ LỊCH làm việc vào giờ này (" . date('H:i') . ")!";
                } else {
                    // Đăng nhập thành công -> Lưu session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['fullname'] = $user['fullname'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Phân quyền chuyển hướng (Tùy chỉnh nếu cần)
                    if ($user['role'] == 'admin') {
                        header("Location: admin/index.php");
                    } else {
                        header("Location: pos/index.php"); // Hoặc index.php tùy cấu trúc của bạn
                    }
                    exit;
                }
                // ---------------------------------------

            } else {
                $error = "Mật khẩu không đúng!";
            }
        } else {
            $error = "Tài khoản không tồn tại hoặc bị khóa!";
        }
        $stmt->close();
    } else {
        $error = "Lỗi hệ thống: " . $mysqli->error;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f1ea; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 400px; padding: 2rem; border-radius: 15px; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-coffee { background-color: #6f4e37; color: white; }
        .btn-coffee:hover { background-color: #5a3e2b; color: white; }
    </style>
</head>
<body>
    <div class="login-card">
        <h3 class="text-center mb-4 text-uppercase fw-bold" style="color: #6f4e37;">Coffee Login</h3>
        
        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST" id="loginForm">
            <div class="mb-3">
                <label class="form-label">Tên đăng nhập</label>
                <input type="text" name="username" class="form-control" required autofocus 
                       value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Mật khẩu</label>
                <input type="password" name="password" class="form-control" required id="passwordField">
            </div>
            
            <button type="submit" name="login_btn" id="loginBtn" class="btn btn-coffee w-100 py-2">Đăng Nhập</button>
        </form>
    </div>

<script>
    // Lắng nghe sự kiện phím bấm trên ô Mật khẩu
    document.getElementById('passwordField').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Ngăn hành vi Enter mặc định
            document.getElementById('loginBtn').click(); // Kích hoạt nút bấm
        }
    });
</script>
</body>
</html>