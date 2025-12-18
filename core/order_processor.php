<?php
    // --- PHẦN 1: CẤU HÌNH ĐỂ TRÁNH LỖI "Unexpected token <" ---
    // Tắt toàn bộ báo lỗi hiển thị ra màn hình (quan trọng nhất)
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

    try {
        // 1. Insert vào bảng ORDERS
        $order_date = date('Y-m-d H:i:s');
        $status = 'paid';
        
        // Dùng Prepared Statement để an toàn
        $stmt = $mysqli->prepare("INSERT INTO orders (total_price, order_date, status) VALUES (?, ?, ?)");
        // Lưu ý: Trong code cũ bạn dùng cột 'total_price', code mới JS gửi 'total_amount'
        // Tôi giữ tên cột trong DB là 'total_price' như code cũ của bạn
        
        if (!$stmt) {
            throw new Exception("Lỗi prepare orders: " . $mysqli->error);
        }

        // d: double (tiền), s: string
        $stmt->bind_param("dss", $total_amount, $order_date, $status);
        
        if (!$stmt->execute()) {
            throw new Exception("Lỗi execute orders: " . $stmt->error);
        }

        // Lấy ID đơn hàng vừa tạo
        $new_order_id = $mysqli->insert_id;
        $stmt->close();

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