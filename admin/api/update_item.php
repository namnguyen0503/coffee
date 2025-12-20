<?php
require '../tinh-nang/db_connection.php';
$conn = connect_db();

header('Content-Type: application/json'); // Bắt buộc dòng này để JS hiểu

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['edit_id'];
    $name = $_POST['edit_name'];
    $price = $_POST['edit_price'];
    $category = $_POST['edit_category'];
    // Đã xóa phần $status vì form bên main.php không gửi sang

    // 1. Lấy ảnh cũ
    $sql_get = "SELECT image_url FROM products WHERE id = ?";
    $stmt_get = $conn->prepare($sql_get);
    $stmt_get->bind_param("i", $id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    $row = $result->fetch_assoc();
    $image_path = $row['image_url'];

    // 2. Nếu có upload ảnh mới
    if (!empty($_FILES['edit_image']['name'])) {
        $target_dir = "../../uploads/"; 
        $filename = time() . "_" . basename($_FILES["edit_image"]["name"]);
        $target_file = $target_dir . $filename;
        
        if (move_uploaded_file($_FILES["edit_image"]["tmp_name"], $target_file)) {
            $image_path = "uploads/" . $filename; 
        }
    }

    // 3. Cập nhật SQL (Đã bỏ cột is_active ra khỏi câu lệnh)
    $sql = "UPDATE products SET name=?, price=?, category_id=?, image_url=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    
    // "siisi" nghĩa là: String (tên), Integer (giá), Integer (danh mục), String (ảnh), Integer (id)
    $stmt->bind_param("siisi", $name, $price, $category, $image_path, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Cập nhật thành công!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi SQL: ' . $conn->error]);
    }
}
?>