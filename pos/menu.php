<?php
session_start();
require_once '../includes/db_connection.php';
global $mysqli;

// 1. Bảo vệ trang
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}



// 2. Lấy danh mục
$categories = $mysqli->query("SELECT * FROM categories");

// 3. Lấy sản phẩm (Lọc is_active = 1 để chỉ hiện món đang bán)
$products = $mysqli->query("SELECT * FROM products WHERE is_active = 1 ORDER BY category_id ASC");
$next_order_id = 1; // Mặc định là 1 nếu chưa có đơn nào
$query_max_id = "SELECT MAX(id) as max_id FROM orders";
$result_max_id = $mysqli->query($query_max_id);

if ($result_max_id && $row_max = $result_max_id->fetch_assoc()) {
    $next_order_id = $row_max['max_id'] + 1;
}
?>

<?php
// ... (Các phần session và kết nối DB giữ nguyên) ...

// 1. Lấy Tồn kho Nguyên liệu (Gửi cho JS)
$ingredients_data = [];
$ing_result = $mysqli->query("SELECT id, quantity FROM ingredients");
while ($row = $ing_result->fetch_assoc()) {
    // Lưu dạng: { "1": 5000, "2": 200 } (ID => Số lượng)
    $ingredients_data[$row['id']] = (float)$row['quantity']; 
}

