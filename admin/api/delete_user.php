<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db_connection.php';

$conn = connect_db();
$id = $_POST['id'] ?? '';

if ($id) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) echo json_encode(['success' => true, 'message' => 'Đã xóa']);
    else echo json_encode(['success' => false, 'message' => 'Lỗi xóa']);
}
disconnect_db($conn);
?>