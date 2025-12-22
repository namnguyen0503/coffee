<?php
require '../../includes/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

if (isset($_GET['product_id'])) {
    $id = $_GET['product_id'];
    $sql = "SELECT ingredient_id, quantity_required FROM recipes WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $recipes = [];
    while($row = $result->fetch_assoc()){
        $recipes[] = $row;
    }
    echo json_encode($recipes);
}
?>