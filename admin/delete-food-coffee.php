<?php 
require 'db_connection.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : '';

function delete_products($id){
    global $conn;
    $conn= connect_db();
    $sql ="DELETE FROM products WHERE id = $id";
    $query= mysqli_query($conn, $sql);
    return $query;
}
if(isset($_POST['submit'])){
delete_products($id);
disconnect_db($conn);
header("Location: drink-display.php");
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="" method="post">
        <input type="submit" name="submit" value="bạn có chắc muốn xoá không?">
    </form>
</body>
</html>