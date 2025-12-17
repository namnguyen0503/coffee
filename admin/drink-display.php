<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Coffee Nguyễn Văn</title>

    <!-- Google Font: Source Sans Pro -->
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"
    />
    <!-- Font Awesome Icons -->
    <link
      rel="stylesheet"
      href="./assets/plugins/fontawesome-free/css/all.min.css"
    />

    <link rel="stylesheet" href="./assets/dist/css/adminlte.min.css" />
    
    <link rel="stylesheet" href="./display-item.css" />
  </head>
  <body class="hold-transition sidebar-mini">
    <div class="wrapper">
      <!-- Navbar -->
      <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"
              ><i class="fas fa-bars"></i
            ></a>
          </li>
          <li class="nav-item d-none d-sm-inline-block">
            <a href="#" class="nav-link">Home</a>
          </li>
          <li class="nav-item d-none d-sm-inline-block">
            <a href="#" class="nav-link">Contact</a>
          </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
          <!--------------------------------------------------- Navbar Search ----------------------------------------------------------------->   
          <li class="nav-item">
            <a
              class="nav-link"
                   
              data-widget="navbar-search"  

              href="#"
              role="button"
            >
              <i class="fas fa-search"></i>
            </a>
            <div class="navbar-search-block">
              <form action="" method="post" class="form-inline">
                <div class="input-group input-group-sm">
                  <input
                    class="form-control form-control-navbar"
                    type="search"
                    placeholder="Search"
                    aria-label="Search"
                    name = "data"
                    value ="<?php if(!empty($errors['data'])) echo $errors['data']; ?>"
                    
                  />
                  <div class="input-group-append">
                    <button class="btn btn-navbar" type="submit">
                      <i class="fas fa-search"></i>
                    </button>
                    <button
                      class="btn btn-navbar"
                      type="button"
                      data-widget="navbar-search"
                    >
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </li>

          
          
          
          <!--------------------------------------------------------- full screen -------------------------------------------------------------->
          <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
              <i class="fas fa-expand-arrows-alt"></i>
            </a>
          </li>
          <li class="nav-item">
            <a
              class="nav-link"
              data-widget="control-sidebar"
              data-slide="true"
              href="#"
              role="button"
            >
              <i class="fas fa-th-large"></i>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.navbar -->

      <!-- Main Sidebar Container -->
      <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Sidebar -->
        <div class="sidebar">
          <!-- Brand Logo -->
          <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
              <img
                src="./assets/dist/img/logo coffee.png"
                class="img-fluid elevation-2"
                alt="Image"
                style="width: 500px; "
              />
            </div>
          </div>          
          <!-- Sidebar Menu -->
          <nav class="mt-2">
            <ul
              class="nav nav-pills nav-sidebar flex-column"
              data-widget="treeview"
              role="menu"
              data-accordion="false"
            >
              <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
              <li class="nav-item menu-open">
                <a href="#" class="nav-link active">
                  <i class="nav-icon fas fa-tachometer-alt"></i>
                  <p>
                    Manage food and drink
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" class="nav-link active">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Manage</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <!------------------------ link đến trang oder ------------------------>
                    <a href="" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Oder</p>
                    </a>
                  </li>
                  <li>
                    <a href="./export.php" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Xuất Excel</p>
                    </a>
                  </li>
                </ul>
              </li>
            </ul>
          </nav>
          <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
      </aside>

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
          <div class="container-fluid">
            <!-- thanh phân loại món -->
            <div class="row mb-2">
              <!-- ============================ -->
              <div class="col-sm-6">
                <h1 class="m-0">Danh sách món</h1>
              </div>
              <!-- ============================ -->

              <!-- ============================ -->
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="./drink-display.php"> <i class="nav-icon fas fa-coffee"></i><p>Đồ uống</p></a></li>
                  <!-------------- link đến trang drink ------------>
                  <li class="breadcrumb-item active">
                  <a href="javascript:void(0)" class="nav-link" onclick="taiNoiDung('do_an')">
                          <i class="nav-icon fas fa-hamburger"></i>
                          <p>Đồ ăn</p>
                        </a>  
                        <!-------------- link đến trang food ------------>
                  </li>
                </ol>
              </div>
              <!-- ============================ -->
            </div>
            <!--------------------------- in danh sách ------------------------------------>
      <?php if(isset($_POST['data'])){?>
        <div class="ds-coffe">
              <?php
              
              require 'db_connection.php';
              $item_search= isset($_POST['data']) ? $_POST['data'] : '';
              $errors = array();
              if(empty($item_search)){
                $errors['data'] = 'not found';
              }
              
                function get_item_search( $item_search){
                  global $conn;
                  $conn= connect_db();
                  $sql= "SELECT *FROM products WHERE name =  '$item_search'";
                  $query=mysqli_query($conn,$sql);
                  $items = [];
                  while ($row = mysqli_fetch_assoc($query)) {
                     $items[] = $row;
                      }
                      return $items;
                }  
                $items = get_item_search( $item_search);


                ?>
              <?php foreach ($items as $item): ?>
                
              <div class="item-coffe">
                <img src="<?php echo $item['image_url']; ?>" alt="" class="thumb" />
                <p><?php echo $item['name'];?></p>
                <p>Giá: <?php echo $item['price']; ?></p>
                <p>Trạng thái: <?php if($item['is_active']){ 
                  echo 'còn bán';}
                  else{
                  echo 'không còn bán';
                  } ?></p>
                  <div class="edit">
                  <input onclick="window.location = 'edit-food-coffee.php?id=<?php echo $item['id']; ?>'" type="button" value="Edit">
                  <input onclick="window.location = 'delete-food-coffee.php?id=<?php echo $item['id']; ?>'" type="button" value="Delete">                                                
                  </div>
                  
              </div>
              
              <?php endforeach; ?>
           
             <?php }else{ ?> 
            <div id="hienthi-sanpham">
            <div class="ds-coffe">
              <?php
              require 'db_connection.php';
                function get_all_item(){
                  global $conn;
                  $conn= connect_db();
                  $sql= "SELECT *FROM products";
                  $query=mysqli_query($conn,$sql);
                  $items = [];
                  while ($row = mysqli_fetch_assoc($query)) {
                     $items[] = $row;
                      }
                      return $items;
                }  
                $items = get_all_item();

                ?>
              <?php foreach ($items as $item): ?>
                <?php if($item['category_id'] == 2){  ?>
              <div class="item-coffe">
                <img src="<?php echo $item['image_url']; ?>" alt="" class="thumb" />
                <p><?php echo $item['name'];?></p>
                <p>Giá: <?php echo $item['price']; ?></p>
                <p>Trạng thái: <?php if($item['is_active']){ 
                  echo 'còn bán';}
                  else{
                  echo 'không còn bán';
                  } ?></p>
                  <div class="edit">
                  <input onclick="window.location = 'edit-food-coffee.php?id=<?php echo $item['id']; ?>'" type="button" value="Edit">
                  <input onclick="window.location = 'delete-food-coffee.php?id=<?php echo $item['id']; ?>'" type="button" value="Delete">                                                
                  </div>
                  
              </div>
              <?php } ?>
              <?php endforeach; ?>
              <?php } ?>
              <!-- in danh sách -->
        </div>
            </div>
          </div>
          <!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->

        <!-- /.content -->
      </div>
      <!-- /.content-wrapper -->

      <!-- Control Sidebar -->
      <aside class="control-sidebar control-sidebar-dark">
        <!------------------------------ Phần thêm món -------------------------------->
        <div class="p-3">
          
          <a href="./add-food-drink.php">Add food/drink</a> <br>
          <a href="./calculate-bill.php">Calculate price</a>
        </div>
      </aside>
      <!-- /.control-sidebar -->

      <!-- Main Footer -->
      <footer class="main-footer">
        <!-- To the right -->
        <div class="float-right d-none d-sm-inline">coffee Nguyễn Văn</div>
        <!-- Default to the left -->
        <strong>Địa chỉ: 147 Văn Cao </strong>
      </footer>
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->

    <!-- jQuery -->
    <script src="assets/plugins/jquery/jquery.min.js"></script>

    <script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="assets/dist/js/adminlte.min.js"></script>
    <script>
    // Hàm này sẽ chạy khi bấm nút
    function taiNoiDung(loaiMon) {
        
        // 1. Xác định file cần gọi dựa trên loại món
        var tenFile = '';
        if (loaiMon === 'do_an') {
            tenFile = 'food-display.php';
        } else if (loaiMon === 'do_uong') {
            tenFile = 'drink-display.php'; 
        }

        //  Fetch API (công cụ lấy dữ liệu ngầm)
        fetch(tenFile)
            .then(response => response.text()) // Chuyển đổi phản hồi thành văn bản HTML
            .then(data => {
                // Nhét HTML vừa lấy được vào cái hộp div trên
                document.getElementById('hienthi-sanpham').innerHTML = data;
            })
            .catch(error => console.error('Lỗi:', error));
    }
</script>
  </body>
</html>
