<?php
session_start();
require_once '../includes/db_connection.php';
header('Content-Type: application/json; charset=utf-8');

// 1. Chặn truy cập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // 2. Nhận tham số từ Client
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 20; // Load 20 dòng mỗi lần
    $offset = ($page - 1) * $limit;

    // Các tham số lọc
    $filter_date_start = $_GET['date_start'] ?? '';
    $filter_date_end = $_GET['date_end'] ?? '';
    $filter_ing = $_GET['ingredient_id'] ?? '';
    $filter_type = $_GET['type'] ?? '';
    $filter_user = $_GET['user_id'] ?? '';

    // 3. Xây dựng câu Query động (Dynamic SQL)
    $sql = "SELECT 
                l.id, l.type, l.quantity, l.cost, l.note, l.created_at,
                i.name as ingredient_name, i.unit,
                u.fullname as user_name
            FROM inventory_log l
            JOIN ingredients i ON l.ingredient_id = i.id
            LEFT JOIN users u ON l.user_id = u.id
            WHERE 1=1";

    $params = [];
    $types = "";

    // Lọc theo ngày
    if (!empty($filter_date_start)) {
        $sql .= " AND DATE(l.created_at) >= ?";
        $params[] = $filter_date_start;
        $types .= "s";
    }
    if (!empty($filter_date_end)) {
        $sql .= " AND DATE(l.created_at) <= ?";
        $params[] = $filter_date_end;
        $types .= "s";
    }

    // Lọc theo Nguyên liệu
    if (!empty($filter_ing)) {
        $sql .= " AND l.ingredient_id = ?";
        $params[] = $filter_ing;
        $types .= "i";
    }

    // Lọc theo Loại (import/export)
    if (!empty($filter_type)) {
        $sql .= " AND l.type = ?";
        $params[] = $filter_type;
        $types .= "s";
    }

    // Lọc theo Người thực hiện
    if (!empty($filter_user)) {
        $sql .= " AND l.user_id = ?";
        $params[] = $filter_user;
        $types .= "i";
    }

    // Sắp xếp và Phân trang
    $sql .= " ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    // 4. Thực thi Query
    $stmt = $mysqli->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        // Format sẵn dữ liệu để JS chỉ việc hiện
        $row['formatted_date'] = date('d/m/Y', strtotime($row['created_at']));
        $row['formatted_time'] = date('H:i', strtotime($row['created_at']));
        $row['cost_display'] = ($row['cost'] > 0) ? number_format($row['cost']) . ' đ' : '-';
        $row['qty_display'] = number_format($row['quantity'], 1);
        $data[] = $row;
    }

    // 5. Kiểm tra xem còn trang sau không (để ẩn hiện nút Load More)
    $has_more = count($data) >= $limit;

    echo json_encode([
        'success' => true,
        'data' => $data,
        'has_more' => $has_more,
        'page' => $page
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>