<?php
require_once __DIR__ . '/../../includes/db_connection.php';
header('Content-Type: application/json');
$conn = connect_db();

$name = $_POST['name'];
$price = $_POST['price'];
$cat_id = $_POST['category_id'];
$ingredients = json_decode($_POST['ingredients'], true); // Mảng JSON: [{id: 1, qty: 20}, ...]

$conn->begin_transaction();
try {
    // 1. Thêm món
    $stmt = $conn->prepare("INSERT INTO products (name, price, category_id) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $name, $price, $cat_id);
    $stmt->execute();
    $product_id = $stmt->insert_id;

    // 2. Thêm công thức (Mapping)
    if (!empty($ingredients)) {
        $stmt_recipe = $conn->prepare("INSERT INTO recipes (product_id, ingredient_id, quantity) VALUES (?, ?, ?)");
        foreach ($ingredients as $ing) {
            $stmt_recipe->bind_param("iid", $product_id, $ing['id'], $ing['qty']);
            $stmt_recipe->execute();
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Thêm món và công thức thành công']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>