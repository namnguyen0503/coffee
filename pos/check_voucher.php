<?php
session_start();
require_once '../includes/db_connection.php'; // Đảm bảo đúng đường dẫn
header('Content-Type: application/json; charset=utf-8');

try {
    // 1. Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Unauthorized");
    }

    // 2. Nhận dữ liệu JSON
    $input = json_decode(file_get_contents("php://input"), true);
    $code = isset($input['code']) ? strtoupper(trim($input['code'])) : '';

    if (empty($code)) {
        throw new Exception("Mã giảm giá trống.");
    }

    // 3. Query Database tìm voucher
    // Giả sử bảng vouchers có cột: id, code, discount_percent, description
    $stmt = $mysqli->prepare("SELECT code, discount_percent, description FROM vouchers WHERE code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    $voucher = $result->fetch_assoc();

    if ($voucher) {
        echo json_encode([
            'success' => true,
            'code' => $voucher['code'],
            'percent' => (float)$voucher['discount_percent'],
            'message' => $voucher['description']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Mã giảm giá không tồn tại!'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>