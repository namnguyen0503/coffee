<?php
require_once __DIR__ . '/../../includes/db_connection.php';
require_once '../vendor/autoload.php'; // Load thư viện Excel

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$conn = connect_db();
$action = $_GET['action'] ?? 'view';
$start_date = $_GET['start'] ?? date('Y-m-01');
$end_date = $_GET['end'] ?? date('Y-m-d');

// SQL lấy thống kê
$sql = "SELECT DATE(order_date) as date, COUNT(id) as total_orders, SUM(total_money) as revenue 
        FROM orders 
        WHERE status = 'completed' AND order_date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'
        GROUP BY DATE(order_date)";
$result = $conn->query($sql);
$data = [];
$total_revenue = 0;
while($row = $result->fetch_assoc()) {
    $data[] = $row;
    $total_revenue += $row['revenue'];
}

if ($action == 'export') {
    // Xuất Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Báo Cáo Doanh Thu');
    $sheet->setCellValue('A2', "Từ $start_date đến $end_date");
    
    $sheet->setCellValue('A4', 'Ngày');
    $sheet->setCellValue('B4', 'Số đơn');
    $sheet->setCellValue('C4', 'Doanh thu');

    $rowNum = 5;
    foreach ($data as $row) {
        $sheet->setCellValue('A' . $rowNum, $row['date']);
        $sheet->setCellValue('B' . $rowNum, $row['total_orders']);
        $sheet->setCellValue('C' . $rowNum, $row['revenue']);
        $rowNum++;
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="baocao.xlsx"');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// Trả về JSON cho biểu đồ
echo json_encode([
    'success' => true, 
    'data' => $data, 
    'total_revenue' => $total_revenue
]);
?>