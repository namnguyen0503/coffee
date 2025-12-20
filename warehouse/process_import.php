<?php
session_start();
require_once '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu
    $ing_id = intval($_POST['ingredient_id']);
    $qty_add = floatval($_POST['quantity_add']);
    $note = trim($_POST['note']);
    $user_id = $_SESSION['user_id'];

    if ($ing_id > 0 && $qty_add > 0) {
        $mysqli->begin_transaction();

        try {
            // 1. Cập nhật số lượng tồn kho (Cộng thêm)
            $stmt = $mysqli->prepare("UPDATE ingredients SET quantity = quantity + ?, last_updated = NOW() WHERE id = ?");
            $stmt->bind_param("di", $qty_add, $ing_id);
            if (!$stmt->execute()) throw new Exception("Lỗi update kho");
            $stmt->close();

            // 2. Ghi log nhập hàng
            $stmt_log = $mysqli->prepare("INSERT INTO inventory_log (ingredient_id, type, quantity, note, user_id, created_at) VALUES (?, 'import', ?, ?, ?, NOW())");
            // 'import' là từ khóa cho việc nhập hàng
            $stmt_log->bind_param("idsi", $ing_id, $qty_add, $note, $user_id);
            if (!$stmt_log->execute()) throw new Exception("Lỗi ghi log");
            $stmt_log->close();

            $mysqli->commit();
            
            // Xong thì quay lại trang chủ kho với thông báo
            echo "<script>alert('Nhập kho thành công!'); window.location.href='index.php';</script>";

        } catch (Exception $e) {
            $mysqli->rollback();
            echo "Lỗi: " . $e->getMessage();
        }
    } else {
        echo "<script>alert('Dữ liệu không hợp lệ'); window.history.back();</script>";
    }
}
?>