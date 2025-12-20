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
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li></ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
          <form class="form-inline" onsubmit="return false;">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" id="searchInput" type="search" placeholder="Tìm món...">
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
            <a href="#" class="nav-link active"><i class="nav-icon fas fa-tachometer-alt"></i><p>Quản lý Menu</p></a>
            <ul class="nav nav-treeview">
              <li class="nav-item"><a href="#" class="nav-link active"><i class="far fa-circle nav-icon"></i><p>Danh sách món</p></a></li>
              <li class="nav-item"><a href="../pos/index.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Order</p></a></li>
              <li class="nav-item">
              <a href="javascript:void(0)" class="nav-link" onclick="hienThiNhanVien()">
              <i class="nav-icon fas fa-users-cog"></i>
                  <p>Quản lý nhân viên</p>
                </a>
              </li>

            </ul>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6"><h1 class="m-0" id="page-title">Danh sách món</h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="javascript:void(0)" onclick="chuyenDanhMuc(2, 'Đồ ăn')"><i class="fas fa-hamburger"></i> Đồ ăn</a></li>
              <li class="breadcrumb-item active"><a href="javascript:void(0)" onclick="chuyenDanhMuc(1, 'Đồ uống')"><i class="fas fa-coffee"></i> Đồ uống</a></li>
            </ol>
          </div>
        </div>
        <div id="hienthi-sanpham"></div>
      </div>
    </div>
  </div>

  <aside class="control-sidebar control-sidebar-dark">
    <div class="p-3"><a href="./add-food-drink.php">Thêm món mới</a><br><a href="./calculate-bill.php">Tính tiền</a></div>
  </aside>

  <footer class="main-footer"><strong>Coffee Nguyễn Văn</strong></footer>
</div>

<div class="modal fade" id="modalDelete">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger"><h5 class="modal-title text-white">Xác nhận xóa</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body"><p>Bạn có chắc chắn muốn xóa món này không?</p><input type="hidden" id="idDelete"></div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button><button type="button" class="btn btn-danger" onclick="xacNhanXoa()">Xóa ngay</button></div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalEdit">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning"><h5 class="modal-title">Sửa thông tin món</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <form id="formEdit" onsubmit="luuSua(event)">
          <div class="modal-body">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="form-group"><label>Tên món</label><input type="text" class="form-control" name="edit_name" id="edit_name" required></div>
            <div class="form-group"><label>Giá tiền</label><input type="number" class="form-control" name="edit_price" id="edit_price" required></div>
            <div class="form-group"><label>Danh mục</label>
                <select class="form-control" name="edit_category" id="edit_category">
                    <option value="1">Đồ uống</option><option value="2">Đồ ăn</option>
                </select>
            </div>
            <div class="form-group"><label>Ảnh món ăn</label><br>
                <img id="preview_img" src="" style="width: 80px; height: 80px; object-fit: cover; margin-bottom: 5px; border: 1px solid #ddd;">
                <input type="file" class="form-control-file" name="edit_image">
            </div>
          </div>
          <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button><button type="submit" class="btn btn-primary">Lưu thay đổi</button></div>
      </form>
    </div>
  </div>
</div>

<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/dist/js/adminlte.js"></script>

<script>
    var currentCategory = 2; 

    $(document).ready(function(){
        taiNoiDung();
        $('#searchInput').on('keyup', function(){ taiNoiDung(); });
    });

    function chuyenDanhMuc(catId, title) {
        currentCategory = catId;
        $('#page-title').text('Danh sách ' + title);
        $('#searchInput').val(''); 
        taiNoiDung();
    }

    function taiNoiDung() {
        var keyword = $('#searchInput').val();
        $.ajax({
            url: 'api/get_items.php', 
            type: 'GET',
            data: { category: currentCategory, search: keyword },
            beforeSend: function() { $('#hienthi-sanpham').css('opacity', '0.5'); },
            success: function(data) {
                $('#hienthi-sanpham').css('opacity', '1');
                $('#hienthi-sanpham').html(data);
            }
        });
    }

    // --- XỬ LÝ NÚT XÓA ---
    $(document).on('click', '.btn-delete', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        $('#idDelete').val(id); 
        $('#modalDelete').modal('show'); 
    });

    function xacNhanXoa() {
        var id = $('#idDelete').val();
        $.ajax({
            url: 'api/delete_item.php', // Đảm bảo file này tồn tại trong admin/api/
            type: 'POST',
            dataType: 'json',
            data: {id: id},
            success: function(res) {
                $('#modalDelete').modal('hide');
                if(res.status == 'success') {
                    taiNoiDung(); 
                } else {
                    alert('Lỗi: ' + res.message);
                }
            },
            error: function(xhr) {
                if(xhr.status == 404) alert("Lỗi: Không tìm thấy file api/delete_item.php");
                else alert("Lỗi kết nối server");
            }
        });
    }

    // --- XỬ LÝ NÚT SỬA ---
    $(document).on('click', '.btn-edit', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        var name = $(this).data('name');
        var price = $(this).data('price').toString().replace(/\./g, '');
        var cat = $(this).data('category');
        var img = $(this).data('img');

        $('#edit_id').val(id);
        $('#edit_name').val(name);
        $('#edit_price').val(price);
        $('#edit_category').val(cat);
        $('#preview_img').attr('src', img);
        $('#modalEdit').modal('show');
    });

    function luuSua(e) {
        e.preventDefault();
        var formData = new FormData(document.getElementById('formEdit'));
        $.ajax({
            url: 'api/update_item.php', // Đảm bảo file này tồn tại trong admin/api/
            type: 'POST',
            data: formData,
            contentType: false, processData: false, dataType: 'json',
            success: function(res) {
                if(res.status == 'success') {
                    $('#modalEdit').modal('hide');
                    alert('Cập nhật thành công!');
                    taiNoiDung();
                } else {
                    alert('Lỗi: ' + res.message);
                }
            },
            error: function(xhr) {
                 if(xhr.status == 404) alert("Lỗi: Không tìm thấy file api/update_item.php");
                 else alert("Lỗi kết nối server: " + xhr.responseText);
            }
        });
    }
