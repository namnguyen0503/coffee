<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Coffee | Quản trị</title>
  <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link">
      <span class="brand-text font-weight-light">Coffee Admin</span>
    </a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
          
          <li class="nav-header">THỐNG KÊ</li>
          <li class="nav-item">
            <a href="#" class="nav-link" onclick="loadView('report')">
              <i class="nav-icon fas fa-chart-bar"></i> <p>Báo cáo doanh thu</p>
            </a>
          </li>

          <li class="nav-header">QUẢN LÝ CỬA HÀNG</li>
          <li class="nav-item">
            <a href="#" class="nav-link" onclick="loadView('orders')">
              <i class="nav-icon fas fa-receipt"></i> <p>Đơn hàng & Hủy</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link" onclick="loadView('products')">
              <i class="nav-icon fas fa-coffee"></i> <p>Món & Công thức</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link" onclick="loadView('vouchers')">
              <i class="nav-icon fas fa-ticket-alt"></i> <p>Mã giảm giá</p>
            </a>
          </li>

          <li class="nav-header">NHÂN SỰ</li>
          <li class="nav-item">
            <a href="#" class="nav-link" onclick="loadView('users')">
              <i class="nav-icon fas fa-users"></i> <p>Nhân viên</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link" onclick="loadView('shifts')">
              <i class="nav-icon fas fa-calendar-alt"></i> <p>Xếp ca làm việc</p>
            </a>
          </li>

        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <h1 class="m-0" id="page-title">Dashboard</h1>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid" id="main-content">
        <div class="alert alert-info">Chào mừng trở lại trang quản trị!</div>
      </div>
    </section>
  </div>

  <footer class="main-footer"><strong>Coffee POS System</strong></footer>
</div>

<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>

<script>
// --- HÀM ĐIỀU HƯỚNG ---
function loadView(viewName) {
    let content = $('#main-content');
    content.html('<div class="spinner-border"></div> Đang tải...');

    if (viewName === 'report') {
        $('#page-title').text('Báo Cáo Doanh Thu');
        renderReportView();
    } else if (viewName === 'orders') {
        $('#page-title').text('Quản Lý Đơn Hàng');
        renderOrdersView();
    } else if (viewName === 'products') {
        $('#page-title').text('Quản Lý Món & Công Thức');
        renderProductView();
    } else if (viewName === 'users') {
        $('#page-title').text('Quản Lý Nhân Viên');
        // Gọi lại hàm cũ hoặc viết mới tương tự
        $.get('api/get_users.php', function(res){
             // Render bảng user (dùng lại code bài trước)
             content.html('Đã load user (Code bài trước)');
        });
    }
}

// --- 1. GIAO DIỆN BÁO CÁO ---
function renderReportView() {
    let html = `
        <div class="card">
            <div class="card-header">
                <div class="form-inline">
                    <input type="date" id="rp_start" class="form-control mr-2" value="${new Date().toISOString().slice(0, 10)}">
                    <input type="date" id="rp_end" class="form-control mr-2" value="${new Date().toISOString().slice(0, 10)}">
                    <button class="btn btn-primary mr-2" onclick="fetchReport()"><i class="fas fa-filter"></i> Xem</button>
                    <button class="btn btn-success" onclick="exportReport()"><i class="fas fa-file-excel"></i> Xuất Excel</button>
                </div>
            </div>
            <div class="card-body">
                <h3>Tổng doanh thu: <span id="total_rev" class="text-danger font-weight-bold">0 đ</span></h3>
                <canvas id="revenueChart" style="height:300px; width:100%;"></canvas>
            </div>
        </div>
    `;
    $('#main-content').html(html);
    fetchReport();
}

function fetchReport() {
    let s = $('#rp_start').val();
    let e = $('#rp_end').val();
    $.get(`api/report_stats.php?start=${s}&end=${e}`, function(res) {
        if(res.success) {
            $('#total_rev').text(new Intl.NumberFormat('vi-VN').format(res.total_revenue) + ' đ');
            drawChart(res.data);
        }
    });
}

function exportReport() {
    let s = $('#rp_start').val();
    let e = $('#rp_end').val();
    window.location.href = `api/report_stats.php?action=export&start=${s}&end=${e}`;
}

function drawChart(data) {
    let labels = data.map(i => i.date);
    let values = data.map(i => i.revenue);
    
    // Hủy biểu đồ cũ nếu có
    if(window.myChart instanceof Chart) window.myChart.destroy();

    var ctx = document.getElementById('revenueChart').getContext('2d');
    window.myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: values,
                backgroundColor: 'rgba(60,141,188,0.9)'
            }]
        }
    });
}

