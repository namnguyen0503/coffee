<?php
require '../tinh-nang/db_connection.php';
$conn = connect_db();

// 1. Nhận từ khóa tìm kiếm
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// 2. Viết câu SQL có điều kiện tìm kiếm
$sql = "SELECT id, fullname, username, role, status FROM users";
if (!empty($search)) {
    $sql .= " WHERE fullname LIKE '%$search%' OR username LIKE '%$search%'";
}
$sql .= " ORDER BY id DESC";

$query = mysqli_query($conn, $sql);

// 3. Nút thêm nhân viên sẽ được xử lý ở main.php, file này chỉ trả về danh sách thẻ (Card)
if (mysqli_num_rows($query) > 0) {
    echo '<div class="row">';
    while ($user = mysqli_fetch_assoc($query)) {
        $status_badge = $user['status'] == 1 ? '<span class="badge badge-success">Đang làm việc</span>' : '<span class="badge badge-danger">Đã khóa</span>';
        
        $role_text = '';
        if ($user['role'] == 'admin') $role_text = 'Quản trị viên';
        elseif ($user['role'] == 'staff') $role_text = 'Nhân viên phục vụ';
        else $role_text = 'Thủ kho';
        
        echo '
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light p-3 rounded-circle mr-3"><i class="fas fa-user-tie fa-2x text-info"></i></div>
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
                        <i class="fas fa-key"></i> Sửa / Đổi mật khẩu
                    </button>
                </div>
            </div>
        </div>';
    }
    echo '</div>';
} else {
    echo '<div class="alert alert-warning text-center">Không tìm thấy nhân viên nào phù hợp.</div>';
}
?>