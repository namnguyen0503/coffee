<?php
require_once 'includes/db_connection.php';

$result = $mysqli->query("SHOW COLUMNS FROM orders LIKE 'payment_method'");
if ($result->num_rows > 0) {
    echo "<h1>✅ OK: Cột 'payment_method' ĐÃ TỒN TẠI.</h1>";
} else {
    echo "<h1>❌ LỖI: Cột 'payment_method' CHƯA CÓ trong bảng orders!</h1>";
}
?>