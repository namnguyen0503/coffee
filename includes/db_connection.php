<?php
    require_once 'config.php';
    global $mysqli;
    function connect_db(){
        global $mysqli;
        $mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
        if ($mysqli->connect_errno) {
            die("Kết nối cơ sở dữ liệu thất bại: " . $mysqli->connect_error);
        }
        $mysqli->set_charset("utf8mb4");
        return $mysqli;
    };
    connect_db();
    // Hàm kiểm tra xem User có đang trong ca làm việc không
function getActiveSessionId($conn, $user_id) {
    $stmt = $conn->prepare("SELECT id FROM work_sessions WHERE user_id = ? AND status = 'open' LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['id']; // Trả về ID phiên làm việc
    }
    return false; // Không có ca nào mở
}
?>