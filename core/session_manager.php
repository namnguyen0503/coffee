<?php
session_start();
require_once '../includes/db_connection.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

if ($user_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

try {
    // 1. Kiểm tra trạng thái hiện tại
    if ($action === 'check_status') {
        $stmt = $mysqli->prepare("SELECT id, start_time, start_cash FROM work_sessions WHERE user_id = ? AND status = 'open' LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($row = $res->fetch_assoc()) {
            echo json_encode(['success' => true, 'is_open' => true, 'data' => $row]);
        } else {
            echo json_encode(['success' => true, 'is_open' => false]);
        }
    }

    // 2. Vào ca (Start Shift)
    // ... (Phần check login giữ nguyên)

    // 2. Vào ca (Start Shift) - CÓ LOGIC TỰ ĐỘNG CHỐT CA CŨ
    elseif ($action === 'start_shift') {
        $input = json_decode(file_get_contents("php://input"), true);
        $start_cash = isset($input['start_cash']) ? (float)$input['start_cash'] : 0;

        // BẮT ĐẦU TRANSACTION ĐỂ ĐẢM BẢO DỮ LIỆU
        $mysqli->begin_transaction();

        try {
            // BƯỚC 1: Kiểm tra xem chính User này có đang mở ca không?
            $check_self = $mysqli->prepare("SELECT id FROM work_sessions WHERE user_id = ? AND status = 'open'");
            $check_self->bind_param("i", $user_id);
            $check_self->execute();
            if ($check_self->get_result()->num_rows > 0) {
                throw new Exception("Bạn đang trong một ca làm việc khác rồi! Hãy F5 lại.");
            }

            // BƯỚC 2: Kiểm tra xem có AI KHÁC đang mở ca không? (Trường hợp A bỏ về)
            // Lưu ý: Chỉ áp dụng nếu quán chỉ có 1 máy POS/1 ngăn kéo tiền.
            // Nếu nhiều máy thì logic sẽ phức tạp hơn (phải check theo device_id).
            // Ở đây giả định quy mô 1 quầy thu ngân.
            
            $check_others = $mysqli->query("SELECT id, user_id, start_time FROM work_sessions WHERE status = 'open' LIMIT 1");
            
            if ($row_other = $check_others->fetch_assoc()) {
                // PHÁT HIỆN CA CŨ CHƯA ĐÓNG!
                $old_session_id = $row_other['id'];
                $old_user_id = $row_other['user_id'];
                $old_start_time = $row_other['start_time'];

                // A. Tính doanh thu của ca cũ đó
                $stmt_sales = $mysqli->prepare("SELECT SUM(total_price) as total FROM orders WHERE user_id = ? AND order_date >= ? AND status = 'paid'");
                $stmt_sales->bind_param("is", $old_user_id, $old_start_time);
                $stmt_sales->execute();
                $total_sales_old = $stmt_sales->get_result()->fetch_assoc()['total'] ?? 0;

                // B. Cưỡng chế đóng ca cũ (FORCE CLOSE)
                // Lấy tiền đầu ca của B gán cho tiền cuối ca của A
                // Ghi chú tự động
                $auto_note = "Hệ thống tự chốt do nhân viên mới vào ca.";
                
                $stmt_close_old = $mysqli->prepare("UPDATE work_sessions SET end_time = NOW(), end_cash = ?, total_sales = ?, note = ?, status = 'closed' WHERE id = ?");
                $stmt_close_old->bind_param("ddsi", $start_cash, $total_sales_old, $auto_note, $old_session_id);
                
                if (!$stmt_close_old->execute()) {
                    throw new Exception("Lỗi hệ thống khi chốt ca cũ.");
                }
            }

            // BƯỚC 3: Mở ca mới cho nhân viên B (Bình thường)
            $stmt_new = $mysqli->prepare("INSERT INTO work_sessions (user_id, start_cash, start_time, status) VALUES (?, ?, NOW(), 'open')");
            $stmt_new->bind_param("id", $user_id, $start_cash);
            
            if ($stmt_new->execute()) {
                $mysqli->commit();
                echo json_encode([
                    'success' => true, 
                    'message' => 'Đã chốt ca cũ (nếu có) và bắt đầu ca mới thành công!'
                ]);
            } else {
                throw new Exception("Lỗi mở ca mới: " . $stmt_new->error);
            }

        } catch (Exception $e) {
            $mysqli->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // 3. Thoát ca (End Shift)
    elseif ($action === 'end_shift') {
        $input = json_decode(file_get_contents("php://input"), true);
        $end_cash = $input['end_cash'] ?? 0;
        $note = $input['note'] ?? '';

        // Tính tổng doanh thu trong phiên này (Query từ bảng orders)
        // Lấy session ID đang mở
        $stmt_get = $mysqli->prepare("SELECT id, start_time FROM work_sessions WHERE user_id = ? AND status = 'open'");
        $stmt_get->bind_param("i", $user_id);
        $stmt_get->execute();
        $session = $stmt_get->get_result()->fetch_assoc();

        if (!$session) {
            throw new Exception("Không tìm thấy ca làm việc nào đang mở.");
        }

        $session_id = $session['id'];
        $start_time = $session['start_time'];

        // Tính doanh thu từ lúc start_time đến giờ
        $stmt_sales = $mysqli->prepare("SELECT SUM(total_price) as total FROM orders WHERE user_id = ? AND order_date >= ? AND status = 'paid'");
        $stmt_sales->bind_param("is", $user_id, $start_time);
        $stmt_sales->execute();
        $sales_data = $stmt_sales->get_result()->fetch_assoc();
        $total_sales = $sales_data['total'] ?? 0;

        // Update đóng ca
        $stmt_update = $mysqli->prepare("UPDATE work_sessions SET end_time = NOW(), end_cash = ?, total_sales = ?, note = ?, status = 'closed' WHERE id = ?");
        $stmt_update->bind_param("ddsi", $end_cash, $total_sales, $note, $session_id);
        
        if ($stmt_update->execute()) {
            // Logout luôn cho an toàn
            session_destroy();
            echo json_encode(['success' => true, 'message' => 'Đã chốt ca thành công! Doanh thu: ' . number_format($total_sales)]);
        } else {
            throw new Exception("Lỗi đóng ca.");
        }
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>