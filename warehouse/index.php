<?php
session_start();
require_once '../includes/db_connection.php';

// 1. Chặn truy cập trái phép
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'wh-staff' && $_SESSION['role'] !== 'admin')) {
    header("Location: ../login.php");
    exit;
}

// 2. Lấy danh sách nguyên liệu
$query = "SELECT * FROM ingredients ORDER BY quantity ASC"; 
$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Kho | Nguyễn Văn Coffee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --wh-primary: #2c3e50; --wh-bg: #ecf0f1; }
        body { background-color: var(--wh-bg); font-family: 'Segoe UI', sans-serif; }
        .navbar-wh { background-color: var(--wh-primary); color: white; }
        .card-stock { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .unit-badge { background: #e0e0e0; color: #333; font-size: 0.8em; padding: 2px 6px; border-radius: 4px; }
        
        /* Trạng thái */
        .status-out { color: #dc3545; font-weight: bold; } /* Đỏ */
        .status-low { color: #ffc107; font-weight: bold; } /* Vàng */
        .status-ok { color: #198754; font-weight: bold; }  /* Xanh lá */
        
        .row-out { background-color: #f8d7da; }
        .row-low { background-color: #fff3cd; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-wh px-3 py-2 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand text-white fw-bold" href="#"><i class="fa-solid fa-boxes-stacked me-2"></i>KHO HÀNG</a>
        <div class="d-flex align-items-center text-white">
            <span class="me-3"><i class="fa-solid fa-user me-1"></i> <?= $_SESSION['fullname'] ?? 'Thủ kho' ?></span>
            <a href="../logout.php" class="btn btn-sm btn-outline-light">Đăng xuất</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <a href="../index.php" class="btn btn-outline-secondary me-3">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
                <h4 class="fw-bold text-dark mb-0">Tồn kho hiện tại</h4>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-plus me-2"></i>Nhập hàng mới
            </button>
        </div>
    </div>

    <div class="card card-stock">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Tên nguyên liệu</th>
                        <th class="text-center">Tồn kho</th>
                        <th class="text-center">Đơn vị</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end pe-4">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <?php 
                            $qty = (float)$row['quantity'];
                            $min = (float)$row['min_quantity'];
                            
                            // Logic phân loại trạng thái
                            $status_text = "Ổn định";
                            $status_class = "badge bg-success"; // Mặc định xanh
                            $row_class = "";

                            if ($qty <= 0) {
                                $status_text = "Đã hết";
                                $status_class = "badge bg-danger"; // Đỏ
                                $row_class = "row-out";
                            } elseif ($qty <= $min) {
                                $status_text = "Sắp hết";
                                $status_class = "badge bg-warning text-dark"; // Vàng (chữ đen cho dễ đọc)
                                $row_class = "row-low";
                            }
                        ?>
                        <tr class="<?= $row_class ?>">
                            <td class="ps-4 fw-bold"><?= $row['name'] ?></td>
                            <td class="text-center fs-5 <?= ($qty <= 0) ? 'text-danger fw-bold' : '' ?>">
                                <?= number_format($qty, 1) ?>
                            </td>
                            <td class="text-center"><span class="unit-badge"><?= $row['unit'] ?></span></td>
                            <td class="text-center">
                                <span class="<?= $status_class ?>">
                                    <?= $status_text ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary btn-quick-import" 
                                        data-id="<?= $row['id'] ?>" 
                                        data-name="<?= $row['name'] ?>"
                                        data-unit="<?= $row['unit'] ?>">
                                    <i class="fa-solid fa-download"></i> Nhập
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="process_import.php" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-box-open me-2"></i>Nhập kho nguyên liệu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Chọn nguyên liệu:</label>
                        <select name="ingredient_id" id="modal_ing_select" class="form-select" required>
                            <option value="">-- Chọn nguyên liệu --</option>
                            <?php 
                            // Reset pointer để loop lại cho select box
                            $result->data_seek(0);
                            while($ing = $result->fetch_assoc()): 
                            ?>

    <option value="<?= $ing['id'] ?>" data-unit="<?= $ing['unit'] ?>">                   
                         <?= $ing['name'] ?> (Hiện có: <?= $ing['quantity'] ?> <?= $ing['unit'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-8">
                            <label class="form-label">Số lượng nhập thêm:</label>
                            <input type="number" name="quantity_add" class="form-control fw-bold" min="0.1" step="0.1" required>
                        </div>
                        <div class="col-4">
                             <label class="form-label">Đơn vị:</label>
                             <input type="text" class="form-control" value="..." disabled id="modal_unit_display">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
    <label class="form-label">Tổng chi phí nhập (VNĐ):</label>
    <div class="input-group">
        <input type="number" name="import_cost" class="form-control fw-bold text-success" 
               min="0" step="1000" placeholder="VD: 500000">
        <span class="input-group-text">đ</span>
    </div>
    <div class="form-text text-muted small">Nhập tổng số tiền trả cho lô hàng này (để tính lãi lỗ).</div>
</div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">Ghi chú (Nguồn gốc/Lô hàng):</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="VD: Nhập từ NCC Vinamilk..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Xác nhận Nhập</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('importModal');
    const modal = new bootstrap.Modal(modalEl);
    const select = document.getElementById('modal_ing_select');
    const unitInput = document.getElementById('modal_unit_display');

    // 1. Xử lý khi bấm nút "Nhập" trên bảng
    document.querySelectorAll('.btn-quick-import').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const unit = this.getAttribute('data-unit'); // Lấy đơn vị từ nút bấm
            
            // Mở modal
            modal.show();
            
            // Điền dữ liệu vào Form
            select.value = id;
            unitInput.value = unit; // Cập nhật ô đơn vị ngay lập tức
        });
    });

    // 2. Xử lý khi thay đổi Select box thủ công (Trong Modal)
    select.addEventListener('change', function() {
        // Tìm option đang được chọn
        const selectedOption = this.options[this.selectedIndex];
        
        // Lấy data-unit từ option đó
        const unit = selectedOption.getAttribute('data-unit');
        
        // Cập nhật vào ô input
        if (unit) {
            unitInput.value = unit;
        } else {
            unitInput.value = '...';
        }
    });
});
</script>
</body>
</html>