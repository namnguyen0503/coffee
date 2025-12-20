<?php
session_start();
require_once '../includes/db_connection.php';

// Kiểm tra quyền
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'wh-staff' && $_SESSION['role'] !== 'admin')) {
    die("Không có quyền truy cập");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Lấy dữ liệu từ Form
    $ing_id = intval($_POST['ingredient_id']);
    $qty_add = floatval($_POST['quantity_add']);
    
    // [MỚI] Lấy số tiền (nếu bỏ trống thì coi là 0)
    $cost = isset($_POST['import_cost']) && $_POST['import_cost'] !== '' ? floatval($_POST['import_cost']) : 0;
    
    $note = trim($_POST['note']);
    $user_id = $_SESSION['user_id'];

    if ($ing_id > 0 && $qty_add > 0) {
        $mysqli->begin_transaction();

        try {
            // 2. Cập nhật số lượng tồn kho (Cộng thêm)
            $stmt = $mysqli->prepare("UPDATE ingredients SET quantity = quantity + ?, last_updated = NOW() WHERE id = ?");
            $stmt->bind_param("di", $qty_add, $ing_id);
            if (!$stmt->execute()) throw new Exception("Lỗi update kho: " . $stmt->error);
            $stmt->close();

            // 3. Ghi log nhập hàng (KÈM THEO GIÁ TIỀN)
            // Cột 'cost' vừa thêm vào Database
            $stmt_log = $mysqli->prepare("INSERT INTO inventory_log (ingredient_id, type, quantity, cost, note, user_id, created_at) VALUES (?, 'import', ?, ?, ?, ?, NOW())");
            
            // "iddsi" nghĩa là: int, double, double, string, int
            $stmt_log->bind_param("iddsi", $ing_id, $qty_add, $cost, $note, $user_id);
            
            if (!$stmt_log->execute()) throw new Exception("Lỗi ghi log: " . $stmt_log->error);
            $stmt_log->close();

            $mysqli->commit();
            
            // Xong thì quay lại trang chủ kho
            echo "<script>alert('Nhập kho thành công!'); window.location.href='index.php';</script>";

        } catch (Exception $e) {
            $mysqli->rollback();
            echo "<script>alert('CÓ LỖI XẢY RA: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Dữ liệu không hợp lệ. Vui lòng kiểm tra số lượng.'); window.history.back();</script>";
    }
}
?>