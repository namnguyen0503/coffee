<?php
require '../tinh-nang/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['edit_id'];
    $name = $_POST['edit_name'];
    $price = $_POST['edit_price'];
    $category = $_POST['edit_category'];

    // 1. Giữ ảnh cũ hoặc upload ảnh mới
    $sql_get = "SELECT image_url FROM products WHERE id = ?";
    $stmt_get = $conn->prepare($sql_get);
    $stmt_get->bind_param("i", $id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    $row = $result->fetch_assoc();
    $image_path = $row['image_url'];

    if (!empty($_FILES['edit_image']['name'])) {
        $target_dir = "../../uploads/";
        $filename = time() . "_" . basename($_FILES["edit_image"]["name"]);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["edit_image"]["tmp_name"], $target_file)) {
            $image_path = "uploads/" . $filename;
        }
    }

    // 2. Cập nhật bảng products
    $sql = "UPDATE products SET name=?, price=?, category_id=?, image_url=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siisi", $name, $price, $category, $image_path, $id);

    if ($stmt->execute()) {
        // 3. Cập nhật công thức: XÓA CŨ -> THÊM MỚI (Cách đơn giản nhất để tránh trùng lặp)
        $conn->query("DELETE FROM recipes WHERE product_id = $id");

        if (isset($_POST['ing_id']) && isset($_POST['ing_qty'])) {
            $ing_ids = $_POST['ing_id'];
            $ing_qtys = $_POST['ing_qty'];
            
            $sql_recipe = "INSERT INTO recipes (product_id, ingredient_id, quantity_required) VALUES (?, ?, ?)";
            $stmt_recipe = $conn->prepare($sql_recipe);

            for ($i = 0; $i < count($ing_ids); $i++) {
                if (!empty($ing_ids[$i]) && $ing_qtys[$i] > 0) {
                    $stmt_recipe->bind_param("iid", $id, $ing_ids[$i], $ing_qtys[$i]);
                    $stmt_recipe->execute();
                }
            }
        }
        echo json_encode(['status' => 'success', 'message' => 'Cập nhật thành công!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi SQL: ' . $conn->error]);
    }
}
?>