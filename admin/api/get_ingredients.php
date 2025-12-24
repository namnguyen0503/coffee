<?php
require '../../includes/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

// Lấy danh sách nguyên liệu
$sql = "SELECT * FROM ingredients ORDER BY name ASC";
$result = $conn->query($sql);

$ingredients = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $ingredients[] = $row;
    }
}

echo json_encode($ingredients);
?>