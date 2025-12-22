<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db_connection.php';

try {
    // 1. Check Login & Ca làm việc
    if (!isset($_SESSION['user_id'])) throw new Exception("Chưa đăng nhập!");
    $current_session_id = getActiveSessionId($mysqli, $_SESSION['user_id']);
    if (!$current_session_id) throw new Exception("Chưa vào ca làm việc!");

    // 2. Nhận dữ liệu từ Client
    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input || empty($input['items'])) throw new Exception("Giỏ hàng rỗng.");

    // --- [XỬ LÝ VOUCHER (SERVER SIDE)] ---
    $voucher_code = isset($input['voucher_code']) ? strtoupper(trim($input['voucher_code'])) : '';
    $requested_percent = isset($input['discount_percent']) ? (float)$input['discount_percent'] : 0;
    $payment_method = isset($data['payment_method']) ? $data['payment_method'] : 'cash';
    $applied_discount_percent = 0; // Mặc định không giảm

    // A. LOGIC VOUCHER ADMIN (ADMINVIP)
    if ($voucher_code === 'ADMINVIP') {
        // Chỉ cho phép nếu user đang login là role 'admin'
        // Bạn cần check cột 'role' trong bảng users. Giả sử session đã lưu role.
        // Nếu session chưa lưu role, cần query DB để check.
        
        $stmt_role = $mysqli->prepare("SELECT role FROM users WHERE id = ?");
        $stmt_role->bind_param("i", $_SESSION['user_id']);
        $stmt_role->execute();
        $user_role = $stmt_role->get_result()->fetch_assoc()['role'] ?? 'staff';

        if ($user_role === 'admin') {
            // Admin được quyền set % tùy ý (nhưng không quá 100%)
            if ($requested_percent < 0 || $requested_percent > 100) {
                throw new Exception("Phần trăm giảm giá không hợp lệ (0-100).");
            }
            $applied_discount_percent = $requested_percent;
        } else {
            // Nếu không phải admin mà dùng mã này -> Phạt hoặc lờ đi
            throw new Exception("Mã ADMINVIP chỉ dành cho Quản lý!");
        }
    }
    // B. LOGIC VOUCHER KHÁCH HÀNG (Cố định)
    elseif ($voucher_code === 'WELCOME') { // Ví dụ mã dùng 1 lần
        $applied_discount_percent = 10; // Giảm cứng 10%
    }
    elseif ($voucher_code === 'FREESHIP') {
        $applied_discount_percent = 5; 
    }
    elseif (!empty($voucher_code)) {
        throw new Exception("Mã giảm giá không tồn tại hoặc hết hạn!");
    }

    // 3. Tính tổng tiền (Server tự tính từ DB)
    $server_total_amount = 0;
    $clean_items = [];
    $stmt_get_price = $mysqli->prepare("SELECT price FROM products WHERE id = ?");

    foreach ($input['items'] as $item) {
        $p_id = (int)$item['product_id'];
        $qty = (int)$item['quantity'];
        if ($qty <= 0) continue;

        $stmt_get_price->bind_param("i", $p_id);
        $stmt_get_price->execute();
        $res = $stmt_get_price->get_result();
        
        if ($row = $res->fetch_assoc()) {
            $real_price = (float)$row['price'];
            $server_total_amount += ($real_price * $qty);
            $clean_items[] = [
                'product_id' => $p_id,
                'quantity' => $qty,
                'note' => $item['note'] ?? ''
            ];
        }
    }

    // 4. Áp dụng giảm giá
    $discount_amount = $server_total_amount * ($applied_discount_percent / 100);
    $final_amount = $server_total_amount - $discount_amount;

    // 5. Lưu vào DB
    $mysqli->begin_transaction();

    // Insert Order (Lưu cả mã voucher và % vào)
    $stmt = $mysqli->prepare("INSERT INTO orders (user_id, session_id, order_date, total_price, status, payment_method) VALUES (?, ?, NOW(), ?, 'paid', ?)");
    $stmt_order = $mysqli->prepare($sql_order);
    // d: double (cho tiền và percent)
    $stmt->bind_param("iids", $user_id, $session_id, $final_amount, $payment_method);    
    if (!$stmt_order->execute()) throw new Exception("Lỗi tạo đơn: " . $stmt_order->error);
    $new_order_id = $mysqli->insert_id;

    // Insert Items & Trừ kho (Giữ nguyên logic cũ)
    $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, note) VALUES (?, ?, ?, ?)";
    $stmt_item = $mysqli->prepare($sql_item);
    
    $sql_recipe = "SELECT ingredient_id, quantity_required FROM recipes WHERE product_id = ?";
    $stmt_recipe = $mysqli->prepare($sql_recipe);
    
    $sql_stock = "UPDATE ingredients SET quantity = quantity - ? WHERE id = ?";
    $stmt_stock = $mysqli->prepare($sql_stock);
    
    $sql_log = "INSERT INTO inventory_log (ingredient_id, type, quantity, note, user_id, created_at) VALUES (?, 'export', ?, ?, ?, NOW())";
    $stmt_log = $mysqli->prepare($sql_log);
    $log_note = "Bán đơn #$new_order_id";

    foreach ($clean_items as $item) {
        $stmt_item->bind_param("iiis", $new_order_id, $item['product_id'], $item['quantity'], $item['note']);
        $stmt_item->execute();

        // Trừ kho
        $stmt_recipe->bind_param("i", $item['product_id']);
        $stmt_recipe->execute();
        $res_recipe = $stmt_recipe->get_result();
        while ($r = $res_recipe->fetch_assoc()) {
            $ing_id = $r['ingredient_id'];
            $need = $r['quantity_required'] * $item['quantity'];
            
            $stmt_stock->bind_param("di", $need, $ing_id);
            $stmt_stock->execute();
            
            $stmt_log->bind_param("idsi", $ing_id, $need, $log_note, $_SESSION['user_id']);
            $stmt_log->execute();
        }
    }

    $mysqli->commit();

    echo json_encode([
        'success' => true, 
        'order_id' => $new_order_id,
        'total_original' => $server_total_amount,
        'discount_percent' => $applied_discount_percent,
        'final_amount' => $final_amount
    ]);

} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>