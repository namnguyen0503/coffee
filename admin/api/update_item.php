<?php
require '../../includes/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['edit_id'];
    $name = $_POST['edit_name'];
    $price = $_POST['edit_price'];
    $category = $_POST['edit_category'];
    $status = $_POST['edit_status'];

    $root_path = dirname(__DIR__, 2); 
    
    // Cập nhật thông tin cơ bản trước
    $sql = "UPDATE products SET name=?, price=?, category_id=?, status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiii", $name, $price, $category, $status, $id);
    
    if ($stmt->execute()) {
        
        // Xử lý Ảnh mới (Nếu có)
        if (!empty($_FILES['edit_image']['name'])) {
            
            // --- ĐOẠN MỚI THÊM: KIỂM TRA KÍCH CỠ ẢNH ---
            $max_size = 500 * 1024; // 500KB
            
            if ($_FILES['edit_image']['size'] > $max_size) {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Ảnh quá lớn (>500KB)! Tên và giá món đã được cập nhật, nhưng ảnh chưa thay đổi. Vui lòng chọn ảnh nhẹ hơn.'
                ]);
                exit; // Dừng xử lý ảnh
            }
            // -------------------------------------------

            $sub_folder = ($category == 1) ? "drink/" : "food/";
            $target_dir = $root_path . "/assets/img/" . $sub_folder;
            
            if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
            
            // --- SỬA Ở ĐÂY: Giữ nguyên tên gốc ---
            $original_name = basename($_FILES["edit_image"]["name"]);
            $clean_name = str_replace(' ', '_', $original_name);
            $filename = $clean_name;
            // ------------------------------------
            
            $target_file = $target_dir . $filename;

            if (move_uploaded_file($_FILES["edit_image"]["tmp_name"], $target_file)) {
                $image_path = "assets/img/" . $sub_folder . $filename;
                
                $sql_img = "UPDATE products SET image_url=? WHERE id=?";
                $stmt_img = $conn->prepare($sql_img);
                $stmt_img->bind_param("si", $image_path, $id);
                $stmt_img->execute();
            }
        }

        // ... (Đoạn cập nhật công thức giữ nguyên như cũ) ...
        $conn->query("DELETE FROM recipes WHERE product_id = $id");
        if (isset($_POST['ing_id']) && isset($_POST['ing_qty'])) {
            $ing_ids = $_POST['ing_id']; $ing_qtys = $_POST['ing_qty'];
            $stmt_r = $conn->prepare("INSERT INTO recipes (product_id, ingredient_id, quantity_required) VALUES (?, ?, ?)");
            for ($i = 0; $i < count($ing_ids); $i++) {
                if (!empty($ing_ids[$i]) && $ing_qtys[$i] > 0) {
                    $stmt_r->bind_param("iid", $id, $ing_ids[$i], $ing_qtys[$i]);
                    $stmt_r->execute();
                }
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Cập nhật thành công!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi SQL: ' . $conn->error]);
    }
}
?>