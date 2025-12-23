<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db_connection.php';

try {
    // 1. Check quyền (Chỉ Admin hoặc Thủ kho)
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'wh-staff' && $_SESSION['role'] !== 'admin')) {
        throw new Exception("Bạn không có quyền thực hiện chức năng này.");
    }

    // 2. Nhận dữ liệu JSON
    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input) throw new Exception("Dữ liệu không hợp lệ.");

    $ing_id = isset($input['ingredient_id']) ? (int)$input['ingredient_id'] : 0;
    $qty_sub = isset($input['quantity_sub']) ? (float)$input['quantity_sub'] : 0;
    $reason = isset($input['reason']) ? trim($input['reason']) : '';
    $user_id = $_SESSION['user_id'];

    // 3. Validate
    if ($ing_id <= 0) throw new Exception("Chưa chọn nguyên liệu.");
    if ($qty_sub <= 0) throw new Exception("Số lượng xuất phải lớn hơn 0.");
    if (empty($reason)) throw new Exception("Vui lòng nhập lý do xuất kho (VD: Hỏng, Kiểm kê sai...).");

    $mysqli->begin_transaction();

    // 4. Kiểm tra tồn kho hiện tại (Không được xuất âm kho)
    $stmt_check = $mysqli->prepare("SELECT quantity, name FROM ingredients WHERE id = ? FOR UPDATE");
    $stmt_check->bind_param("i", $ing_id);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    $current = $res_check->fetch_assoc();

    if (!$current) throw new Exception("Nguyên liệu không tồn tại.");
    if ($current['quantity'] < $qty_sub) {
        throw new Exception("Không đủ hàng để xuất! Tồn hiện tại: " . $current['quantity']);
    }

    // 5. Trừ kho
    $stmt_update = $mysqli->prepare("UPDATE ingredients SET quantity = quantity - ? WHERE id = ?");
    $stmt_update->bind_param("di", $qty_sub, $ing_id);
    if (!$stmt_update->execute()) {
        throw new Exception("Lỗi SQL Update: " . $mysqli->error);
    }

    // 6. Ghi Log (Lưu ý type là 'export')
    // Ghi chú sẽ có dạng: "Xuất hủy: [Lý do]"
    $full_note = "Xuất kho điều chỉnh: " . $reason;
    
    $stmt_log = $mysqli->prepare("INSERT INTO inventory_log (ingredient_id, type, quantity, note, user_id, created_at) VALUES (?, 'export', ?, ?, ?, NOW())");
    $stmt_log->bind_param("idsi", $ing_id, $qty_sub, $full_note, $user_id);
    
    if (!$stmt_log->execute()) {
        throw new Exception("Lỗi ghi Log.");
    }

    $mysqli->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Đã trừ kho thành công!'
    ]);

} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>