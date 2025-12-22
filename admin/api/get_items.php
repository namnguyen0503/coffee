<?php
require '../../includes/db_connection.php';
$conn = connect_db();

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0; 
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$sql = "SELECT * FROM products WHERE is_active = 1";
if ($category_id > 0) $sql .= " AND category_id = $category_id";
if (!empty($search)) $sql .= " AND name LIKE '%$search%'";
$sql .= " ORDER BY id DESC";

$query = mysqli_query($conn, $sql);

if (mysqli_num_rows($query) > 0) {
    echo '<div class="row">';
    while ($row = mysqli_fetch_assoc($query)) {
        $img_url = $row['image_url'];

        // --- LOGIC XỬ LÝ ĐƯỜNG DẪN ẢNH (QUAN TRỌNG) ---
        
        // Trường hợp 1: Ảnh Upload (DB lưu "uploads/...") 
        // -> Thêm "../" để lùi ra root lấy ảnh
        if (strpos($img_url, 'uploads') === 0) {
            $img_url = '../' . $img_url; 
        }
        // Trường hợp 2: Ảnh trong Assets (DB lưu "assets/..." mà thiếu "../")
        // -> Thêm "../" để lùi ra root rồi mới vào assets (vì assets nằm ngang hàng admin)
        elseif (strpos($img_url, 'assets') === 0) {
             $img_url = '../' . $img_url;
        }
        // Trường hợp 3: DB đã lưu đúng "../assets/..." 
        // -> Giữ nguyên (Đây là trường hợp chuẩn trong file SQL của bạn)

        $price = number_format($row['price'], 0, ',', '.');
        
        echo '
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="card h-100 shadow-sm">
                <div style="height: 120px; overflow: hidden; border-bottom: 1px solid #eee;">
                    <img src="'.$img_url.'" class="card-img-top" alt="'.$row['name'].'" 
                         style="width: 100%; height: 100%; object-fit: cover;"
                         onerror="this.src=\'assets/dist/img/logo coffee.png\'">
                </div>
                <div class="card-body p-2">
                    <h5 class="card-title font-weight-bold" style="font-size: 1rem; height: 35px; overflow: hidden; margin-bottom: 5px;">'.$row['name'].'</h5>
                    <p class="card-text text-danger font-weight-bold mb-2" style="font-size: 0.95rem;">'.$price.' đ</p>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-sm btn-warning w-45 btn-edit" style="font-size: 0.85rem;"
                            data-id="'.$row['id'].'"
                            data-name="'.$row['name'].'"
                            data-price="'.$row['price'].'"
                            data-category="'.$row['category_id'].'"
                            data-img="'.$img_url.'">
                            <i class="fas fa-edit"></i> Sửa
                        </button>
                        <button type="button" class="btn btn-sm btn-danger w-45 btn-delete" style="font-size: 0.85rem;"
                            data-id="'.$row['id'].'">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </div>
                </div>
            </div>
        </div>';
    }
    echo '</div>';
} else {
    echo '<div class="col-12 text-center text-muted p-5"><h4>Không tìm thấy món nào.</h4></div>';
}
?>