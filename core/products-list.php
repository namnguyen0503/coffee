<?php
    require_once '../includes/db_connection.php';
    $sql = "SELECT * FROM products 
WHERE is_active = 1 
ORDER BY category_id ASC;";
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        die("Lỗi chuẩn bị: " . $mysqli->error);
    }
    $stmt->execute();
    $result_products= $stmt->get_result();
    
    $sql = "SELECT * FROM categories;";
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        die("Lỗi chuẩn bị: " . $mysqli->error);
    }
    $stmt->execute();
    $result_categories= $stmt->get_result();

    $sql = "SELECT * FROM products;";
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        die("Lỗi chuẩn bị: " . $mysqli->error);
    }

?>