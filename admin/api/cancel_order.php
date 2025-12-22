<?php
require '../../includes/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $order_id = $_POST['id'];
    $user_id = 1; // ID của Admin đang thao tác (tạm thời để 1)

    // Bắt đầu Transaction để đảm bảo an toàn dữ liệu
    $conn->begin_transaction();

    try {
        // 1. Kiểm tra đơn hàng
        $check = $conn->query("SELECT status, final_amount, session_id FROM orders WHERE id = $order_id");
        $order = $check->fetch_assoc();

        if (!$order) throw new Exception("Đơn hàng không tồn tại.");
        if ($order['status'] == 'canceled') throw new Exception("Đơn này đã hủy rồi.");

        // 2. Cập nhật trạng thái đơn -> Đã hủy
        $conn->query("UPDATE orders SET status = 'canceled' WHERE id = $order_id");

        // 3. HOÀN KHO (RESTOCK) - Cộng lại nguyên liệu
        $items = $conn->query("SELECT product_id, quantity FROM order_items WHERE order_id = $order_id");
        while ($item = $items->fetch_assoc()) {
            $pid = $item['product_id'];
            $qty = $item['quantity'];

            // Lấy công thức món
            $recipes = $conn->query("SELECT ingredient_id, quantity_required FROM recipes WHERE product_id = $pid");
            while ($r = $recipes->fetch_assoc()) {
                $ing_id = $r['ingredient_id'];
                $amount = $r['quantity_required'] * $qty;

                // Cộng lại vào kho
                $conn->query("UPDATE ingredients SET quantity = quantity + $amount WHERE id = $ing_id");
                
                // Ghi log
                $conn->query("INSERT INTO inventory_log (ingredient_id, type, quantity, note, user_id) 
                              VALUES ($ing_id, 'import', $amount, 'Hoàn kho hủy đơn #$order_id', $user_id)");
            }
        }

        // 4. TRỪ DOANH THU CA LÀM VIỆC (Nếu ca đang mở)
        if ($order['session_id']) {
            $sess_id = $order['session_id'];
            $ss = $conn->query("SELECT status FROM work_sessions WHERE id = $sess_id")->fetch_assoc();
            if ($ss && $ss['status'] == 'open') {
                $money = $order['final_amount'];
                $conn->query("UPDATE work_sessions SET total_sales = total_sales - $money WHERE id = $sess_id");
            }
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Đã hủy đơn hàng thành công!']);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>