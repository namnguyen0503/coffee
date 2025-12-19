<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>PHP Password Hash Generator</title>
    <style>
        body { font-family: sans-serif; margin: 40px; line-height: 1.6; }
        .result { background: #f4f4f4; padding: 15px; border-left: 5px solid #2ecc71; word-break: break-all; }
        input[type="text"] { padding: 8px; width: 300px; }
        button { padding: 8px 15px; cursor: pointer; }
    </style>
</head>
<body>

    <h2>Tạo mã Hash cho mật khẩu</h2>

    <form method="POST" action="">
        <input type="text" name="password_string" placeholder="Nhập chuỗi cần hash..." required>
        <button type="submit" name="submit">Hash Password</button>
    </form>

    <br>

    <?php
    if (isset($_POST['submit'])) {
        // Lấy dữ liệu từ input
        $inputString = $_POST['password_string'];

        /**
         * password_hash() tạo ra một chuỗi hash bảo mật.
         * PASSWORD_DEFAULT: Sử dụng thuật toán mạnh nhất hiện tại (thường là Bcrypt).
         */
        $hashedPassword = password_hash($inputString, PASSWORD_DEFAULT);

        echo "<div class='result'>";
        echo "<strong>Chuỗi gốc:</strong> " . htmlspecialchars($inputString) . "<br>";
        echo "<strong>Kết quả password_hash():</strong><br> <code>" . $hashedPassword . "</code>";
        echo "</div>";
        
        echo "<p><small><em>Lưu ý: Mỗi lần bạn nhấn Submit, mã hash sẽ thay đổi (do cơ chế 'salt' tự động) nhưng đều hợp lệ khi kiểm tra bằng password_verify().</em></small></p>";
    }
    ?>

</body>
</html>