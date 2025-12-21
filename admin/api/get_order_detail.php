<?php
require '../tinh-nang/db_connection.php';
$conn = connect_db();

if (isset($_GET['id'])) {
    $order_id = $_GET['id'];
    
    $sql = "SELECT oi.*, p.name as product_name, p.image_url 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = $order_id";
            
    $query = mysqli_query($conn, $sql);
    
    echo '<table class="table table-bordered">
            <thead>
                <tr>
                    <th>Món</th>
                    <th>SL</th>
                    <th>Ghi chú</th>
                </tr>
            </thead>
            <tbody>';
            
    while ($row = mysqli_fetch_assoc($query)) {
        echo '<tr>
                <td>'.$row['product_name'].'</td>
                <td><strong>'.$row['quantity'].'</strong></td>
                <td><small class="text-muted">'.$row['note'].'</small></td>
              </tr>';
    }
    echo '</tbody></table>';
}
?>