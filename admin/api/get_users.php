<?php
header('Content-Type: application/json');
require_once '../tinh-nang/db_connection.php';

$conn = connect_db();
$sql = "SELECT id, fullname, username, role FROM users ORDER BY id DESC";
$result = $conn->query($sql);

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode(['success' => true, 'data' => $data]);
disconnect_db($conn);
?>