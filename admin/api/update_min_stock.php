<?php
// admin/api/update_min_stock.php
require_once '../../includes/db_connection.php';
header('Content-Type: application/json');

// Xử lý biến kết nối (hỗ trợ cả $conn và $mysqli để tránh lỗi)
if (!isset($conn) && isset($mysqli)) {
    $conn = $mysqli;
}
if (!isset($conn) && function_exists('connect_db')) {
    $conn = connect_db();
}

// Kiểm tra kết nối
if (!$conn || $conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi kết nối Database']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['data'])) {
        $data = json_decode($_POST['data'], true);
        
        if (is_array($data)) {
            // Chuẩn bị câu lệnh SQL cập nhật
            $stmt = $conn->prepare("UPDATE ingredients SET min_quantity = ? WHERE id = ?");
            
            $successCount = 0;
            foreach ($data as $item) {
                $id = intval($item['id']);
                $min = floatval($item['min_quantity']);
                
                $stmt->bind_param("di", $min, $id);
                if ($stmt->execute()) {
                    $successCount++;
                }
            }
            $stmt->close();
            
            echo json_encode(['status' => 'success', 'message' => "Đã cập nhật $successCount nguyên liệu"]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không đúng định dạng']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Không nhận được dữ liệu']);
    }
}
?>