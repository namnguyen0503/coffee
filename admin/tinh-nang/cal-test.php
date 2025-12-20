<?php
require 'db_connection.php';
//  Lấy tất cả các đơn hàng đã thanh toán trong ngày
    global $conn;
    $conn = connect_db();
    $sql = "SELECT * FROM orders WHERE status = 'paid' AND DATE(order_date) = CURDATE()";
    $query=mysqli_query($conn,$sql);
    while($row=mysqli_fetch_assoc($query)){
        $data[]=$row;
    }
    // Lấy riêng từng vật phẩm
    function get_product($oder_item)
    {
       global $conn;
    $conn = connect_db(); 
        $sql2="SELECT * FROM products WHERE id = {$data1['product_id']}";
    $query2=mysqli_query($conn,$sql2);
    $data2=array();
    While($row2=mysqli_fetch_assoc($query2)){
        $data2[]=$row2;
    }
    return $data2;
    }
// Lấy các vật phẩm được trong 1 đơn cùng ngày
function get_oder($item)
{
    global $conn;
    $conn = connect_db();
    $sql1 ="SELECT * FROM order_items WHERE order_id = {$item['id']}";
    $query1=mysqli_query($conn,$sql1);
    $data1=array();
    While($row1=mysqli_fetch_assoc($query1)){
        $data1[]=$row1;
    }
    $oder_items=array();
    foreach($data1 as $data)
    {
        $data0= array();
        $data0= get_product($data['product_id']);
        $total_price_item = $data0['price'] * $data['quantity'];
        $name = $data0['name']; 
        $quantity=$data['quantity'];
        
        $oder_items[] = [           
            'ten' => $name,           
            'so_luong' => $quantity,
            'total_price' => $total_price_item
        ];
    }
    return $oder_items;
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
    <table>
        <tr>
            <td>Đơn số</td>
            <td>Tên món</td>
            <td>Số lượng</td>
            <td>Thành tiền</td>

        </tr>
        <?php $inc=1;?>
        <?php foreach($data as $items){?>
            
            <?php $inc++; ?>
            <?php $tmp=array(); 
             $tmp= get_oder($items); ?>
            <?php foreach($tmp as $item){ ?>
        <tr>
            <td><?php echo 'Đơn số' . $inc; ?></td>
            <td><?php echo $item['ten']; ?></td>
            <td><?php echo $item['so_luong']; ?></td>
            <td><?php echo $item['total_price']; ?></td>
        </tr>
        <?php } ?>
            <?php } ?>
    </table>
    <a href="./drink-display.php">back</a>
</body>
</html>
