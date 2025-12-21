<?php
require_once __DIR__ . '/../../includes/db_connection.php';
header('Content-Type: application/json');

$conn = connect_db();

// Lấy đơn hàng + tên nhân viên (join bảng users)
$sql = "SELECT o.*, u.fullname as staff_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);
$orders = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

echo json_encode(['success' => true, 'data' => $orders]);
?>