<?php
require 'db_connection.php';


function edit_products($id, $name, $price, $category, $image_url, $is_active){
    global $conn;
    $conn = connect_db(); 
    
    
    $id = mysqli_real_escape_string($conn, $id);
    $name = mysqli_real_escape_string($conn, $name);
    $price = mysqli_real_escape_string($conn, $price);
    $category = mysqli_real_escape_string($conn, $category);
    $image_url = mysqli_real_escape_string($conn, $image_url); 
    $is_active = mysqli_real_escape_string($conn, $is_active);   

   
    $sql = "UPDATE products SET name = '$name', price = '$price', category_id = '$category', image_url = '$image_url', is_active = '$is_active' WHERE id = '$id'";
    
    $query = mysqli_query($conn, $sql);
    return $query;
}

function get_products($id){
    global $conn;
    $conn = connect_db();
   
    $id = (int)$id; 
    $sql = "SELECT * FROM products WHERE id = $id";
    $query = mysqli_query($conn, $sql);
    return $query;
}


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data_back = array();


if($id){
    $result = get_products($id);
    if($result && mysqli_num_rows($result) > 0){
        $data_back = mysqli_fetch_assoc($result);
    } else {
        die("Không tìm thấy sản phẩm");
    }
}

$errors = array();


if(!empty($_POST['sub-edit'])){
   
    $data['name'] = isset($_POST['name']) ? $_POST['name'] : '';
    $data['price'] = isset($_POST['price']) ? $_POST['price'] : '';
    $data['category_id'] = isset($_POST['category_id']) ? $_POST['category_id'] : '';
    $data['is_active'] = isset($_POST['is_active']) ? $_POST['is_active'] : '';
    
    
    if(empty($data['name'])) $errors['name'] = 'Chưa nhập tên';
    if(empty($data['price'])) $errors['price'] = 'Chưa nhập giá';
    
    
    $image_path_to_save = $data_back['image_url']; 

    
    if (isset($_FILES['image-url']) && $_FILES['image-url']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image-url']['tmp_name'];
        $file_name = $_FILES['image-url']['name'];
        $file_size = $_FILES['image-url']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_ext, $allowed_ext)) {
            if ($file_size < 5242880) {
                $new_name = time() . '.' . $file_ext; 
                $upload_dir = "uploads/";
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $target_path = $upload_dir . $new_name;
                
                if(move_uploaded_file($file_tmp, $target_path)){
                    $image_path_to_save = $target_path; 
                } else {
                    $errors['image-url'] = "Lỗi lưu file.";
                }
            } else {
                $errors['image-url'] = "File quá lớn (>5MB).";
            }
        } else {
            $errors['image-url'] = "Định dạng file không hỗ trợ.";
        }
    } 
    
    
    if(empty($errors)){
        
        edit_products($id, $data['name'], $data['price'], $data['category_id'], $image_path_to_save, $data['is_active']);
        
       
        if(isset($conn)) disconnect_db($conn);
        header("Location: drink-display.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
</head>
<body>
    <form action="?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">

        Name: <input type="text" name="name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : $data_back['name']; ?>">
        <span style="color:red"><?php if(!empty($errors['name'])) echo $errors['name']; ?></span>
        <br>

        Price: <input type="number" name="price" value="<?php echo isset($_POST['price']) ? $_POST['price'] : $data_back['price']; ?>">
        <span style="color:red"><?php if(!empty($errors['price'])) echo $errors['price']; ?></span>
        <br>

        Category: 
        <select name="category_id">
            <option value="1" <?php echo ($data_back['category_id'] == 1) ? 'selected' : ''; ?>>Đồ ăn</option>
            <option value="2" <?php echo ($data_back['category_id'] == 2) ? 'selected' : ''; ?>>Đồ uống</option>
        </select>
        <br>
     
        Current Image: <img src="<?php echo $data_back['image_url']; ?>" width="100"><br>
        Change Image: <input type="file" name="image-url"> <span style="color:red"><?php if(!empty($errors['image-url'])) echo $errors['image-url']?></span>
        <br>

        Status:
        <select name="is_active">
            <option value="1" <?php echo ($data_back['is_active'] == 1) ? 'selected' : ''; ?>>Còn hàng</option>
            <option value="0" <?php echo ($data_back['is_active'] == 0) ? 'selected' : ''; ?>>Hết hàng</option>
        </select>
        <br><br>

        <input type="submit" name="sub-edit" value="Lưu thay đổi">
    </form>
   
</body>
</html>