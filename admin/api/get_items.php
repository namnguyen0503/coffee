<?php
require '../../includes/db_connection.php';
$conn = connect_db();

// 1. Nhận tham số
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 1;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// 2. Xây dựng SQL
$sql = "SELECT * FROM products WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND name LIKE '%$search%'";
} else {
    $sql .= " AND category_id = $category_id";
}
$sql .= " ORDER BY id DESC";

// 3. Thực thi
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo '<div class="row">';
    while($row = $result->fetch_assoc()){
        
        // Đường dẫn ảnh
        $dbPath = $row['image_url'];
        if (strpos($dbPath, 'assets/') === 0) {
            $displayImg = '../' . $dbPath; 
        } else {
            $displayImg = $dbPath; 
        }
        $fallbackImg = '../assets/dist/img/logo.png'; 

        // Trạng thái
        $statusLabel = ($row['status'] == 1) 
            ? '<span class="badge badge-success" style="position:absolute; top:10px; right:10px; z-index:10;">Đang bán</span>' 
            : '<span class="badge badge-secondary" style="position:absolute; top:10px; right:10px; z-index:10;">Ngừng bán</span>';
        
        $cardStyle = ($row['status'] == 0) ? 'opacity: 0.75; background: #f4f4f4;' : '';

        echo '<div class="col-6 col-md-4 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm border-0" style="'.$cardStyle.'">
                '.$statusLabel.'
                <div style="height: 160px; overflow: hidden;" class="rounded-top">
                    <img src="'.$displayImg.'" class="card-img-top w-100 h-100" style="object-fit: cover;" onerror="this.src=\''.$fallbackImg.'\'">
                </div>
                <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="font-weight-bold mb-1 text-dark">'.$row['name'].'</h6>
                        <div class="text-danger font-weight-bold mb-2">'.number_format($row['price'], 0, ',', '.').' đ</div>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-2">
                        <button class="btn btn-sm btn-warning btn-edit px-4 shadow-sm font-weight-bold" 
                            data-id="'.$row['id'].'" 
                            data-name="'.$row['name'].'" 
                            data-price="'.$row['price'].'" 
                            data-category="'.$row['category_id'].'" 
                            data-img="'.$displayImg.'"  
                            data-status="'.$row['status'].'">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </button>
                    </div>
                </div>
            </div>
        </div>';
    }
    echo '</div>';
} else {
    echo '<div class="text-center mt-5 text-muted">Không tìm thấy món nào.</div>';
}
?>