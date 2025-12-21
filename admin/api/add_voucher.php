<?php
require '../tinh-nang/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = strtoupper(trim($_POST['code'])); // Chuyển thành chữ hoa
    $percent = (int)$_POST['percent'];
    $desc = $_POST['description'];

    // Validate
    if(empty($code)) {
        echo json_encode(['status' => 'error', 'message' => 'Mã không được để trống']);
        exit;
    }

    // Kiểm tra trùng
    $check = $conn->query("SELECT id FROM vouchers WHERE code = '$code'");
    if($check->num_rows > 0){
        echo json_encode(['status' => 'error', 'message' => 'Mã này đã tồn tại!']);
        exit;
    }

    $sql = "INSERT INTO vouchers (code, discount_percent, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sis", $code, $percent, $desc);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Thêm mã thành công!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống!']);
    }
}
?>