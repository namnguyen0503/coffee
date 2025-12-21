<?php
require '../tinh-nang/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

$sql = "SELECT id, name, unit FROM ingredients ORDER BY name ASC";
$result = $conn->query($sql);

$ingredients = [];
while ($row = $result->fetch_assoc()) {
    $ingredients[] = $row;
}
echo json_encode($ingredients);
?>