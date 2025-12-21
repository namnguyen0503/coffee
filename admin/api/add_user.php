<?php
header('Content-Type: application/json');
require_once '../tinh-nang/db_connection.php';

$conn = connect_db();

$fullname = $_POST['fullname'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? 'Staff';

if (!$fullname || !$username || !$password) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin!']);
    exit;
}

// Check trùng user
$check = $conn->prepare("SELECT id FROM users WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Tên đăng nhập đã tồn tại!']);
    exit;
}

// Mã hóa và thêm
$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (fullname, username, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $fullname, $username, $hashed, $role);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Thêm thành công!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error]);
}
disconnect_db($conn);
?>