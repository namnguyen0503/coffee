<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quản lý Cửa Hàng Cafe</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="tinh-nang/display-item.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
      .modal-backdrop { z-index: 1040 !important; }
      .modal { z-index: 1050 !important; }
      .mode-section { display: none; }
      /* CSS cho bảng lịch */
      .table-schedule th { text-align: center; vertical-align: middle; background-color: #343a40; color: white; }
      .table-schedule td { height: 100px; vertical-align: top; position: relative; }
      .shift-item { background: #e8f5e9; border: 1px solid #4caf50; padding: 2px 5px; margin-bottom: 2px; border-radius: 3px; font-size: 0.85rem; display: flex; justify-content: space-between; align-items: center; }
      .shift-item .btn-del-shift { color: #dc3545; cursor: pointer; margin-left: 5px; }
      .btn-add-shift { position: absolute; bottom: 5px; right: 5px; font-size: 0.7rem; }
  </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li></ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
          <form class="form-inline" id="searchForm" onsubmit="return false;">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" id="searchInput" type="search" placeholder="Tìm kiếm...">
              <div class="input-group-append"><button class="btn btn-navbar" type="button" onclick="taiNoiDung()"><i class="fas fa-search"></i></button></div>
            </div>
          </form>
      </li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image"><img src="./assets/dist/img/logo coffee.png" class="img-fluid elevation-2" style="width: 50px;"></div>
        <div class="info"><a href="#" class="d-block">Nguyễn Văn Coffee</a></div>
      </div>
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item menu-open">
            <a href="#" class="nav-link active"><i class="nav-icon fas fa-tachometer-alt"></i><p>Quản lý chung</p></a>
            <ul class="nav nav-treeview">
              <li class="nav-item"><a href="javascript:void(0)" class="nav-link active" id="link-menu" onclick="chuyenCheDo('menu')"><i class="fas fa-coffee nav-icon"></i><p>Danh sách món</p></a></li>
              <li class="nav-item"><a href="javascript:void(0)" class="nav-link" id="link-order" onclick="chuyenCheDo('order')"><i class="fas fa-file-invoice-dollar nav-icon"></i><p>Quản lý Đơn hàng</p></a></li>
              <li class="nav-item"><a href="javascript:void(0)" class="nav-link" id="link-voucher" onclick="chuyenCheDo('voucher')"><i class="fas fa-ticket-alt nav-icon"></i><p>Mã giảm giá</p></a></li>
              
              <li class="nav-item"><a href="javascript:void(0)" class="nav-link" id="link-schedule" onclick="chuyenCheDo('schedule')"><i class="fas fa-calendar-alt nav-icon"></i><p>Quản lý Phân ca</p></a></li>

              <li class="nav-item"><a href="javascript:void(0)" class="nav-link" id="link-report" onclick="chuyenCheDo('report')"><i class="fas fa-chart-bar nav-icon"></i><p>Báo cáo doanh thu</p></a></li>
              <li class="nav-item"><a href="javascript:void(0)" class="nav-link" id="link-user" onclick="chuyenCheDo('user')"><i class="fas fa-users-cog nav-icon"></i><p>Quản lý nhân viên</p></a></li>
               <li class="nav-item"><a href="../pos/index.php" class="nav-link"><i class="fas fa-cash-register nav-icon"></i><p>Về trang bán hàng</p></a></li>
            </ul>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6 d-flex align-items-center">
              <h1 class="m-0 mr-3" id="page-title">Danh sách món</h1>
              
              <span id="btn-group-menu" class="mode-section"><button class="btn btn-success btn-sm shadow-sm" data-toggle="modal" data-target="#modalAddItem"><i class="fas fa-plus-circle"></i> Thêm món mới</button></span>
              <span id="btn-group-user" class="mode-section"><button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#modalAddUser"><i class="fas fa-user-plus"></i> Thêm nhân viên</button></span>
              <span id="btn-group-voucher" class="mode-section"><button class="btn btn-info btn-sm shadow-sm" data-toggle="modal" data-target="#modalAddVoucher"><i class="fas fa-ticket-alt"></i> Tạo mã</button></span>
              
              <span id="btn-group-schedule" class="mode-section">
                  <button class="btn btn-outline-secondary btn-sm" onclick="doiTuan(-1)"><i class="fas fa-chevron-left"></i> Tuần trước</button>
                  <span id="schedule-range" class="mx-2 font-weight-bold">...</span>
                  <button class="btn btn-outline-secondary btn-sm" onclick="doiTuan(1)">Tuần sau <i class="fas fa-chevron-right"></i></button>
              </span>
          </div>

          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right mode-section" id="breadcrumb-menu">
              <li class="breadcrumb-item"><a href="javascript:void(0)" onclick="chuyenDanhMuc(2, 'Đồ ăn')"><i class="fas fa-hamburger"></i> Đồ ăn</a></li>
              <li class="breadcrumb-item active"><a href="javascript:void(0)" onclick="chuyenDanhMuc(1, 'Đồ uống')"><i class="fas fa-coffee"></i> Đồ uống</a></li>
            </ol>
            
            <div class="mode-section" id="report-controls">
                <form class="form-inline float-sm-right" onsubmit="locBaoCao(event)">
                    <input type="date" id="report_start" class="form-control form-control-sm mr-2" required>
                    <input type="date" id="report_end" class="form-control form-control-sm mr-2" required>
                    <button type="submit" class="btn btn-sm btn-primary mr-2">Xem</button>
                    <button type="button" class="btn btn-sm btn-success" onclick="xuatExcel()">Excel</button>
                </form>
            </div>
          </div>
        </div>
        
        <div id="hienthi-sanpham"></div>

        <div id="hienthi-baocao" class="mode-section">
            <div class="row"><div class="col-12 col-sm-6 col-md-3"><div class="info-box"><span class="info-box-icon bg-info elevation-1"><i class="fas fa-coins"></i></span><div class="info-box-content"><span class="info-box-text">Doanh thu</span><span class="info-box-number" id="rpt-revenue">0 đ</span></div></div></div><div class="col-12 col-sm-6 col-md-3"><div class="info-box mb-3"><span class="info-box-icon bg-danger elevation-1"><i class="fas fa-shopping-cart"></i></span><div class="info-box-content"><span class="info-box-text">Tổng đơn</span><span class="info-box-number" id="rpt-orders">0</span></div></div></div><div class="col-12 col-sm-6 col-md-3"><div class="info-box mb-3"><span class="info-box-icon bg-success elevation-1"><i class="fas fa-wallet"></i></span><div class="info-box-content"><span class="info-box-text">Tiền mặt</span><span class="info-box-number" id="rpt-cash">0 đ</span></div></div></div><div class="col-12 col-sm-6 col-md-3"><div class="info-box mb-3"><span class="info-box-icon bg-warning elevation-1"><i class="fas fa-tag"></i></span><div class="info-box-content"><span class="info-box-text">Giảm giá</span><span class="info-box-number" id="rpt-discount">0 đ</span></div></div></div></div>
            <div class="row"><div class="col-md-12"><div class="card"><div class="card-body"><canvas id="revenue-chart" height="250"></canvas></div></div></div></div>
            <div class="row"><div class="col-12"><div class="card"><div class="card-body table-responsive p-0" style="height: 300px;"><table class="table table-head-fixed text-nowrap"><thead><tr><th>Mã đơn</th><th>Thời gian</th><th>Nhân viên</th><th class="text-right">Thành tiền</th></tr></thead><tbody id="rpt-table-body"></tbody></table></div></div></div></div>
        </div>

        <div id="hienthi-schedule" class="mode-section">
            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-bordered table-schedule">
                        <thead>
                            <tr>
                                <th style="width: 10%">Ca</th>
                                <th style="width: 12%">Thứ 2<br><small id="d-0"></small></th>
                                <th style="width: 12%">Thứ 3<br><small id="d-1"></small></th>
                                <th style="width: 12%">Thứ 4<br><small id="d-2"></small></th>
                                <th style="width: 12%">Thứ 5<br><small id="d-3"></small></th>
                                <th style="width: 12%">Thứ 6<br><small id="d-4"></small></th>
                                <th style="width: 12%">Thứ 7<br><small id="d-5"></small></th>
                                <th style="width: 12%">CN<br><small id="d-6"></small></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th><strong>SÁNG</strong><br>(7h - 12h)</th>
                                <td id="cell-morning-0"></td><td id="cell-morning-1"></td><td id="cell-morning-2"></td>
                                <td id="cell-morning-3"></td><td id="cell-morning-4"></td><td id="cell-morning-5"></td><td id="cell-morning-6"></td>
                            </tr>
                            <tr>
                                <th><strong>CHIỀU</strong><br>(12h - 17h)</th>
                                <td id="cell-afternoon-0"></td><td id="cell-afternoon-1"></td><td id="cell-afternoon-2"></td>
                                <td id="cell-afternoon-3"></td><td id="cell-afternoon-4"></td><td id="cell-afternoon-5"></td><td id="cell-afternoon-6"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

      </div>
    </div>
  </div>

  <footer class="main-footer"><strong>Coffee Nguyễn Văn</strong></footer>
</div>

<div class="modal fade" id="modalAddSchedule">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header bg-primary"><h5 class="modal-title text-white">Xếp nhân viên</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <form id="formSchedule" onsubmit="luuPhanCa(event)">
          <div class="modal-body">
             <input type="hidden" name="shift_date" id="sch_date">
             <input type="hidden" name="shift_type" id="sch_type">
             <p>Ngày: <b id="lbl_date"></b> - Ca: <b id="lbl_type"></b></p>
             <div class="form-group">
                 <label>Chọn nhân viên:</label>
                 <select class="form-control" name="user_id" id="sch_users"></select>
             </div>
          </div>
          <div class="modal-footer"><button type="submit" class="btn btn-primary btn-block">Thêm vào ca</button></div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAddVoucher"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-info"><h5 class="modal-title text-white">Tạo Mã Giảm Giá</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><form id="formAddVoucher" onsubmit="themVoucher(event)"><div class="modal-body"><div class="form-group"><label>Mã Voucher</label><input type="text" class="form-control" name="code" style="text-transform: uppercase;" required></div><div class="form-group"><label>Giảm (%)</label><input type="number" class="form-control" name="percent" min="1" max="100" required></div><div class="form-group"><label>Mô tả</label><input type="text" class="form-control" name="description"></div></div><div class="modal-footer"><button type="submit" class="btn btn-info">Tạo mã</button></div></form></div></div></div>
<div class="modal fade" id="modalOrderDetail"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-info"><h5 class="modal-title text-white">Chi tiết đơn hàng</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body" id="order-detail-content"></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button></div></div></div></div>
<div class="modal fade" id="modalDelete"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-danger"><h5 class="modal-title text-white">Xác nhận xóa</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><p>Xác nhận xóa?</p><input type="hidden" id="idDelete"></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button><button type="button" class="btn btn-danger" onclick="xacNhanXoa()">Xóa</button></div></div></div></div>
<div class="modal fade" id="modalAddItem"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header bg-success"><h5 class="modal-title text-white">Thêm món mới</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><form id="formAdd" onsubmit="themMon(event)"><div class="modal-body"><div class="row"><div class="col-md-6"><div class="form-group"><label>Tên món</label><input type="text" class="form-control" name="name" required></div><div class="form-group"><label>Giá tiền</label><input type="number" class="form-control" name="price" required></div><div class="form-group"><label>Danh mục</label><select class="form-control" name="category"><option value="1">Đồ uống</option><option value="2">Đồ ăn</option></select></div><div class="form-group"><label>Ảnh</label><input type="file" class="form-control-file" name="image"></div></div><div class="col-md-6 border-left"><label>Công thức</label><div id="recipe-container-add" style="max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 10px;"></div><button type="button" class="btn btn-sm btn-info mt-2" onclick="addIngredientRow('recipe-container-add')"><i class="fas fa-plus"></i> Thêm NL</button></div></div></div><div class="modal-footer"><button type="submit" class="btn btn-success">Lưu</button></div></form></div></div></div>
<div class="modal fade" id="modalEdit"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header bg-warning"><h5 class="modal-title">Sửa món</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><form id="formEdit" onsubmit="luuSua(event)"><div class="modal-body"><input type="hidden" name="edit_id" id="edit_id"><div class="row"><div class="col-md-6"><div class="form-group"><label>Tên món</label><input type="text" class="form-control" name="edit_name" id="edit_name" required></div><div class="form-group"><label>Giá tiền</label><input type="number" class="form-control" name="edit_price" id="edit_price" required></div><div class="form-group"><label>Danh mục</label><select class="form-control" name="edit_category" id="edit_category"><option value="1">Đồ uống</option><option value="2">Đồ ăn</option></select></div><div class="form-group"><label>Ảnh</label><br><img id="preview_img" src="" style="width: 80px; height: 80px; object-fit: cover;"><input type="file" class="form-control-file" name="edit_image"></div></div><div class="col-md-6 border-left"><label>Công thức</label><div id="recipe-container-edit" style="max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 10px;"></div><button type="button" class="btn btn-sm btn-info mt-2" onclick="addIngredientRow('recipe-container-edit')"><i class="fas fa-plus"></i> Thêm NL</button></div></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Lưu</button></div></form></div></div></div>
<div class="modal fade" id="modalAddUser"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-primary"><h5 class="modal-title text-white">Thêm nhân viên</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><form id="formAddUser" onsubmit="xuLyThemUser(event)"><div class="modal-body"><div class="form-group"><label>Họ và tên</label><input type="text" class="form-control" name="fullname" required></div><div class="form-group"><label>Username</label><input type="text" class="form-control" name="username" required></div><div class="form-group"><label>Password</label><input type="password" class="form-control" name="password" required></div><div class="form-group"><label>Chức vụ</label><select class="form-control" name="role"><option value="staff">Nhân viên</option><option value="wh-staff">Thủ kho</option><option value="admin">Admin</option></select></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Lưu</button></div></form></div></div></div>
<div class="modal fade" id="modalEditUser"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-warning"><h5 class="modal-title">Sửa user</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><form id="formEditUser" onsubmit="xuLySuaUser(event)"><div class="modal-body"><input type="hidden" name="user_id" id="edit_user_id"><div class="form-group"><label>Họ tên</label><input type="text" class="form-control" name="fullname" id="edit_user_fullname" required></div><div class="form-group"><label>Username</label><input type="text" class="form-control" id="edit_user_username" disabled></div><div class="form-group"><label>Pass mới</label><input type="password" class="form-control" name="password"></div><div class="form-group"><label>Chức vụ</label><select class="form-control" name="role" id="edit_user_role"><option value="staff">Nhân viên</option><option value="wh-staff">Thủ kho</option><option value="admin">Admin</option></select></div><div class="form-group"><label>Trạng thái</label><select class="form-control" name="status" id="edit_user_status"><option value="1">Đang làm</option><option value="0">Khóa</option></select></div></div><div class="modal-footer"><button type="submit" class="btn btn-warning">Lưu</button></div></form></div></div></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
    var currentMode = 'menu';
    var currentCategory = 1;
    var ingredientsList = [];
    var usersList = []; // Danh sách user để chọn khi xếp lịch
    var currentWeekStart = ''; // Ngày bắt đầu tuần đang xem
    var myChart = null;

    $(document).ready(function(){
        // Auto update status khi load trang
        autoUpdateStatus();

        var today = new Date();
        // Tìm ngày thứ 2 của tuần này
        var day = today.getDay() || 7; 
        if(day !== 1) today.setHours(-24 * (day - 1)); 
        currentWeekStart = today.toISOString().split('T')[0];

        // Mặc định ngày báo cáo
        var rptEnd = new Date().toISOString().split('T')[0];
        $('#report_end').val(rptEnd);
        $('#report_start').val(currentWeekStart);

        taiNoiDung();
        loadIngredients();
        loadUsersForSchedule(); // Tải sẵn danh sách user
        $('#searchInput').on('keyup', function(){ taiNoiDung(); });
    });

    // --- TỰ ĐỘNG CẬP NHẬT TRẠNG THÁI USER ---
    function autoUpdateStatus() {
        $.ajax({
            url: 'api/auto_update_status.php',
            type: 'GET',
            success: function(res) {
                console.log("Auto Update Status: " + res.message);
            }
        });
    }

    // --- CHUYỂN CHẾ ĐỘ ---
    window.chuyenCheDo = function(mode) {
        currentMode = mode;
        $('#searchInput').val('');
        $('.nav-link').removeClass('active');
        $('.mode-section').hide();
        $('#hienthi-sanpham, #hienthi-baocao, #hienthi-schedule').hide();
        $('#searchForm').show();

        if(mode == 'menu') {
            $('#link-menu').addClass('active'); $('#page-title').text('Danh sách món'); $('#btn-group-menu, #breadcrumb-menu').show(); $('#hienthi-sanpham').show(); taiNoiDung();
        } else if(mode == 'user') {
            $('#link-user').addClass('active'); $('#page-title').text('Quản lý nhân viên'); $('#btn-group-user').show(); $('#hienthi-sanpham').show(); taiNoiDung();
        } else if(mode == 'order') {
            $('#link-order').addClass('active'); $('#page-title').text('Quản lý Đơn hàng'); $('#hienthi-sanpham').show(); taiNoiDung();
        } else if(mode == 'voucher') {
            $('#link-voucher').addClass('active'); $('#page-title').text('Mã giảm giá'); $('#btn-group-voucher').show(); $('#hienthi-sanpham').show(); taiNoiDung();
        } else if(mode == 'report') {
            $('#link-report').addClass('active'); $('#page-title').text('Báo cáo doanh thu'); $('#searchForm').hide(); $('#report-controls').show(); $('#hienthi-baocao').show(); taiBaoCao();
        } else if(mode == 'schedule') {
            $('#link-schedule').addClass('active'); $('#page-title').text('Lịch làm việc'); $('#searchForm').hide(); $('#btn-group-schedule').show(); $('#hienthi-schedule').show();
            taiLichLamViec();
        }
    };

    // --- LOGIC PHÂN CA (SCHEDULE) ---
    function taiLichLamViec() {
        $.ajax({
            url: 'api/get_schedules.php',
            type: 'GET',
            data: {start_date: currentWeekStart},
            dataType: 'json',
            success: function(res) {
                renderScheduleTable(res.data, res.start_date);
            }
        });
    }

    function renderScheduleTable(data, startDate) {
        // Xóa dữ liệu cũ
        $('.table-schedule td').html('');
        $('.btn-add-shift').remove();

        var start = new Date(startDate);
        var endDate = new Date(start); endDate.setDate(start.getDate() + 6);
        $('#schedule-range').text(formatDate(start) + ' - ' + formatDate(endDate));

        // Render header ngày
        for(var i=0; i<7; i++) {
            var d = new Date(start);
            d.setDate(start.getDate() + i);
            $('#d-'+i).text(formatDate(d));
            
            // Nút thêm ca
            var dateStr = d.toISOString().split('T')[0];
            $('#cell-morning-'+i).append(`<button class="btn btn-outline-primary btn-xs btn-add-shift" onclick="openAddShift('${dateStr}', 'morning')">+ Thêm</button>`);
            $('#cell-afternoon-'+i).append(`<button class="btn btn-outline-primary btn-xs btn-add-shift" onclick="openAddShift('${dateStr}', 'afternoon')">+ Thêm</button>`);
        }

        // Render dữ liệu nhân viên
        data.forEach(function(item){
            // Tính xem item thuộc cột thứ mấy (0-6)
            var itemDate = new Date(item.shift_date);
            var diffTime = Math.abs(itemDate - start);
            var dayIndex = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
            
            var cellId = '#cell-' + item.shift_type + '-' + dayIndex;
            var html = `<div class="shift-item">
                            <span>${item.fullname}</span>
                            <i class="fas fa-times btn-del-shift" onclick="xoaCa(${item.id})"></i>
                        </div>`;
            $(cellId).prepend(html); // Prepend để nút thêm luôn ở dưới
        });
    }

    function doiTuan(direction) {
        var d = new Date(currentWeekStart);
        d.setDate(d.getDate() + (direction * 7));
        currentWeekStart = d.toISOString().split('T')[0];
        taiLichLamViec();
    }

    function loadUsersForSchedule() {
        $.ajax({url: 'api/get_users.php', success: function(html){
            // Hacky way: parse HTML to get names, better create separate API returning JSON
            // But for simplicity, I'll assume usersList is populated or I'll just allow typing ID
            // Let's optimize: Create a simpler dropdown from the HTML response is hard.
            // Better: call a dedicated JSON API. I will simulate it by parsing the card titles if needed
            // Or simpler: Just re-use get_users.php but modify it to return JSON if param json=1 passed.
            // Let's stick to existing: I will fetch users once and store global.
        }});
        // REPLACEMENT: Fetch users for dropdown
        // Since get_users.php returns HTML, I will create a quick logic here or assume admin knows IDs?
        // NO, UX needs dropdown. I'll rely on a small trick: 
        // I will add a parameter to get_users.php?format=json in future.
        // For now, I'll extract from the HTML if displayed, OR better:
        // I will just use the same API `api/get_users.php` but modify it slightly or parse it.
        // WAIT: I can just fetch `api/get_users.php`... actually let's just create a quick cleaner way.
        // I'll update the `sch_users` select options dynamically when user mode loads.
    }
    
    // Quick Fix to populate Select Box for Shift
    // We need list of users (ID + Name). 
    // Since we don't have a JSON API for users, I will add a small inline PHP block in `get_users.php` or just parse.
    // Let's assume the user will just type ID for now? NO.
    // I will write a small ajax call to `api/get_users.php` and parse the names.
    function updateSelectUsers() {
         // This is a bit manual because get_users returns HTML
         // Ideally create `api/get_users_json.php`
         // For this demo, I will assume the user clicks "User Manager" tab once to load data :)
         // Or better: I will add a specific call here that expects JSON if I modified `get_users.php`.
         // Since I cannot modify `get_users.php` to return JSON without breaking other parts, 
         // I will parse the `users` table directly? No.
         // Let's just use the HTML response.
         $.get('api/get_users.php', function(data){
             var html = $(data);
             var options = '';
             html.find('.btn-edit-user').each(function(){
                 var id = $(this).data('id');
                 var name = $(this).data('fullname');
                 options += `<option value="${id}">${name}</option>`;
             });
             $('#sch_users').html(options);
         });
    }

    function openAddShift(date, type) {
        $('#sch_date').val(date);
        $('#sch_type').val(type);
        $('#lbl_date').text(formatDate(new Date(date)));
        $('#lbl_type').text(type == 'morning' ? 'Sáng (7h-12h)' : 'Chiều (12h-17h)');
        updateSelectUsers();
        $('#modalAddSchedule').modal('show');
    }

    function luuPhanCa(e) {
        e.preventDefault();
        $.ajax({
            url: 'api/add_schedule.php',
            type: 'POST',
            data: $('#formSchedule').serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.status == 'success') {
                    $('#modalAddSchedule').modal('hide');
                    taiLichLamViec();
                } else { alert(res.message); }
            }
        });
    }

    function xoaCa(id) {
        if(!confirm("Xóa nhân viên khỏi ca này?")) return;
        $.ajax({ url: 'api/delete_schedule.php', type: 'POST', data: {id: id}, success: function(){ taiLichLamViec(); } });
    }

    function formatDate(date) {
        return date.getDate() + '/' + (date.getMonth()+1);
    }

    // --- OTHER FUNCTIONS (Keep existing) ---
    window.taiNoiDung = function() {
        if(currentMode == 'report' || currentMode == 'schedule') return;
        var keyword = $('#searchInput').val();
        var urlApi = '', dataApi = { search: keyword };
        if (currentMode == 'menu') { urlApi = 'api/get_items.php'; dataApi.category = currentCategory; } 
        else if (currentMode == 'user') { urlApi = 'api/get_users.php'; } 
        else if (currentMode == 'order') { urlApi = 'api/get_orders.php'; }
        else if (currentMode == 'voucher') { urlApi = 'api/get_vouchers.php'; }
        $.ajax({ url: urlApi, type: 'GET', data: dataApi, success: function(data) { $('#hienthi-sanpham').html(data); } });
    };
    // Keep existing helper functions (taiBaoCao, renderChart, themVoucher, xoaVoucher, etc.)
    // ... (Toàn bộ code cũ của các chức năng khác giữ nguyên) ...
    window.locBaoCao = function(e) { e.preventDefault(); taiBaoCao(); }
    window.taiBaoCao = function() { var start = $('#report_start').val(); var end = $('#report_end').val(); $.ajax({ url: 'api/get_report_stats.php', type: 'GET', data: {start: start, end: end}, dataType: 'json', success: function(res) { $('#rpt-revenue').text(new Intl.NumberFormat('vi-VN').format(res.summary.revenue) + ' đ'); $('#rpt-orders').text(res.summary.orders); $('#rpt-cash').text(new Intl.NumberFormat('vi-VN').format(res.summary.cash) + ' đ'); $('#rpt-discount').text(new Intl.NumberFormat('vi-VN').format(res.summary.discount) + ' đ'); $('#rpt-table-body').html(res.table); renderChart(res.chart.labels, res.chart.data); } }); }
    function renderChart(labels, data) { var ctx = document.getElementById('revenue-chart').getContext('2d'); if (myChart) { myChart.destroy(); } myChart = new Chart(ctx, { type: 'bar', data: { labels: labels, datasets: [{ label: 'Doanh thu (VNĐ)', data: data, backgroundColor: 'rgba(60, 141, 188, 0.9)', borderColor: 'rgba(60, 141, 188, 0.8)', borderWidth: 1 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } } }); }
    window.xuatExcel = function() { var start = $('#report_start').val(); var end = $('#report_end').val(); window.location.href = 'api/export_excel.php?start=' + start + '&end=' + end; }
    window.themVoucher = function(e) { e.preventDefault(); $.ajax({ url: 'api/add_voucher.php', type: 'POST', data: $('#formAddVoucher').serialize(), dataType: 'json', success: function(res) { if(res.status == 'success') { alert(res.message); $('#modalAddVoucher').modal('hide'); $('#formAddVoucher')[0].reset(); taiNoiDung(); } else { alert(res.message); } } }); }
    window.xoaVoucher = function(id) { if(!confirm("Xóa mã này?")) return; $.ajax({ url: 'api/delete_voucher.php', type: 'POST', data: {id: id}, dataType: 'json', success: function(res) { taiNoiDung(); } }); }
    window.xuLyThemUser = function(e) { e.preventDefault(); $.ajax({ url: 'api/add_user.php', type: 'POST', data: $('#formAddUser').serialize(), dataType: 'json', success: function(res) { if(res.status=='success'){ alert(res.message); $('#modalAddUser').modal('hide'); taiNoiDung(); } else alert(res.message); } }); };
    window.xuLySuaUser = function(e) { e.preventDefault(); $.ajax({ url: 'api/update_user.php', type: 'POST', data: $('#formEditUser').serialize(), dataType: 'json', success: function(res) { if(res.status=='success'){ alert(res.message); $('#modalEditUser').modal('hide'); taiNoiDung(); } else alert(res.message); } }); };
    $(document).on('click', '.btn-edit-user', function(){ $('#edit_user_id').val($(this).data('id')); $('#edit_user_fullname').val($(this).data('fullname')); $('#edit_user_username').val($(this).data('username')); $('#edit_user_role').val($(this).data('role')); $('#edit_user_status').val($(this).data('status')); $('#modalEditUser').modal('show'); });
    window.loadIngredients = function() { $.ajax({ url: 'api/get_ingredients.php', dataType: 'json', success: function(data) { ingredientsList = data; } }); }
    window.addIngredientRow = function(containerId, ingId = null, qty = '') { var options = '<option value="">-- Chọn NL --</option>'; if(ingredientsList.length > 0) ingredientsList.forEach(function(ing) { var selected = (ing.id == ingId) ? 'selected' : ''; options += `<option value="${ing.id}" ${selected}>${ing.name} (${ing.unit})</option>`; }); var html = `<div class="d-flex mb-2 align-items-center"><select name="ing_id[]" class="form-control form-control-sm mr-2" style="width:60%">${options}</select><input type="number" step="0.01" name="ing_qty[]" class="form-control form-control-sm mr-2" style="width:25%" value="${qty}"><button type="button" class="btn btn-sm btn-danger" onclick="$(this).parent().remove()">X</button></div>`; $('#' + containerId).append(html); };
    window.chuyenDanhMuc = function(catId, title) { currentCategory = catId; $('.breadcrumb-item').removeClass('active'); $('#page-title').text('Danh sách ' + title); $('#searchInput').val(''); taiNoiDung(); };
    window.themMon = function(e) { e.preventDefault(); var fd = new FormData(document.getElementById('formAdd')); $.ajax({ url: 'api/add_item.php', type: 'POST', data: fd, contentType: false, processData: false, dataType: 'json', success: function(res) { if(res.status=='success'){ $('#modalAddItem').modal('hide'); alert(res.message); $('#formAdd')[0].reset(); $('#recipe-container-add').html(''); taiNoiDung(); } else alert(res.message); } }); };
    $(document).on('click', '.btn-edit', function(e){ e.preventDefault(); var id = $(this).data('id'); $('#edit_id').val(id); $('#edit_name').val($(this).data('name')); $('#edit_price').val($(this).data('price').toString().replace(/\./g, '')); $('#edit_category').val($(this).data('category')); $('#preview_img').attr('src', $(this).data('img')); $('#recipe-container-edit').html(''); $.ajax({ url: 'api/get_recipe_detail.php', data: {product_id: id}, dataType: 'json', success: function(recipes) { if(recipes && recipes.length > 0) recipes.forEach(r => addIngredientRow('recipe-container-edit', r.ingredient_id, r.quantity_required)); else addIngredientRow('recipe-container-edit'); } }); $('#modalEdit').modal('show'); });
    window.luuSua = function(e) { e.preventDefault(); var fd = new FormData(document.getElementById('formEdit')); $.ajax({ url: 'api/update_item.php', type: 'POST', data: fd, contentType: false, processData: false, dataType: 'json', success: function(res){ if(res.status=='success'){ $('#modalEdit').modal('hide'); alert('Cập nhật xong!'); taiNoiDung(); } else alert(res.message); } }); };
    $(document).on('click', '.btn-delete', function(){ $('#idDelete').val($(this).data('id')); $('#modalDelete').modal('show'); });
    window.xacNhanXoa = function() { $.ajax({ url: 'api/delete_item.php', type: 'POST', data: {id: $('#idDelete').val()}, dataType: 'json', success: function(res){ $('#modalDelete').modal('hide'); if(res.status=='success') taiNoiDung(); else alert(res.message); } }); };

</script>
</body>
</html>