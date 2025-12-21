<?php
require '../tinh-nang/db_connection.php';
$conn = connect_db();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $conn->query("DELETE FROM work_schedules WHERE id = $id");
    echo json_encode(['status' => 'success']);
}
?>