// 2. Lấy Công thức (Recipes) (Gửi cho JS)
$recipes_data = [];
$recipe_result = $mysqli->query("SELECT product_id, ingredient_id, quantity_required FROM recipes");
while ($row = $recipe_result->fetch_assoc()) {
    $pid = $row['product_id'];
    if (!isset($recipes_data[$pid])) {
        $recipes_data[$pid] = [];
    }
    // Lưu dạng: ProductID => [ {ing_id: 1, qty: 20}, ... ]
    $recipes_data[$pid][] = [
        'id' => $row['ingredient_id'],
        'qty' => (float)$row['quantity_required']
    ];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Menu | Coffee Shop</title>
    <script>
    // Truyền dữ liệu từ PHP sang JS
    let SERVER_INGREDIENTS = <?php echo json_encode($ingredients_data); ?>;
    const SERVER_RECIPES = <?php echo json_encode($recipes_data); ?>;
</script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/pos_style.css">

    <style>
        :root { --primary-color: #6f4e37; --bg-color: #f8f9fa; --sidebar-width: 350px; }
        body { background-color: var(--bg-color); height: 100vh; overflow: hidden; font-family: 'Segoe UI', sans-serif; }

        /* Navbar */
        .pos-navbar { height: 60px; background: white; border-bottom: 1px solid #ddd; padding: 0 20px; display: flex; align-items: center; justify-content: space-between; }
        .back-btn { text-decoration: none; color: var(--primary-color); font-weight: bold; transition: 0.3s; }
        .back-btn:hover { color: #4a332a; transform: translateX(-5px); }

        /* Layout chính */
        .pos-container { display: flex; height: calc(100vh - 60px); }
        
        /* Cột TRÁI: Menu */
        .menu-area { flex: 1; padding: 20px; overflow-y: auto; overflow-x: hidden; }
        
        /* Thanh tìm kiếm & Filter */
        .filter-bar { background: white; padding: 15px; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .category-scroll { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 5px; }
        .category-scroll::-webkit-scrollbar { height: 4px; }
        .category-scroll::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
        
        /* Nút filter (Custom lại class filter-btn của bạn) */
        .filter-btn { white-space: nowrap; border-radius: 20px; padding: 8px 20px; border: 1px solid #ddd; background: white; color: #333; transition: 0.2s; }
        .filter-btn:hover { background: #eee; }
        .filter-btn.active { background: var(--primary-color); color: white; border-color: var(--primary-color); }
        
        /* Card sản phẩm */
        .product-card-wrapper { transition: 0.3s; }
        .product-item { 
            border: none; border-radius: 15px; overflow: hidden; background: white; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s; height: 100%;
        }
        .product-item:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); border: 1px solid var(--primary-color); }
        .card-img-top { height: 140px; object-fit: cover; }
        .card-title { font-size: 0.95rem; font-weight: bold; margin-bottom: 5px; color: #333; }
        .price-tag { color: var(--primary-color); font-weight: 800; font-size: 1.1rem; }
        
        /* Xử lý món hết hàng */
        .product-item.disabled { opacity: 0.6; pointer-events: none; filter: grayscale(1); }
        .badge-stock { position: absolute; top: 10px; right: 10px; background: #dc3545; color: white; padding: 3px 8px; border-radius: 5px; font-size: 0.75rem; font-weight: bold; }

        /* Cột PHẢI: Giỏ hàng (Fixed) */
        .cart-sidebar { width: var(--sidebar-width); background: white; border-left: 1px solid #ddd; display: flex; flex-direction: column; height: 100%; box-shadow: -5px 0 15px rgba(0,0,0,0.05); }
        .cart-header { padding: 15px; background: var(--primary-color); color: white; text-align: center; }
        .cart-items-container { flex: 1; overflow-y: auto; padding: 0; }
        .cart-footer { padding: 20px; background: #f8f9fa; border-top: 1px dashed #ccc; }
        
        /* Tùy chỉnh List Group Item trong giỏ hàng (Override Bootstrap) */
        .list-group-item { border: none; border-bottom: 1px solid #eee; padding: 15px; }
        .btn-qty { width: 28px; height: 28px; border-radius: 50%; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: bold; }
    </style>
</head>
<body>

    <header class="pos-navbar">
        <a href="index.php" class="back-btn"><i class="fa-solid fa-arrow-left me-2"></i>Dashboard</a>
        <h5 class="m-0 fw-bold text-uppercase">Bán Hàng</h5>
        <div class="user-info">
            <span class="text-muted small">Thu ngân:</span> 
            <strong><?= htmlspecialchars($_SESSION['fullname']) ?></strong>
            <button class="btn btn-outline-danger btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#modalEndShift">
    <i class="fa-solid fa-right-from-bracket"></i> Thoát ca
</button>
        </div>
        
    </header>

    <div class="pos-container">
        
        <div class="menu-area">
            <div class="filter-bar">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                            <input type="text" id="search-input" class="form-control border-start-0 ps-0" placeholder="Tìm tên món...">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="category-scroll">
                            <button class="filter-btn active" data-filter="all">Tất cả</button>
                            <?php while($cat = $categories->fetch_assoc()): ?>
                                <button class="filter-btn" data-filter="<?= $cat['id'] ?>"><?= $cat['name'] ?></button>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div id="product-list-container" class="row g-3">
                <?php while($row = $products->fetch_assoc()): ?>
                    <?php 
                        // Logic ẩn hiện món hết hàng
                        $is_out_of_stock = ($row['status'] == 0);
                        $class_disabled = $is_out_of_stock ? 'disabled' : '';
                    ?>
                    <div class="col-6 col-md-3 col-lg-2 product-column product-card-wrapper mb-3">
                        <div class="card product-item text-center p-2 h-100 <?= $class_disabled ?>"
     data-id="<?= $row['id'] ?>" 
     data-price="<?= $row['price'] ?>"
     data-category-id="<?= $row['category_id'] ?>">
     
    <div class="stock-remaining" id="stock-<?= $row['id'] ?>">
        Còn: <span class="qty-val">--</span>
    </div>
    <?php if($is_out_of_stock): ?>
        <div class="badge-stock">Tạm hết</div>
    <?php endif; ?>

    <img src="<?= !empty($row['image_url']) ? '../uploads/'.$row['image_url'] : 'https://placehold.co/150x150?text=No+Img' ?>" 
         class="card-img-top rounded-3 mb-2" alt="<?= $row['name'] ?>">
    
    <div class="card-body p-1 d-flex flex-column justify-content-between">
        <h6 class="card-title text-truncate" title="<?= htmlspecialchars($row['name']) ?>">
            <?= $row['name'] ?>
        </h6>
        <div class="price-tag"><?= number_format($row['price']) ?></div>
    </div>
</div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="cart-sidebar">
            <div class="cart-header">
                <h5 class="mb-0"><i class="fa-solid fa-receipt me-2"></i>Hóa Đơn</h5>
            </div>
            
            <ul id="cart-list" class="list-group list-group-flush cart-items-container">
                </ul>

            <div class="cart-footer">
                <div class="input-group mb-3">
    <span class="input-group-text"><i class="fa-solid fa-ticket"></i></span>
    <input type="text" id="voucher-code" class="form-control" placeholder="Mã giảm giá (Nếu có)">
    <button class="btn btn-outline-secondary" type="button" onclick="checkVoucher()">Áp dụng</button>
</div>

<div class="d-flex justify-content-between mb-2">
    <span>Giảm giá:</span>
    <span class="text-danger fw-bold" id="discount-display">0%</span>
</div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted fw-bold">Tổng tiền:</span>
                    <span id="total-amount" class="fs-3 fw-bold text-danger">0 đ</span>
                </div>
                
                <div class="d-grid gap-2">
                    
                    <button id="checkout-btn" class="btn btn-success btn-lg fw-bold">
                        <i class="fa-regular fa-credit-card me-2"></i> THANH TOÁN
                    </button>
                    <button id="cancel-btn" class="btn btn-outline-danger">
                        <i class="fa-solid fa-trash me-2"></i> Hủy đơn
                    </button>
                </div>
                <div class="text-center mt-2 small text-muted">
                    Order ID: <span class="text-warning">#<span id="order-id" class="text-warning"><?= $next_order_id ?></span></span>
                </div>
            </div>
        </div>

    </div>

<div id="invoice-pos" class="d-none">
    <div class="invoice-content">
        <div class="invoice-header text-center mb-2">
            <h4 class="store-name fw-bold text-uppercase mb-1">NGUYỄN VĂN COFFEE</h4>
            <p class="store-info mb-0 small">ĐC: 36 Văn Cao, Hải Phòng</p>
            <p class="store-info mb-1 small">Hotline: 090.123.4567</p>
            <div class="dashed-line my-2"></div>
            <h5 class="invoice-title fw-bold">PHIẾU THANH TOÁN</h5>
        </div>

        <div class="invoice-meta small mb-2">
            <div class="d-flex justify-content-between">
                <span>Số phiếu:</span>
                <span class="fw-bold">#<span id="print-order-id">000</span></span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Thời gian:</span>
                <span id="print-date">--/--/----</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Thu ngân:</span>
                <span id="print-staff"><?= $_SESSION['fullname'] ?></span>
            </div>
        </div>

        <div class="dashed-line my-2"></div>

        <table class="table table-borderless table-sm mb-2 w-100 receipt-table small">
            <thead>
                <tr class="text-uppercase border-bottom border-dark">
                    <th class="text-start" style="width: 40%">Món</th>
                    <th class="text-center" style="width: 15%">SL</th>
                    <th class="text-end" style="width: 20%">Đ.Giá</th>
                    <th class="text-end" style="width: 25%">T.Tiền</th>
                </tr>
            </thead>
            <tbody id="print-items-body">
                </tbody>
        </table>

        <div class="dashed-line my-2"></div>

        <div class="invoice-footer">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-bold fs-6">TỔNG CỘNG:</span>
                <span class="total-amount fw-bold fs-5" id="print-total">0 đ</span>
            </div>
            
            <div class="dashed-line my-3"></div>
            
            <div class="thank-you text-center small fst-italic">
                <p class="mb-1">Cảm ơn quý khách & Hẹn gặp lại!</p>
                <p class="mb-0 fw-bold">Wifi: nguyenvan_coffee / Pass: 12345678</p>
            </div>
        </div>
    </div>
</div>
<div id="sticker-container" class="d-none">
    </div>

<div class="modal fade" id="modalStartShift" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa-solid fa-clock"></i> BẮT ĐẦU CA LÀM VIỆC</h5>
            </div>
            <div class="modal-body">
                <p>Chào <strong><?= $_SESSION['fullname'] ?></strong>! Vui lòng kiểm đếm tiền trong két (tiền lẻ/tiền mặt có sẵn) để bắt đầu.</p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Tiền đầu ca (VNĐ):</label>
                    <input type="number" id="start-cash-input" class="form-control form-control-lg" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100" onclick="startShift()">Xác nhận & Mở bán</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEndShift" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fa-solid fa-door-closed"></i> KẾT THÚC CA & ĐĂNG XUẤT</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    Sau khi kết thúc, hệ thống sẽ tự động đăng xuất.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Tổng tiền mặt thực tế trong két:</label>
                    <input type="number" id="end-cash-input" class="form-control form-control-lg" placeholder="Nhập số tiền đếm được...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Ghi chú (nếu lệch tiền):</label>
                    <textarea id="end-note-input" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" onclick="endShift()">Chốt ca</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPayment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fa-solid fa-cash-register"></i> XÁC NHẬN THANH TOÁN</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <small class="text-muted text-uppercase fw-bold">Tổng thanh toán</small>
                    <div class="display-4 fw-bold text-success" id="pay-total-display">0 đ</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Khách đưa (VNĐ):</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-money-bill-wave"></i></span>
                        <input type="number" id="customer-pay-input" class="form-control form-control-lg fw-bold fs-4 text-primary" placeholder="0">
                    </div>
                    <div class="mt-2 d-flex gap-2 justify-content-center">
                        <button class="btn btn-outline-secondary btn-sm quick-pay" data-value="50000">50k</button>
                        <button class="btn btn-outline-secondary btn-sm quick-pay" data-value="100000">100k</button>
                        <button class="btn btn-outline-secondary btn-sm quick-pay" data-value="200000">200k</button>
                        <button class="btn btn-outline-secondary btn-sm quick-pay" data-value="500000">500k</button>
                        <button class="btn btn-outline-primary btn-sm" id="btn-pay-exact">Đủ tiền</button>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-5">Tiền thối lại:</span>
                    <span class="fw-bold fs-2 text-danger" id="change-due-display">0 đ</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Quay lại</button>
                <button type="button" class="btn btn-success btn-lg px-4" id="btn-confirm-print">
                    <i class="fa-solid fa-print"></i> IN HÓA ĐƠN
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="customAlertModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header text-white" id="alert-header">
                <h5 class="modal-title" id="alert-title">Thông báo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i id="alert-icon" class="fa-solid fa-circle-info fa-3x mb-3 text-primary"></i>
                <p id="alert-message" class="fw-bold mb-0">Nội dung thông báo...</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary w-50" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="customConfirmModal" tabindex="-1" data-bs-backdrop="static" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fa-solid fa-circle-question"></i> XÁC NHẬN</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body fs-6" id="confirm-message">
                Bạn có chắc chắn muốn thực hiện hành động này?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Không</button>
                <button type="button" class="btn btn-warning fw-bold px-4" id="btn-confirm-yes">Đồng ý</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="customPromptModal" tabindex="-1" data-bs-backdrop="static" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fa-solid fa-user-shield"></i> ADMIN INPUT</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="prompt-message" class="mb-2">Nhập giá trị:</p>
                <input type="text" id="prompt-input" class="form-control form-control-lg text-center fw-bold" autofocus>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary px-4" id="btn-prompt-submit">Xác nhận</button>
            </div>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="js/pos_main.js"></script>

</body>
</html>