</script>
<div class="modal fade" id="modalAddUser">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary"><h5 class="modal-title text-white">Thêm nhân viên mới</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <form id="formAddUser" onsubmit="xuLyThemUser(event)">
          <div class="modal-body">
            <div class="form-group"><label>Họ và tên</label><input type="text" class="form-control" name="fullname" required></div>
            <div class="form-group"><label>Tên đăng nhập</label><input type="text" class="form-control" name="username" required></div>
            <div class="form-group"><label>Mật khẩu</label><input type="password" class="form-control" name="password" required></div>
            <div class="form-group"><label>Chức vụ</label>
                <select class="form-control" name="role">
                    <option value="staff">Nhân viên phục vụ</option><option value="wh-staff">Thủ kho</option><option value="admin">Quản trị viên</option>
                </select>
            </div>
          </div>
          <div class="modal-footer"><button type="submit" class="btn btn-primary">Lưu tài khoản</button></div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalEditUser">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning"><h5 class="modal-title">Cấu hình tài khoản</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <form id="formEditUser" onsubmit="xuLySuaUser(event)">
          <div class="modal-body">
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="form-group"><label>Họ và tên</label><input type="text" class="form-control" name="fullname" id="edit_user_fullname" required></div>
            <div class="form-group"><label>Mật khẩu mới (Để trống nếu không đổi)</label><input type="password" class="form-control" name="password"></div>
            <div class="form-group"><label>Chức vụ</label>
                <select class="form-control" name="role" id="edit_user_role">
                    <option value="staff">Nhân viên phục vụ</option><option value="wh-staff">Thủ kho</option><option value="admin">Quản trị viên</option>
                </select>
            </div>
            <div class="form-group"><label>Trạng thái</label>
                <select class="form-control" name="status" id="edit_user_status">
                    <option value="1">Đang làm việc</option><option value="0">Khóa tài khoản</option>
                </select>
            </div>
          </div>
          <div class="modal-footer"><button type="submit" class="btn btn-warning">Lưu thay đổi</button></div>
      </form>
    </div>
  </div>
</div>

<script>
// Hàm hiển thị giao diện quản lý nhân viên
function hienThiNhanVien() {
    $('#page-title').text('Quản lý nhân viên');
    $('.breadcrumb-item').removeClass('active');
    $.ajax({
        url: 'api/get_users.php',
        type: 'GET',
        success: function(data) {
            $('#hienthi-sanpham').html(data);
        }
    });
}

// Bắt sự kiện bấm nút Sửa nhân viên
$(document).on('click', '.btn-edit-user', function(){
    $('#edit_user_id').val($(this).data('id'));
    $('#edit_user_fullname').val($(this).data('fullname'));
    $('#edit_user_role').val($(this).data('role'));
    $('#edit_user_status').val($(this).data('status'));
    $('#modalEditUser').modal('show');
});

// Xử lý thêm
function xuLyThemUser(e) {
    e.preventDefault();
    $.ajax({
        url: 'api/add_user.php',
        type: 'POST',
        data: $('#formAddUser').serialize(),
        success: function(res) {
            if(res.status == 'success') {
                alert(res.message);
                $('#modalAddUser').modal('hide');
                $('#formAddUser')[0].reset();
                hienThiNhanVien();
            } else { alert(res.message); }
        }
    });
}

// Xử lý sửa
function xuLySuaUser(e) {
    e.preventDefault();
    $.ajax({
        url: 'api/update_user.php',
        type: 'POST',
        data: $('#formEditUser').serialize(),
        success: function(res) {
            if(res.status == 'success') {
                alert(res.message);
                $('#modalEditUser').modal('hide');
                hienThiNhanVien();
            } else { alert(res.message); }
        }
    });
}
</script>
</body>
</html>