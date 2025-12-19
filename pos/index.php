<?php
    require_once '../includes/db_connection.php';
    session_start();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php?error=no_permission");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS | Màn hình Bán hàng Quán Cà Phê</title>

    <!-- <lin href="./css/bootstrap.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"> -->
    <link rel="stylesheet" href="./css/bootstrap.css">
    <link rel="stylesheet" href="./css/all.min.css">
    <!-- <link rel="stylesheet" href="./pos_style.css"> -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
    <link rel="stylesheet" href="./css/pos_style.css">
    
    <style>
        .pos-container {
            height: 100vh;
            padding: 0;
            margin: 0;
        }
        .menu-panel {
            background-color: #f4f4f4; 
            padding: 15px;
            overflow-y: auto;
            max-height: 100vh;
        }
        .cart-panel {
            background-color: #ffffff; 
            padding: 20px;
            border-left: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            max-height: 100vh;
        }
        .product-item {
            cursor: pointer;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
            transition: transform 0.2s;
        }
        .product-item:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0,0,0,.15);
        }
        .cart-items-list {
            flex-grow: 1; 
            overflow-y: auto;
        }
    </style>
</head>

<body>

<div class="container-fluid pos-container">
    <div class="row h-100">
        
        <div class="col-md-8 menu-panel">
            
             <h3 class="mb-4 fw-bold" style="color: #4B3621;">
                <i class="fas fa-mug-hot me-2" style="color: #4B3621;"></i>Thực Đơn
</h3>
           
            <div class="input-group mb-3">
    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass"></i></span>
    <input type="text" id="search-input" class="form-control border-start-0 ps-0" placeholder="Nhập tên món cần tìm nhanh..." autocomplete="off">
</div>
            
           <div class="d-flex mb-4 overflow-auto" id="filter-container">
    <button class="btn btn-dark btn-sm me-2 filter-btn active" data-filter="all">Tất cả</button>
    
    <button class="btn btn-outline-dark btn-sm me-2 filter-btn" data-filter="1">
        Đồ Uống
    </button>
    
    <button class="btn btn-outline-dark btn-sm me-2 filter-btn" data-filter="2">
        Đồ Ăn
    </button>
</div>

            <div class="row" id="product-list-container">
                
                <?php
                    require_once '../core/products-list.php';
    if ($result_products && $result_products->num_rows > 0) {
        
        while ($row = $result_products->fetch_assoc()) {
            

    $product_id = $row['id'];
    $product_name = htmlspecialchars($row['name']);
    $product_price = number_format($row['price'], 0, ',', '.');
    $raw_price = $row['price'];
    $image_url = $row['image_url'];
    
    $category_id = $row['category_id']; 

    echo '<div class="col-4 col-sm-3 col-lg-2 product-column">'; 
    
        echo '<div class="card product-item text-center p-2" ';
        echo 'data-id="' . $product_id . '" data-price="' . $raw_price . '" data-category-id="' . $category_id . '">';
        
            echo '<img src="' . $image_url . '" class="card-img-top mx-auto" alt="' . $product_name . '" style="width: 80px; height: 80px; object-fit: cover;">';
            
            echo '<div class="card-body p-1">';
                echo '<h6 class="card-title mb-0">' . $product_name . '</h6>';
                echo '<p class="text-danger fw-bold mb-0">' . $product_price . ' đ</p>';
            echo '</div>';
            
        echo '</div>';
    
    echo '</div>';

        }
    } else {
        echo '<div class="col-12"><p class="alert alert-warning">Chưa có sản phẩm nào trong menu.</p></div>';
    }
    
    if (isset($result) && $result instanceof mysqli_result) {
        $result->free();
    }
                ?>
               
                
            
                
            </div>
            
        </div>
        
        <div class="col-md-4 cart-panel">
             
            <h4 class="mb-3 text-success" >Đơn hàng #<span id="order-id">
                <?php 
                    global $mysqli;
                    connect_db();
                    $sql = "SELECT MAX(id) FROM orders;";
                    $query= mysqli_query($mysqli, $sql);
                    $id = mysqli_fetch_array($query);
                    echo $id[0]+1;
                ?>
            </span></h4>
            
            <div class="cart-items-list border-bottom pb-2 mb-3">
                <ul class="list-group list-group-flush" id="cart-list">
                    <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                        <div>
                            <span class="fw-bold"></span> <br>
                            <small class="text-muted"></small>
                        </div>
                        <div class="d-flex align-items-center">
                            <!-- <button class="btn btn-sm btn-outline-secondary me-2">-</button> -->
                            <!-- <span class="fw-bold me-2" data-quantity="2"></span> -->
                            <!-- <button class="btn btn-sm btn-outline-secondary"></button>
                            <button class="btn btn-sm btn-danger ms-3"></button> -->
                        </div>
                    </li>
                </ul>
            </div>
            
            <div class="payment-summary mt-auto">
                <div class="d-flex justify-content-between fw-bold fs-5 mb-2">
                    <span>TỔNG TIỀN:</span>
                    <span id="total-amount" class="text-success"></span>
                </div>
                
               
                <button id="cancel-btn" class="btn btn-success w-100 fs-4 py-3">
                    <i class="fa-solid fa-trash"></i> HUỶ ĐƠN
                </button>
                <button id="checkout-btn" class="btn btn-success w-100 fs-4 py-3">
                    <i class="fa-solid fa-dollar-sign"></i> THANH TOÁN
                </button>
            </div>
        </div>
        
    </div>
</div>

<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script> -->
<script src="./js/bootstrap.bundle.min.js"></script>
<script src="./js/pos_main.js"></script>

</body>
</html>