<?php
require '../tinh-nang/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['user_id'];
    $fullname = $_POST['fullname'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    // Nếu có nhập mật khẩu mới thì cập nhật, không thì giữ nguyên
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE users SET fullname=?, role=?, status=?, password=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisi", $fullname, $role, $status, $password, $id);
    } else {
        $sql = "UPDATE users SET fullname=?, role=?, status=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $fullname, $role, $status, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Cập nhật tài khoản thành công!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi cập nhật!']);
    }
}
?>