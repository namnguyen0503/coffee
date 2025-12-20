<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="ds-coffe">
              <?php
              
              require 'db_connection.php';
              $item_search= isset($_POST['data']) ? $_POST['data'] : '';
              $errors = array();
              if(empty($item_search)){
                $errors['data'] = 'not found';
              }
              
                function get_item_search( $item_search){
                  global $conn;
                  $conn= connect_db();
                  $sql= "SELECT *FROM products WHERE name =  $item_search";
                  $query=mysqli_query($conn,$sql);
                  $items = [];
                  while ($row = mysqli_fetch_assoc($query)) {
                     $items[] = $row;
                      }
                      return $items;
                }  
                $items = get_item_search( $item_search);


                ?>
              <?php foreach ($items as $item): ?>
                
              <div class="item-coffe">
                <img src="<?php echo $item['image_url']; ?>" alt="" class="thumb" />
                <p><?php echo $item['name'];?></p>
                <p>Giá: <?php echo $item['price']; ?></p>
                <p>Trạng thái: <?php if($item['is_active']){ 
                  echo 'còn bán';}
                  else{
                  echo 'không còn bán';
                  } ?></p>
                  <div class="edit">
                  <input onclick="window.location = 'edit-food-coffee.php?id=<?php echo $item['id']; ?>'" type="button" value="Edit">
                  <input onclick="window.location = 'delete-food-coffee.php?id=<?php echo $item['id']; ?>'" type="button" value="Delete">                                                
                  </div>
                  
              </div>
              
              <?php endforeach; ?>
</body>
</html>