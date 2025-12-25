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
        
        .row-out { background-color: #f8d7da; }
        .row-low { background-color: #fff3cd; }
        
        /* Style cho Tab trong Modal */
        .nav-tabs .nav-link { color: #555; font-weight: 600; }
        .nav-tabs .nav-link.active { color: var(--wh-primary); border-top: 3px solid var(--wh-primary); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-wh px-3 py-2 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand text-white fw-bold" href="#"><i class="fa-solid fa-boxes-stacked me-2"></i>KHO HÀNG</a>
        <div class="d-flex align-items-center text-white">
            <span class="me-3"><i class="fa-solid fa-user me-1"></i> <?= $_SESSION['fullname'] ?? 'Thủ kho' ?></span>
            <button class="btn btn-sm btn-outline-light" onclick="confirmEndShiftWarehouse()">
                <i class="fa-solid fa-right-from-bracket me-1"></i> Thoát ca
            </button>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <a href="../index.php" class="btn btn-outline-secondary me-3">
                    <i class="fa-solid fa-arrow-left"></i> Trang chủ
                </a>
                <h4 class="fw-bold text-dark mb-0">Tồn kho hiện tại</h4>
            </div>
            
            <div>
                <a href="history.php" class="btn btn-outline-dark me-2">
                    <i class="fa-solid fa-clock-rotate-left me-1"></i> Xem Lịch sử
                </a>
                <button class="btn btn-primary" onclick="openImportModal(null, null, 'new')">
                    <i class="fa-solid fa-plus me-2"></i>Tạo / Nhập hàng mới
                </button>
            </div>
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
                            $status_text = "Ổn định"; $status_class = "badge bg-success"; $row_class = "";

                            if ($qty <= 0) {
                                $status_text = "Đã hết"; $status_class = "badge bg-danger"; $row_class = "row-out";
                            } elseif ($qty <= $min) {
                                $status_text = "Sắp hết"; $status_class = "badge bg-warning text-dark"; $row_class = "row-low";
                            }
                        ?>
                        <tr class="<?= $row_class ?>">
                            <td class="ps-4 fw-bold"><?= $row['name'] ?></td>
                            <td class="text-center fs-5 <?= ($qty <= 0) ? 'text-danger fw-bold' : '' ?>"><?= number_format($qty, 1) ?></td>
                            <td class="text-center"><span class="unit-badge"><?= $row['unit'] ?></span></td>
                            <td class="text-center"><span class="<?= $status_class ?>"><?= $status_text ?></span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary me-1" 
                                        onclick="openImportModal('<?= $row['id'] ?>', '<?= $row['unit'] ?>', 'existing')">
                                    <i class="fa-solid fa-download"></i> Nhập
                                </button>
                                
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="openExportModal('<?= $row['id'] ?>', '<?= $row['name'] ?>', '<?= $row['quantity'] ?>', '<?= $row['unit'] ?>')">
                                    <i class="fa-solid fa-trash-can"></i> Hủy
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
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa-solid fa-warehouse me-2"></i>Quản lý Nhập Kho</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body pb-0">
                <ul class="nav nav-tabs" id="importTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-existing-btn" data-bs-toggle="tab" data-bs-target="#tab-existing" type="button" role="tab">
                            <i class="fa-solid fa-boxes-packing"></i> Nhập hàng có sẵn
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-new-btn" data-bs-toggle="tab" data-bs-target="#tab-new" type="button" role="tab">
                            <i class="fa-solid fa-plus-circle"></i> Tạo nguyên liệu mới
                        </button>
                    </li>
                </ul>

                <div class="tab-content pt-3 pb-3" id="importTabContent">
                    
                    <div class="tab-pane fade show active" id="tab-existing" role="tabpanel">
                        <form id="importForm">
                            <div class="mb-3">
                                <label class="form-label">Chọn nguyên liệu:</label>
                                <select id="modal_ing_select" class="form-select" onchange="updateUnitDisplay()">
                                    <option value="">-- Chọn nguyên liệu --</option>
                                    <?php 
                                    $result->data_seek(0);
                                    while($ing = $result->fetch_assoc()): 
                                    ?>
                                        <option value="<?= $ing['id'] ?>" data-unit="<?= $ing['unit'] ?>">
                                            <?= $ing['name'] ?> (Tồn: <?= $ing['quantity'] ?> <?= $ing['unit'] ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-8">
                                    <label class="form-label">Số lượng nhập thêm:</label>
                                    <input type="number" id="quantity_add" class="form-control fw-bold" min="0.1" step="0.1" placeholder="0">
                                </div>
                                <div class="col-4">
                                     <label class="form-label">Đơn vị:</label>
                                     <input type="text" class="form-control" value="..." disabled id="modal_unit_display">
                                </div>
                            </div>

                            <div class="mb-3 mt-3">
                                <label class="form-label">Tổng chi phí (VNĐ):</label>
                                <input type="number" id="import_cost" class="form-control" min="0" step="1000" placeholder="0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ghi chú:</label>
                                <textarea id="import_note" class="form-control" rows="2" placeholder="VD: Nhập thêm..."></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="button" class="btn btn-primary fw-bold" onclick="submitImportAJAX()">
                                    <i class="fa-solid fa-download"></i> XÁC NHẬN NHẬP
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="tab-new" role="tabpanel">
                        <form id="newIngForm">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tên nguyên liệu mới <span class="text-danger">*</span>:</label>
                                <input type="text" id="new_name" class="form-control border-primary" placeholder="VD: Bột Matcha, Trân châu trắng...">
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold">Đơn vị tính <span class="text-danger">*</span>:</label>
                                    <select id="new_unit" class="form-select">
                                        <option value="ml">ml</option>
                                        <option value="g">gram (g)</option>
                                        <option value="kg">kg</option>
                                        <option value="l">lít (l)</option>
                                        <option value="hop">hộp</option>
                                        <option value="chai">chai</option>
                                        <option value="goi">gói</option>
                                        <option value="qua">quả</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Cảnh báo khi dưới:</label>
                                    <input type="number" id="new_min_qty" class="form-control" value="10" placeholder="Mức tối thiểu">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label">Số lượng ban đầu:</label>
                                    <input type="number" id="new_qty" class="form-control fw-bold" value="0" min="0">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Chi phí ban đầu:</label>
                                    <input type="number" id="new_cost" class="form-control" value="0">
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="button" class="btn btn-success fw-bold" onclick="submitAddNewAJAX()">
                                    <i class="fa-solid fa-plus"></i> TẠO & LƯU KHO
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalImportResult" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center">
            <div class="modal-body p-4">
                <div id="result-icon" class="mb-3"></div>
                <h5 id="result-title" class="fw-bold"></h5>
                <p id="result-message" class="mb-0"></p>
            </div>
            <div class="modal-footer justify-content-center p-2">
                <button type="button" class="btn btn-dark btn-sm px-4" onclick="location.reload()">OK</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEndShiftWh" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fa-solid fa-door-closed"></i> KẾT THÚC CA</h5>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">Bạn đang trong ca. Vui lòng chốt tiền két.</div>
                <input type="number" id="wh-end-cash-input" class="form-control mb-3" placeholder="Tiền mặt thực tế...">
                <textarea id="wh-end-note-input" class="form-control" placeholder="Ghi chú..."></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button class="btn btn-danger" onclick="submitEndShiftWh()">Xác nhận</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Xuất Hủy Kho</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm">
                    <input type="hidden" id="export_ing_id">
                    <input type="text" id="export_ing_name" class="form-control-plaintext fw-bold text-danger mb-2" readonly>
                    <div class="input-group mb-3">
                        <input type="text" id="export_current_qty" class="form-control" disabled>
                        <span class="input-group-text" id="export_unit_display_1">...</span>
                    </div>
                    <label class="text-danger fw-bold">Trừ đi:</label>
                    <div class="input-group mb-3">
                        <input type="number" id="quantity_sub" class="form-control border-danger" required>
                        <span class="input-group-text bg-danger text-white" id="export_unit_display_2">...</span>
                    </div>
                    <select id="export_reason_select" class="form-select mb-2" onchange="updateReason()">
                        <option value="">-- Lý do --</option>
                        <option value="Hỏng/Hết hạn">Hỏng/Hết hạn</option>
                        <option value="Rơi vỡ">Rơi vỡ</option>
                        <option value="Kiểm kê sai">Kiểm kê sai</option>
                        <option value="other">Khác...</option>
                    </select>
                    <textarea id="export_note" class="form-control" placeholder="Chi tiết lý do..."></textarea>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger w-100" onclick="submitExportAJAX()">XÁC NHẬN TRỪ</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // 1. Hàm mở Modal nhập (Có tham số mode để chọn tab)
    function openImportModal(id = null, unit = null, mode = 'existing') {
        const modal = new bootstrap.Modal(document.getElementById('importModal'));
        
        // Reset forms
        document.getElementById('importForm').reset();
        document.getElementById('newIngForm').reset();
        
        // Switch Tab
        if (mode === 'new') {
            const newTabBtn = document.querySelector('#tab-new-btn');
            const newTab = new bootstrap.Tab(newTabBtn);
            newTab.show();
        } else {
            const existTabBtn = document.querySelector('#tab-existing-btn');
            const existTab = new bootstrap.Tab(existTabBtn);
            existTab.show();
            
            // Pre-fill data if existing
            const select = document.getElementById('modal_ing_select');
            const unitInput = document.getElementById('modal_unit_display');
            if (id) {
                select.value = id;
                unitInput.value = unit;
            } else {
                select.value = "";
                unitInput.value = "...";
            }
        }
        
        modal.show();
    }

    function updateUnitDisplay() {
        const select = document.getElementById('modal_ing_select');
        const unitInput = document.getElementById('modal_unit_display');
        const selectedOption = select.options[select.selectedIndex];
        const unit = selectedOption.getAttribute('data-unit');
        unitInput.value = unit ? unit : '...';
    }

    // 2. Gửi AJAX Nhập hàng CŨ
    function submitImportAJAX() {
        const ingId = document.getElementById('modal_ing_select').value;
        const qty = document.getElementById('quantity_add').value;
        const cost = document.getElementById('import_cost').value;
        const note = document.getElementById('import_note').value;

        if (!ingId || qty <= 0) { alert("Vui lòng kiểm tra lại thông tin!"); return; }

        // Đóng modal
        bootstrap.Modal.getInstance(document.getElementById('importModal')).hide();

        fetch('process_import.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ingredient_id: ingId, quantity_add: qty, import_cost: cost, note: note })
        })
        .then(res => res.json())
        .then(data => showResultModal(data.success, data.message))
        .catch(err => showResultModal(false, "Lỗi kết nối!"));
    }

    // 3. Gửi AJAX Tạo hàng MỚI (NEW)
    function submitAddNewAJAX() {
        const name = document.getElementById('new_name').value.trim();
        const unit = document.getElementById('new_unit').value;
        const minQty = document.getElementById('new_min_qty').value;
        const qty = document.getElementById('new_qty').value;
        const cost = document.getElementById('new_cost').value;

        if (!name) { alert("Chưa nhập tên nguyên liệu!"); return; }
        if (!unit) { alert("Chưa chọn đơn vị!"); return; }

        // Đóng modal
        bootstrap.Modal.getInstance(document.getElementById('importModal')).hide();

        fetch('process_add_new.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                name: name, unit: unit, 
                quantity: qty, min_quantity: minQty, cost: cost 
            })
        })
        .then(res => res.json())
        .then(data => showResultModal(data.success, data.message))
        .catch(err => showResultModal(false, "Lỗi kết nối!"));
    }

    // 4. Helper hiển thị kết quả
    function showResultModal(isSuccess, message) {
        const iconDiv = document.getElementById('result-icon');
        const titleDiv = document.getElementById('result-title');
        
        if (isSuccess) {
            iconDiv.innerHTML = '<i class="fa-solid fa-circle-check text-success fa-3x"></i>';
            titleDiv.textContent = "THÀNH CÔNG";
            titleDiv.className = "fw-bold text-success";
        } else {
            iconDiv.innerHTML = '<i class="fa-solid fa-circle-xmark text-danger fa-3x"></i>';
            titleDiv.textContent = "THẤT BẠI";
            titleDiv.className = "fw-bold text-danger";
        }
        document.getElementById('result-message').textContent = message;
        new bootstrap.Modal(document.getElementById('modalImportResult')).show();
    }

    // --- LOGIC XUẤT KHO ---
    function openExportModal(id, name, currentQty, unit) {
        const modal = new bootstrap.Modal(document.getElementById('exportModal'));
        document.getElementById('export_ing_id').value = id;
        document.getElementById('export_ing_name').value = name;
        document.getElementById('export_current_qty').value = currentQty;
        document.getElementById('export_unit_display_1').textContent = unit;
        document.getElementById('export_unit_display_2').textContent = unit;
        document.getElementById('quantity_sub').value = '';
        document.getElementById('quantity_sub').max = currentQty;
        modal.show();
    }

    function updateReason() {
        const select = document.getElementById('export_reason_select');
        const note = document.getElementById('export_note');
        if (select.value && select.value !== 'other') note.value = select.value;
        else if (select.value === 'other') note.value = '', note.focus();
    }

    function submitExportAJAX() {
        const id = document.getElementById('export_ing_id').value;
        const qty = document.getElementById('quantity_sub').value;
        const note = document.getElementById('export_note').value;

        if (qty <= 0 || !note) { alert("Kiểm tra lại số lượng và lý do!"); return; }
        if(!confirm("Chắc chắn xuất kho?")) return;

        bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();

        fetch('process_export.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ingredient_id: id, quantity_sub: qty, reason: note })
        })
        .then(res => res.json())
        .then(data => showResultModal(data.success, data.message))
        .catch(err => showResultModal(false, "Lỗi kết nối!"));
    }

    // --- LOGIC THOÁT CA ---
    function confirmEndShiftWarehouse() {
        fetch('../core/session_manager.php?action=check_status')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.is_open) {
                new bootstrap.Modal(document.getElementById('modalEndShiftWh')).show();
            } else {
                window.location.href = '../logout.php';
            }
        });
    }

    function submitEndShiftWh() {
        const cash = document.getElementById('wh-end-cash-input').value;
        const note = document.getElementById('wh-end-note-input').value;
        if (!cash) { alert("Nhập tiền két!"); return; }
        
        fetch('../core/session_manager.php?action=end_shift', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ end_cash: cash, note: note })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) window.location.href = '../login.php';
            else alert(data.message);
        });
    }
</script>
</body>
</html>