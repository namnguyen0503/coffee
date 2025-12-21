<?php
require_once __DIR__ . '/../../includes/db_connection.php';
header('Content-Type: application/json');

$conn = connect_db();
$order_id = $_POST['order_id'] ?? null;

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID đơn hàng']);
    exit;
}

// Bắt đầu Transaction (Quan trọng để đảm bảo dữ liệu toàn vẹn)
$conn->begin_transaction();

try {
    // 1. Kiểm tra trạng thái đơn
    $stmt = $conn->prepare("SELECT status, total_money FROM orders WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order['status'] === 'cancelled') {
        throw new Exception("Đơn hàng này đã hủy trước đó!");
    }

    // 2. LOGIC HOÀN KHO: Lấy món trong đơn -> tra công thức -> cộng lại nguyên liệu
    $sql_items = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $items = $stmt_items->get_result();

    while ($item = $items->fetch_assoc()) {
        $prod_id = $item['product_id'];
        $qty_sold = $item['quantity'];

        // Lấy công thức của món
        $sql_recipe = "SELECT ingredient_id, quantity FROM recipes WHERE product_id = ?";
        $stmt_recipe = $conn->prepare($sql_recipe);
        $stmt_recipe->bind_param("i", $prod_id);
        $stmt_recipe->execute();
        $recipes = $stmt_recipe->get_result();

        while ($recipe = $recipes->fetch_assoc()) {
            $ing_id = $recipe['ingredient_id'];
            $amount_per_unit = $recipe['quantity'];
            $total_restock = $amount_per_unit * $qty_sold;

            // Cộng lại kho (Revert)
            $update_stock = $conn->prepare("UPDATE ingredients SET quantity = quantity + ? WHERE id = ?");
            $update_stock->bind_param("di", $total_restock, $ing_id);
            $update_stock->execute();

            // Ghi Log
            $log_msg = "Hoàn kho do hủy đơn #$order_id";
            $conn->query("INSERT INTO inventory_log (ingredient_id, change_amount, action_type, notes) 
                          VALUES ($ing_id, $total_restock, 'Restock', '$log_msg')");
        }
    }

    // 3. LOGIC TIỀN TỆ: Trừ doanh thu ca làm việc (nếu ca đang mở)
    // Tìm ca làm việc đang mở (không có end_time)
    $stmt_session = $conn->prepare("SELECT id FROM work_sessions WHERE end_time IS NULL ORDER BY id DESC LIMIT 1");
    $stmt_session->execute();
    $session = $stmt_session->get_result()->fetch_assoc();

    if ($session) {
        $session_id = $session['id'];
        $money_to_deduct = $order['total_money'];
        // Trừ tiền
        $conn->query("UPDATE work_sessions SET total_money = total_money - $money_to_deduct WHERE id = $session_id");
    }

    // 4. Cập nhật trạng thái đơn hàng
    $conn->query("UPDATE orders SET status = 'cancelled' WHERE id = $order_id");

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Đã hủy đơn và hoàn kho thành công!']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>