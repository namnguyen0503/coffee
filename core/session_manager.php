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

    // 3. Thoát ca (End Shift) - PHIÊN BẢN LOGOS: CHỐT CHẶT DÒNG TIỀN
    elseif ($action === 'end_shift') {
        $input = json_decode(file_get_contents("php://input"), true);
        $end_cash = isset($input['end_cash']) ? (float)$input['end_cash'] : 0; // Số tiền thực tế nhân viên đếm được
        $note = $input['note'] ?? '';

        // 1. Tìm ca làm việc đang mở của User này
        $stmt_get = $mysqli->prepare("SELECT id, start_time, start_cash FROM work_sessions WHERE user_id = ? AND status = 'open'");
        $stmt_get->bind_param("i", $user_id);
        $stmt_get->execute();
        $session = $stmt_get->get_result()->fetch_assoc();

        if (!$session) {
            throw new Exception("Không tìm thấy ca làm việc nào đang mở.");
        }

        $session_id = $session['id'];
        $start_time = $session['start_time'];
        $start_cash = (float)$session['start_cash'];

        // 2. Tính TỔNG DOANH THU (Để quản lý biết năng suất bán hàng)
        $stmt_total = $mysqli->prepare("SELECT SUM(total_price) as total FROM orders WHERE session_id = ? AND status = 'paid'");
        $stmt_total->bind_param("i", $session_id);
        $stmt_total->execute();
        $total_sales = $stmt_total->get_result()->fetch_assoc()['total'] ?? 0;

        // 3. Tính DOANH THU TIỀN MẶT (Để đối soát két sắt)
        $stmt_cash = $mysqli->prepare("SELECT SUM(total_price) as total_cash FROM orders WHERE session_id = ? AND status = 'paid' AND payment_method = 'cash'");
        $stmt_cash->bind_param("i", $session_id);
        $stmt_cash->execute();
        $cash_revenue = $stmt_cash->get_result()->fetch_assoc()['total_cash'] ?? 0;

        // 4. Tính toán số tiền LỆCH KÉT
        // Tiền mặt lý thuyết phải có = Vốn đầu ca + Doanh thu Tiền mặt
        $expected_cash = $start_cash + $cash_revenue;
        $difference = $end_cash - $expected_cash;

        // 5. Ghi chú chi tiết tự động vào Database (Dành cho Admin soi)
        $transfer_revenue = $total_sales - $cash_revenue;
        $auto_note = "[TỔNG KẾT CA]: " . 
                     "Doanh thu TM: " . number_format($cash_revenue) . "đ | " .
                     "Chuyển khoản: " . number_format($transfer_revenue) . "đ | " .
                     "Vốn đầu: " . number_format($start_cash) . "đ | " .
                     "Kỳ vọng két: " . number_format($expected_cash) . "đ | " .
                     "Thực tế: " . number_format($end_cash) . "đ | " .
                     "LỆCH: " . ($difference >= 0 ? "+" : "") . number_format($difference) . "đ. " . 
                     ($note ? " - Ghi chú thêm: " : "") . $note;

        // 6. Cập nhật đóng ca
        $stmt_update = $mysqli->prepare("UPDATE work_sessions SET end_time = NOW(), end_cash = ?, total_sales = ?, note = ?, status = 'closed' WHERE id = ?");
        $stmt_update->bind_param("ddsi", $end_cash, $total_sales, $auto_note, $session_id);
        
        if ($stmt_update->execute()) {
            // Logout để dọn sạch dấu vết phiên làm việc
            session_destroy();
            
            $msg = "Đã chốt ca thành công!\n";
            $msg .= "Tổng doanh thu: " . number_format($total_sales) . "đ\n";
            $msg .= "Chênh lệch két: " . number_format($difference) . "đ";
            
            echo json_encode(['success' => true, 'message' => $msg]);
        } else {
            throw new Exception("Lỗi cập nhật Database khi đóng ca.");
        }
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>