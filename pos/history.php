<?php
session_start();
require_once '../includes/db_connection.php';
global $mysqli;

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];

// Xử lý bộ lọc ngày (nếu khách muốn xem theo ngày cụ thể)
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
$where_clause = "WHERE user_id = $user_id";
if ($filter_date) {
    $where_clause .= " AND DATE(order_date) = '$filter_date'";
}

// Truy vấn danh sách đơn hàng
$query = "SELECT * FROM orders $where_clause ORDER BY order_date DESC";
$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử đơn hàng cá nhân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f1ea; }
        .table-container { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fa-solid fa-clock-rotate-left me-2"></i>Lịch sử đơn hàng của bạn</h4>
        <a href="index.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form class="row g-3" method="GET">
                <div class="col-auto">
                    <label class="form-label">Xem theo ngày:</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="<?= $filter_date ?>">
                </div>
                <div class="col-auto d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm me-2">Lọc</button>
                    <a href="history.php" class="btn btn-outline-secondary btn-sm">Tất cả</a>
                </div>
            </form>
        </div>
    </div>

    <div class="table-container">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Mã đơn</th>
                    <th>Ngày giờ</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $row['id'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['order_date'])) ?></td>
                        <td class="fw-bold"><?= number_format($row['total_price']) ?>đ</td>
                        <td>
                            <?php
                            switch ($row['status']) {
                                case 'not_paid':
                                    echo '<span class="badge bg-warning text-dark">Chờ xử lý</span>';
                                    break;
                                case 'paid':
                                    echo '<span class="badge bg-success">Hoàn thành</span>';
                                    break;
                                case 'canceled':
                                    echo '<span class="badge bg-danger">Đã hủy</span>';
                                    break;
                                default:
                                    echo '<span class="badge bg-secondary">Không xác định</span>';
                            }
                            ?>
                        <td>
                            <td class="text-center">
    <button class="btn btn-sm btn-outline-primary btn-view-detail" 
            data-id="<?= $row['id'] ?>">
        <i class="fa-solid fa-eye"></i> Xem món
    </button>
</td>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted">Bạn chưa có đơn hàng nào trong ngày này.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Chi tiết đơn hàng #<span id="modal-order-id"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-striped mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">Món</th>
                            <th class="text-center">SL</th>
                            <th class="text-end pe-3">Giá</th>
                        </tr>
                    </thead>
                    <tbody id="modal-items-body">
                        </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
<script src="js/bootstrap.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const detailModal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
    const modalBody = document.getElementById('modal-items-body');
    const modalOrderId = document.getElementById('modal-order-id');

    // Bắt sự kiện click vào các nút "Xem món"
    document.querySelectorAll('.btn-view-detail').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-id');
            
            // 1. Cập nhật tiêu đề modal
            modalOrderId.textContent = orderId;
            
            // 2. Hiển thị loading trong lúc chờ
            modalBody.innerHTML = '<tr><td colspan="3" class="text-center py-3"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải...</td></tr>';
            detailModal.show();

            // 3. Gọi AJAX lấy dữ liệu
            fetch('get_order_details.php?id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    modalBody.innerHTML = ''; // Xóa loading

                    if (data.length > 0) {
                        let total = 0;
                        data.forEach(item => {
                            // Xử lý ảnh (nếu ko có ảnh thì dùng ảnh placeholder)
                            let imgPath = item.image_url ? item.image_url : 'https://placehold.co/50';
                            
                            // Tính tổng dòng
                            let lineTotal = item.quantity * item.price;
                            total += lineTotal;

                            let row = `
                                <tr>
                                    <td class="ps-3 align-middle">
                                        <div class="d-flex align-items-center">
                                            <img src="${imgPath}" class="rounded me-2" width="40" height="40" style="object-fit:cover;">
                                            <span class="fw-bold text-dark">${item.name}</span>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle fw-bold">${item.quantity}</td>
                                    <td class="text-end pe-3 align-middle">
                                        ${parseInt(item.price).toLocaleString('vi-VN')} đ
                                    </td>
                                </tr>
                            `;
                            modalBody.innerHTML += row;
                        });
                        
                        // Thêm dòng tổng tiền vào cuối (tùy chọn)
                        modalBody.innerHTML += `
                            <tr class="table-primary border-top border-dark">
                                <td colspan="2" class="text-end fw-bold pt-3">TỔNG CỘNG:</td>
                                <td class="text-end fw-bold text-danger pe-3 pt-3 fs-5">${total.toLocaleString('vi-VN')} đ</td>
                            </tr>
                        `;

                    } else {
                        modalBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">Không tìm thấy chi tiết món.</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                    modalBody.innerHTML = '<tr><td colspan="3" class="text-center text-danger py-3">Lỗi tải dữ liệu!</td></tr>';
                });
        });
    });
});
</script>
</body>
</html>