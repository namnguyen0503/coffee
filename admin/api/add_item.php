<?php
require '../../includes/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    
    // 1. Xử lý ảnh
    $image_path = "assets/dist/img/default.jpg"; // Ảnh mặc định
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../../uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = "uploads/" . $filename;
        }
    }

    // 2. Insert món mới vào bảng products
    $sql = "INSERT INTO products (name, price, category_id, image_url, is_active) VALUES (?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siis", $name, $price, $category, $image_path);
    
    if ($stmt->execute()) {
        $product_id = $conn->insert_id; // Lấy ID vừa tạo

        // 3. Insert công thức (Mapping nguyên liệu)
        if (isset($_POST['ing_id']) && isset($_POST['ing_qty'])) {
            $ing_ids = $_POST['ing_id'];
            $ing_qtys = $_POST['ing_qty'];
            
            $sql_recipe = "INSERT INTO recipes (product_id, ingredient_id, quantity_required) VALUES (?, ?, ?)";
            $stmt_recipe = $conn->prepare($sql_recipe);

            for ($i = 0; $i < count($ing_ids); $i++) {
                if (!empty($ing_ids[$i]) && $ing_qtys[$i] > 0) {
                    $stmt_recipe->bind_param("iid", $product_id, $ing_ids[$i], $ing_qtys[$i]);
                    $stmt_recipe->execute();
                }
            }
        }
        echo json_encode(['status' => 'success', 'message' => 'Thêm món và công thức thành công!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi SQL: ' . $conn->error]);
    }
}
?>