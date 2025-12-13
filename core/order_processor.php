<?php
    // SỬA: Phải trả về JSON
    global $mysqli;
    require_once '../includes/db_connection.php';
    error_reporting(0);
    header('Content-Type: application/json; charset=utf-8');
    $json_data = file_get_contents('php://input');
    $order_data = json_decode($json_data, true);
    if (!$order_data) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Dữ liệu đơn hàng không hợp lệ.']);
        exit;
    }


   
   
    // function connect_db(){
    //     global $mysqli;
    //     $mysqli = new mysqli('localhost','root','','coffee');
    //     if ($mysqli->connect_errno) {
    //         die("Kết nối cơ sở dữ liệu thất bại: " . $mysqli->connect_error);
    //     }
    //     // $mysqli->set_charset("utf8mb4");
    //     return $mysqli;
    // }
    // connect_db();
    $total_price = $order_data['total_price'];
        // $sql = "INSERT INTO orders (total_price, status) VALUES ($total_price, 'paid');";  
    $sql = "INSERT INTO orders (total_price, status) VALUES ($total_price, 'paid');";        
        $query = mysqli_query($mysqli, $sql);
    $sql = "SELECT MAX(id) FROM orders;";
                    $query= mysqli_query($mysqli, $sql);
                    $order_id = mysqli_fetch_array($query);
                    
    // $sql = "INSERT INTO orders (total_price) VALUES (?);";  
    // $stmt = $mysqli->prepare($sql);
    // if ($stmt === false) {
    //     http_response_code(500);
    //     echo json_encode(['status' => 'error', 'message' => 'Lỗi chuẩn bị câu lệnh: ' . $mysqli->error]);
    //     exit;
    // }
    // // 2. Xử lý đơn hàng (Chèn vào CSDL, v.v.)

    // $total_price = $order_data['total_price'];
    // $stmt->bind_param('i', $total_price);
    // if (!$stmt->execute()) {
    //     http_response_code(500);
    //     echo json_encode(['status' => 'error', 'message' => 'Lỗi thực thi câu lệnh: ' . $stmt->error]);
    //     exit;
    // }




    // Giả sử xử lý thành công (Thay thế bằng code CSDL thực tế)
// $order_id = $mysqli->insert_id;// ID đơn hàng sau khi insert vào CSDL

 
// 3. Phản hồi Thành công (PHẢI BỔ SUNG)
http_response_code(200);
echo json_encode([
    'status' => 'success', 
    'message' => 'Thanh toán thành công.', 
    'order_id' => $order_id[0]
]);

exit; // Luôn dùng exit để dừng script sau khi echo phản hồi cuối cùng
?>