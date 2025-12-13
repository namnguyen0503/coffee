
<?php
require 'db_connection.php';
function edit_products($id,$name,$price,$category,$image_url,$is_active){
    global $conn;
    $conn= connect_db();
    // sql injection




    $sql = " UPDATE products SET name = '$name' , price = '$price ', category_id =' $category', image_url = '$image_url',is_active = '$is_active' WHERE id = $id";
    $query = mysqli_query($conn,$sql);
    return $query;
}
function get_products($id){
    global $conn;
    $conn= connect_db();
    $sql = "SELECT * FROM products WHERE id = $id ";
    $query = mysqli_query($conn, $sql);
    return $query;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : '';
if($id){
    $data =array();
    $data = mysqli_fetch_assoc(get_products($id));
}
if(!empty($_POST['sub-edit'])){
    $data['name'] = isset($_POST['name']) ? $_POST['name'] : '';
    $data['price'] = isset($_POST['price']) ? $_POST['price'] : '';
    $data['category_id']= isset($_POST['category_id']) ? $_POST['category_id'] : '';
    $data['is_active'] = isset($_POST['is_active']) ? $_POST['is_active'] : '';
    $errors = array();
    if(empty($data['name'])){
        $errors['name'] ='chưa nhập tên';
    }
    if(empty($data['price'])){
        $errors['price'] ='chưa nhập giá';
    }
    
    if(empty($data['is_active'])){
        $errors['is_active'] ='chưa nhập trạng thái';
    }
    
    if (isset($_FILES['image-url']) && $_FILES['image-url']['error'] == UPLOAD_ERR_OK) {

        $file_tmp = $_FILES['image-url']['tmp_name'];
        $file_name = $_FILES['image-url']['name'];
        $file_size = $_FILES['image-url']['size'];

        // 1. Tách đuôi file để kiểm tra
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // 2. Danh sách đuôi file cho phép
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        // 3. Kiểm tra định dạng ảnh
        if (in_array($file_ext, $allowed_ext)) {

            // 4. Kiểm tra kích thước (Ví dụ: giới hạn 5MB = 5 * 1024 * 1024)
            if ($file_size < 5242880) {

                // 5. Tạo tên file mới an toàn hơn (chỉ dùng time và đuôi file)
                
                $new_name = time() . '.' . $file_ext; 
                $upload_dir = "uploads/";

                // Kiểm tra nếu thư mục chưa có thì tạo mới
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $target_path = $upload_dir . $new_name;

                // Di chuyển file
                if(move_uploaded_file($file_tmp, $target_path)){
                    $data['image_url'] = $target_path;
                } else {
                    $errors['image-url'] = "Không thể lưu file (Lỗi phân quyền folder?)";
                }

            } else {
                $errors['image-url'] = "File quá lớn. Vui lòng chọn ảnh dưới 5MB.";
            }
        } 

    } else {
        // Bắt buộc phải chọn ảnh
        $errors['image-url'] = "Hãy chọn một file ảnh.";
    }
    
    if(!$errors){
        edit_products($data['id'],$data['name'], $data['price'],$data['category_id'],$data['image_url'],$data['is_active']);
        disconnect_db($conn);
        header("Location: drink-display.php");
    }
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
    <form action="" method="post" enctype="multipart/form-data">

        Name: <input type="text" name="name" value="<?php echo $data['name']; ?>"> <br>
        Price: <input type="number" name="price" value="<?php echo $data['price']; ?>"><br>

        Category_id 
        <select name="category_id">
            <option value="1" <?php if($data['category_id'] ==1) echo 'selected'; ?>>Đồ ăn</option>
            <option value="2"<?php if($data['category_id'] ==2) echo 'selected'; ?> >Đồ uống</option>
        </select><br>

        Image_url: <input type="file" name="image-url" ><br>
        <?php if(!empty($errors['image-url'])) echo $errors['image-url']?>

        <select name="is_active">
            <option value="1" <?php if($data['is_active'] ==1) echo 'selected'; ?>>Còn hàng</option>
            <option value="0" <?php if($data['is_active'] ==0) echo 'selected'; ?>>Hết hàng</option>
        </select><br>

        <input type="submit" name="sub-edit" value="Gửi">
    </form>
   
</body>
</html>