<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db_connection.php';

try {
    // 1. Nhận dữ liệu JSON từ AJAX
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!$input) {
        throw new Exception("Dữ liệu gửi lên không hợp lệ (Phải là JSON).");
    }

    $ing_id = isset($input['ingredient_id']) ? (int)$input['ingredient_id'] : 0;
    $qty_add = isset($input['quantity_add']) ? (float)$input['quantity_add'] : 0;
    $cost = isset($input['import_cost']) ? (float)$input['import_cost'] : 0;
    $note_input = isset($input['note']) ? trim($input['note']) : '';
    $user_id = $_SESSION['user_id'] ?? 0;

    // Validate
    if ($ing_id <= 0) throw new Exception("Chưa chọn nguyên liệu.");
    if ($qty_add <= 0) throw new Exception("Số lượng nhập phải lớn hơn 0.");

    $mysqli->begin_transaction();

    // 2. Cập nhật số lượng tồn kho
    $stmt_update = $mysqli->prepare("UPDATE ingredients SET quantity = quantity + ? WHERE id = ?");
    $stmt_update->bind_param("di", $qty_add, $ing_id);
    
    if (!$stmt_update->execute()) {
        throw new Exception("Lỗi SQL Update: " . $mysqli->error);
    }

    // 3. Ghi Log nhập kho
    // Ghép giá nhập vào ghi chú (Vì bảng log có thể chưa có cột price)
    // Nếu bạn muốn lưu giá nhập, hãy thêm cột 'price' vào bảng inventory_log
    $full_note = $note_input;
    if ($cost > 0) {
        $full_note .= " [Chi phí: " . number_format($cost) . "đ]";
    }

    // Trong process_import.php, đoạn INSERT phải như này:
$stmt_log = $mysqli->prepare("INSERT INTO inventory_log (ingredient_id, type, quantity, cost, note, user_id, created_at) VALUES (?, 'import', ?, ?, ?, ?, NOW())");
$stmt_log->bind_param("iddsi", $ing_id, $qty_add, $cost, $note_input, $user_id);
    
    if (!$stmt_log->execute()) {
        throw new Exception("Lỗi ghi Log.");
    }

    $mysqli->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Đã nhập kho thành công!'
    ]);

} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>