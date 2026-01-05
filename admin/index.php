<?php
session_start();
require_once '../includes/db_connection.php';

// 1. Chặn truy cập trái phép
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin')) {
    header("Location: ../login.php");
    exit;
}
?>
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

      /* CSS cho nút chọn danh mục */
      .category-selector { display: flex; gap: 15px; margin-bottom: 10px; }
      .btn-category {
          flex: 1; padding: 15px; border: none; border-radius: 12px; background: white; color: #555; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: center; border: 2px solid transparent;
      }
      .btn-category i { margin-right: 10px; font-size: 1.4rem; }
      .btn-category:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
      .btn-category.active[data-type="drink"] { background: linear-gradient(135deg, #3c8dbc, #2980b9); color: white; box-shadow: 0 4px 10px rgba(60, 141, 188, 0.4); }
      .btn-category.active[data-type="food"] { background: linear-gradient(135deg, #e67e22, #d35400); color: white; box-shadow: 0 4px 10px rgba(230, 126, 34, 0.4); }
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
    <a href="index.php" class="brand-link">
      <img src="assets/dist/img/logo.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light font-weight-bold">Coffee Ng Văn</span>
    </a>

    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item menu-open">
            <ul class="nav nav-treeview">
              <li class="nav-item"><a href="javascript:void(0)" class="nav-link active" id="link-menu" onclick="chuyenCheDo('menu')"><i class="fas fa-coffee nav-icon"></i><p>Danh sách món</p></a></li>
              <li class="nav-item"><a href="javascript:void(0)" class="nav-link" id="link-order" onclick="chuyenCheDo('order')"><i class="fas fa-file-invoice-dollar nav-icon"></i><p>Quản lý Đơn hàng</p></a></li>
              <li class="nav-item"><a href="javascript:void(0)" class="nav-link" id="link-voucher" onclick="chuyenCheDo('voucher')"><i class="fas fa-ticket-alt nav-icon"></i><p>Mã giảm giá</p></a></li>
              <li class="nav-item"><a href="javascript:void(0)" class="nav-link" id="link-schedule" onclick="chuyenCheDo('schedule')"><i class="fas fa-calendar-alt nav-icon"></i><p>Quản lý Phân ca</p></a></li>
              <li class="nav-item"><a href="javascript:void(0)" class="nav-link" id="link-report" onclick="chuyenCheDo('report')"><i class="fas fa-chart-bar nav-icon"></i><p>Báo cáo doanh thu</p></a></li>
              
              <li class="nav-item">
                <a href="javascript:void(0)" class="nav-link" id="link-stock" onclick="chuyenCheDo('stock')">
                    <i class="fas fa-exclamation-triangle nav-icon"></i>
                    <p>Cảnh báo kho <span class="badge badge-danger right" id="badge-stock-sidebar" style="display:none">0</span></p>
                </a>
              </li>

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
            <div class="mode-section" id="cat-selector-group">
                <div class="category-selector float-sm-right" style="width: 100%; max-width: 400px;">
                    <button class="btn-category active" data-type="drink" onclick="chuyenDanhMuc(1, 'Đồ uống')"><i class="fas fa-coffee"></i> Đồ uống</button>
                    <button class="btn-category" data-type="food" onclick="chuyenDanhMuc(2, 'Đồ ăn')"><i class="fas fa-hamburger"></i> Đồ ăn</button>
                </div>
            </div>
            
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

        <div id="hienthi-warehouse" class="mode-section" style="display:none">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-list"></i> Cấu hình mức cảnh báo tồn kho
                    </h3>
                    <div class="card-tools">
                        <button class="btn btn-primary btn-sm" onclick="luuCauHinhKho()">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <div class="alert alert-light m-2">
                        <i class="fas fa-info-circle text-info"></i> Nhập số lượng tối thiểu vào cột <b>"Mức báo động"</b>. Nếu tồn kho thực tế thấp hơn mức này, hệ thống sẽ báo đỏ.
                    </div>
                    <table class="table table-hover table-striped text-nowrap">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Nguyên liệu</th>
                                <th class="text-center" style="width: 20%;">Tồn hiện tại</th>
                                <th class="text-center" style="width: 20%;">Mức báo động (Min)</th>
                                <th class="text-center" style="width: 15%;">Đơn vị</th>
                                <th class="text-center" style="width: 15%;">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody id="tbl-stock-body">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="hienthi-baocao" class="mode-section">
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-coins"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Doanh thu</span><span class="info-box-number" id="rpt-revenue">0 đ</span></div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-box-open"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Tiền nguyên liệu</span><span class="info-box-number text-danger" id="rpt-cogs">0 đ</span></div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-piggy-bank"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Lợi nhuận thực</span><span class="info-box-number text-success" id="rpt-profit">0 đ</span></div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-shopping-cart"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Tổng đơn</span><span class="info-box-number" id="rpt-orders">0</span></div>
                    </div>
                </div>
            </div>
            <div class="row"><div class="col-md-12"><div class="card"><div class="card-body"><canvas id="revenue-chart" height="250"></canvas></div></div></div></div>
            
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body table-responsive p-0" style="height: 300px;">
                            <table class="table table-head-fixed text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Thời gian</th>
                                        <th>Nhân viên</th>
                                        <th class="text-right">Doanh thu</th>
                                        <th class="text-right">Giá vốn (Est)</th>
                                        <th class="text-right">Lợi nhuận</th>
                                    </tr>
                                </thead>
                                <tbody id="rpt-table-body">
                                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
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
                            <tr>
                                <th><strong>TỐI</strong><br>(17h - 23h30)</th>
                                <td id="cell-evening-0"></td><td id="cell-evening-1"></td><td id="cell-evening-2"></td>
                                <td id="cell-evening-3"></td><td id="cell-evening-4"></td><td id="cell-evening-5"></td><td id="cell-evening-6"></td>
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

<div class="modal fade" id="modalAddSchedule"><div class="modal-dialog modal-sm"><div class="modal-content"><div class="modal-header bg-primary"><h5 class="modal-title text-white">Xếp nhân viên</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><form id="formSchedule" onsubmit="luuPhanCa(event)"><div class="modal-body"><input type="hidden" name="shift_date" id="sch_date"><input type="hidden" name="shift_type" id="sch_type"><p>Ngày: <b id="lbl_date"></b> - Ca: <b id="lbl_type"></b></p><div class="form-group"><label>Chọn nhân viên:</label><select class="form-control" name="user_id" id="sch_users"></select></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary btn-block">Thêm vào ca</button></div></form></div></div></div>
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
    var usersList = [];
    var currentWeekStart = '';
    var myChart = null;

    // --- HÀM XỬ LÝ NGÀY THÁNG ĐỊA PHƯƠNG (Fix lỗi lệch ngày) ---
    function getLocalDateString(date) {
        var year = date.getFullYear();
        var month = (date.getMonth() + 1).toString().padStart(2, '0');
        var day = date.getDate().toString().padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    $(document).ready(function(){
        autoUpdateStatus();
        
        // Tính ngày đầu tuần (Thứ 2) dựa trên giờ địa phương
        var today = new Date();
        var day = today.getDay(); // 0 (CN) -> 6 (T7)
        var currentDay = (day === 0) ? 7 : day; // Đổi CN thành 7
        
        // Lùi về Thứ 2
        var monday = new Date(today);
        monday.setDate(today.getDate() - (currentDay - 1));
        
        currentWeekStart = getLocalDateString(monday);

        // Set ngày mặc định cho báo cáo (Đầu tháng -> Hôm nay)
        var rptEnd = getLocalDateString(today);
        var rptStart = getLocalDateString(new Date(today.getFullYear(), today.getMonth(), 1));
        $('#report_end').val(rptEnd);
        $('#report_start').val(rptStart);

        taiNoiDung();
        loadIngredients(); // Tải danh sách nguyên liệu để dùng cho Modal thêm món
        loadStockData();   // Tải dữ liệu kho để cập nhật badge cảnh báo
        updateSelectUsers(); 
        $('#searchInput').on('keyup', function(){ taiNoiDung(); });
    });

    function autoUpdateStatus() {
        $.ajax({ url: 'api/auto_update_status.php', type: 'GET', success: function(res) { console.log("Auto Update Status: " + res.message); } });
    }

    // --- CHUYỂN CHẾ ĐỘ ---
    window.chuyenCheDo = function(mode) {
        currentMode = mode;
        $('#searchInput').val('');
        $('.nav-link').removeClass('active');
        $('.mode-section').hide();
        $('#hienthi-sanpham, #hienthi-baocao, #hienthi-schedule, #hienthi-warehouse').hide();
        $('#searchForm').show();

        if(mode == 'menu') {
            $('#link-menu').addClass('active'); $('#page-title').text('Danh sách món'); 
            $('#btn-group-menu').show(); $('#cat-selector-group').show();
            $('#hienthi-sanpham').show(); taiNoiDung();
        } else if(mode == 'user') {
            $('#link-user').addClass('active'); $('#page-title').text('Quản lý nhân viên'); $('#btn-group-user').show(); $('#hienthi-sanpham').show(); taiNoiDung();
        } else if(mode == 'order') {
            $('#link-order').addClass('active'); $('#page-title').text('Quản lý Đơn hàng'); $('#hienthi-sanpham').show(); taiNoiDung();
        } else if(mode == 'voucher') {
            $('#link-voucher').addClass('active'); $('#page-title').text('Mã giảm giá'); $('#btn-group-voucher').show(); $('#hienthi-sanpham').show(); taiNoiDung();
        } else if(mode == 'report') {
            $('#link-report').addClass('active'); $('#page-title').text('Báo cáo doanh thu'); $('#searchForm').hide(); $('#report-controls').show(); $('#hienthi-baocao').show(); taiBaoCao();
        } else if(mode == 'schedule') {
            $('#link-schedule').addClass('active'); $('#page-title').text('Lịch làm việc'); $('#searchForm').hide(); $('#btn-group-schedule').show(); $('#hienthi-schedule').show(); taiLichLamViec();
        
        // --- CHẾ ĐỘ KHO MỚI ---
        } else if(mode == 'stock') {
            $('#link-stock').addClass('active'); 
            $('#page-title').text('Cảnh báo & Cấu hình kho'); 
            $('#searchForm').hide(); // Ẩn thanh tìm kiếm nếu không dùng
            $('#hienthi-warehouse').show(); 
            loadStockData(); // Gọi hàm tải dữ liệu kho
        }
    };

    window.chuyenDanhMuc = function(catId, title) {
        currentCategory = catId;
        $('.btn-category').removeClass('active');
        if (catId == 1) { $('.btn-category[data-type="drink"]').addClass('active'); } 
        else { $('.btn-category[data-type="food"]').addClass('active'); }
        $('#page-title').text('Danh sách ' + title);
        $('#searchInput').val('');
        taiNoiDung();
    };

    // --- LOGIC QUẢN LÝ KHO (NEW) ---
    function loadStockData() {
        $('#tbl-stock-body').html('<tr><td colspan="5" class="text-center">Đang tải dữ liệu...</td></tr>');
        
        $.ajax({
            url: 'api/get_ingredients.php', 
            dataType: 'json',
            success: function(data) {
                var html = '';
                var warningCount = 0;
                
                data.forEach(function(item) {
                    var qty = parseFloat(item.quantity);
                    var min = parseFloat(item.min_quantity) || 0; // Mặc định là 0 nếu chưa cài
                    var isLow = qty <= min; // Kiểm tra xem có thấp hơn mức tối thiểu không
                    
                    if (isLow) warningCount++;

                    // Tạo giao diện từng dòng
                    var statusHtml = isLow 
                        ? '<span class="badge badge-danger">Sắp hết hàng</span>' 
                        : '<span class="badge badge-success">Ổn định</span>';
                    
                    // Tô màu nền nhẹ cho dòng cảnh báo
                    var rowStyle = isLow ? 'background-color: #fff3cd;' : '';

                    html += `<tr style="${rowStyle}">
                        <td class="align-middle font-weight-bold">${item.name}</td>
                        <td class="text-center align-middle ${isLow ? 'text-danger font-weight-bold' : ''}">${qty}</td>
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm text-center input-min-qty mx-auto" 
                                   style="max-width: 100px; border-color: ${isLow ? '#dc3545' : '#ced4da'}"
                                   data-id="${item.id}" value="${min}" step="0.1" min="0">
                        </td>
                        <td class="text-center align-middle">${item.unit}</td>
                        <td class="text-center align-middle">${statusHtml}</td>
                    </tr>`;
                });

                $('#tbl-stock-body').html(html);
                
                // Cập nhật số lượng cảnh báo lên Menu bên trái
                if (warningCount > 0) {
                    $('#badge-stock-sidebar').text(warningCount).show();
                } else {
                    $('#badge-stock-sidebar').hide();
                }
            },
            error: function() {
                $('#tbl-stock-body').html('<tr><td colspan="5" class="text-center text-danger">Lỗi kết nối server!</td></tr>');
            }
        });
    }

    window.luuCauHinhKho = function() {
        var updates = [];
        // Quét tất cả các ô input để lấy giá trị mới
        $('.input-min-qty').each(function() {
            updates.push({
                id: $(this).data('id'),
                min_quantity: $(this).val()
            });
        });

        // Gửi lên server
        $.ajax({
            url: 'api/update_min_stock.php',
            type: 'POST',
            data: { data: JSON.stringify(updates) },
            dataType: 'json',
            success: function(res) {
                if(res.status == 'success') {
                    alert('Đã cập nhật mức cảnh báo thành công!');
                    loadStockData(); // Tải lại bảng để cập nhật màu sắc/trạng thái
                } else {
                    alert('Lỗi: ' + res.message);
                }
            },
            error: function() {
                alert('Lỗi kết nối khi lưu dữ liệu! Hãy kiểm tra file update_min_stock.php');
            }
        });
    }

    // --- LOGIC BÁO CÁO ---
    window.locBaoCao = function(e) { 
        if(e) e.preventDefault(); 
        taiBaoCao(); 
    }

    window.taiBaoCao = function() {
        var start = $('#report_start').val();
        var end = $('#report_end').val();
        
        $('#rpt-table-body').html('<tr><td colspan="6" class="text-center">Đang tải dữ liệu...</td></tr>');

        $.ajax({
            url: 'api/get_report_stats.php',
            type: 'GET',
            data: {start: start, end: end},
            dataType: 'json',
            success: function(res) {
                if (res.status === 'error') {
                    alert(res.message); 
                    $('#rpt-table-body').html('<tr><td colspan="6" class="text-center text-danger">Có lỗi xảy ra.</td></tr>');
                    return;
                }

                var fmt = new Intl.NumberFormat('vi-VN');
                
                $('#rpt-revenue').text(fmt.format(res.summary.revenue) + ' đ');
                $('#rpt-cogs').text(fmt.format(res.summary.cogs) + ' đ');
                $('#rpt-profit').text(fmt.format(res.summary.profit) + ' đ');
                $('#rpt-orders').text(res.summary.orders);
                
                $('#rpt-table-body').html(res.html);
                
                if(res.chart) {
                    renderChart(res.chart.labels, res.chart.data);
                } else {
                    if(myChart) { myChart.destroy(); }
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert('Lỗi khi tải báo cáo.');
            }
        });
    }

    // --- LOGIC LỊCH LÀM VIỆC ---
    function taiLichLamViec() {
        $.ajax({
            url: 'api/get_schedules.php', type: 'GET', data: {start_date: currentWeekStart}, dataType: 'json',
            success: function(res) { renderScheduleTable(res.data, res.start_date); }
        });
    }
    
    function renderScheduleTable(data, startDate) {
        $('.table-schedule td').html(''); $('.btn-add-shift').remove();
        
        var parts = startDate.split('-');
        var start = new Date(parts[0], parts[1] - 1, parts[2]); 
        
        var endDate = new Date(start); 
        endDate.setDate(start.getDate() + 6);
        
        $('#schedule-range').text(formatDate(start) + ' - ' + formatDate(endDate));
        
        for(var i=0; i<7; i++) {
            var d = new Date(start); 
            d.setDate(start.getDate() + i); 
            
            $('#d-'+i).text(formatDate(d));
            var dateStr = getLocalDateString(d); 
            
            $('#cell-morning-'+i).append(`<button class="btn btn-outline-primary btn-xs btn-add-shift" onclick="openAddShift('${dateStr}', 'morning')">+ Thêm</button>`);
            $('#cell-afternoon-'+i).append(`<button class="btn btn-outline-primary btn-xs btn-add-shift" onclick="openAddShift('${dateStr}', 'afternoon')">+ Thêm</button>`);
            $('#cell-evening-'+i).append(`<button class="btn btn-outline-primary btn-xs btn-add-shift" onclick="openAddShift('${dateStr}', 'evening')">+ Thêm</button>`);
        }
        
        data.forEach(function(item){
            var itemDateParts = item.shift_date.split('-');
            var itemDate = new Date(itemDateParts[0], itemDateParts[1] - 1, itemDateParts[2]);
            
            var diffTime = itemDate - start;
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 

            if (diffDays >= 0 && diffDays <= 6) {
                var cellId = '#cell-' + item.shift_type + '-' + diffDays;
                $(cellId).prepend(`<div class="shift-item"><span>${item.fullname}</span><i class="fas fa-times btn-del-shift" onclick="xoaCa(${item.id})"></i></div>`);
            }
        });
    }

    function doiTuan(dir) { 
        var parts = currentWeekStart.split('-');
        var d = new Date(parts[0], parts[1] - 1, parts[2]);
        d.setDate(d.getDate() + dir*7); 
        currentWeekStart = getLocalDateString(d); 
        taiLichLamViec(); 
    }

    function updateSelectUsers() { $.get('api/get_users.php', function(data){ var h=$(data), o=''; h.find('.btn-edit-user').each(function(){o+=`<option value="${$(this).data('id')}">${$(this).data('fullname')}</option>`}); $('#sch_users').html(o); }); }
    
    function openAddShift(d, t) { 
        $('#sch_date').val(d); $('#sch_type').val(t); 
        var parts = d.split('-');
        var displayDate = parts[2] + '/' + parts[1] + '/' + parts[0];
        $('#lbl_date').text(displayDate); 
        
        var shiftName = (t=='morning') ? 'Sáng' : (t=='afternoon' ? 'Chiều' : 'Tối');
        $('#lbl_type').text(shiftName); 
        $('#modalAddSchedule').modal('show'); 
    }
    
    function luuPhanCa(e, forceUnlock = false) {
        if(e) e.preventDefault(); 
        var formData = $('#formSchedule').serializeArray();
        if (forceUnlock) { formData.push({name: 'force_unlock', value: 'true'}); }
        $.ajax({
            url: 'api/add_schedule.php', type: 'POST', data: formData, dataType: 'json',
            success: function(res) {
                if(res.status == 'success') { $('#modalAddSchedule').modal('hide'); alert(res.message); taiLichLamViec(); } 
                else if (res.status == 'locked_user') {
                    if (confirm(res.message)) { luuPhanCa(null, true); }
                } 
                else { alert(res.message); }
            },
            error: function() { alert('Lỗi kết nối server.'); }
        });
    }
    function xoaCa(id) { if(confirm("Xóa?")) $.ajax({ url: 'api/delete_schedule.php', type: 'POST', data: {id: id}, success: function(){ taiLichLamViec(); } }); }
    function formatDate(d) { return d.getDate()+'/'+(d.getMonth()+1); }
    window.taiNoiDung = function() {
        if(currentMode == 'report' || currentMode == 'schedule' || currentMode == 'stock') return;
        var k = $('#searchInput').val(); var url='', d={search: k};
        if (currentMode == 'menu') { url='api/get_items.php'; d.category=currentCategory; } 
        else if (currentMode == 'user') url='api/get_users.php';
        else if (currentMode == 'order') url='api/get_orders.php';
        else if (currentMode == 'voucher') url='api/get_vouchers.php';
        $.ajax({ url: url, type: 'GET', data: d, success: function(data) { $('#hienthi-sanpham').html(data); } });
    };
    function renderChart(l, d) { var ctx = document.getElementById('revenue-chart').getContext('2d'); if (myChart) myChart.destroy(); myChart = new Chart(ctx, { type: 'bar', data: { labels: l, datasets: [{ label: 'Doanh thu', data: d, backgroundColor: 'rgba(60,141,188,0.9)' }] }, options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true } } } }); }
    window.xuatExcel = function() { window.location.href = 'api/export_excel.php?start='+$('#report_start').val()+'&end='+$('#report_end').val(); }
    window.themVoucher = function(e) { e.preventDefault(); $.ajax({ url: 'api/add_voucher.php', type: 'POST', data: $('#formAddVoucher').serialize(), dataType: 'json', success: function(res) { if(res.status == 'success') { alert(res.message); $('#modalAddVoucher').modal('hide'); $('#formAddVoucher')[0].reset(); taiNoiDung(); } else { alert(res.message); } } }); }
    window.xoaVoucher = function(id) { if(!confirm("Xóa mã này?")) return; $.ajax({ url: 'api/delete_voucher.php', type: 'POST', data: {id: id}, dataType: 'json', success: function(res) { taiNoiDung(); } }); }
    window.xuLyThemUser = function(e) { e.preventDefault(); $.ajax({ url: 'api/add_user.php', type: 'POST', data: $('#formAddUser').serialize(), dataType: 'json', success: function(res) { if(res.status=='success'){ alert(res.message); $('#modalAddUser').modal('hide'); taiNoiDung(); } else alert(res.message); } }); };
    window.xuLySuaUser = function(e) { 
        e.preventDefault(); $.ajax({
             url: 'api/update_user.php', type: 'POST',
             data: $('#formEditUser').serialize(), 
             dataType: 'json', success: function(res) { 
                if(res.status=='success'){ alert(res.message); 
                    $('#modalEditUser').modal('hide'); taiNoiDung(); 
                } else alert(res.message); } }); };
    $(document).on('click', '.btn-edit-user', function(){ $('#edit_user_id').val($(this).data('id')); $('#edit_user_fullname').val($(this).data('fullname')); $('#edit_user_username').val($(this).data('username')); $('#edit_user_role').val($(this).data('role')); $('#edit_user_status').val($(this).data('status')); $('#modalEditUser').modal('show'); });
    window.loadIngredients = function() { $.ajax({ url: 'api/get_ingredients.php', dataType: 'json', success: function(data) { ingredientsList = data; } }); }
    window.addIngredientRow = function(containerId, ingId = null, qty = '') { var options = '<option value="">-- Chọn NL --</option>'; if(ingredientsList.length > 0) ingredientsList.forEach(function(ing) { var selected = (ing.id == ingId) ? 'selected' : ''; options += `<option value="${ing.id}" ${selected}>${ing.name} (${ing.unit})</option>`; }); var html = `<div class="d-flex mb-2 align-items-center"><select name="ing_id[]" class="form-control form-control-sm mr-2" style="width:60%">${options}</select><input type="number" step="0.01" name="ing_qty[]" class="form-control form-control-sm mr-2" style="width:25%" value="${qty}"><button type="button" class="btn btn-sm btn-danger" onclick="$(this).parent().remove()">X</button></div>`; $('#' + containerId).append(html); };
    window.themMon = function(e) { e.preventDefault(); var fd = new FormData(document.getElementById('formAdd')); $.ajax({ url: 'api/add_item.php', type: 'POST', data: fd, contentType: false, processData: false, dataType: 'json', success: function(res) { if(res.status=='success'){ $('#modalAddItem').modal('hide'); alert(res.message); $('#formAdd')[0].reset(); $('#recipe-container-add').html(''); taiNoiDung(); } else alert(res.message); } }); };
    $(document).on('click', '.btn-edit', function(e){ e.preventDefault(); var id = $(this).data('id'); $('#edit_id').val(id); $('#edit_name').val($(this).data('name')); $('#edit_price').val($(this).data('price').toString().replace(/\./g, '')); $('#edit_category').val($(this).data('category')); $('#preview_img').attr('src', $(this).data('img')); $('#recipe-container-edit').html(''); $.ajax({ url: 'api/get_recipe_detail.php', data: {product_id: id}, dataType: 'json', success: function(recipes) { if(recipes && recipes.length > 0) recipes.forEach(r => addIngredientRow('recipe-container-edit', r.ingredient_id, r.quantity_required)); else addIngredientRow('recipe-container-edit'); } }); $('#modalEdit').modal('show'); });
    window.luuSua = function(e) {
         e.preventDefault(); 
         var fd = new FormData(document.getElementById('formEdit')); 
         $.ajax({ url: 'api/update_item.php', 
            type: 'POST', 
            data: fd, 
            contentType: false, 
            processData: false, 
            dataType: 'json', success: 
            function(res){ if(res.status=='success'){ $('#modalEdit').modal('hide'); alert('Cập nhật xong!'); taiNoiDung(); } else alert(res.message); } }); };
    $(document).on('click', '.btn-delete', function(){ $('#idDelete').val($(this).data('id')); $('#modalDelete').modal('show'); });
    window.xacNhanXoa = function() { $.ajax({ url: 'api/delete_item.php', type: 'POST', data: {id: $('#idDelete').val()}, dataType: 'json', success: function(res){ $('#modalDelete').modal('hide'); if(res.status=='success') taiNoiDung(); else alert(res.message); } }); };
    window.xemChiTietDon = function(orderId) { $.ajax({ url: 'api/get_order_detail.php', type: 'GET', data: { id: orderId }, success: function(data) { $('#order-detail-content').html(data); $('#modalOrderDetail').modal('show'); }, error: function() { alert('Lỗi: Không thể tải chi tiết đơn hàng.'); } }); };
    window.xacNhanHuy = function(orderId) { if (!confirm('Bạn có chắc chắn muốn HỦY đơn hàng #' + orderId + '?\nNguyên liệu sẽ được hoàn lại kho và doanh thu ca làm việc sẽ bị trừ.')) { return; } $.ajax({ url: 'api/cancel_order.php', type: 'POST', data: { id: orderId }, dataType: 'json', success: function(res) { if (res.status == 'success') { alert(res.message); taiNoiDung(); } else { alert('Lỗi: ' + res.message); } }, error: function() { alert('Lỗi kết nối server khi thực hiện hủy đơn.'); } }); };
</script>
</body>
</html>