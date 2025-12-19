<?php
    // --- PHẦN 1: CẤU HÌNH ĐỂ TRÁNH LỖI "Unexpected token <" ---
    // Tắt toàn bộ báo lỗi hiển thị ra màn hình (quan trọng nhất)
    session_start();
    error_reporting(0); 
    
    // Set header JSON chuẩn xác
    header('Content-Type: application/json; charset=utf-8');

    // Gọi file kết nối
    require_once '../includes/db_connection.php';
    
    // Lấy biến kết nối từ file db_connection (như code cũ của bạn)
    global $mysqli; 

    // Kiểm tra kết nối
    if (!$mysqli) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi kết nối CSDL (mysqli is null)']);
        exit;
    }

    // --- PHẦN 2: NHẬN DỮ LIỆU TỪ JS ---
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    // Kiểm tra dữ liệu đầu vào
    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
        exit;
    }

    // Map dữ liệu từ JS gửi lên (Lưu ý: JS mới gửi key là 'total_amount')
    $total_amount = isset($data['total_amount']) ? $data['total_amount'] : 0;
    $items = isset($data['items']) ? $data['items'] : [];

    // --- PHẦN 3: XỬ LÝ TRANSACTION (Thêm vào 2 bảng) ---
    
    // Bắt đầu giao dịch
    $mysqli->begin_transaction();

   // --- TRONG PHẦN 3: XỬ LÝ TRANSACTION ---

// 0. Khởi tạo session để lấy user_id (nếu chưa có ở đầu file)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Mặc định là 1 nếu test không qua login

try {
    // 1. Insert vào bảng ORDERS (Thêm user_id vào đây)
    $order_date = date('Y-m-d H:i:s');
    $status = 'paid';
    
    // SỬA TẠI ĐÂY: Thêm cột user_id và thêm một dấu ?
    $stmt = $mysqli->prepare("INSERT INTO orders (total_price, order_date, status, user_id) VALUES (?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Lỗi prepare orders: " . $mysqli->error);
    }

    // SỬA TẠI ĐÂY: Thêm kiểu dữ liệu "i" (integer) cho user_id và truyền biến $user_id vào
    // "dssi" tương ứng với: double, string, string, integer
    $stmt->bind_param("dssi", $total_amount, $order_date, $status, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Lỗi execute orders: " . $stmt->error);
    }

    $new_order_id = $mysqli->insert_id;
    $stmt->close();

    // ... (Các phần insert order_items và trừ kho giữ nguyên) ...
        // 2. Insert vào bảng ORDER_ITEMS (nếu có món ăn)
        if (!empty($items)) {
            $stmt_item = $mysqli->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
            
            if (!$stmt_item) {
                throw new Exception("Lỗi prepare items: " . $mysqli->error);
            }

            foreach ($items as $item) {
                $p_id = $item['product_id'];
                $qty = $item['quantity'];

                // i: integer -> order_id, product_id, quantity
                $stmt_item->bind_param("iii", $new_order_id, $p_id, $qty);
                
                if (!$stmt_item->execute()) {
                    throw new Exception("Lỗi thêm món ID $p_id");
                }
            }
            $stmt_item->close();
        }

        // --- ĐOẠN NÀY ĐẶT TRONG TRANSACTION CỦA order_processor.php ---

foreach ($items as $item) {
    $p_id = $item['product_id'];
    $order_qty = $item['quantity'];

    // 1. Lấy công thức món
    $stmt_recipe = $mysqli->prepare("SELECT ingredient_id, quantity_required FROM recipes WHERE product_id = ?");
    $stmt_recipe->bind_param("i", $p_id);
    $stmt_recipe->execute();
    $recipe_result = $stmt_recipe->get_result();

    while ($recipe = $recipe_result->fetch_assoc()) {
        $ing_id = $recipe['ingredient_id'];
        $qty_needed = $recipe['quantity_required'] * $order_qty;

        // 2. KIỂM TRA TỒN KHO TRƯỚC (Nâng cao)
        $stmt_check = $mysqli->prepare("SELECT name, quantity FROM ingredients WHERE id = ?");
        $stmt_check->bind_param("i", $ing_id);
        $stmt_check->execute();
        $ing_data = $stmt_check->get_result()->fetch_assoc();

        if ($ing_data['quantity'] < $qty_needed) {
            // Nếu không đủ, hủy giao dịch và báo lỗi về POS
            throw new Exception("Món này tạm hết vì không đủ: " . $ing_data['name']);
        }

        // 3. TRỪ KHO
        $stmt_update = $mysqli->prepare("UPDATE ingredients SET quantity = quantity - ? WHERE id = ?");
        $stmt_update->bind_param("di", $qty_needed, $ing_id);
        if (!$stmt_update->execute()) {
            throw new Exception("Lỗi cập nhật kho");
        }

        // 4. GHI LOG (Cho chuyên nghiệp)
        $log_note = "Bán đơn hàng #" . $new_order_id;
        $stmt_log = $mysqli->prepare("INSERT INTO inventory_log (ingredient_id, type, quantity, note, user_id) VALUES (?, 'export', ?, ?, ?)");
        $u_id = $_SESSION['user_id'] ?? 1; // Mặc định là user 1 nếu chưa login
        $stmt_log->bind_param("idsi", $ing_id, $qty_needed, $log_note, $u_id);
        $stmt_log->execute();
    }
}

        // 3. Nếu mọi thứ OK -> Commit
        $mysqli->commit();

        // Phản hồi thành công
        http_response_code(200);
        echo json_encode([
            'success' => true, // JS mới check biến này
            'status' => 'success',
            'message' => 'Thanh toán thành công.',
            'order_id' => $new_order_id
        ]);

    } catch (Exception $e) {
        // Nếu lỗi -> Rollback (Hủy)
        $mysqli->rollback();
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi xử lý: ' . $e->getMessage()
        ]);
    }

    exit;
?>