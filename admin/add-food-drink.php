<?php
require 'db_connection.php';

function add_categories($name){
    global $conn;
    $conn = connect_db();

    $sql = "INSERT INTO categories(name) VALUES ('$name')";
    return mysqli_query($conn, $sql);
}

function add_products($name, $price, $category_id, $image_url, $is_active){
    global $conn;
    $conn = connect_db();

    $sql = "INSERT INTO products(name, price, category_id, image_url, is_active) 
            VALUES ('$name', '$price', '$category_id', '$image_url', '$is_active')";

    return mysqli_query($conn, $sql);
}


// =============================================
// XỬ LÝ THÊM CATEGORIES
// =============================================
if(isset($_POST['sub-categories'])){

    $name = $_POST['name-categories'] ?? '';
    $errors = array();

    if(empty($name)){
        $errors['name-categories'] = 'Hãy nhập tên';
    }

    if(!$errors){
        add_categories($name);
        disconnect_db($conn);
    }
}



// =============================================
// XỬ LÝ THÊM PRODUCTS (CÓ UPLOAD FILE)
// =============================================
if(isset($_POST['sub-products'])){

    $errors = [];

    // Lấy dữ liệu text
    $data['name'] = $_POST['name'] ?? '';
    $data['price'] = $_POST['price'] ?? '';
    $data['category_id'] = $_POST['category_id'] ?? '';
    $data['is_active'] = $_POST['is_active'] ?? '';

    // KIỂM TRA DỮ LIỆU
    if(empty($data['name']))      $errors['name'] = "Hãy nhập tên";
    if(empty($data['price']))     $errors['price'] = "Hãy nhập giá tiền";
    if(empty($data['category_id'])) $errors['category_id'] = "Hãy nhập category id";


    // =============================================
    // XỬ LÝ UPLOAD FILE
    // =============================================

    // =============================================
    // XỬ LÝ UPLOAD FILE (ĐÃ NÂNG CẤP)
    // =============================================

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
                    $data['image-url'] = $target_path;
                } else {
                    $errors['image-url'] = "Không thể lưu file (Lỗi phân quyền folder?)";
                }

            } else {
                $errors['image-url'] = "File quá lớn. Vui lòng chọn ảnh dưới 5MB.";
            }
        } else {
            $errors['image-url'] = "Chỉ chấp nhận file ảnh (JPG, JPEG, PNG, GIF).";
        }

    } else {
        // Bắt buộc phải chọn ảnh
        $errors['image-url'] = "Hãy chọn một file ảnh.";
    }


    // =============================================
    // NẾU KHÔNG CÓ LỖI => LƯU DATABASE
    // =============================================
    if(!$errors){

        add_products(
            $data['name'],
            $data['price'],
            $data['category_id'],
            $data['image-url'],     // ĐƯỜNG DẪN FILE ĐÃ UPLOAD
            $data['is_active']
        );

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
    <a href="./drink-display.php">home</a>
    <!-- thêm vào categories -->
    <div class="categories">
    <h1>Thêm vào categories</h1>
    <form action="" method="post">
        Name: <input type="text" name="name-categories" value=""> <br>
        <?php if(!empty($errors['name-categories'])) echo $errors['name-categories']; ?>
        <input type="submit" name="sub-categories" value="Gửi">
    </form>
    </div>

    <!-- thêm vào product -->
    <div class="products">
    <h1>Thêm vào products</h1>

    <!-- BẮT BUỘC CÓ enctype="multipart/form-data" -->
    <form action="" method="post" enctype="multipart/form-data">

        Name: <input type="text" name="name" value=""> <br>
        Price: <input type="number" name="price" value=""><br>

        Category_id 
        <select name="category_id">
            <option value="1">Đồ ăn</option>
            <option value="2">Đồ uống</option>
        </select><br>

        Image_url: <input type="file" name="image-url"><br>

        <select name="is_active">
            <option value="1">Còn hàng</option>
            <option value="0">Hết hàng</option>
        </select><br>

        <input type="submit" name="sub-products" value="Gửi">

    </form>
    </div>
</body>
</html>


