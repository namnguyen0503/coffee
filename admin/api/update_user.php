<?php
header('Content-Type: application/json');
require_once '../tinh-nang/db_connection.php';

$conn = connect_db();
$id = $_POST['id'] ?? '';
$action = $_POST['action'] ?? '';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID']);
    exit;
}

if ($action === 'reset_password') {
    $password = $_POST['password'] ?? '';
    if (!$password) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu trống']);
        exit;
    }
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed, $id);
    
    if ($stmt->execute()) echo json_encode(['success' => true, 'message' => 'Đã đổi mật khẩu']);
    else echo json_encode(['success' => false, 'message' => 'Lỗi DB']);

} elseif ($action === 'edit_info') {
    $fullname = $_POST['fullname'] ?? '';
    $role = $_POST['role'] ?? 'Staff';
    
    $stmt = $conn->prepare("UPDATE users SET fullname = ?, role = ? WHERE id = ?");
    $stmt->bind_param("ssi", $fullname, $role, $id);
    
    if ($stmt->execute()) echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
    else echo json_encode(['success' => false, 'message' => 'Lỗi DB']);
}
disconnect_db($conn);
?>