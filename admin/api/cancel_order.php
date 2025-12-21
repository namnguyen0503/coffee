<?php
require '../tinh-nang/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $order_id = $_POST['id'];
    $admin_id = 1; // Mặc định là admin hiện tại (hoặc lấy từ session nếu có login)

    // BẮT ĐẦU TRANSACTION (Quan trọng: Để nếu lỗi thì hoàn tác tất cả)
    $conn->begin_transaction();

    try {
        // 1. Kiểm tra trạng thái đơn
        $check = $conn->query("SELECT status, session_id, final_amount FROM orders WHERE id = $order_id");
        $order = $check->fetch_assoc();

        if (!$order) throw new Exception("Đơn hàng không tồn tại.");
        if ($order['status'] == 'canceled') throw new Exception("Đơn hàng này đã bị hủy trước đó.");

        // 2. Cập nhật trạng thái đơn thành 'canceled'
        $conn->query("UPDATE orders SET status = 'canceled' WHERE id = $order_id");

        // 3. HOÀN KHO (RESTOCK) DỰA TRÊN CÔNG THỨC
        // Lấy danh sách món trong đơn
        $items_sql = "SELECT product_id, quantity FROM order_items WHERE order_id = $order_id";
        $items_query = $conn->query($items_sql);

        while ($item = $items_query->fetch_assoc()) {
            $p_id = $item['product_id'];
            $qty_sold = $item['quantity'];

            // Lấy công thức của món đó
            $recipe_sql = "SELECT ingredient_id, quantity_required FROM recipes WHERE product_id = $p_id";
            $recipe_query = $conn->query($recipe_sql);

            while ($recipe = $recipe_query->fetch_assoc()) {
                $ing_id = $recipe['ingredient_id'];
                $amount_per_unit = $recipe['quantity_required'];
                
                // Tổng lượng nguyên liệu cần hoàn lại = Định lượng * Số món đã bán
                $total_refund = $amount_per_unit * $qty_sold;

                // Cộng lại vào kho
                $conn->query("UPDATE ingredients SET quantity = quantity + $total_refund WHERE id = $ing_id");

                // Ghi log nhập kho (import)
                $note = "Hoàn kho do hủy đơn #$order_id";
                $log_sql = "INSERT INTO inventory_log (ingredient_id, type, quantity, cost, note, user_id) 
                            VALUES ($ing_id, 'import', $total_refund, 0, '$note', $admin_id)";
                $conn->query($log_sql);
            }
        }

        // 4. TRỪ DOANH THU CA LÀM VIỆC (Nếu ca đó đang mở)
        // Nếu ca đã đóng, về nguyên tắc kế toán sẽ không sửa số liệu cũ mà ghi nhận hủy vào ca hiện tại 
        // hoặc giữ nguyên (tùy logic). Ở đây tôi làm logic: Chỉ trừ nếu ca ĐANG MỞ.
        if (!empty($order['session_id'])) {
            $session_id = $order['session_id'];
            $ss_check = $conn->query("SELECT status FROM work_sessions WHERE id = $session_id");
            $ss = $ss_check->fetch_assoc();

            if ($ss && $ss['status'] == 'open') {
                $refund_amount = $order['final_amount'];
                // Trừ doanh thu
                $conn->query("UPDATE work_sessions SET total_sales = total_sales - $refund_amount WHERE id = $session_id");
            }
        }

        // MỌI THỨ OK -> LƯU LẠI
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Đã hủy đơn hàng và hoàn kho thành công!']);

    } catch (Exception $e) {
        // CÓ LỖI -> QUAY LUI (Không sửa gì cả)
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}
?>