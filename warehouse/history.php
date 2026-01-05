<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'wh-staff' && $_SESSION['role'] !== 'admin')) {
    header("Location: ../login.php");
    exit;
}

// Lấy danh sách Nguyên liệu & User để đổ vào Select box lọc
$ingredients = $mysqli->query("SELECT id, name FROM ingredients ORDER BY name ASC");
$users = $mysqli->query("SELECT id, fullname FROM users ORDER BY fullname ASC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử Kho | Nguyễn Văn Coffee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --wh-primary: #2c3e50; --wh-bg: #ecf0f1; }
        body { background-color: var(--wh-bg); font-family: 'Segoe UI', sans-serif; }
        .navbar-wh { background-color: var(--wh-primary); color: white; }
        .card-stock { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        
        .badge-import { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; } 
        .badge-export { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; } 
        .time-text { font-size: 0.85em; color: #6c757d; }
        .cost-col { font-weight: bold; color: #2c3e50; }
        
        /* Loading Spinner */
        #loading-spinner { display: none; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-wh px-3 py-2 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand text-white fw-bold" href="index.php"><i class="fa-solid fa-boxes-stacked me-2"></i>KHO HÀNG</a>
        <div class="d-flex align-items-center text-white">
            <span class="me-3"><i class="fa-solid fa-user me-1"></i> <?= $_SESSION['fullname'] ?? 'Thủ kho' ?></span>
            <a href="../logout.php" class="btn btn-sm btn-outline-light">Đăng xuất</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="row mb-3 g-2 align-items-end">
        <div class="col-md-12 mb-2 d-flex justify-content-between">
            <div class="d-flex align-items-center">
                <a href="index.php" class="btn btn-outline-secondary me-3"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>
                <h4 class="fw-bold text-dark mb-0">Lịch sử Nhập / Xuất</h4>
            </div>
        </div>

        <div class="col-md-2">
            <label class="form-label small fw-bold">Từ ngày:</label>
            <input type="date" id="filter-date-start" class="form-control form-control-sm filter-input">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold">Đến ngày:</label>
            <input type="date" id="filter-date-end" class="form-control form-control-sm filter-input">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold">Nguyên liệu:</label>
            <select id="filter-ing" class="form-select form-select-sm filter-input">
                <option value="">-- Tất cả --</option>
                <?php while($row = $ingredients->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-1">
            <label class="form-label small fw-bold">Loại:</label>
            <select id="filter-type" class="form-select form-select-sm filter-input">
                <option value="">-- Tất cả --</option>
                <option value="import">Nhập kho (+)</option>
                <option value="export">Xuất kho (-)</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold">Người thực hiện:</label>
            <select id="filter-user" class="form-select form-select-sm filter-input">
                <option value="">-- Tất cả --</option>
                <?php while($row = $users->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= $row['fullname'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-1">
             <button class="btn btn-dark btn-sm w-100 mt-4" onclick="resetFilters()">
                <i class="fa-solid fa-rotate"></i> Reset
             </button>
             
        </div>
        <div class="col-md-1">
            <button id="btn-export-excel" class="btn btn-success btn-sm w-100 mt-4">
            <i class="fa-solid fa-file-excel"></i> Xuất Excel
</button>

        </div>
        
    </div>

    <div class="card card-stock">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Thời gian</th>
                            <th class="text-center">Loại</th>
                            <th>Nguyên liệu</th>
                            <th class="text-end">Số lượng</th>
                            <th class="text-end">Chi phí</th>
                            <th class="text-center">Người thực hiện</th>
                            <th>Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody id="history-table-body">
                        </tbody>
                </table>
            </div>
            
            <div id="loading-spinner" class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted small">Đang tải dữ liệu...</p>
            </div>
            <div id="no-data-message" class="text-center py-5 d-none">
                <i class="fa-solid fa-file-circle-xmark fa-2x text-muted mb-2"></i>
                <p class="text-muted">Không tìm thấy dữ liệu phù hợp.</p>
            </div>

            <div class="text-center p-3 border-top" id="load-more-container" style="display: none;">
                <button class="btn btn-outline-primary px-4" onclick="loadHistory(false)">
                    Xem thêm cũ hơn <i class="fa-solid fa-angle-down"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let currentPage = 1;
    let isLoading = false;

    // 1. Hàm load dữ liệu (AJAX)
    // reset = true: Xóa bảng cũ load lại từ đầu (Khi lọc)
    // reset = false: Load trang tiếp theo (Lazy load)
    function loadHistory(reset = false) {
        if (isLoading) return;
        isLoading = true;

        if (reset) {
            currentPage = 1;
            document.getElementById('history-table-body').innerHTML = '';
            document.getElementById('load-more-container').style.display = 'none';
            document.getElementById('no-data-message').classList.add('d-none');
        }

        document.getElementById('loading-spinner').style.display = 'block';

        // Lấy giá trị bộ lọc
        const params = new URLSearchParams({
            page: currentPage,
            date_start: document.getElementById('filter-date-start').value,
            date_end: document.getElementById('filter-date-end').value,
            ingredient_id: document.getElementById('filter-ing').value,
            type: document.getElementById('filter-type').value,
            user_id: document.getElementById('filter-user').value
        });

        fetch(`get_history_api.php?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('loading-spinner').style.display = 'none';
                
                if (data.success) {
                    renderRows(data.data);
                    
                    // Xử lý nút Load More
                    if (data.has_more) {
                        document.getElementById('load-more-container').style.display = 'block';
                        currentPage++;
                    } else {
                        document.getElementById('load-more-container').style.display = 'none';
                    }

                    // Xử lý khi không có dữ liệu
                    if (data.data.length === 0 && currentPage === 1) {
                        document.getElementById('no-data-message').classList.remove('d-none');
                    }
                } else {
                    alert("Lỗi: " + data.message);
                }
                isLoading = false;
            })
            .catch(err => {
                console.error(err);
                document.getElementById('loading-spinner').style.display = 'none';
                isLoading = false;
            });
    }

    // 2. Hàm vẽ dòng HTML
    function renderRows(items) {
        const tbody = document.getElementById('history-table-body');
        
        items.forEach(row => {
            const isImport = (row.type === 'import');
            const badgeClass = isImport ? 'badge-import' : 'badge-export';
            const icon = isImport ? '<i class="fa-solid fa-arrow-down"></i>' : '<i class="fa-solid fa-arrow-up"></i>';
            const typeText = isImport ? 'NHẬP KHO' : 'XUẤT KHO';
            const sign = isImport ? '+' : '-';
            const qtyColor = isImport ? 'text-success' : 'text-danger';
            
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="ps-4">
                    <div class="fw-bold">${row.formatted_time}</div>
                    <div class="time-text">${row.formatted_date}</div>
                </td>
                <td class="text-center">
                    <span class="badge ${badgeClass} px-3 py-2">
                        ${icon} ${typeText}
                    </span>
                </td>
                <td class="fw-bold text-primary">
                    ${row.ingredient_name}
                </td>
                <td class="text-end fw-bold fs-5 ${qtyColor}">
                    ${sign}${row.qty_display} 
                    <small class="text-muted fw-normal fs-6">${row.unit}</small>
                </td>
                <td class="text-end cost-col">
                    ${row.cost_display}
                </td>
                <td class="text-center">
                    <small><i class="fa-regular fa-user-circle text-muted"></i> ${row.user_name || 'N/A'}</small>
                </td>
                <td class="text-muted fst-italic">
                    ${row.note || ''}
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // 3. Sự kiện thay đổi bộ lọc -> Load lại từ đầu
    const filters = document.querySelectorAll('.filter-input');
    filters.forEach(input => {
        input.addEventListener('change', () => loadHistory(true));
    });

    // Hàm Reset
    function resetFilters() {
        filters.forEach(input => input.value = '');
        loadHistory(true);
    }

    // Load lần đầu khi vào trang
    document.addEventListener('DOMContentLoaded', () => loadHistory(true));

</script>
<script>
document.getElementById('btn-export-excel')?.addEventListener('click', () => {
    const params = new URLSearchParams();

    const dateStart = document.getElementById('filter-date-start')?.value || '';
    const dateEnd   = document.getElementById('filter-date-end')?.value || '';
    const ing       = document.getElementById('filter-ing')?.value || '';
    const type      = document.getElementById('filter-type')?.value || '';
    const user      = document.getElementById('filter-user')?.value || '';

    if (dateStart) params.append('date_start', dateStart);
    if (dateEnd)   params.append('date_end', dateEnd);
    if (ing)       params.append('ingredient_id', ing);
    if (type)      params.append('type', type);
    if (user)      params.append('user_id', user);

    // Gọi endpoint export (cùng folder warehouse)
    const url = 'export_history_excel.php' + (params.toString() ? ('?' + params.toString()) : '');
    window.location.href = url;
});
</script>

</body>
</html>