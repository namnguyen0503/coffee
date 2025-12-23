<?php
session_start();

// 1. Gọi file kết nối
require_once './includes/db_connection.php';

// 2. Khai báo sử dụng biến toàn cục $mysqli (theo đúng cấu trúc dự án của bạn)
global $mysqli;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Kiểm tra xem biến kết nối có tồn tại không để tránh lỗi Fatal Error
    if (!$mysqli) {
        die("Lỗi: Biến kết nối CSDL (\$mysqli) bị null. Vui lòng kiểm tra file db_connection.php");
    }

    // 3. Sửa câu lệnh chuẩn bị (Dùng $mysqli thay vì $conn)
    $stmt = $mysqli->prepare("SELECT id, fullname, password, role FROM users WHERE username = ? AND status = 1");
    
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Kiểm tra mật khẩu
            if (password_verify($password, $user['password'])) {
                
                
                // Lưu session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];
                
                // Chuyển hướng
                header("Location: index.php");
                exit;
            } else {
                $error = "Mật khẩu không đúng!";
            }
        } else {
            $error = "Tài khoản không tồn tại hoặc bị khóa!";
        }
        $stmt->close();
    } else {
        // Báo lỗi nếu SQL sai
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
            <div class="alert alert-danger"><?= $error ?></div>
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

<script>
    // Lắng nghe sự kiện phím bấm trên ô Mật khẩu
    document.getElementById('passwordField').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Ngăn hành vi Enter mặc định (để tránh xung đột)
            
            // Thay vì submit form, ta kích hoạt sự kiện CLICK lên nút
            document.getElementById('loginBtn').click();
        }
    });
</script>
</body>
</html>

