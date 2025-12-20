<?php
require 'db_connection.php';
function get_oder(){
    global $conn;
    $conn = connect_db();
    $sql = "SELECT * FROM orders WHERE status = 'paid'";
    $query=mysqli_query($conn,$sql);
    return $query;
}
function total_price(){
$item = get_oder();
$total=0;
if($item){
$monney = array();
while($row = mysqli_fetch_assoc($item)){
    $monney[] = $row;
}
foreach($monney as $total_price){
    $total += $total_price['total_price'];
}
}
return $total;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./calculate.css">
    <title>Document</title>
    
</head>
<body>
    <div class="price-fullscreen-container">
        <h1>Tổng doanh thu</h1>
        
        <p class="price-value">
            <?php 
            $ans = total_price();
           
            echo number_format($ans); 
            ?>
        </p>
    </div>
    <a href="./drink-display.php">back</a>
</body>
</html>
