<?php
// pos/get_order_details.php
require_once '../includes/db_connection.php';

// Kiểm tra xem có ID được gửi lên không
if (isset($_GET['id'])) {
    $order_id = intval($_GET['id']);

    // SQL UPDATE: Lấy giá (price) từ bảng products (p) thay vì order_items (oi)
    $sql = "SELECT 
                oi.quantity, 
                p.name, 
                p.price, 
                p.image_url 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?";

    $stmt = $mysqli->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        // Trả về JSON
        header('Content-Type: application/json');
        echo json_encode($items);
        
        $stmt->close();
    } else {
        // Trả về lỗi nếu SQL sai
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi truy vấn Database']);
    }
} else {
    echo json_encode([]);
}
?>