<?php
require '../tinh-nang/db_connection.php';
$conn = connect_db();

$sql = "SELECT id, fullname, username, role, status FROM users ORDER BY id DESC";
$query = mysqli_query($conn, $sql);

echo '<div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-coffee">Danh sách nhân viên</h2>
        <button class="btn btn-primary" onclick="$(\'#modalAddUser\').modal(\'show\')"><i class="fas fa-plus"></i> Thêm nhân viên</button>
      </div>';

if (mysqli_num_rows($query) > 0) {
    echo '<div class="row">';
    while ($user = mysqli_fetch_assoc($query)) {
        $status_badge = $user['status'] == 1 ? '<span class="badge badge-success">Đang làm việc</span>' : '<span class="badge badge-danger">Đã khóa</span>';
        $role_text = ($user['role'] == 'admin') ? 'Quản trị viên' : (($user['role'] == 'staff') ? 'Nhân viên phục vụ' : 'Thủ kho');
        
        echo '
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-latte p-3 rounded-circle mr-3"><i class="fas fa-user-tie fa-2x text-coffee"></i></div>
                        <div>
                            <h5 class="card-title mb-0 font-weight-bold">'.$user['fullname'].'</h5>
                            <small class="text-muted">@'.$user['username'].'</small>
                        </div>
                    </div>
                    <p class="mb-1"><strong>Chức vụ:</strong> '.$role_text.'</p>
                    <p class="mb-3"><strong>Trạng thái:</strong> '.$status_badge.'</p>
                    <button class="btn btn-sm btn-warning btn-block btn-edit-user" 
                        data-id="'.$user['id'].'" 
                        data-fullname="'.$user['fullname'].'" 
                        data-username="'.$user['username'].'" 
                        data-role="'.$user['role'].'" 
                        data-status="'.$user['status'].'">
                        <i class="fas fa-user-edit"></i> Sửa tài khoản
                    </button>
                </div>
            </div>
        </div>';
    }
    echo '</div>';
}
?>