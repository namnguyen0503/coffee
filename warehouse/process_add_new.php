<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/db_connection.php';

try {
    // 1. Check quyền
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'wh-staff' && $_SESSION['role'] !== 'admin')) {
        throw new Exception("Unauthorized");
    }

    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input) throw new Exception("Dữ liệu lỗi.");

    $name = trim($input['name'] ?? '');
    $unit = trim($input['unit'] ?? '');
    $qty = (float)($input['quantity'] ?? 0);
    $min_qty = (float)($input['min_quantity'] ?? 0);
    $cost = (float)($input['cost'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if (empty($name) || empty($unit)) throw new Exception("Tên và Đơn vị tính không được để trống.");
    if ($qty < 0) throw new Exception("Số lượng ban đầu không được âm.");

    // 2. Check trùng tên
    $stmt_check = $mysqli->prepare("SELECT id FROM ingredients WHERE name = ?");
    $stmt_check->bind_param("s", $name);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        throw new Exception("Nguyên liệu '$name' đã tồn tại trong hệ thống!");
    }

    $mysqli->begin_transaction();

    // 3. Insert Ingredients
    $stmt_ins = $mysqli->prepare("INSERT INTO ingredients (name, unit, quantity, min_quantity, updated_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt_ins->bind_param("ssdd", $name, $unit, $qty, $min_qty);
    
    if ($stmt_ins->execute()) {
        $new_id = $mysqli->insert_id;

        // 4. Ghi Log nhập kho lần đầu (Nếu có số lượng ban đầu)
        if ($qty > 0) {
            $log_note = "Khởi tạo nguyên liệu mới. Vốn: " . number_format($cost);
            $stmt_log = $mysqli->prepare("INSERT INTO inventory_log (ingredient_id, type, quantity, note, user_id, created_at) VALUES (?, 'import', ?, ?, ?, NOW())");
            $stmt_log->bind_param("idsi", $new_id, $qty, $log_note, $user_id);
            $stmt_log->execute();
        }

        $mysqli->commit();
        echo json_encode(['success' => true, 'message' => 'Đã thêm nguyên liệu mới thành công!']);
    } else {
        throw new Exception("Lỗi SQL: " . $mysqli->error);
    }

} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>