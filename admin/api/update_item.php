<?php
require '../../includes/db_connection.php';
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

    // --- THAY TOÀN BỘ ĐOẠN NÀY TRONG update_item.php ---
// if (!empty($_FILES['edit_image']['name'])) { ... }

if (!empty($_FILES['edit_image']['name'])) {

    // 1) Đồng bộ cách lưu ảnh giống add_item.php
    $root_path = dirname(__DIR__, 2); // admin/api -> root
    $sub_folder = ($category == 1) ? "drink/" : "food/";
    $target_dir = $root_path . "/assets/img/" . $sub_folder;

    // 2) Tạo thư mục nếu chưa tồn tại
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // 3) Kiểm tra lỗi upload cơ bản
    if (!isset($_FILES['edit_image']['error']) || $_FILES['edit_image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Upload ảnh thất bại. Mã lỗi: ' . ($_FILES['edit_image']['error'] ?? 'unknown')
        ]);
        exit;
    }

    // 4) Kiểm tra kích thước file (size limit)
    $maxBytes = 2 * 1024 * 1024; // 2MB
    $fileSize = (int)($_FILES['edit_image']['size'] ?? 0);

    if ($fileSize <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'File ảnh rỗng hoặc không đọc được kích thước.']);
        exit;
    }

    if ($fileSize > $maxBytes) {
        $maxMB = $maxBytes / (1024 * 1024);
        $actualMB = $fileSize / (1024 * 1024);
        echo json_encode([
            'status' => 'error',
            'message' => sprintf('Ảnh quá lớn. Tối đa %.1fMB, file hiện tại %.2fMB.', $maxMB, $actualMB)
        ]);
        exit;
    }

    // 5) Validate extension
    $ext = strtolower(pathinfo($_FILES['edit_image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed, true)) {
        echo json_encode(['status' => 'error', 'message' => 'File ảnh không hợp lệ (chỉ jpg/jpeg/png/webp).']);
        exit;
    }

    // 6) Đặt tên file an toàn
    $safe_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['edit_image']['name']));
    $filename = time() . "_" . $safe_name;

    $target_file = $target_dir . $filename;

    // 7) Upload
    if (move_uploaded_file($_FILES["edit_image"]["tmp_name"], $target_file)) {
        $image_path = "assets/img/" . $sub_folder . $filename;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Upload ảnh thất bại (không ghi được file lên server).']);
        exit;
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