// --- 2. GIAO DIỆN ĐƠN HÀNG (HỦY ĐƠN) ---
// --- 2. GIAO DIỆN QUẢN LÝ ĐƠN HÀNG ---
function renderOrdersView() {
    $('#page-title').text('Quản Lý Đơn Hàng');
    let content = $('#main-content');
    
    // 1. Tạo khung bảng
    let html = `
    <div class="card shadow">
        <div class="card-header border-0">
            <h3 class="card-title">Danh sách giao dịch</h3>
            <div class="card-tools">
                <button class="btn btn-tool" onclick="renderOrdersView()"><i class="fas fa-sync"></i> Tải lại</button>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-striped table-valign-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Thời gian</th>
                        <th>Nhân viên</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="order_list_body">
                    <tr><td colspan="6" class="text-center">Đang tải dữ liệu...</td></tr>
                </tbody>
            </table>
        </div>
    </div>`;
    
    content.html(html);

    // 2. Gọi API lấy dữ liệu và đổ vào bảng
    $.get('api/get_orders.php', function(res) {
        if (res.success) {
            let rows = '';
            res.data.forEach(order => {
                // Format tiền tệ
                let money = new Intl.NumberFormat('vi-VN').format(order.total_money);
                
                // Xử lý hiển thị trạng thái
                let statusBadge = '';
                let actionBtns = '';

                if (order.status === 'completed') {
                    statusBadge = '<span class="badge badge-success">Hoàn thành</span>';
                    // CHỈ HIỆN NÚT HỦY KHI ĐƠN CÒN HOẠT ĐỘNG
                    actionBtns = `
                        <button onclick="cancelOrder(${order.id})" class="btn btn-sm btn-danger">
                            <i class="fas fa-times-circle"></i> Hủy đơn
                        </button>
                    `;
                } else if (order.status === 'cancelled') {
                    statusBadge = '<span class="badge badge-secondary">Đã hủy</span>';
                    actionBtns = '<span class="text-muted text-sm">Không thể thao tác</span>';
                } else {
                    statusBadge = '<span class="badge badge-warning">Chờ xử lý</span>';
                    actionBtns = `
                        <button onclick="cancelOrder(${order.id})" class="btn btn-sm btn-danger">Hủy</button>
                    `;
                }

                rows += `
                    <tr>
                        <td>#${order.id}</td>
                        <td>${order.order_date}</td>
                        <td>${order.staff_name || 'Không rõ'}</td>
                        <td class="font-weight-bold text-success">${money} đ</td>
                        <td>${statusBadge}</td>
                        <td>${actionBtns}</td>
                    </tr>
                `;
            });
            $('#order_list_body').html(rows);
        } else {
            $('#order_list_body').html('<tr><td colspan="6" class="text-center text-danger">Không tải được dữ liệu</td></tr>');
        }
    });
}

// Hàm xử lý logic Hủy (Giữ nguyên logic gọi API hoàn kho)
function cancelOrder(orderId) {
    if(!confirm('CẢNH BÁO QUAN TRỌNG:\n- Hành động này sẽ hoàn lại nguyên liệu vào kho.\n- Trừ doanh thu của ca làm việc hiện tại.\n\nBạn chắc chắn muốn hủy đơn #' + orderId + ' chứ?')) {
        return;
    }
    
    $.post('api/cancel_order.php', {order_id: orderId}, function(res) {
        if(res.success) {
            alert(res.message); // Thông báo thành công
            renderOrdersView(); // Tải lại bảng để cập nhật trạng thái
        } else {
            alert('Lỗi: ' + res.message);
        }
    }, 'json').fail(function() {
        alert('Lỗi kết nối server!');
    });
}

// --- 3. GIAO DIỆN THÊM MÓN & CÔNG THỨC ---
function renderProductView() {
    // Modal HTML để thêm món + mapping nguyên liệu
    let html = `
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#modalAddProduct">Thêm Món Mới</button>
    <div class="modal fade" id="modalAddProduct">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Thêm Món & Công Thức</h5></div>
                <div class="modal-body">
                    <form id="formProduct">
                        <div class="row">
                            <div class="col-6"><input type="text" name="name" class="form-control" placeholder="Tên món" required></div>
                            <div class="col-6"><input type="number" name="price" class="form-control" placeholder="Giá bán" required></div>
                        </div>
                        <div class="mt-2">
                            <label>Nguyên liệu tiêu hao:</label>
                            <div id="ingredient_rows"></div>
                            <button type="button" class="btn btn-sm btn-info mt-1" onclick="addIngredientRow()">+ Thêm nguyên liệu</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" onclick="saveProduct()">Lưu Món</button>
                </div>
            </div>
        </div>
    </div>
    `;
    $('#main-content').html(html);
}

function addIngredientRow() {
    // Cần API lấy danh sách ingredient để bỏ vào select option
    // Demo HTML
    let row = `
    <div class="d-flex mt-1 ing-row">
        <select class="form-control mr-2 ing-select">
            <option value="1">Cafe Hạt (Demo)</option>
            <option value="2">Sữa Đặc (Demo)</option>
        </select>
        <input type="number" class="form-control mr-2 ing-qty" placeholder="Số lượng (g/ml)">
        <button type="button" class="btn btn-danger btn-sm" onclick="$(this).parent().remove()">X</button>
    </div>`;
    $('#ingredient_rows').append(row);
}

function saveProduct() {
    let ingredients = [];
    $('.ing-row').each(function() {
        let id = $(this).find('.ing-select').val();
        let qty = $(this).find('.ing-qty').val();
        if(id && qty) ingredients.push({id: id, qty: qty});
    });

    let data = {
        name: $('input[name="name"]').val(),
        price: $('input[name="price"]').val(),
        category_id: 1, // Demo
        ingredients: JSON.stringify(ingredients)
    };

    $.post('api/save_product.php', data, function(res) {
        alert(res.message);
        if(res.success) $('#modalAddProduct').modal('hide');
    }, 'json');
}
</script>
</body>
</html>