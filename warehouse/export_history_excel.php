<?php
// /warehouse/export_history_excel.php
session_start();

// Chặn truy cập: tối thiểu phải đăng nhập (và nên đúng role như history.php)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['wh-staff', 'admin'])) {
    http_response_code(401);
    exit('Unauthorized');
}

// Load PhpSpreadsheet (dựa theo cách admin đang dùng autoload trong admin/api/export_excel.php)
// admin/api/export_excel.php: require '../vendor/autoload.php'
// => vendor nằm trong /admin/vendor (scan đã ignore thư mục vendor)
require_once __DIR__ . '/../admin/vendor/autoload.php';

require_once __DIR__ . '/../includes/db_connection.php';
$mysqli = connect_db();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

header('Content-Type: text/html; charset=utf-8');

try {
    // Các tham số lọc: khớp get_history_api.php
    $filter_date_start = $_GET['date_start'] ?? '';
    $filter_date_end   = $_GET['date_end'] ?? '';
    $filter_ing        = $_GET['ingredient_id'] ?? '';
    $filter_type       = $_GET['type'] ?? '';
    $filter_user       = $_GET['user_id'] ?? '';

    // Query giống warehouse/get_history_api.php (JOIN + WHERE động)
    // get_history_api.php SELECT: l.id,l.type,l.quantity,l.cost,l.note,l.created_at, i.name,i.unit, u.fullname ...
    $sql = "SELECT 
                l.id, l.type, l.quantity, l.cost, l.note, l.created_at,
                i.name as ingredient_name, i.unit,
                u.fullname as user_name
            FROM inventory_log l
            JOIN ingredients i ON l.ingredient_id = i.id
            LEFT JOIN users u ON l.user_id = u.id
            WHERE 1=1";

    $params = [];
    $types  = "";

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
    if (!empty($filter_ing)) {
        $sql .= " AND l.ingredient_id = ?";
        $params[] = (int)$filter_ing;
        $types .= "i";
    }
    if (!empty($filter_type)) {
        $sql .= " AND l.type = ?";
        $params[] = $filter_type;
        $types .= "s";
    }
    if (!empty($filter_user)) {
        $sql .= " AND l.user_id = ?";
        $params[] = (int)$filter_user;
        $types .= "i";
    }

    $sql .= " ORDER BY l.created_at DESC";

    // Thực thi
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) throw new Exception("Prepare failed: " . $mysqli->error);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Tạo Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Warehouse History');

    // Tiêu đề báo cáo
    $title = "BÁO CÁO LỊCH SỬ NHẬP/XUẤT KHO";
    $sheet->setCellValue('A1', $title);
    $sheet->mergeCells('A1:J1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // ---- Bộ lọc: hiển thị TÊN thay vì chỉ ID ----
function db_scalar(mysqli $mysqli, string $sql, string $types = "", array $params = []) {
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) return null;

    if ($types !== "" && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_row() : null;
    $stmt->close();
    return $row ? $row[0] : null;
}

$userName = null;
$ingredientName = null;

if (!empty($filter_user)) {
    $userName = db_scalar($mysqli, "SELECT fullname FROM users WHERE id = ?", "i", [(int)$filter_user]);
}
if (!empty($filter_ing)) {
    $ingredientName = db_scalar($mysqli, "SELECT name FROM ingredients WHERE id = ?", "i", [(int)$filter_ing]);
}

$typeLabel = "";
if (!empty($filter_type)) {
    if ($filter_type === "import") $typeLabel = "Nhập kho";
    else if ($filter_type === "export") $typeLabel = "Xuất kho";
    else $typeLabel = $filter_type;
}

$filterParts = [];
if (!empty($filter_date_start)) $filterParts[] = "Từ: " . $filter_date_start;
if (!empty($filter_date_end))   $filterParts[] = "Đến: " . $filter_date_end;

if (!empty($filter_type)) {
    $filterParts[] = "Loại: " . ($typeLabel ?: $filter_type);
}

if (!empty($filter_ing)) {
    $filterParts[] = $ingredientName
        ? "Nguyên liệu: {$ingredientName} (ID " . (int)$filter_ing . ")"
        : "Nguyên liệu ID: " . (int)$filter_ing;
}

if (!empty($filter_user)) {
    $filterParts[] = $userName
        ? "Người thực hiện: {$userName} (ID " . (int)$filter_user . ")"
        : "Người thực hiện ID: " . (int)$filter_user;
}

$filterText = "Bộ lọc: " . (count($filterParts) ? implode(" | ", $filterParts) : "Không có");
$sheet->setCellValue('A2', $filterText);
$sheet->mergeCells('A2:J2');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

    $sheet->mergeCells('A2:J2');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

    // Header cột
    $headerRow = 4;
    $headers = ['ID', 'Ngày', 'Giờ', 'Loại', 'Nguyên liệu', 'Số lượng', 'Đơn vị', 'Chi phí', 'Ghi chú', 'Người thực hiện'];
    $col = 'A';
    foreach ($headers as $h) {
        $sheet->setCellValue($col.$headerRow, $h);
        $col++;
    }

    // Style header
    $sheet->getStyle("A{$headerRow}:J{$headerRow}")->getFont()->setBold(true);
    $sheet->getStyle("A{$headerRow}:J{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Border style
    $borderStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN
            ]
        ]
    ];

    // Fill data
    $rowIndex = $headerRow + 1;
    while ($row = $result->fetch_assoc()) {
        $date = date('d/m/Y', strtotime($row['created_at']));
        $time = date('H:i', strtotime($row['created_at']));

        $sheet->setCellValue("A{$rowIndex}", $row['id']);
        $sheet->setCellValue("B{$rowIndex}", $date);
        $sheet->setCellValue("C{$rowIndex}", $time);
        $sheet->setCellValue("D{$rowIndex}", $row['type']);
        $sheet->setCellValue("E{$rowIndex}", $row['ingredient_name']);
        $sheet->setCellValue("F{$rowIndex}", (float)$row['quantity']);
        $sheet->setCellValue("G{$rowIndex}", $row['unit']);
        $sheet->setCellValue("H{$rowIndex}", ($row['cost'] !== null && $row['cost'] !== '') ? (float)$row['cost'] : '');
        $sheet->setCellValue("I{$rowIndex}", $row['note']);
        $sheet->setCellValue("J{$rowIndex}", $row['user_name']);

        $rowIndex++;
    }

    // Apply border + format numeric columns
    $lastRow = $rowIndex - 1;
    if ($lastRow >= $headerRow) {
        $sheet->getStyle("A{$headerRow}:J{$lastRow}")->applyFromArray($borderStyle);
        $sheet->getStyle("F{$headerRow}:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.0');
        $sheet->getStyle("H{$headerRow}:H{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
    }

    // Auto size
    foreach (range('A', 'J') as $c) {
        $sheet->getColumnDimension($c)->setAutoSize(true);
    }

    // Output file
    $filename = 'warehouse_history_' . date('Ymd_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo "Export failed: " . $e->getMessage();
}
