<?php
require '../../includes/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $status = isset($_POST['status']) ? $_POST['status'] : 1; 

    // 1. Xác định đường dẫn gốc
    $root_path = dirname(__DIR__, 2); // C:/xampp/htdocs/coffee
    
    // Mặc định
    $image_path = "assets/dist/img/default.jpg"; 

    if (!empty($_FILES['image']['name'])) {
        
        // --- SỬA Ở ĐÂY: GIỚI HẠN 500KB ---
        $max_size = 500 * 1024; // 500KB = 512,000 bytes
        
        if ($_FILES['image']['size'] > $max_size) {
            echo json_encode(['status' => 'error', 'message' => 'Ảnh quá lớn! Vui lòng chọn ảnh có dung lượng nhỏ hơn 500KB.']);
            exit; 
        }
        // ---------------------------------

        $sub_folder = ($category == 1) ? "drink/" : "food/";
        $target_dir = $root_path . "/assets/img/" . $sub_folder;
        
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $original_name = basename($_FILES["image"]["name"]);
        $clean_name = str_replace(' ', '_', $original_name);
        
        $filename = $clean_name;

        $target_file = $target_dir . $filename; 

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = "assets/img/" . $sub_folder . $filename;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi upload. Kiểm tra folder: ' . $target_dir]);
            exit;
        }
    }

    $sql = "INSERT INTO products (name, price, category_id, image_url, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siisi", $name, $price, $category, $image_path, $status);
    
    if ($stmt->execute()) {
        $product_id = $conn->insert_id; 
        
        if (isset($_POST['ing_id']) && isset($_POST['ing_qty'])) {
            $ing_ids = $_POST['ing_id']; $ing_qtys = $_POST['ing_qty'];
            $stmt_r = $conn->prepare("INSERT INTO recipes (product_id, ingredient_id, quantity_required) VALUES (?, ?, ?)");
            for ($i = 0; $i < count($ing_ids); $i++) {
                if (!empty($ing_ids[$i]) && $ing_qtys[$i] > 0) {
                    $stmt_r->bind_param("iid", $product_id, $ing_ids[$i], $ing_qtys[$i]);
                    $stmt_r->execute();
                }
            }
        }
        echo json_encode(['status' => 'success', 'message' => 'Thêm món thành công!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi SQL: ' . $conn->error]);
    }
}
?>