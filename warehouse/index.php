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
        
        <button class="btn btn-primary" onclick="openImportModal()">
            <i class="fa-solid fa-plus me-2"></i>Nhập hàng mới
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
            onclick="openImportModal('<?= $row['id'] ?>', '<?= $row['unit'] ?>')">
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
                <h5 class="modal-title"><i class="fa-solid fa-box-open me-2"></i>Nhập kho nguyên liệu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="importForm">
                    <div class="mb-3">
                        <label class="form-label">Chọn nguyên liệu:</label>
                        <select id="modal_ing_select" class="form-select" required onchange="updateUnitDisplay()">
                            <option value="">-- Chọn nguyên liệu --</option>
                            <?php 
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
                            <input type="number" id="quantity_add" class="form-control fw-bold" min="0.1" step="0.1" required placeholder="0">
                        </div>
                        <div class="col-4">
                             <label class="form-label">Đơn vị:</label>
                             <input type="text" class="form-control" value="..." disabled id="modal_unit_display">
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">Tổng chi phí nhập (VNĐ):</label>
                        <div class="input-group">
                            <input type="number" id="import_cost" class="form-control fw-bold text-success" min="0" step="1000" placeholder="VD: 500000">
                            <span class="input-group-text">đ</span>
                        </div>
                        <div class="form-text text-muted small">Bỏ trống nếu không muốn ghi nhận chi phí.</div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">Ghi chú (Nguồn gốc/Lô hàng):</label>
                        <textarea id="import_note" class="form-control" rows="2" placeholder="VD: Nhập từ NCC Vinamilk..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary px-4 fw-bold" onclick="submitImportAJAX()">Xác nhận Nhập</button>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // 1. Hàm mở Modal nhập
    function openImportModal(id = null, unit = null) {
        const modal = new bootstrap.Modal(document.getElementById('importModal'));
        const select = document.getElementById('modal_ing_select');
        const unitInput = document.getElementById('modal_unit_display');
        
        // Reset form
        document.getElementById('importForm').reset();
        
        if (id) {
            select.value = id;
            unitInput.value = unit;
        } else {
            select.value = "";
            unitInput.value = "...";
        }
        
        modal.show();
    }

    // 2. Hàm cập nhật đơn vị khi chọn dropdown
    function updateUnitDisplay() {
        const select = document.getElementById('modal_ing_select');
        const unitInput = document.getElementById('modal_unit_display');
        const selectedOption = select.options[select.selectedIndex];
        const unit = selectedOption.getAttribute('data-unit');
        
        if (unit) unitInput.value = unit;
        else unitInput.value = '...';
    }

    // 3. Hàm Gửi AJAX (Quan trọng nhất)
    function submitImportAJAX() {
        const ingId = document.getElementById('modal_ing_select').value;
        const qty = document.getElementById('quantity_add').value;
        const cost = document.getElementById('import_cost').value;
        const note = document.getElementById('import_note').value;

        // Validate cơ bản
        if (!ingId) { alert("Vui lòng chọn nguyên liệu!"); return; }
        if (!qty || qty <= 0) { alert("Số lượng phải lớn hơn 0!"); return; }

        // Đóng modal nhập
        const importModalEl = document.getElementById('importModal');
        const importModal = bootstrap.Modal.getInstance(importModalEl);
        importModal.hide();

        // Gửi dữ liệu JSON
        const dataToSend = {
            ingredient_id: ingId,
            quantity_add: qty,
            import_cost: cost,
            note: note
        };

        fetch('process_import.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dataToSend)
        })
        .then(res => res.json())
        .then(data => {
            showResultModal(data.success, data.message);
        })
        .catch(err => {
            console.error(err);
            showResultModal(false, "Lỗi kết nối Server! Vui lòng thử lại.");
        });
    }

    // 4. Hàm hiện thông báo kết quả đẹp
    function showResultModal(isSuccess, message) {
        const iconDiv = document.getElementById('result-icon');
        const titleDiv = document.getElementById('result-title');
        const msgDiv = document.getElementById('result-message');

        if (isSuccess) {
            iconDiv.innerHTML = '<i class="fa-solid fa-circle-check text-success fa-3x"></i>';
            titleDiv.textContent = "THÀNH CÔNG";
            titleDiv.className = "fw-bold text-success";
        } else {
            iconDiv.innerHTML = '<i class="fa-solid fa-circle-xmark text-danger fa-3x"></i>';
            titleDiv.textContent = "THẤT BẠI";
            titleDiv.className = "fw-bold text-danger";
        }
        
        msgDiv.textContent = message;

        const modal = new bootstrap.Modal(document.getElementById('modalImportResult'));
        modal.show();
    }
