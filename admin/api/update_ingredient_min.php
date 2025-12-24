<?php
require '../../includes/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $min_qty = $_POST['min_quantity'];

    $sql = "UPDATE ingredients SET min_quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $min_qty, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Đã cập nhật mức cảnh báo!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $conn->error]);
    }
}
?>