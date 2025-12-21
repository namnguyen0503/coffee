<?php
require '../tinh-nang/db_connection.php';$conn = connect_db();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $sql = "INSERT INTO users (fullname, username, password, role, status) VALUES (?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $fullname, $username, $password, $role);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Thêm nhân viên thành công!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi: Tên đăng nhập có thể đã tồn tại.']);
    }
}
?>