</script>
<div class="modal fade" id="modalEndShiftWh" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fa-solid fa-door-closed"></i> KẾT THÚC CA & ĐĂNG XUẤT</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-dark text-start">
                <div class="alert alert-warning">
                    <i class="fa-solid fa-circle-exclamation"></i> Bạn đang trong ca làm việc. Vui lòng kiểm kê tiền két trước khi đăng xuất.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Tổng tiền mặt thực tế:</label>
                    <input type="number" id="wh-end-cash-input" class="form-control form-control-lg" placeholder="Nhập số tiền...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Ghi chú:</label>
                    <textarea id="wh-end-note-input" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger px-4" onclick="submitEndShiftWh()">Xác nhận Đăng xuất</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fa-solid fa-triangle-exclamation me-2"></i>Xuất Hủy / Điều Chỉnh Kho</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm">
                    <input type="hidden" id="export_ing_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nguyên liệu:</label>
                        <input type="text" id="export_ing_name" class="form-control-plaintext fs-5 fw-bold text-danger" readonly>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                             <label class="form-label">Tồn hiện tại:</label>
                             <div class="input-group">
                                <input type="text" id="export_current_qty" class="form-control bg-light" disabled>
                                <span class="input-group-text" id="export_unit_display_1">...</span>
                             </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-danger">Số lượng TRỪ đi:</label>
                            <div class="input-group">
                                <input type="number" id="quantity_sub" class="form-control fw-bold border-danger text-danger" min="0.1" step="0.1" required>
                                <span class="input-group-text bg-danger text-white" id="export_unit_display_2">...</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lý do xuất kho <span class="text-danger">*</span>:</label>
                        <select id="export_reason_select" class="form-select mb-2" onchange="updateReason()">
                            <option value="">-- Chọn lý do nhanh --</option>
                            <option value="Hàng bị hỏng/Hết hạn">Hàng bị hỏng / Hết hạn</option>
                            <option value="Rơi vỡ/Đổ">Rơi vỡ / Đổ</option>
                            <option value="Kiểm kê sai lệch">Kiểm kê sai lệch (Hao hụt)</option>
                            <option value="Xuất dùng nội bộ/Test món">Xuất dùng nội bộ / Test món</option>
                            <option value="other">Khác...</option>
                        </select>
                        <textarea id="export_note" class="form-control" rows="2" placeholder="Nhập chi tiết lý do... (Bắt buộc)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Thoát</button>
                <button type="button" class="btn btn-danger px-4 fw-bold" onclick="submitExportAJAX()">Xác nhận TRỪ KHO</button>
            </div>
        </div>
    </div>
</div>
<script>
    // 1. Kiểm tra trạng thái ca khi bấm nút Thoát ca
function confirmEndShiftWarehouse() {
    fetch('../core/session_manager.php?action=check_status')
    .then(res => res.json())
    .then(data => {
        if (data.success && data.is_open) {
            // Nếu đang mở ca -> Hiện modal bắt chốt tiền
            const modal = new bootstrap.Modal(document.getElementById('modalEndShiftWh'));
            modal.show();
        } else {
            // Nếu không có ca (hoặc đã chốt) -> Cho logout thẳng
            window.location.href = '../logout.php';
        }
    })
    .catch(err => {
        console.error("Lỗi kết nối:", err);
        window.location.href = '../logout.php'; // Fail-safe: Vẫn cho logout nếu lỗi API
    });
}

// 2. Gửi dữ liệu chốt ca
function submitEndShiftWh() {
    const cashInput = document.getElementById('wh-end-cash-input');
    const noteInput = document.getElementById('wh-end-note-input');

    if (!cashInput.value || cashInput.value === "") {
        alert("Vui lòng nhập số tiền mặt thực tế!");
        return;
    }

    if (!confirm("Xác nhận chốt ca và đăng xuất?")) return;

    fetch('../core/session_manager.php?action=end_shift', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            end_cash: cashInput.value, 
            note: noteInput.value 
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = '../login.php';
        } else {
            alert("Lỗi: " + data.message);
        }
    })
    .catch(err => alert("Lỗi kết nối máy chủ!"));
}

// --- PHẦN XỬ LÝ XUẤT KHO (MỚI) ---

    // 1. Hàm mở Modal Xuất
    function openExportModal(id, name, currentQty, unit) {
        const modal = new bootstrap.Modal(document.getElementById('exportModal'));
        
        // Điền dữ liệu vào form
        document.getElementById('export_ing_id').value = id;
        document.getElementById('export_ing_name').value = name;
        document.getElementById('export_current_qty').value = currentQty;
        document.getElementById('export_unit_display_1').textContent = unit;
        document.getElementById('export_unit_display_2').textContent = unit;
        
        // Reset input
        document.getElementById('quantity_sub').value = '';
        document.getElementById('quantity_sub').max = currentQty; // Không cho nhập quá tồn
        document.getElementById('export_note').value = '';
        document.getElementById('export_reason_select').value = '';

        modal.show();
    }

    // 2. Helper cập nhật lý do từ dropdown vào textarea
    function updateReason() {
        const select = document.getElementById('export_reason_select');
        const note = document.getElementById('export_note');
        if (select.value && select.value !== 'other') {
            note.value = select.value;
        } else if (select.value === 'other') {
            note.value = '';
            note.focus();
        }
    }

    // 3. Gửi AJAX Trừ kho
    function submitExportAJAX() {
        const id = document.getElementById('export_ing_id').value;
        const qty = parseFloat(document.getElementById('quantity_sub').value);
        const currentQty = parseFloat(document.getElementById('export_current_qty').value);
        const note = document.getElementById('export_note').value.trim();

        // Validate
        if (!qty || qty <= 0) { alert("Vui lòng nhập số lượng hợp lệ!"); return; }
        if (qty > currentQty) { alert("Lỗi: Số lượng xuất lớn hơn tồn kho hiện tại!"); return; }
        if (!note) { alert("Vui lòng nhập lý do xuất kho!"); return; }

        if(!confirm("CẢNH BÁO: Hành động này sẽ trừ kho vĩnh viễn.\nBạn chắc chắn muốn tiếp tục?")) return;

        // Đóng modal
        const modalEl = document.getElementById('exportModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();

        // Gửi dữ liệu
        fetch('process_export.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                ingredient_id: id,
                quantity_sub: qty,
                reason: note
            })
        })
        .then(res => res.json())
        .then(data => {
            showResultModal(data.success, data.message); // Tái sử dụng modal kết quả của phần nhập
        })
        .catch(err => {
            console.error(err);
            showResultModal(false, "Lỗi kết nối Server!");
        });
    }
</script>


</body>
</html>