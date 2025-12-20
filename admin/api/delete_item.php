<?php
require '../tinh-nang/db_connection.php';
$conn = connect_db();

header('Content-Type: application/json'); // Thêm dòng này

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    $sql = "UPDATE products SET is_active = 0 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Đã xóa thành công!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống!']);
    }
}
?>